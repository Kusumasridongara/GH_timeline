<?php
session_start();
require_once 'functions.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email']) && !isset($_POST['unsubscribe_verification_code'])) {
        // Unsubscribe email submission
        $email = filter_var(trim($_POST['unsubscribe_email']), FILTER_VALIDATE_EMAIL);
        
        if ($email) {
            $file = __DIR__ . '/registered_emails.txt';
            $emailExists = false;
            
            if (file_exists($file)) {
                $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                // Normalize input email
                $emailNormalized = trim(strtolower($email));

                // Normalize all stored emails
                $normalizedEmails = array_map(function($e) {
                    return trim(strtolower($e));
                }, $emails);

                $emailExists = in_array($emailNormalized, $normalizedEmails);
            }
            
            if ($emailExists) {
                $code = generateVerificationCode();
                storeVerificationCode($email, $code, 'unsubscribe');
                
                if (sendUnsubscribeEmail($email, $code)) {
                    $message = 'Unsubscribe verification code sent to your email!';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to send unsubscribe email. Please try again.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Email address not found in our subscription list.';
                $messageType = 'error';
            }
        } else {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['unsubscribe_verification_code']) && isset($_POST['unsubscribe_email'])) {
        // Unsubscribe verification code submission
        $email = filter_var(trim($_POST['unsubscribe_email']), FILTER_VALIDATE_EMAIL);
        $inputCode = trim($_POST['unsubscribe_verification_code']);
        
        if ($email && $inputCode) {
            $storedCode = getVerificationCode($email, 'unsubscribe');
            
            if ($storedCode && $storedCode === $inputCode) {
                if (unsubscribeEmail($email)) {
                    removeVerificationCode($email, 'unsubscribe');
                    $message = 'Successfully unsubscribed from GitHub timeline updates!';
                    $messageType = 'success';
                } else {
                    $message = 'Email not found or unsubscription failed.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Invalid or expired verification code.';
                $messageType = 'error';
            }
        } else {
            $message = 'Please provide valid email and verification code.';
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - GitHub Timeline</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #dc3545;
        }

        .btn {
            background: #dc3545;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .btn:hover {
            background: #c82333;
        }

        .btn-back {
            background: #6c757d;
            margin-top: 15px;
        }

        .btn-back:hover {
            background: #545b62;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e1e5e9;
        }

        .divider span {
            background: white;
            padding: 0 20px;
            color: #666;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚫 Unsubscribe</h1>
            <p>We're sorry to see you go. Unsubscribe from GitHub timeline updates</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>📧 Request Unsubscription</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="unsubscribe_email">Email Address:</label>
                    <input type="email" id="unsubscribe_email" name="unsubscribe_email" required placeholder="Enter your registered email">
                </div>
                <button type="submit" id="submit-unsubscribe" class="btn">Send Unsubscribe Code</button>
            </form>

            <div class="divider">
                <span>THEN</span>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="unsubscribe_email_verify">Email Address (for verification):</label>
                    <input type="email" id="unsubscribe_email_verify" name="unsubscribe_email" required placeholder="Enter your email address">
                </div>
                <div class="form-group">
                    <label for="unsubscribe_verification_code">Verification Code:</label>
                    <input type="text" id="unsubscribe_verification_code" name="unsubscribe_verification_code" maxlength="6" required placeholder="Enter 6-digit code">
                </div>
                <button type="submit" id="verify-unsubscribe" class="btn">Confirm Unsubscription</button>
            </form>

            <a href="index.php" style="text-decoration: none;">
                <button type="button" class="btn btn-back">← Back to Subscription</button>
            </a>
        </div>
    </div>
</body>
</html>
