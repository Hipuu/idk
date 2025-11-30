# ROM Builder Bot - Complete Deployment Guide

This guide will walk you through deploying the ROM Builder Telegram Bot from scratch.

## Prerequisites

Before you begin, you'll need:

- **A computer** running Windows, Linux, or macOS
- **Python 3.8+** installed
- **A Telegram account**
- **A GitHub account**
- **A Google account** (for Google Drive storage)
- **Git** installed (optional but recommended)

---

## Part 1: Create Telegram Bot

### Step 1: Get Bot Token from BotFather

1. Open Telegram and search for `@BotFather`
2. Start a chat and send `/newbot`
3. Follow the prompts:
   - **Bot name**: Enter a display name (e.g., "ROM Builder Bot")
   - **Bot username**: Must end with "bot" (e.g., "myrom_builder_bot")
4. **Copy the bot token** (looks like: `1234567890:ABCdefGHIjklMNOpqrsTUVwxyz`)
5. Keep this token safe - you'll need it later!

**Optional but recommended:**
- Send `/setdescription` to add a bot description
- Send `/setabouttext` to add about text
- Send `/setuserpic` to upload a profile picture

---

## Part 2: Setup GitHub Repository

### Step 1: Create GitHub Repository

1. Go to [github.com](https://github.com) and log in
2. Click the **"+"** icon → **"New repository"**
3. Repository settings:
   - **Name**: `rom-builder` (or your preferred name)
   - **Visibility**: Private (recommended) or Public
   - **Initialize**: Don't add README, .gitignore, or license (we already have them)
4. Click **"Create repository"**

### Step 2: Get GitHub Personal Access Token

1. Go to GitHub Settings → Developer settings → [Personal access tokens](https://github.com/settings/tokens) → Tokens (classic)
2. Click **"Generate new token (classic)"**
3. Settings:
   - **Note**: "ROM Builder Bot"
   - **Expiration**: 90 days (or longer)
   - **Scopes**: Check these boxes:
     - ✅ `repo` (all sub-items)
     - ✅ `workflow`
4. Click **"Generate token"**
5. **Copy the token** (starts with `ghp_`) - you won't see it again!

### Step 3: Push Code to GitHub

Open terminal/command prompt in the `c:\Users\idk\Desktop\rom` directory:

```bash
# Initialize git (if not already done)
git init

# Add all files
git add .

# Commit
git commit -m "Initial ROM Builder Bot setup"

# Add remote (replace YOUR_USERNAME and YOUR_REPO)
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git

# Push to GitHub
git branch -M main
git push -u origin main
```

**Note**: Replace `YOUR_USERNAME` and `YOUR_REPO` with your actual GitHub username and repository name.

---

## Part 3: Configure Google Drive with rclone

### Step 1: Install rclone

**Windows:**
```powershell
# Download from rclone.org or use chocolatey
choco install rclone
```

**Linux/macOS:**
```bash
curl https://rclone.org/install.sh | sudo bash
```

### Step 2: Configure Google Drive Remote

Run the configuration wizard:

```bash
rclone config
```

Follow these steps:

1. Type `n` for "New remote"
2. **Name**: Enter `gdrive` (or your preferred name)
3. **Storage**: Type `drive` or find "Google Drive" in the list (usually option 15)
4. **Google Application Client ID**: Press Enter (leave blank)
5. **Google Application Client Secret**: Press Enter (leave blank)
6. **Scope**: Choose `1` (Full access)
7. **Root folder ID**: Press Enter (leave blank)
8. **Service Account File**: Press Enter (leave blank)
9. **Advanced config**: Type `n`
10. **Auto config**: 
    - If on local machine: Type `y` (browser will open)
    - If on remote server: Type `n` and follow instructions
11. **Browser will open**: Log in with your Google account and authorize rclone
12. **Configure as team drive**: Type `n`
13. Confirm: Type `y`
14. Quit: Type `q`

### Step 3: Test rclone

```bash
# List remotes
rclone listremotes

# Should show: gdrive:

# Create ROM_Builds folder
rclone mkdir gdrive:ROM_Builds

# List files (should be empty)
rclone ls gdrive:ROM_Builds
```

### Step 4: Get Base64 Encoded Config

**Linux/macOS:**
```bash
cat ~/.config/rclone/rclone.conf | base64 -w 0
```

**Windows (PowerShell):**
```powershell
[Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes((Get-Content $env:USERPROFILE\.config\rclone\rclone.conf -Raw)))
```

**Copy the entire output** - this is your `RCLONE_CONFIG` secret!

---

## Part 4: Configure GitHub Secrets

1. Go to your GitHub repository
2. Click **Settings** → **Secrets and variables** → **Actions**
3. Click **"New repository secret"** for each of these:

### Secret 1: RCLONE_CONFIG
- **Name**: `RCLONE_CONFIG`
- **Value**: Paste the base64 encoded rclone config from Step 3.4

### Secret 2: DRIVE_FOLDER_PATH
- **Name**: `DRIVE_FOLDER_PATH`
- **Value**: `ROM_Builds`

### Secret 3: RCLONE_REMOTE_NAME
- **Name**: `RCLONE_REMOTE_NAME`
- **Value**: `gdrive` (or whatever you named your remote)

---

## Part 5: Configure Local Bot Environment

### Step 1: Create .env File

In the `c:\Users\idk\Desktop\rom` directory, create a `.env` file from the example:

```bash
cp .env.example .env
```

### Step 2: Edit .env File

Open `.env` in a text editor and fill in your values:

```bash
# Telegram Bot Token (from Part 1)
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz

# GitHub Personal Access Token (from Part 2)
GITHUB_TOKEN=ghp_your_github_token_here
GITHUB_REPO_OWNER=your_github_username
GITHUB_REPO_NAME=rom-builder

# Google Drive Configuration
DRIVE_FOLDER_PATH=ROM_Builds
RCLONE_REMOTE_NAME=gdrive

# Optional
MAX_CONCURRENT_JOBS=3
```

**Important**: Replace all placeholder values with your actual credentials!

### Step 3: Install Python Dependencies

```bash
# Navigate to project directory
cd c:\Users\idk\Desktop\rom

# Install dependencies
pip install -r requirements.txt
```

### Step 4: Verify Setup

Run the setup checker:

```bash
python setup_check.py
```

You should see ✓ marks for all checks. Fix any issues before proceeding.

---

## Part 6: Run the Bot

### Option A: Run Locally (Development/Testing)

In the `rom` directory:

```bash
python bot.py
```

You should see:
```
INFO:__main__:Starting ROM Builder Bot...
```

Keep this terminal window open while the bot is running.

### Option B: Run as Background Service (Linux)

Create a systemd service:

```bash
sudo nano /etc/systemd/system/rom-builder-bot.service
```

Add this content:

```ini
[Unit]
Description=ROM Builder Telegram Bot
After=network.target

[Service]
Type=simple
User=YOUR_USERNAME
WorkingDirectory=/path/to/rom
Environment="PATH=/usr/bin:/usr/local/bin"
ExecStart=/usr/bin/python3 /path/to/rom/bot.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:

```bash
sudo systemctl daemon-reload
sudo systemctl enable rom-builder-bot
sudo systemctl start rom-builder-bot

# Check status
sudo systemctl status rom-builder-bot
```

### Option C: Run with Screen (Linux/macOS)

```bash
# Start screen session
screen -S rom-bot

# Run bot
cd ~/rom
python bot.py

# Detach: Press Ctrl+A then D
# Reattach: screen -r rom-bot
```

### Option D: Deploy to Cloud

**Heroku, Railway, or VPS** - any platform that supports Python apps:

1. Ensure `requirements.txt` is present
2. Add `Procfile`:
   ```
   worker: python bot.py
   ```
3. Set environment variables in platform dashboard
4. Deploy via Git or platform CLI

---

## Part 7: Test the Bot

### Step 1: Start Conversation

1. Open Telegram
2. Search for your bot username (e.g., `@myrom_builder_bot`)
3. Click **START** or send `/start`

You should see the welcome message with instructions.

### Step 2: Test Conversion (Dry Run)

For testing, you can use a small sample ROM or test URL:

```
/convert https://example.com/test_rom.zip hybrid
```

**Note**: For real testing, you need an actual ROM download URL.

### Step 3: Monitor Progress

- Check bot messages for status updates
- Check GitHub Actions:
  - Go to your repo → **Actions** tab
  - You should see the workflow running
- Check logs in terminal where bot is running

### Step 4: Verify Output

After conversion completes:
1. Bot sends download link
2. Check Google Drive `ROM_Builds` folder
3. Download and verify the ROM package

---

## Part 8: Troubleshooting

### Bot doesn't respond
- ✅ Check if bot is running: `python bot.py` should show "Starting..."
- ✅ Verify bot token in `.env` is correct
- ✅ Check firewall/network isn't blocking Telegram API

### GitHub Actions fails
- ✅ Check Actions logs in GitHub
- ✅ Verify all secrets are set correctly
- ✅ Ensure GitHub token has correct permissions
- ✅ Check if ROM URL is accessible

### Upload to Drive fails
- ✅ Verify `RCLONE_CONFIG` secret is base64 encoded correctly
- ✅ Test rclone locally: `rclone ls gdrive:ROM_Builds`
- ✅ Check Google Drive has enough space
- ✅ Verify remote name matches (`gdrive`)

### Bot shows "Job not found"
- ✅ Job IDs expire after workflow completes
- ✅ Use the job ID provided when you started conversion

### Conversion takes too long
- Normal for large ROMs (can take 30+ minutes)
- GitHub Actions has 6-hour timeout
- Check Actions logs for progress

---

## Part 9: Production Recommendations

### Security
- ✅ Use **private** GitHub repository
- ✅ **Never commit** `.env` file (it's in `.gitignore`)
- ✅ Rotate tokens periodically
- ✅ Consider adding user whitelist to bot

### Reliability
- ✅ Use systemd service or cloud hosting for 24/7 uptime
- ✅ Set up monitoring/alerts
- ✅ Keep logs for debugging
- ✅ Test with small ROMs first

### Performance
- ✅ Monitor GitHub Actions minutes usage
- ✅ Organize Drive folders by date or ROM type
- ✅ Clean up old ROMs periodically

### User Experience
- ✅ Add rate limiting to prevent abuse
- ✅ Provide progress updates during conversion
- ✅ Include ROM changelog/version info
- ✅ Add `/help` command with examples

---

## Part 10: Common Commands Reference

### Bot Commands
```
/start          - Show welcome message
/convert <url> <type>  - Convert ROM (super or hybrid)
/status <job_id>       - Check conversion status
```

### rclone Commands
```bash
rclone listremotes              # List configured remotes
rclone ls gdrive:ROM_Builds     # List files in folder
rclone delete gdrive:ROM_Builds/old_rom.zip  # Delete file
rclone size gdrive:ROM_Builds   # Check folder size
```

### Git Commands
```bash
git pull origin main            # Update local code
git add .                       # Stage changes
git commit -m "message"         # Commit changes
git push origin main            # Push to GitHub
```

### Systemd Commands (Linux)
```bash
sudo systemctl status rom-builder-bot    # Check status
sudo systemctl restart rom-builder-bot   # Restart bot
sudo systemctl stop rom-builder-bot      # Stop bot
sudo journalctl -u rom-builder-bot -f    # View logs
```

---

## Summary Checklist

Before going live, ensure:

- [x] Telegram bot created and token saved
- [x] GitHub repository created and code pushed
- [x] GitHub Personal Access Token generated
- [x] GitHub Actions secrets configured (3 secrets)
- [x] rclone configured with Google Drive
- [x] `.env` file created with all credentials
- [x] Python dependencies installed
- [x] `python setup_check.py` passes all checks
- [x] Bot running and responding to `/start`
- [x] Test conversion completed successfully
- [x] Download link works and ROM is valid

---

## Next Steps

1. **Test thoroughly** with various ROM types
2. **Monitor** first few conversions closely
3. **Adjust** settings based on ROM sizes and conversion times
4. **Share** bot with trusted users for beta testing
5. **Iterate** based on feedback

## Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review GitHub Actions logs
3. Check bot terminal output
4. Verify all secrets and environment variables

---

**Congratulations! Your ROM Builder Bot is now deployed! 🎉**
