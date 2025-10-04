<?php
/**
 * Quick test to verify activation email fix
 */
require_once 'config.php';

// Test the fixed activation email function
echo "Testing activation email function...\n\n";

$test_email = "admin@adzdiag.co.uk"; // Use your own email for testing
$test_username = "Test User";

echo "Attempting to send activation email to: " . $test_email . "\n";
echo "Username: " . $test_username . "\n\n";

$result = sendAccountActivationEmail($test_email, $test_username);

if ($result === true) {
    echo "✅ SUCCESS: Activation email sent successfully!\n";
    echo "Check your email inbox at " . $test_email . "\n";
} else {
    echo "❌ FAILED: " . $result . "\n";
}

echo "\n" . str_repeat("-", 50) . "\n";
echo "Testing backup method...\n\n";

$backup_result = sendActivationEmailViaOTP($test_email, $test_username);

if ($backup_result === true) {
    echo "✅ SUCCESS: Backup activation email sent successfully!\n";
} else {
    echo "❌ FAILED: Backup method failed\n";
}
?>