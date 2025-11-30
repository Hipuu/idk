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

# Convert output directory to absolute path
OUTPUT_DIR=$(cd "$(dirname "$OUTPUT_DIR")" && pwd)/$(basename "$OUTPUT_DIR")
mkdir -p "$OUTPUT_DIR"
echo "Absolute output path: $OUTPUT_DIR"

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
# Copy ALL extracted partition images (not just specific ones)
for img_file in "$EXTRACT_DIR"/*.img; do
    if [ -f "$img_file" ]; then
        PARTITION_NAME=$(basename "$img_file")
        echo "  Found $PARTITION_NAME"
        cp "$img_file" "$ZIP_DIR/"
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

set_progress 0.1

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

# Count total images for progress calculation
TOTAL_IMAGES=$(ls -1 *.img 2>/dev/null | wc -l)
CURRENT=0

# Flash all partition images dynamically
for img_file in *.img; do
    if [ -f "$img_file" ]; then
        PARTITION=$(basename "$img_file" .img)
        
        # Calculate progress
        CURRENT=$((CURRENT + 1))
        PROGRESS=$(awk "BEGIN {printf \"%.2f\", 0.1 + (0.85 * $CURRENT / $TOTAL_IMAGES)}")
        set_progress $PROGRESS
        
        # Determine block device path
        BLOCK_DEVICE="/dev/block/bootdevice/by-name/${PARTITION}${SLOT_SUFFIX}"
        
        # Check if partition exists
        if [ -e "$BLOCK_DEVICE" ] || [ -e "/dev/block/bootdevice/by-name/${PARTITION}" ]; then
            ui_print "Flashing $PARTITION..."
            dd if="$img_file" of="$BLOCK_DEVICE" bs=1M 2>/dev/null || \
            dd if="$img_file" of="/dev/block/bootdevice/by-name/${PARTITION}" bs=1M 2>/dev/null
            ui_print "  ✓ $PARTITION flashed"
        else
            ui_print "  ⊘ Skipping $PARTITION (partition not found)"
        fi
    fi
done

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
zip -0 -r "$FINAL_ZIP" . -q  # -0 = store mode (no compression)

echo "=== Conversion Complete ==="
echo "Output file: $FINAL_ZIP"
ls -lh "$FINAL_ZIP"

# Cleanup
rm -rf "$WORK_DIR"

echo "Done!"
