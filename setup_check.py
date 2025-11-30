#!/usr/bin/env python3
"""
Setup verification script for ROM Builder Bot
Checks if all requirements are properly configured
"""
import os
import sys
import subprocess
from pathlib import Path

def print_status(message, status):
    """Print status with color coding."""
    colors = {
        'ok': '\033[92m✓\033[0m',
        'fail': '\033[91m✗\033[0m',
        'warn': '\033[93m⚠\033[0m'
    }
    print(f"{colors.get(status, '?')} {message}")

def check_python_version():
    """Check Python version."""
    version = sys.version_info
    if version.major == 3 and version.minor >= 8:
        print_status(f"Python {version.major}.{version.minor}.{version.micro}", 'ok')
        return True
    else:
        print_status(f"Python {version.major}.{version.minor} (requires 3.8+)", 'fail')
        return False

def check_dependencies():
    """Check if Python dependencies are installed."""
    try:
        import telegram
        import requests
        import dotenv
        print_status("Python dependencies installed", 'ok')
        return True
    except ImportError as e:
        print_status(f"Missing Python dependencies: {e}", 'fail')
        print("  Run: pip install -r requirements.txt")
        return False

def check_env_file():
    """Check if .env file exists and has required variables."""
    if not os.path.exists('.env'):
        print_status(".env file not found", 'fail')
        print("  Copy .env.example to .env and configure it")
        return False
    
    from dotenv import load_dotenv
    load_dotenv()
    
    required_vars = [
        'TELEGRAM_BOT_TOKEN',
        'GITHUB_TOKEN',
        'GITHUB_REPO_OWNER',
        'GITHUB_REPO_NAME'
    ]
    
    missing = []
    for var in required_vars:
        if not os.getenv(var) or os.getenv(var).startswith('your_'):
            missing.append(var)
    
    if missing:
        print_status(f".env file incomplete: {', '.join(missing)}", 'warn')
        return False
    else:
        print_status(".env file configured", 'ok')
        return True

def check_github_workflow():
    """Check if GitHub Actions workflow exists."""
    workflow_path = Path('.github/workflows/rom-converter.yml')
    if workflow_path.exists():
        print_status("GitHub Actions workflow exists", 'ok')
        return True
    else:
        print_status("GitHub Actions workflow not found", 'fail')
        return False

def check_scripts():
    """Check if conversion scripts exist and are executable."""
    scripts = [
        'scripts/convert_to_super.sh',
        'scripts/convert_to_hybrid.sh',
        'scripts/upload_to_drive.sh',
        'scripts/extract_rom_info.py'
    ]
    
    all_exist = True
    for script in scripts:
        if os.path.exists(script):
            # Try to make executable (Linux/Mac)
            try:
                os.chmod(script, 0o755)
            except:
                pass
        else:
            print_status(f"Missing: {script}", 'fail')
            all_exist = False
    
    if all_exist:
        print_status("All conversion scripts present", 'ok')
        return True
    return False

def check_rclone_config():
    """Check if rclone is configured."""
    try:
        result = subprocess.run(['rclone', 'version'], 
                              capture_output=True, 
                              text=True, 
                              timeout=5)
        if result.returncode == 0:
            print_status("rclone is installed", 'ok')
            
            # Check if config exists
            result = subprocess.run(['rclone', 'listremotes'], 
                                  capture_output=True, 
                                  text=True, 
                                  timeout=5)
            if result.stdout.strip():
                print_status(f"rclone remotes configured: {result.stdout.strip()}", 'ok')
                return True
            else:
                print_status("No rclone remotes configured", 'warn')
                print("  Run: rclone config")
                return False
        else:
            print_status("rclone not working properly", 'fail')
            return False
    except (subprocess.TimeoutExpired, FileNotFoundError):
        print_status("rclone not installed", 'warn')
        print("  Note: rclone is needed for GitHub Actions, not for bot")
        return False

def main():
    """Run all checks."""
    print("=" * 50)
    print("ROM Builder Bot - Setup Verification")
    print("=" * 50)
    print()
    
    checks = [
        ("Python Version", check_python_version),
        ("Python Dependencies", check_dependencies),
        ("Environment Configuration", check_env_file),
        ("GitHub Workflow", check_github_workflow),
        ("Conversion Scripts", check_scripts),
        ("rclone Configuration", check_rclone_config),
    ]
    
    results = []
    for name, check_func in checks:
        print(f"\nChecking {name}...")
        results.append(check_func())
    
    print()
    print("=" * 50)
    passed = sum(results)
    total = len(results)
    
    if passed == total:
        print_status(f"All checks passed ({passed}/{total})", 'ok')
        print("\nYou're ready to run the bot!")
        print("Start with: python bot.py")
        return 0
    else:
        print_status(f"Some checks failed ({passed}/{total})", 'warn')
        print("\nPlease fix the issues above before running the bot.")
        return 1

if __name__ == '__main__':
    sys.exit(main())
