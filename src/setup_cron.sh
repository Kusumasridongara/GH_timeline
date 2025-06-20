#!/bin/bash
# This script should set up a CRON job to run cron.php every 5 minutes.
# You need to implement the CRON setup logic here.
#!/bin/bash

# setup_cron.sh - Automatically configure CRON job for GitHub timeline updates
# This script must be executed to set up the CRON job

set -e

# Get the current directory (where the script is located)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CRON_PHP_FILE="$SCRIPT_DIR/cron.php"

# Check if cron.php exists
if [ ! -f "$CRON_PHP_FILE" ]; then
    echo "Error: cron.php not found at $CRON_PHP_FILE"
    exit 1
fi

# Create the CRON job entry
CRON_ENTRY="*/5 * * * * /usr/bin/php $CRON_PHP_FILE >/dev/null 2>&1"

# Check if CRON job already exists
if crontab -l 2>/dev/null | grep -q "$CRON_PHP_FILE"; then
    echo "CRON job for GitHub timeline updates already exists."
    echo "Current CRON jobs:"
    crontab -l 2>/dev/null | grep "$CRON_PHP_FILE" || true
else
    # Add the CRON job
    echo "Adding CRON job for GitHub timeline updates..."
    
    # Get current crontab, add new entry, and install
    (crontab -l 2>/dev/null || true; echo "$CRON_ENTRY") | crontab -
    
    echo "CRON job successfully added!"
    echo "The following job will run every 5 minutes:"
    echo "$CRON_ENTRY"
fi

# Verify CRON service is running
if command -v systemctl >/dev/null 2>&1; then
    if systemctl is-active cron >/dev/null 2>&1 || systemctl is-active crond >/dev/null 2>&1; then
        echo "CRON service is running."
    else
        echo "Warning: CRON service may not be running. Please start it manually:"
        echo "  sudo systemctl start cron"
        echo "  or"
        echo "  sudo systemctl start crond"
    fi
elif command -v service >/dev/null 2>&1; then
    if service cron status >/dev/null 2>&1 || service crond status >/dev/null 2>&1; then
        echo "CRON service is running."
    else
        echo "Warning: CRON service may not be running. Please start it manually:"
        echo "  sudo service cron start"
        echo "  or"
        echo "  sudo service crond start"
    fi
else
    echo "Note: Please ensure CRON service is running on your system."
fi

# Create log files with proper permissions
touch "$SCRIPT_DIR/cron_log.txt"
touch "$SCRIPT_DIR/cron_errors.log"
chmod 644 "$SCRIPT_DIR/cron_log.txt"
chmod 644 "$SCRIPT_DIR/cron_errors.log"

echo ""
echo "Setup complete! The CRON job will:"
echo "1. Run every 5 minutes"
echo "2. Fetch GitHub timeline data"
echo "3. Send HTML-formatted emails to all registered users"
echo "4. Log activity to: $SCRIPT_DIR/cron_log.txt"
echo "5. Log errors to: $SCRIPT_DIR/cron_errors.log"
echo ""
echo "You can monitor the logs with:"
echo "  tail -f $SCRIPT_DIR/cron_log.txt"
echo "  tail -f $SCRIPT_DIR/cron_errors.log"
