# Quick Start Guide

## 1. Get Telegram Bot Token
1. Open Telegram and search for @BotFather
2. Send `/newbot` and follow instructions
3. Copy the bot token

## 2. Get GitHub Personal Access Token
1. Go to GitHub Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click "Generate new token (classic)"
3. Select scopes: `repo`, `workflow`
4. Generate and copy the token

## 3. Configure rclone for Google Drive
```bash
rclone config
# Follow prompts to add Google Drive remote (name it "gdrive")
```

## 4. Setup the Bot
```bash
# Install dependencies
pip install -r requirements.txt

# Configure environment
cp .env.example .env
# Edit .env with your tokens

# Verify setup
python setup_check.py
```

## 5. Configure GitHub Secrets
Go to your GitHub repository → Settings → Secrets and variables → Actions

Add these secrets:
- `RCLONE_CONFIG` - Run: `cat ~/.config/rclone/rclone.conf | base64 -w 0`
- `DRIVE_FOLDER_PATH` - Example: `ROM_Builds`
- `RCLONE_REMOTE_NAME` - Example: `gdrive`

## 6. Run the Bot
```bash
python bot.py
```

## 7. Test It
Send to your bot:
```
/start
/convert https://example.com/rom.zip hybrid
```

Done! 🎉
