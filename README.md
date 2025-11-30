# ROM Builder Telegram Bot

A Telegram bot that converts Android base ROMs into flashable formats using GitHub Actions as the conversion backend.

## Features

- 🤖 **Telegram Bot Interface** - Simple command-based interaction
- 🔄 **Two ROM Types**:
  - **Super ROM** - Flashable via fastboot with Windows/Linux flash scripts
  - **Hybrid ROM** - TWRP-flashable with dual A/B slot support
- 📦 **Automated Conversion** - GitHub Actions handles heavy processing
- ☁️ **Cloud Storage** - Automatic upload to Google Drive via rclone
- 🏷️ **Smart Naming** - Auto-extracts ROM metadata (codename-version-romtype.zip)
- ✅ **Dual Slot Support** - Hybrid ROMs flash to both A and B slots in TWRP

## Setup

### Prerequisites

1. **Telegram Bot Token**
   - Create a bot via [@BotFather](https://t.me/botfather)
   - Save the token

2. **GitHub Repository**
   - Fork or create a new repository
   - Enable GitHub Actions
   - Get a Personal Access Token with `repo` and `workflow` permissions

3. **Google Drive & rclone**
   - Set up rclone with Google Drive
   - Run `rclone config` to create a remote (e.g., "gdrive")
   - Get base64 encoded config: `cat ~/.config/rclone/rclone.conf | base64 -w 0`

### Installation

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd rom
   ```

2. **Install Python dependencies**
   ```bash
   pip install -r requirements.txt
   ```

3. **Configure environment variables**
   ```bash
   cp .env.example .env
   # Edit .env with your credentials
   ```

4. **Set up GitHub Secrets**
   
   Go to your repository settings → Secrets and add:
   - `RCLONE_CONFIG` - Base64 encoded rclone config
   - `DRIVE_FOLDER_PATH` - Google Drive folder path (e.g., "ROM_Builds")
   - `RCLONE_REMOTE_NAME` - rclone remote name (e.g., "gdrive")

5. **Run the bot**
   ```bash
   python bot.py
   ```

## Usage

### Bot Commands

- `/start` - Show welcome message and usage instructions
- `/convert <rom_url> <type>` - Start ROM conversion
  - `<rom_url>` - Direct download URL for base ROM
  - `<type>` - Either `super` or `hybrid`
- `/status <job_id>` - Check conversion status

### Example

```
/convert https://example.com/base_rom.zip hybrid
```

The bot will:
1. Trigger GitHub Actions workflow
2. Download the ROM
3. Extract metadata (codename, version)
4. Convert to hybrid format with dual A/B slot support
5. Upload to Google Drive with naming: `PKG110-16.0.0.205-hybrid.zip`
6. Send you the download link

## ROM Types Explained

### Super ROM (Fastboot)

- Flashable via fastboot on **Windows**
- **✓ Includes Android Platform Tools** (fastboot.exe) - no separate installation needed!
- Includes:
  - `super.img` - Combined system/vendor/product partitions
  - `boot.img`, `dtbo.img`, `vbmeta.img` - Boot partitions
  - `flash-all.bat` - Windows flasher script
  - `platform-tools/` - Bundled Android tools

**Usage:**
```batch
# Boot device to fastboot mode
# Windows (uses bundled fastboot):
flash-all.bat
```

### Hybrid ROM (TWRP A/B)

- Flashable via TWRP recovery
- Automatically flashes to **both slot A and slot B**
- Ensures both slots have identical ROM installation
- Includes shell-based update-binary for maximum compatibility

**Usage:**
1. Boot to TWRP recovery
2. Install the ZIP file
3. Both slots A and B will be flashed
4. Reboot to system

## File Structure

```
rom/
├── .github/
│   └── workflows/
│       └── rom-converter.yml    # GitHub Actions workflow
├── scripts/
│   ├── convert_to_super.sh      # Super ROM converter
│   ├── convert_to_hybrid.sh     # Hybrid ROM converter
│   ├── extract_rom_info.py      # Metadata extraction
│   └── upload_to_drive.sh       # rclone upload script
├── bot.py                       # Main Telegram bot
├── config.py                    # Configuration
├── requirements.txt             # Python dependencies
├── .env.example                 # Environment variables template
└── README.md                    # This file
```

## Configuration

### Environment Variables (.env)

```bash
# Telegram
TELEGRAM_BOT_TOKEN=your_bot_token

# GitHub
GITHUB_TOKEN=your_github_token
GITHUB_REPO_OWNER=your_username
GITHUB_REPO_NAME=rom

# Google Drive
DRIVE_FOLDER_PATH=ROM_Builds
RCLONE_REMOTE_NAME=gdrive

# Optional
MAX_CONCURRENT_JOBS=3
```

### GitHub Secrets

| Secret | Description | Example |
|--------|-------------|---------|
| `RCLONE_CONFIG` | Base64 encoded rclone config | `W3JlbW90ZV0...` |
| `DRIVE_FOLDER_PATH` | Google Drive folder path | `ROM_Builds` |
| `RCLONE_REMOTE_NAME` | rclone remote name | `gdrive` |

## How It Works

1. **User sends command** → Bot receives ROM URL and type
2. **Bot triggers GitHub Actions** → Repository dispatch event
3. **GitHub Actions workflow runs**:
   - Downloads ROM from URL
   - Extracts metadata (codename, version)
   - Converts to super/hybrid format
   - Uploads to Google Drive
   - Generates shareable link
4. **Bot monitors workflow** → Polls GitHub API for completion
5. **Bot sends link** → User receives download URL

## Troubleshooting

### Bot not responding
- Check if bot token is correct
- Ensure bot is running (`python bot.py`)
- Check logs for errors

### Workflow fails
- Check GitHub Actions logs
- Verify ROM URL is accessible
- Ensure rclone config is properly base64 encoded

### Upload fails
- Verify rclone configuration
- Check Google Drive permissions
- Ensure sufficient storage space

### ROM won't flash
- **Super ROM**: Ensure device is in fastboot mode and bootloader is unlocked
- **Hybrid ROM**: Use TWRP recovery compatible with your device

## Credits

- **lpunpack/lpmake** - For super partition handling
- **rclone** - For Google Drive integration
- **python-telegram-bot** - For Telegram bot framework

## License

MIT License - Feel free to modify and distribute

## Support

For issues or questions, please open an issue on GitHub.
