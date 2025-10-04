<?php
/**
 * Email Configuration Test Page
 * Use this page to test your email configuration
 */

// Start session and check authentication
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admin privileges required.");
}

$test_result = "";
$test_error = "";

// Handle test email request
if (isset($_POST['action']) && $_POST['action'] === 'test_email') {
    $result = testEmailConfiguration();
    if ($result === true) {
        $test_result = "✅ Test email sent successfully! Check your inbox at " . MAIL_FROM_EMAIL;
    } else {
        $test_error = "❌ Email test failed: " . $result;
    }
}

// Handle activation email test
if (isset($_POST['action']) && $_POST['action'] === 'test_activation') {
    $test_email = $_POST['test_email'] ?? MAIL_FROM_EMAIL;
    $test_username = $_POST['test_username'] ?? 'Test User';
    
    $result = sendAccountActivationEmail($test_email, $test_username);
    if ($result === true) {
        $test_result = "✅ Activation email sent successfully to " . htmlspecialchars($test_email);
    } else {
        $test_error = "❌ Activation email test failed: " . $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Configuration Test - AdzDIAG</title>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet"/>
    <style>
        .container { max-width: 800px; margin: 50px auto; padding: 20px; }
        .config-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .test-section { border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Configuration Test</h1>
        <p>Use this page to test your email configuration and diagnose issues.</p>
        
        <!-- Current Configuration -->
        <div class="config-info">
            <h3>Current Email Configuration:</h3>
            <ul>
                <li><strong>SMTP Host:</strong> <?php echo SMTP_HOST; ?></li>
                <li><strong>SMTP Port:</strong> <?php echo SMTP_PORT; ?></li>
                <li><strong>SMTP Encryption:</strong> <?php echo SMTP_ENCRYPTION; ?></li>
                <li><strong>Username:</strong> <?php echo SMTP_USERNAME; ?></li>
                <li><strong>From Email:</strong> <?php echo MAIL_FROM_EMAIL; ?></li>
                <li><strong>From Name:</strong> <?php echo MAIL_FROM_NAME; ?></li>
            </ul>
        </div>

        <!-- Test Results -->
        <?php if ($test_result): ?>
            <div class="success"><?php echo $test_result; ?></div>
        <?php endif; ?>

        <?php if ($test_error): ?>
            <div class="error"><?php echo $test_error; ?></div>
        <?php endif; ?>

        <!-- Basic Email Test -->
        <div class="test-section">
            <h3>🧪 Basic Email Test</h3>
            <p>Send a simple test email to verify SMTP configuration.</p>
            <form method="POST">
                <input type="hidden" name="action" value="test_email">
                <button type="submit" class="btn btn-primary">Send Test Email</button>
            </form>
        </div>

        <!-- Activation Email Test -->
        <div class="test-section">
            <h3>🎯 Activation Email Test</h3>
            <p>Test the actual activation email that gets sent to users.</p>
            <form method="POST">
                <input type="hidden" name="action" value="test_activation">
                <div class="mb-3">
                    <label class="form-label">Test Email Address:</label>
                    <input type="email" name="test_email" class="form-control" value="<?php echo MAIL_FROM_EMAIL; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Test Username:</label>
                    <input type="text" name="test_username" class="form-control" value="Test User" required>
                </div>
                <button type="submit" class="btn btn-success">Send Activation Email Test</button>
            </form>
        </div>

        <!-- Alternative Configurations -->
        <div class="test-section">
            <h3>🔄 Alternative Email Configurations</h3>
            <p>If the current configuration isn't working, try these alternatives in your config.php:</p>
            
            <h4>Option 1: TLS on Port 587</h4>
            <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
define('SMTP_HOST', 'mail.adzdiag.co.uk');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_USERNAME', 'admin@adzdiag.co.uk');
define('SMTP_PASSWORD', 'Kidkurd123_');
            </pre>
            
            <h4>Option 2: No Encryption (less secure, but may work)</h4>
            <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
define('SMTP_HOST', 'mail.adzdiag.co.uk');
define('SMTP_PORT', 25);
define('SMTP_ENCRYPTION', ''); // No encryption
define('SMTP_USERNAME', 'admin@adzdiag.co.uk');
define('SMTP_PASSWORD', 'Kidkurd123_');
            </pre>
            
            <h4>Option 3: Gmail (if you want to use Gmail instead)</h4>
            <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_USERNAME', 'your-gmail@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Use App Password
            </pre>
        </div>

        <!-- Troubleshooting -->
        <div class="test-section">
            <h3>🔧 Troubleshooting Tips</h3>
            <ul>
                <li><strong>SSL Certificate Issues:</strong> The SMTP config has SSL verification disabled, which should help with self-signed certificates.</li>
                <li><strong>Port Issues:</strong> Make sure port 465 (SSL) or 587 (TLS) is open on your server.</li>
                <li><strong>Authentication:</strong> Verify the username and password are correct.</li>
                <li><strong>Firewall:</strong> Check if your hosting provider blocks outgoing SMTP connections.</li>
                <li><strong>Email Provider:</strong> Some providers require app-specific passwords instead of regular passwords.</li>
                <li><strong>Debug Mode:</strong> Debug mode is currently enabled in config.php - check your server logs for detailed SMTP errors.</li>
            </ul>
        </div>

        <div style="margin-top: 30px;">
            <a href="admin.php" class="btn btn-outline-primary">← Back to Admin Panel</a>
        </div>
    </div>
</body>
</html>