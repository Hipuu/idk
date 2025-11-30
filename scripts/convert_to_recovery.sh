#!/bin/bash
# Convert base ROM to Recovery ROM format for TWRP flashing (current slot only)

set -e

ROM_PATH="$1"
OUTPUT_DIR="$2"

if [ -z "$ROM_PATH" ] || [ -z "$OUTPUT_DIR" ]; then
    echo "Usage: $0 <rom_path> <output_dir>"
    exit 1
fi

echo "=== Converting to Recovery ROM ==="
echo "Input ROM: $ROM_PATH"
echo "Output directory: $OUTPUT_DIR"

# Create working directories
WORK_DIR="$(mktemp -d)"
EXTRACT_DIR="$WORK_DIR/extracted"
ZIP_DIR="$WORK_DIR/flashable"

mkdir -p "$EXTRACT_DIR" "$ZIP_DIR" "$OUTPUT_DIR"

echo "Working directory: $WORK_DIR"

# Extract ROM
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

# Create flashable ZIP structure
echo "Creating flashable ZIP structure..."
mkdir -p "$ZIP_DIR/META-INF/com/google/android"

# Copy partition images
echo "Copying partition images..."
for partition in boot system vendor product system_ext odm dtbo vbmeta vendor_boot recovery; do
    if [ -f "$EXTRACT_DIR/${partition}.img" ]; then
        echo "  Found ${partition}.img"
        cp "$EXTRACT_DIR/${partition}.img" "$ZIP_DIR/"
    fi
done

# If super.img exists, unpack it
if [ -f "$EXTRACT_DIR/super.img" ]; then
    echo "Found super.img, unpacking partitions..."
    SUPER_UNPACK="$WORK_DIR/super_partitions"
    mkdir -p "$SUPER_UNPACK"
    
    lpunpack "$EXTRACT_DIR/super.img" "$SUPER_UNPACK/" || {
        echo "Trying with sparse conversion..."
        simg2img "$EXTRACT_DIR/super.img" "$WORK_DIR/super_raw.img"
        lpunpack "$WORK_DIR/super_raw.img" "$SUPER_UNPACK/"
    }
    
    # Copy unpacked partitions
    for img in "$SUPER_UNPACK"/*.img; do
        if [ -f "$img" ]; then
            PARTITION_NAME=$(basename "$img")
            echo "  Extracted $PARTITION_NAME from super"
            cp "$img" "$ZIP_DIR/"
        fi
    done
fi

# Create update-binary (edify interpreter)
echo "Creating update-binary..."
# Simple shell script for TWRP (current slot only)
cat > "$ZIP_DIR/META-INF/com/google/android/update-binary" << 'EOF'
#!/sbin/sh
# TWRP Recovery ROM Installer Script (Current Slot Only)

OUTFD=$2
ZIPFILE=$3

ui_print() {
    echo "ui_print $1" > /proc/self/fd/$OUTFD
    echo "ui_print" > /proc/self/fd/$OUTFD
}

set_progress() {
    echo "set_progress $1" > /proc/self/fd/$OUTFD
}

package_extract_file() {
    unzip -p "$ZIPFILE" "$1" > "$2"
}

ui_print "========================================";
ui_print "  Recovery ROM Installer (TWRP)        ";
ui_print "========================================";
ui_print " ";

TMPDIR=/tmp/rom_install
rm -rf $TMPDIR
mkdir -p $TMPDIR
cd $TMPDIR

# Extract all images
ui_print "Extracting ROM files...";
unzip -o "$ZIPFILE" "*.img" -d $TMPDIR 2>/dev/null

set_progress 0.2

# Detect current slot
CURRENT_SLOT=$(getprop ro.boot.slot_suffix)
if [ -z "$CURRENT_SLOT" ]; then
    # Non-A/B device
    ui_print "Non-A/B device detected"
    SLOT_SUFFIX=""
else
    ui_print "Current slot: $CURRENT_SLOT"
    SLOT_SUFFIX=$CURRENT_SLOT
fi

# Flash boot
if [ -f boot.img ]; then
    ui_print "Flashing boot partition..."
    dd if=boot.img of=/dev/block/bootdevice/by-name/boot${SLOT_SUFFIX} bs=1M
    ui_print "  ✓ boot flashed"
fi

set_progress 0.3

# Flash dtbo
if [ -f dtbo.img ]; then
    ui_print "Flashing dtbo partition..."
    dd if=dtbo.img of=/dev/block/bootdevice/by-name/dtbo${SLOT_SUFFIX} bs=1M
    ui_print "  ✓ dtbo flashed"
fi

set_progress 0.4

# Flash vbmeta (disable verification)
if [ -f vbmeta.img ]; then
    ui_print "Flashing vbmeta partition..."
    dd if=vbmeta.img of=/dev/block/bootdevice/by-name/vbmeta${SLOT_SUFFIX} bs=1M
    ui_print "  ✓ vbmeta flashed"
fi

set_progress 0.5

# Flash vendor_boot
if [ -f vendor_boot.img ]; then
    ui_print "Flashing vendor_boot partition..."
    dd if=vendor_boot.img of=/dev/block/bootdevice/by-name/vendor_boot${SLOT_SUFFIX} bs=1M
    ui_print "  ✓ vendor_boot flashed"
fi

set_progress 0.6

# Flash system
if [ -f system.img ]; then
    ui_print "Flashing system partition..."
    ui_print "  This may take a while..."
    dd if=system.img of=/dev/block/bootdevice/by-name/system${SLOT_SUFFIX} bs=1M
    ui_print "  ✓ system flashed"
fi

set_progress 0.75

# Flash vendor
if [ -f vendor.img ]; then
    ui_print "Flashing vendor partition..."
    dd if=vendor.img of=/dev/block/bootdevice/by-name/vendor${SLOT_SUFFIX} bs=1M
    ui_print "  ✓ vendor flashed"
fi

set_progress 0.85

# Flash product
if [ -f product.img ]; then
    ui_print "Flashing product partition..."
    dd if=product.img of=/dev/block/bootdevice/by-name/product${SLOT_SUFFIX} bs=1M
    ui_print "  ✓ product flashed"
fi

# Flash system_ext
if [ -f system_ext.img ]; then
    ui_print "Flashing system_ext partition..."
    dd if=system_ext.img of=/dev/block/bootdevice/by-name/system_ext${SLOT_SUFFIX} bs=1M
    ui_print "  ✓ system_ext flashed"
fi

# Flash odm
if [ -f odm.img ]; then
    ui_print "Flashing odm partition..."
    dd if=odm.img of=/dev/block/bootdevice/by-name/odm${SLOT_SUFFIX} bs=1M
    ui_print "  ✓ odm flashed"
fi

set_progress 0.95

ui_print " "
ui_print "Cleaning up..."
cd /
rm -rf $TMPDIR

set_progress 1.0

ui_print " "
ui_print "========================================";
ui_print "  Installation Complete!                ";
ui_print "  ROM flashed to current slot           ";
ui_print "========================================";
ui_print " "
ui_print "Please reboot your device.";

exit 0
EOF

chmod +x "$ZIP_DIR/META-INF/com/google/android/update-binary"

# Create README
cat > "$ZIP_DIR/README.txt" << EOF
Recovery ROM Flash Instructions (TWRP)
========================================

This ROM is designed to be flashed via TWRP recovery to your current slot.

Requirements:
- TWRP Recovery
- Unlocked bootloader

Installation:
1. Boot into TWRP recovery
2. (Optional) Wipe System, Data, Cache, Dalvik
3. Install this ZIP file
4. Reboot to system

Important Notes:
- This ROM will flash to your CURRENT slot only
- Works on both A/B and non-A/B devices
- Make a backup before flashing!

After Installation:
- First boot may take 5-10 minutes
- Clear data if coming from a different ROM
- Enjoy your new ROM!
EOF

# Package into flashable ZIP
echo "Creating flashable ZIP..."
FINAL_ZIP="$OUTPUT_DIR/recovery_rom.zip"
cd "$ZIP_DIR"
zip -r "$FINAL_ZIP" . -q

echo "=== Conversion Complete ==="
echo "Output file: $FINAL_ZIP"
ls -lh "$FINAL_ZIP"

# Cleanup
rm -rf "$WORK_DIR"

echo "Done!"
