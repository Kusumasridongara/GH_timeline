<?php
// CRON job file to send GitHub timeline updates
// This file should be executed every 5 minutes via CRON

// Set up error reporting for CRON job
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/cron_errors.log');

// Include functions
require_once __DIR__ . '/functions.php';

// Log file path
$logFile = __DIR__ . '/cron_log.txt';
$timestamp = date('Y-m-d H:i:s');

// Log function
function logMessage($message) {
    global $logFile, $timestamp;
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

try {
    logMessage("CRON job started");

    // Check if email file exists
    $emailFile = __DIR__ . '/registered_emails.txt';
    if (!file_exists($emailFile)) {
        logMessage("No registered emails file found");
        echo "No registered_emails.txt file found.";
        exit(0);
    }

    // Load email list
    $emails = file($emailFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($emails)) {
        logMessage("No registered emails found");
        echo "No registered emails found.";
        exit(0);
    }

    logMessage("Found " . count($emails) . " registered emails");

    // Send updates
    $successCount = sendGitHubUpdatesToSubscribers();

    if ($successCount !== false) {
        logMessage("Successfully sent updates to $successCount subscribers");
        echo "Sent updates to $successCount subscribers.";
    } else {
        logMessage("Failed to send updates to subscribers");
        echo "Failed to send updates.";
    }

    logMessage("CRON job completed");

} catch (Exception $e) {
    logMessage("CRON job error: " . $e->getMessage());
    error_log("CRON job error: " . $e->getMessage());
    echo "Exception: " . $e->getMessage();
} catch (Error $e) {
    logMessage("CRON job fatal error: " . $e->getMessage());
    error_log("CRON job fatal error: " . $e->getMessage());
    echo "Fatal Error: " . $e->getMessage();
}
?>
