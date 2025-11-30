"""
Configuration file for ROM Builder Telegram Bot
"""
import os
from dotenv import load_dotenv

load_dotenv()

# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN = os.getenv('TELEGRAM_BOT_TOKEN')

# GitHub Configuration
GITHUB_TOKEN = os.getenv('GITHUB_TOKEN')
GITHUB_REPO_OWNER = os.getenv('GITHUB_REPO_OWNER')
GITHUB_REPO_NAME = os.getenv('GITHUB_REPO_NAME')

# Google Drive Configuration
DRIVE_FOLDER_PATH = os.getenv('DRIVE_FOLDER_PATH', 'ROM_Builds')
RCLONE_REMOTE_NAME = os.getenv('RCLONE_REMOTE_NAME', 'gdrive')

# Bot Settings
ALLOWED_ROM_EXTENSIONS = ['.zip', '.img', '.tar', '.tar.gz', '.tgz']
MAX_CONCURRENT_JOBS = int(os.getenv('MAX_CONCURRENT_JOBS', '3'))

# GitHub Actions Workflow
WORKFLOW_FILE = 'rom-converter.yml'
