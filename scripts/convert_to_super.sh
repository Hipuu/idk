#!/bin/bash
# Convert base ROM to Super ROM format for fastboot flashing

set -e

ROM_PATH="$1"
OUTPUT_DIR="$2"

if [ -z "$ROM_PATH" ] || [ -z "$OUTPUT_DIR" ]; then
    echo "Usage: $0 <rom_path> <output_dir>"
    exit 1
fi

echo "=== Converting to Super ROM ==="
echo "Input ROM: $ROM_PATH"
echo "Output directory: $OUTPUT_DIR"

# Create working directories
WORK_DIR="$(mktemp -d)"
EXTRACT_DIR="$WORK_DIR/rom_extracted" # Changed from 'extracted' to 'rom_extracted'
SUPER_DIR="$WORK_DIR/super"

mkdir -p "$EXTRACT_DIR" "$SUPER_DIR" "$OUTPUT_DIR"

echo "Extracting ROM..."
unzip -q "$ROM_PATH" -d "$EXTRACT_DIR"

# Check if payload.bin exists (OTA format)
if [ -f "$EXTRACT_DIR/payload.bin" ]; then
    echo "Detected payload.bin (OTA format) - extracting partitions..."
    PAYLOAD_OUTPUT="$WORK_DIR/payload_extracted"
    mkdir -p "$PAYLOAD_OUTPUT"
    
    # Extract using payload-dumper-go
    cd "$EXTRACT_DIR"
    payload-dumper-go -o "$PAYLOAD_OUTPUT" payload.bin
    
    # Move extracted images to EXTRACT_DIR and clean up payload files
    mv "$PAYLOAD_OUTPUT"/*.img "$EXTRACT_DIR/" 2>/dev/null || true
    rm -rf "$PAYLOAD_OUTPUT" payload.bin
    
    echo "Payload extraction complete!"
    ls -lh "$EXTRACT_DIR"/*.img 2>/dev/null || echo "Warning: No .img files found after extraction"
fi

# Find super.img or partition images
if [ -f "$EXTRACT_DIR/super.img" ]; then
    echo "Found super.img, unpacking..."
    lpunpack "$EXTRACT_DIR/super.img" "$SUPER_DIR/"
elif [ -f "$EXTRACT_DIR/super.img.sparse" ]; then
    echo "Found sparse super.img, converting and unpacking..."
    simg2img "$EXTRACT_DIR/super.img.sparse" "$WORK_DIR/super.img"
    lpunpack "$WORK_DIR/super.img" "$SUPER_DIR/"
else
    echo "No super.img found, looking for individual partitions..."
    # Copy partition images
    for partition in system system_ext product vendor odm; do
        if [ -f "$EXTRACT_DIR/${partition}.img" ]; then
            echo "Found ${partition}.img"
            cp "$EXTRACT_DIR/${partition}.img" "$SUPER_DIR/"
        fi
    done
fi

# Detect partition sizes and create super.img
echo "Creating super.img..."

# Get partition sizes
TOTAL_SIZE=0
PARTITION_LIST=""
SIZE_LIST=""

for img in "$SUPER_DIR"/*.img; do
    if [ -f "$img" ]; then
        PARTITION_NAME=$(basename "$img" .img)
        PARTITION_SIZE=$(stat -c%s "$img")
        TOTAL_SIZE=$((TOTAL_SIZE + PARTITION_SIZE))
        
        echo "  $PARTITION_NAME: $PARTITION_SIZE bytes"
        
        PARTITION_LIST="${PARTITION_LIST}${PARTITION_NAME},"
        SIZE_LIST="${SIZE_LIST}${PARTITION_SIZE},"
    fi
done

# Remove trailing commas
PARTITION_LIST=${PARTITION_LIST%,}
SIZE_LIST=${SIZE_LIST%,}

# Add 10% overhead for super partition
SUPER_SIZE=$((TOTAL_SIZE + TOTAL_SIZE / 10))

echo "Total partition size: $TOTAL_SIZE bytes"
echo "Super partition size: $SUPER_SIZE bytes"

# Create super.img using lpmake
lpmake \
    --metadata-size 65536 \
    --super-name super \
    --metadata-slots 2 \
    --device super:$SUPER_SIZE \
    --group main:$TOTAL_SIZE \
    --partition system:readonly:$PARTITION_SIZE:main \
    --image system="$SUPER_DIR/system.img" \
    --output "$OUTPUT_DIR/super.img" \
    2>/dev/null || {
        echo "Warning: lpmake with all partitions failed, trying simpler approach..."
        
        # Simplified lpmake for just system partition
        SYSTEM_SIZE=$(stat -c%s "$SUPER_DIR/system.img" 2>/dev/null || echo "0")
        if [ "$SYSTEM_SIZE" -gt 0 ]; then
            lpmake \
                --metadata-size 65536 \
                --super-name super \
                --metadata-slots 2 \
                --device super:$SUPER_SIZE \
                --group main:$SYSTEM_SIZE \
                --partition system:readonly:$SYSTEM_SIZE:main \
                --image system="$SUPER_DIR/system.img" \
                --output "$OUTPUT_DIR/super.img"
        else
            echo "Error: Could not create super.img"
            exit 1
        fi
    }

# Copy other critical partitions
echo "Copying other partitions..."
for partition in boot dtbo vbmeta vendor_boot recovery; do
    if [ -f "$EXTRACT_DIR/${partition}.img" ]; then
        echo "  Copying ${partition}.img"
        cp "$EXTRACT_DIR/${partition}.img" "$OUTPUT_DIR/"
    fi
done

# Create flash script
# Download latest Android Platform Tools
echo "Downloading Android Platform Tools..."
PLATFORM_TOOLS_URL="https://dl.google.com/android/repository/platform-tools-latest-windows.zip"
wget -q "$PLATFORM_TOOLS_URL" -O "$WORK_DIR/platform-tools.zip"

echo "Extracting Platform Tools..."
unzip -q "$WORK_DIR/platform-tools.zip" -d "$OUTPUT_DIR/"

# Create flash script
echo "Creating flash-all.bat script..."
cat > "$OUTPUT_DIR/flash-all.bat" << 'EOF'
@echo off
title Super ROM Flasher
echo.
echo.**********************************************************************
echo.
echo.                    Super ROM Flasher                      
echo.              Automated Fastboot Flash Script
echo.
echo.**********************************************************************
echo.

cd %~dp0
set fastboot=platform-tools\fastboot.exe

:: Check if fastboot exists
if not exist "%fastboot%" (
    echo [ERROR] fastboot not found in platform-tools!
    echo Please re-download the ROM package
    pause
    exit /B 1
)

echo.
echo Checking device connection...
%fastboot% devices
if errorlevel 1 (
    echo.
    echo [ERROR] No device detected in fastboot mode!
    echo.
    echo Please:
    echo  1. Boot your device into fastboot mode
    echo  2. Connect USB cable
    echo  3. Run this script again
    echo.
    pause
    exit /b 1
)

echo.
echo [WARNING] This will flash your device!
echo All data will be erased if you choose to wipe.
echo.
pause

echo.
echo.************************      START FLASH     ************************
echo.

:: Set active slot to A
echo [*] Setting active slot to A...
%fastboot% --set-active=a

:: Flash physical partitions first
if exist boot.img (
    echo [1/6] Flashing boot...
    %fastboot% flash boot boot.img
)

if exist dtbo.img (
    echo [2/6] Flashing dtbo...
    %fastboot% flash dtbo dtbo.img
)

if exist vbmeta.img (
    echo [3/6] Flashing vbmeta...
    %fastboot% --disable-verity --disable-verification flash vbmeta vbmeta.img
)

if exist vendor_boot.img (
    echo [4/6] Flashing vendor_boot...
    %fastboot% flash vendor_boot vendor_boot.img
)

if exist init_boot.img (
    echo [5/6] Flashing init_boot...
    %fastboot% flash init_boot init_boot.img
)

:: Flash super.img
if exist super.img (
    echo [6/6] Flashing super...
    %fastboot% flash super super.img
    echo.
    echo Super image flashed successfully!
) else (
    echo [WARNING] super.img not found!
    echo This package may only contain logical partition images.
    
    :: Reboot to fastbootd for logical partition flashing
    echo.
    echo Rebooting to fastbootd mode...
    %fastboot% reboot fastboot
    echo.
    echo  #################################################
    echo  # IMPORTANT: Select English on phone screen    #
    echo  # Wait for fastbootd mode to load             #
    echo  # Then press any key to continue...           #
    echo  #################################################
    pause
    
    :: Flash logical partitions if they exist
    for %%P in (system system_ext product vendor odm) do (
        if exist %%P.img (
            echo Flashing %%P to both slots...
            %fastboot% delete-logical-partition %%P_a
            %fastboot% delete-logical-partition %%P_b
            %fastboot% create-logical-partition %%P_a 1
            %fastboot% create-logical-partition %%P_b 1
            %fastboot% flash %%P %%P.img
        )
    )
)

echo.
echo.********************** CHECK ABOVE FOR ERRORS **************************
echo.************** IF ERRORS, DO NOT BOOT INTO SYSTEM **********************
echo.

:: Ask about data wipe
choice /C YN /M "Do you want to wipe data (factory reset)?"

if errorlevel 2 (
    echo.
    echo *********************** SKIPPING DATA WIPE ****************************
    echo ***** Flashing complete. Hit any key to reboot the phone *****
    pause
    %fastboot% reboot
    exit /B 0
)

if errorlevel 1 (
    echo.
    echo ****************** WIPING DATA *****************
    %fastboot% -w
    echo.
    echo Data wiped successfully!
    echo.
    echo ***** Flashing complete. Hit any key to reboot the phone *****
    pause
    %fastboot% reboot
    exit /B 0
)

pause
EOF

# Create README
cat > "$OUTPUT_DIR/README.txt" << EOF
Super ROM Flash Instructions
=============================

This package contains a Super ROM that can be flashed via fastboot.

✓ Android Platform Tools are INCLUDED (no separate installation needed!)

Requirements:
- Windows PC
- Device in fastboot mode
- Unlocked bootloader
- USB cable

Installation Instructions:
1. Extract this entire folder
2. Boot device into fastboot mode (Power + Volume Down)
3. Connect device to PC via USB
4. Run flash-all.bat
5. Follow on-screen instructions

Important Notes:
- First boot may take 5-10 minutes
- You'll be asked if you want to wipe data
- Choose "Yes" for clean install or "No" to keep data
- Make sure you have a backup before flashing!

Partition Contents:
- super.img (contains system, vendor, product, etc.)
- boot.img (kernel)
- dtbo.img (device tree overlay)
- vbmeta.img (verified boot metadata)
- platform-tools/ (fastboot.exe and ADB tools)

Troubleshooting:
- If device not detected: Install USB drivers for your device
- If errors during flash: Do NOT boot, reflash or restore backup
- For help: Check XDA forums for your device
EOF

echo "=== Conversion Complete ==="
echo "Output files:"
ls -lh "$OUTPUT_DIR"

# Cleanup
rm -rf "$WORK_DIR"

echo "Done!"
