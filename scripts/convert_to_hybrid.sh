#!/bin/bash
# Convert base ROM to Hybrid ROM format for TWRP flashing with dual A/B slot support

set -e

ROM_PATH="$1"
OUTPUT_DIR="$2"

if [ -z "$ROM_PATH" ] || [ -z "$OUTPUT_DIR" ]; then
    echo "Usage: $0 <rom_path> <output_dir>"
    exit 1
fi

echo "=== Converting to Hybrid ROM ==="
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

# Create updater-script with dual A/B slot support
echo "Creating updater-script..."
cat > "$ZIP_DIR/META-INF/com/google/android/updater-script" << 'EOF'
ui_print("========================================");
ui_print("  Hybrid ROM Installer (A/B Slots)     ");
ui_print("========================================");
ui_print(" ");

# Get current slot
set_progress(0.1);
ui_print("Detecting current slot...");
run_program("/system/bin/sh", "-c", "boot_slot=$(getprop ro.boot.slot_suffix); echo $boot_slot > /tmp/current_slot");

ui_print("Mounting partitions...");
run_program("/system/bin/mount", "-a");

set_progress(0.2);

# Flash boot partition to both slots
ui_print("Flashing boot partition...");
if file_getprop("/tmp/aroma.prop", "install.slot") == "both" || file_getprop("/tmp/aroma.prop", "install.slot") == "" then
    package_extract_file("boot.img", "/dev/block/bootdevice/by-name/boot_a");
    package_extract_file("boot.img", "/dev/block/bootdevice/by-name/boot_b");
    ui_print("  ✓ Flashed to both slot A and B");
else
    ui_print("  Flashing to current slot only");
    package_extract_file("boot.img", "/dev/block/bootdevice/by-name/boot" + getprop("ro.boot.slot_suffix"));
endif;

set_progress(0.3);

# Flash dtbo partition to both slots
if file_exists(package_extract_file("dtbo.img")) then
    ui_print("Flashing dtbo partition...");
    package_extract_file("dtbo.img", "/dev/block/bootdevice/by-name/dtbo_a");
    package_extract_file("dtbo.img", "/dev/block/bootdevice/by-name/dtbo_b");
    ui_print("  ✓ Flashed to both slot A and B");
endif;

set_progress(0.4);

# Flash vbmeta partition to both slots
if file_exists(package_extract_file("vbmeta.img")) then
    ui_print("Flashing vbmeta partition...");
    package_extract_file("vbmeta.img", "/dev/block/bootdevice/by-name/vbmeta_a");
    package_extract_file("vbmeta.img", "/dev/block/bootdevice/by-name/vbmeta_b");
    ui_print("  ✓ Flashed to both slot A and B");
endif;

set_progress(0.5);

# Flash vendor_boot partition to both slots (if exists)
if file_exists(package_extract_file("vendor_boot.img")) then
    ui_print("Flashing vendor_boot partition...");
    package_extract_file("vendor_boot.img", "/dev/block/bootdevice/by-name/vendor_boot_a");
    package_extract_file("vendor_boot.img", "/dev/block/bootdevice/by-name/vendor_boot_b");
    ui_print("  ✓ Flashed to both slot A and B");
endif;

set_progress(0.6);

# Flash system partition to both slots
if file_exists(package_extract_file("system.img")) then
    ui_print("Flashing system partition...");
    ui_print("  This may take a while...");
    package_extract_file("system.img", "/dev/block/bootdevice/by-name/system_a");
    package_extract_file("system.img", "/dev/block/bootdevice/by-name/system_b");
    ui_print("  ✓ Flashed to both slot A and B");
endif;

set_progress(0.75);

# Flash vendor partition to both slots
if file_exists(package_extract_file("vendor.img")) then
    ui_print("Flashing vendor partition...");
    package_extract_file("vendor.img", "/dev/block/bootdevice/by-name/vendor_a");
    package_extract_file("vendor.img", "/dev/block/bootdevice/by-name/vendor_b");
    ui_print("  ✓ Flashed to both slot A and B");
endif;

set_progress(0.85);

# Flash product partition to both slots (if exists)
if file_exists(package_extract_file("product.img")) then
    ui_print("Flashing product partition...");
    package_extract_file("product.img", "/dev/block/bootdevice/by-name/product_a");
    package_extract_file("product.img", "/dev/block/bootdevice/by-name/product_b");
    ui_print("  ✓ Flashed to both slot A and B");
endif;

# Flash system_ext partition to both slots (if exists)
if file_exists(package_extract_file("system_ext.img")) then
    ui_print("Flashing system_ext partition...");
    package_extract_file("system_ext.img", "/dev/block/bootdevice/by-name/system_ext_a");
    package_extract_file("system_ext.img", "/dev/block/bootdevice/by-name/system_ext_b");
    ui_print("  ✓ Flashed to both slot A and B");
endif;

# Flash odm partition to both slots (if exists)
if file_exists(package_extract_file("odm.img")) then
    ui_print("Flashing odm partition...");
    package_extract_file("odm.img", "/dev/block/bootdevice/by-name/odm_a");
    package_extract_file("odm.img", "/dev/block/bootdevice/by-name/odm_b");
    ui_print("  ✓ Flashed to both slot A and B");
endif;

set_progress(0.95);

ui_print(" ");
ui_print("Setting active slot...");
# Ensure current slot is active
run_program("/system/bin/sh", "-c", "setprop ro.boot.slot $(getprop ro.boot.slot_suffix | sed 's/_//')");

ui_print(" ");
ui_print("Unmounting partitions...");
unmount("/system");
unmount("/vendor");

set_progress(1.0);

ui_print(" ");
ui_print("========================================");
ui_print("  Installation Complete!                ");
ui_print("  Both A and B slots have been flashed  ");
ui_print("========================================");
ui_print(" ");
ui_print("Please reboot your device.");
EOF

# Create update-binary (edify interpreter)
echo "Creating update-binary..."
# We'll use a simple shell script wrapper for TWRP
cat > "$ZIP_DIR/META-INF/com/google/android/update-binary" << 'EOF'
#!/sbin/sh
# TWRP A/B Installer Script

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
ui_print "  Hybrid ROM Installer (A/B Slots)     ";
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
ui_print "Current slot: $CURRENT_SLOT"

# Flash boot to both slots
if [ -f boot.img ]; then
    ui_print "Flashing boot partition..."
    dd if=boot.img of=/dev/block/bootdevice/by-name/boot_a
    dd if=boot.img of=/dev/block/bootdevice/by-name/boot_b
    ui_print "  ✓ Flashed to both slot A and B"
fi

set_progress 0.3

# Flash dtbo to both slots
if [ -f dtbo.img ]; then
    ui_print "Flashing dtbo partition..."
    dd if=dtbo.img of=/dev/block/bootdevice/by-name/dtbo_a
    dd if=dtbo.img of=/dev/block/bootdevice/by-name/dtbo_b
    ui_print "  ✓ Flashed to both slot A and B"
fi

set_progress 0.4

# Flash vbmeta to both slots (disable verification)
if [ -f vbmeta.img ]; then
    ui_print "Flashing vbmeta partition..."
    dd if=vbmeta.img of=/dev/block/bootdevice/by-name/vbmeta_a
    dd if=vbmeta.img of=/dev/block/bootdevice/by-name/vbmeta_b
    ui_print "  ✓ Flashed to both slot A and B"
fi

set_progress 0.5

# Flash vendor_boot to both slots
if [ -f vendor_boot.img ]; then
    ui_print "Flashing vendor_boot partition..."
    dd if=vendor_boot.img of=/dev/block/bootdevice/by-name/vendor_boot_a
    dd if=vendor_boot.img of=/dev/block/bootdevice/by-name/vendor_boot_b
    ui_print "  ✓ Flashed to both slot A and B"
fi

set_progress 0.6

# Flash system to both slots
if [ -f system.img ]; then
    ui_print "Flashing system partition..."
    ui_print "  This may take a while..."
    dd if=system.img of=/dev/block/bootdevice/by-name/system_a bs=1M
    dd if=system.img of=/dev/block/bootdevice/by-name/system_b bs=1M
    ui_print "  ✓ Flashed to both slot A and B"
fi

set_progress 0.75

# Flash vendor to both slots
if [ -f vendor.img ]; then
    ui_print "Flashing vendor partition..."
    dd if=vendor.img of=/dev/block/bootdevice/by-name/vendor_a bs=1M
    dd if=vendor.img of=/dev/block/bootdevice/by-name/vendor_b bs=1M
    ui_print "  ✓ Flashed to both slot A and B"
fi

set_progress 0.85

# Flash product to both slots
if [ -f product.img ]; then
    ui_print "Flashing product partition..."
    dd if=product.img of=/dev/block/bootdevice/by-name/product_a bs=1M
    dd if=product.img of=/dev/block/bootdevice/by-name/product_b bs=1M
    ui_print "  ✓ Flashed to both slot A and B"
fi

# Flash system_ext to both slots
if [ -f system_ext.img ]; then
    ui_print "Flashing system_ext partition..."
    dd if=system_ext.img of=/dev/block/bootdevice/by-name/system_ext_a bs=1M
    dd if=system_ext.img of=/dev/block/bootdevice/by-name/system_ext_b bs=1M
    ui_print "  ✓ Flashed to both slot A and B"
fi

# Flash odm to both slots
if [ -f odm.img ]; then
    ui_print "Flashing odm partition..."
    dd if=odm.img of=/dev/block/bootdevice/by-name/odm_a bs=1M
    dd if=odm.img of=/dev/block/bootdevice/by-name/odm_b bs=1M
    ui_print "  ✓ Flashed to both slot A and B"
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
ui_print "  Both A and B slots have been flashed  ";
ui_print "========================================";
ui_print " "
ui_print "Please reboot your device.";

exit 0
EOF

chmod +x "$ZIP_DIR/META-INF/com/google/android/update-binary"

# Create README
cat > "$ZIP_DIR/README.txt" << EOF
Hybrid ROM Flash Instructions (TWRP A/B)
========================================

This ROM is designed to be flashed via TWRP recovery and will
automatically flash to BOTH slot A and slot B.

Requirements:
- TWRP Recovery
- Unlocked bootloader
- A/B device with dual slots

Installation:
1. Boot into TWRP recovery
2. (Optional) Wipe System, Data, Cache, Dalvik
3. Install this ZIP file
4. Reboot to system

Important Notes:
- This ROM will flash to BOTH slot A and B
- Both slots will have identical ROM installation
- You can switch between slots if one fails
- Make a backup before flashing!

After Installation:
- First boot may take 5-10 minutes
- Clear data if coming from a different ROM
- Enjoy your new ROM!
EOF

# Package into flashable ZIP
echo "Creating flashable ZIP..."
FINAL_ZIP="$OUTPUT_DIR/hybrid_rom.zip"
cd "$ZIP_DIR"
zip -r "$FINAL_ZIP" . -q

echo "=== Conversion Complete ==="
echo "Output file: $FINAL_ZIP"
ls -lh "$FINAL_ZIP"

# Cleanup
rm -rf "$WORK_DIR"

echo "Done!"
