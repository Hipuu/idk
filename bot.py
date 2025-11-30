"""
ROM Builder Telegram Bot
Converts Android base ROMs to super/hybrid format using GitHub Actions
"""
import asyncio
import logging
import time
from telegram import Update
from telegram.ext import Application, CommandHandler, ContextTypes
import requests
from config import *

# Enable logging
logging.basicConfig(
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    level=logging.INFO
)
logger = logging.getLogger(__name__)

# Store active jobs
active_jobs = {}


async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Send welcome message when /start is issued."""
    welcome_message = """
🤖 **ROM Builder Bot**

Welcome! I can convert Android base ROMs to:
• **Super ROM** - Flashable via fastboot
• **Hybrid ROM** - Flashable via TWRP (dual A/B slots)

**Usage:**
`/convert <rom_url> <type>`

**Example:**
`/convert https://example.com/rom.zip hybrid`

**Types:** `super` or `hybrid`

**Status:**
`/status <job_id>` - Check conversion status

The converted ROM will be uploaded to Google Drive and you'll receive the download link!
    """
    await update.message.reply_text(welcome_message, parse_mode='Markdown')


async def convert(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Handle /convert command to start ROM conversion."""
    user_id = update.effective_user.id
    chat_id = update.effective_chat.id
    
    # Check arguments
    if len(context.args) < 2:
        await update.message.reply_text(
            "❌ **Invalid usage!**\n\n"
            "Usage: `/convert <rom_url> <type>`\n"
            "Example: `/convert https://example.com/rom.zip hybrid`\n\n"
            "Types: `super` or `hybrid`",
            parse_mode='Markdown'
        )
        return
    
    rom_url = context.args[0]
    rom_type = context.args[1].lower()
    
    # Validate ROM type
    if rom_type not in ['super', 'hybrid']:
        await update.message.reply_text(
            "❌ **Invalid ROM type!**\n\n"
            "Please use either `super` or `hybrid`",
            parse_mode='Markdown'
        )
        return
    
    # Send processing message
    msg = await update.message.reply_text(
        f"🔄 **Processing your request...**\n\n"
        f"ROM URL: `{rom_url}`\n"
        f"Type: `{rom_type}`\n\n"
        f"Triggering GitHub Actions workflow...",
        parse_mode='Markdown'
    )
    
    try:
        # Trigger GitHub Actions workflow
        workflow_run_id = trigger_github_workflow(rom_url, rom_type, user_id, chat_id)
        
        if not workflow_run_id:
            await msg.edit_text(
                "❌ **Failed to trigger workflow!**\n\n"
                "Please check GitHub token and repository settings.",
                parse_mode='Markdown'
            )
            return
        
        # Store job information
        job_id = f"{user_id}_{int(time.time())}"
        active_jobs[job_id] = {
            'workflow_run_id': workflow_run_id,
            'chat_id': chat_id,
            'rom_type': rom_type,
            'rom_url': rom_url,
            'status': 'running'
        }
        
        await msg.edit_text(
            f"✅ **Workflow triggered successfully!**\n\n"
            f"Job ID: `{job_id}`\n"
            f"Workflow Run ID: `{workflow_run_id}`\n\n"
            f"⏳ Conversion in progress...\n"
            f"You'll be notified when it's complete!\n\n"
            f"Use `/status {job_id}` to check progress.",
            parse_mode='Markdown'
        )
        
        # Start monitoring workflow in background
        asyncio.create_task(monitor_workflow(context.application, job_id))
        
    except Exception as e:
        logger.error(f"Error in convert command: {e}")
        await msg.edit_text(
            f"❌ **Error:** {str(e)}",
            parse_mode='Markdown'
        )


async def status(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Check the status of a conversion job."""
    if len(context.args) < 1:
        await update.message.reply_text(
            "❌ **Invalid usage!**\n\n"
            "Usage: `/status <job_id>`",
            parse_mode='Markdown'
        )
        return
    
    job_id = context.args[0]
    
    if job_id not in active_jobs:
        await update.message.reply_text(
            "❌ **Job not found!**\n\n"
            f"Job ID `{job_id}` doesn't exist or has expired.",
            parse_mode='Markdown'
        )
        return
    
    job = active_jobs[job_id]
    status_emoji = {
        'running': '⏳',
        'completed': '✅',
        'failed': '❌'
    }
    
    status_text = f"{status_emoji.get(job['status'], '❓')} **Status: {job['status'].upper()}**\n\n"
    status_text += f"Job ID: `{job_id}`\n"
    status_text += f"ROM Type: `{job['rom_type']}`\n"
    
    if job['status'] == 'completed' and 'download_url' in job:
        status_text += f"\n📥 **Download Link:**\n{job['download_url']}"
    elif job['status'] == 'failed' and 'error' in job:
        status_text += f"\n❌ **Error:**\n{job['error']}"
    
    await update.message.reply_text(status_text, parse_mode='Markdown')


def trigger_github_workflow(rom_url: str, rom_type: str, user_id: int, chat_id: int):
    """Trigger GitHub Actions workflow via repository dispatch."""
    url = f"https://api.github.com/repos/{GITHUB_REPO_OWNER}/{GITHUB_REPO_NAME}/actions/workflows/{WORKFLOW_FILE}/dispatches"
    
    headers = {
        'Authorization': f'token {GITHUB_TOKEN}',
        'Accept': 'application/vnd.github.v3+json'
    }
    
    payload = {
        'ref': 'main',  # or 'master', depending on your default branch
        'inputs': {
            'rom_url': rom_url,
            'rom_type': rom_type,
            'user_id': str(user_id),
            'chat_id': str(chat_id)
        }
    }
    
    try:
        response = requests.post(url, json=payload, headers=headers)
        response.raise_for_status()
        
        # Get the latest workflow run ID
        time.sleep(2)  # Wait for workflow to be created
        runs_url = f"https://api.github.com/repos/{GITHUB_REPO_OWNER}/{GITHUB_REPO_NAME}/actions/runs"
        runs_response = requests.get(runs_url, headers=headers)
        runs_response.raise_for_status()
        
        runs_data = runs_response.json()
        if runs_data['workflow_runs']:
            return runs_data['workflow_runs'][0]['id']
        
        return None
        
    except Exception as e:
        logger.error(f"Failed to trigger workflow: {e}")
        return None


async def monitor_workflow(application: Application, job_id: str):
    """Monitor GitHub Actions workflow completion."""
    job = active_jobs.get(job_id)
    if not job:
        return
    
    workflow_run_id = job['workflow_run_id']
    chat_id = job['chat_id']
    
    headers = {
        'Authorization': f'token {GITHUB_TOKEN}',
        'Accept': 'application/vnd.github.v3+json'
    }
    
    poll_interval = 30  # Check every 30 seconds
    max_attempts = 240  # 2 hours maximum (240 * 30 seconds)
    attempts = 0
    
    while attempts < max_attempts:
        try:
            # Check workflow status
            url = f"https://api.github.com/repos/{GITHUB_REPO_OWNER}/{GITHUB_REPO_NAME}/actions/runs/{workflow_run_id}"
            response = requests.get(url, headers=headers)
            response.raise_for_status()
            
            data = response.json()
            status = data.get('status')
            conclusion = data.get('conclusion')
            
            if status == 'completed':
                if conclusion == 'success':
                    # Get output from workflow (download link)
                    download_url = get_workflow_output(workflow_run_id)
                    
                    job['status'] = 'completed'
                    job['download_url'] = download_url
                    
                    message = (
                        f"✅ **ROM Conversion Complete!**\n\n"
                        f"ROM Type: `{job['rom_type']}`\n\n"
                        f"📥 **Download Link:**\n{download_url}\n\n"
                        f"Hash will be in the Drive folder!"
                    )
                    
                    await application.bot.send_message(
                        chat_id=chat_id,
                        text=message,
                        parse_mode='Markdown'
                    )
                else:
                    job['status'] = 'failed'
                    job['error'] = f"Workflow failed with conclusion: {conclusion}"
                    
                    await application.bot.send_message(
                        chat_id=chat_id,
                        text=f"❌ **ROM Conversion Failed!**\n\nConclusion: {conclusion}",
                        parse_mode='Markdown'
                    )
                
                return
            
            attempts += 1
            await asyncio.sleep(poll_interval)
            
        except Exception as e:
            logger.error(f"Error monitoring workflow: {e}")
            job['status'] = 'failed'
            job['error'] = str(e)
            
            await application.bot.send_message(
                chat_id=chat_id,
                text=f"❌ **Error monitoring workflow:**\n{str(e)}",
                parse_mode='Markdown'
            )
            return
    
    # Timeout
    job['status'] = 'failed'
    job['error'] = 'Workflow timeout (exceeded 2 hours)'
    
    await application.bot.send_message(
        chat_id=chat_id,
        text="❌ **Workflow timeout!**\n\nConversion took longer than 2 hours.",
        parse_mode='Markdown'
    )


def get_workflow_output(workflow_run_id: int) -> str:
    """Get the download URL from workflow artifacts or output."""
    headers = {
        'Authorization': f'token {GITHUB_TOKEN}',
        'Accept': 'application/vnd.github.v3+json'
    }
    
    # Check for artifacts that contain the download URL
    url = f"https://api.github.com/repos/{GITHUB_REPO_OWNER}/{GITHUB_REPO_NAME}/actions/runs/{workflow_run_id}/artifacts"
    
    try:
        response = requests.get(url, headers=headers)
        response.raise_for_status()
        
        data = response.json()
        # The workflow will create a text artifact with the download URL
        # This is a placeholder - actual implementation will read from artifact
        
        # For now, return a placeholder
        return "https://drive.google.com/YOUR_DOWNLOAD_LINK"
        
    except Exception as e:
        logger.error(f"Failed to get workflow output: {e}")
        return "Download link unavailable - check Google Drive folder"


def main():
    """Start the bot."""
    # Create the Application
    application = Application.builder().token(TELEGRAM_BOT_TOKEN).build()
    
    # Register command handlers
    application.add_handler(CommandHandler("start", start))
    application.add_handler(CommandHandler("convert", convert))
    application.add_handler(CommandHandler("status", status))
    
    # Start the bot
    logger.info("Starting ROM Builder Bot...")
    application.run_polling(allowed_updates=Update.ALL_TYPES)


if __name__ == '__main__':
    main()
