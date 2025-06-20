<?php
session_start();
require_once 'functions.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !isset($_POST['verification_code'])) {
        // Email submission
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        
        if ($email) {
            $code = generateVerificationCode();
            storeVerificationCode($email, $code, 'register');
            
            if (sendVerificationEmail($email, $code)) {
                $message = 'Verification code sent to your email!';
                $messageType = 'success';
            } else {
                $message = 'Failed to send verification email. Please try again.';
                $messageType = 'error';
            }
        } else {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['verification_code']) && isset($_POST['email'])) {
        // Verification code submission
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $inputCode = trim($_POST['verification_code']);
        
        if ($email && $inputCode) {
            $storedCode = getVerificationCode($email, 'register');
            
            if ($storedCode && $storedCode === $inputCode) {
                if (registerEmail($email)) {
                    removeVerificationCode($email, 'register');
                    $message = 'Successfully registered for GitHub timeline updates!';
                    $messageType = 'success';
                } else {
                    $message = 'Email already registered or registration failed.';
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
    <title>GitHub Timeline Subscription</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
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
            border-color: #007bff;
        }
        
        .btn {
            background: #007bff;
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
            background: #0056b3;
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
            <h1>🐙 GitHub Timeline</h1>
            <p>Subscribe to receive the latest GitHub timeline updates in your inbox</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>📧 Subscribe to Updates</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email address">
                </div>
                <button type="submit" id="submit-email" class="btn">Send Verification Code</button>
            </form>
            
            <div class="divider">
                <span>THEN</span>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email_verify">Email Address (for verification):</label>
                    <input type="email" id="email_verify" name="email" required placeholder="Enter your email address">
                </div>
                <div class="form-group">
                    <label for="verification_code">Verification Code:</label>
                    <input type="text" id="verification_code" name="verification_code" maxlength="6" required placeholder="Enter 6-digit code">
                </div>
                <button type="submit" id="submit-verification" class="btn">Verify & Subscribe</button>
            </form>
        </div>
        
        <div class="card">
            <h2>🔗 Quick Links</h2>
            <p style="margin-bottom: 15px;">Need to unsubscribe? <a href="unsubscribe.php" style="color: #007bff; text-decoration: none;">Click here</a></p>
            <p style="color: #666; font-size: 0.9rem;">You'll receive GitHub timeline updates every 5 minutes after subscribing.</p>
        </div>
    </div>
</body>
</html>
