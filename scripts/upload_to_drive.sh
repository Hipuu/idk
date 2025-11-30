#!/bin/bash
# Upload converted ROM to Google Drive using rclone

set -e

ROM_FILE="$1"
DRIVE_PATH="$2"

if [ -z "$ROM_FILE" ] || [ -z "$DRIVE_PATH" ]; then
    echo "Usage: $0 <rom_file> <drive_path>"
    exit 1
fi

if [ ! -f "$ROM_FILE" ]; then
    echo "Error: ROM file not found: $ROM_FILE"
    exit 1
fi

echo "=== Uploading to Google Drive ==="
echo "File: $ROM_FILE"
echo "Destination: $DRIVE_PATH"

# Upload file
rclone copy "$ROM_FILE" "$DRIVE_PATH" \
    --progress \
    --stats 10s \
    --transfers 4 \
    --checkers 8 \
    --buffer-size 16M \
    --drive-chunk-size 64M

echo "Upload complete!"

# Get file name
FILENAME=$(basename "$ROM_FILE")

# Generate shareable link
echo "Generating shareable link..."

# Extract remote name and path
REMOTE_NAME=$(echo "$DRIVE_PATH" | cut -d: -f1)
FOLDER_PATH=$(echo "$DRIVE_PATH" | cut -d: -f2)

# Get file ID and create share link
FILE_ID=$(rclone lsjson "$DRIVE_PATH" | python3 -c "
import sys, json
data = json.load(sys.stdin)
for item in data:
    if item['Name'] == '$FILENAME':
        print(item.get('ID', ''))
        break
")

if [ -n "$FILE_ID" ]; then
    # Create shareable link using rclone link command
    SHARE_LINK=$(rclone link "${REMOTE_NAME}:${FOLDER_PATH}/${FILENAME}" 2>/dev/null || echo "")
    
    if [ -z "$SHARE_LINK" ]; then
        # Fallback: construct Google Drive link manually
        SHARE_LINK="https://drive.google.com/file/d/${FILE_ID}/view?usp=sharing"
    fi
    
    echo "Shareable link: $SHARE_LINK"
    echo "$SHARE_LINK"
else
    echo "Warning: Could not get file ID, link generation may have failed"
    echo "File uploaded to: $DRIVE_PATH/$FILENAME"
    echo "$DRIVE_PATH/$FILENAME"
fi
