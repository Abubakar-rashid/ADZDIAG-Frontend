<?php

// Include PHPMailer autoloader
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Database Configuration
$host = "217.154.59.146"; 
$port = "5432";     
$user = "admin";      
$pass = "admin123";     
$dbname = "mydb";          

try {
    // Create PDO connection for PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Optional: Set timezone if needed
    // $conn->exec("SET timezone = 'UTC'");

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Email Configuration for PHPMailer
define('SMTP_HOST', 'mail.adzdiag.co.uk'); // Change to your SMTP server (Gmail, Outlook, etc.)
define('SMTP_PORT', 465); // 587 for TLS, 465 for SSL
define('SMTP_ENCRYPTION', 'ssl'); // 'ssl' for port 465, 'tls' for port 587
define('SMTP_USERNAME', 'admin@adzdiag.co.uk'); // Your email address
define('SMTP_PASSWORD', 'Kidkurd123'); // old password was Kidkurd123_
define('MAIL_FROM_EMAIL', 'admin@adzdiag.co.uk'); // Email address to send from
define('MAIL_FROM_NAME', 'AdzDIAG System'); // Name to appear as sender
define('MAIL_REPLY_TO', 'your-email@gmail.com'); // Reply-to email address

// Email helper function
function sendEmail($to, $subject, $body, $isHTML = true) {
    try {
        $mail = new PHPMailer(true);
        
        // Enable debug output (remove in production)
        $mail->SMTPDebug = 2; // 0 = off, 1 = client messages, 2 = client and server messages
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        
        // Additional SMTP options
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(MAIL_REPLY_TO, MAIL_FROM_NAME);
        
        // Content
        $mail->isHTML($isHTML);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Additional email utility functions

// Send welcome email to new users
function sendWelcomeEmail($userEmail, $username) {
    $subject = "Welcome to AdzDIAG!";
    $body = "
    <html>
    <body>
        <h2>Welcome to AdzDIAG, {$username}!</h2>
        <p>Thank you for registering with AdzDIAG. Your account has been successfully created.</p>
        <p>You can now:</p>
        <ul>
            <li>Access our key programming tools</li>
            <li>Manage your vehicle keys</li>
            <li>Use our diagnostic features</li>
        </ul>
        <p>If you have any questions, feel free to contact us.</p>
        <p>Best regards,<br>The AdzDIAG Team</p>
    </body>
    </html>
    ";
    
    return sendEmail($userEmail, $subject, $body);
}

// Send password reset email
function sendPasswordResetEmail($userEmail, $resetToken, $username) {
    $resetLink = "http://your-domain.com/reset-password.php?token=" . $resetToken;
    $subject = "Password Reset Request - AdzDIAG";
    $body = "
    <html>
    <body>
        <h2>Password Reset Request</h2>
        <p>Hello {$username},</p>
        <p>We received a request to reset your password. Click the link below to reset your password:</p>
        <p><a href='{$resetLink}' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
        <p>If you didn't request this, you can safely ignore this email.</p>
        <p>This link will expire in 1 hour.</p>
        <p>Best regards,<br>The AdzDIAG Team</p>
    </body>
    </html>
    ";
    
    return sendEmail($userEmail, $subject, $body);
}

// Send notification email to admin
function sendAdminNotification($subject, $message) {
    $adminEmail = MAIL_FROM_EMAIL; // Admin email from config
    $body = "
    <html>
    <body>
        <h2>Admin Notification</h2>
        <p><strong>Subject:</strong> {$subject}</p>
        <p><strong>Message:</strong></p>
        <p>{$message}</p>
        <p>Time: " . date('Y-m-d H:i:s') . "</p>
    </body>
    </html>
    ";
    
    return sendEmail($adminEmail, "Admin Notification: " . $subject, $body);
}

// Send plain text email (alternative to HTML)
function sendPlainTextEmail($to, $subject, $message) {
    return sendEmail($to, $subject, $message, false);
}

// Send account activation email when admin activates a user
function sendAccountActivationEmail($userEmail, $username) {
    $subject = "Account Activated - Welcome to AdzDIAG!";
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #28a745; margin: 0;'>🎉 Account Activated!</h1>
            </div>
            
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                <h2 style='color: #007bff; margin-top: 0;'>Welcome to AdzDIAG, {$username}!</h2>
                <p style='font-size: 16px; margin-bottom: 10px;'>
                    Great news! Your account has been <strong>activated</strong> by an administrator and you now have full access to our platform.
                </p>
            </div>
            
            <div style='margin-bottom: 30px;'>
                <h3 style='color: #007bff;'>What you can do now:</h3>
                <ul style='list-style-type: none; padding: 0;'>
                    <li style='margin-bottom: 10px; padding-left: 25px; position: relative;'>
                        <span style='position: absolute; left: 0; color: #28a745;'>✓</span>
                        Access our comprehensive key programming tools
                    </li>
                    <li style='margin-bottom: 10px; padding-left: 25px; position: relative;'>
                        <span style='position: absolute; left: 0; color: #28a745;'>✓</span>
                        Manage and program vehicle keys
                    </li>
                    <li style='margin-bottom: 10px; padding-left: 25px; position: relative;'>
                        <span style='position: absolute; left: 0; color: #28a745;'>✓</span>
                        Use advanced diagnostic features
                    </li>
                    <li style='margin-bottom: 10px; padding-left: 25px; position: relative;'>
                        <span style='position: absolute; left: 0; color: #28a745;'>✓</span>
                        Access your personal dashboard
                    </li>
                    <li style='margin-bottom: 10px; padding-left: 25px; position: relative;'>
                        <span style='position: absolute; left: 0; color: #28a745;'>✓</span>
                        Get support from our team
                    </li>
                </ul>
            </div>
            
            <div style='background-color: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff; margin-bottom: 20px;'>
                <p style='margin: 0; font-weight: bold;'>
                    🚀 Ready to get started? Log in to your account and explore all the features AdzDIAG has to offer!
                </p>
            </div>
            
            <div style='text-align: center; margin-bottom: 30px;'>
                <a href='#' style='display: inline-block; background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                    Access Your Dashboard
                </a>
            </div>
            
            <div style='border-top: 1px solid #ddd; padding-top: 20px; text-align: center; color: #666;'>
                <p>If you have any questions or need assistance, feel free to contact our support team.</p>
                <p style='margin: 0;'>
                    <strong>Best regards,</strong><br>
                    The AdzDIAG Team
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    try {
        return sendEmail($userEmail, $subject, $body);
    } catch (Exception $e) {
        error_log("Account activation email failed: " . $e->getMessage());
        return "Failed to send activation email: " . $e->getMessage();
    }
}

// Backup method for sending activation emails using OTP infrastructure
function sendActivationEmailViaOTP($userEmail, $username) {
    $subject = "Account Activated - AdzDIAG Access Granted!";
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #28a745; margin: 0;'>✅ Account Activated!</h1>
            </div>
            
            <div style='background-color: #d4edda; padding: 20px; border-radius: 8px; border: 1px solid #c3e6cb; margin-bottom: 20px;'>
                <h2 style='color: #155724; margin-top: 0;'>Hello {$username},</h2>
                <p style='font-size: 16px; margin-bottom: 10px; color: #155724;'>
                    Your AdzDIAG account has been successfully activated! You can now access all platform features.
                </p>
            </div>
            
            <div style='margin-bottom: 20px;'>
                <p>Your account activation includes access to:</p>
                <div style='background-color: #f1f3f4; padding: 15px; border-radius: 5px;'>
                    <ul style='margin: 0; padding-left: 20px;'>
                        <li>Key programming tools and utilities</li>
                        <li>Vehicle diagnostic features</li>
                        <li>Personal dashboard and settings</li>
                        <li>Technical support and resources</li>
                    </ul>
                </div>
            </div>
            
            <div style='text-align: center; margin-bottom: 20px;'>
                <div style='background-color: #007bff; color: white; padding: 15px; border-radius: 8px; display: inline-block;'>
                    <strong>🔑 You're ready to start programming keys!</strong>
                </div>
            </div>
            
            <div style='border-top: 1px solid #ddd; padding-top: 15px; text-align: center; color: #666; font-size: 14px;'>
                <p>This is a backup notification sent via our alternative email system.</p>
                <p style='margin: 0;'>
                    <strong>AdzDIAG Team</strong><br>
                    <em>Your trusted automotive diagnostic partner</em>
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    try {
        return sendEmail($userEmail, $subject, $body);
    } catch (Exception $e) {
        error_log("Backup activation email failed: " . $e->getMessage());
        return "Failed to send backup activation email: " . $e->getMessage();
    }
}

// OTP Functions for Password Change

// Generate and send OTP for password change
function generateAndSendOTP($userId) {
    global $conn;
    
    try {
        // Generate 6-digit OTP as integer
        $otp = mt_rand(100000, 999999);
        
        // Set expiry time (10 minutes from now) - PostgreSQL TIMESTAMPTZ format
        $otpExpiry = date('c', strtotime('+10 minutes')); // ISO 8601 format for PostgreSQL
        
        // Always update/replace user's OTP in database (overwrites any existing OTP)
        $stmt = $conn->prepare("UPDATE users SET otp = ?, otp_date = ? WHERE user_id = ?");
        $stmt->execute([$otp, $otpExpiry, $userId]);
        
        // Get user email
        $stmt = $conn->prepare("SELECT email, username FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Send OTP email
            $subject = "Password Change OTP - AdzDIAG";
            $body = "
            <html>
            <body>
                <h2>Password Change Request</h2>
                <p>Hello {$user['username']},</p>
                <p>You requested to change your password. Please use the following OTP to proceed:</p>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0;'>
                    <h1 style='color: #007bff; font-size: 36px; margin: 0; letter-spacing: 5px;'>{$otp}</h1>
                </div>
                <p><strong>This OTP will expire in 10 minutes.</strong></p>
                <p>If you didn't request this password change, please ignore this email.</p>
                <p>Best regards,<br>The AdzDIAG Team</p>
            </body>
            </html>
            ";
            
            return sendEmail($user['email'], $subject, $body);
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("OTP generation failed: " . $e->getMessage());
        return false;
    }
}

// Verify OTP for password change
function verifyOTP($userId, $inputOtp) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT otp, otp_date FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user || !$user['otp'] || !$user['otp_date']) {
            return false;
        }
        
        // Check if OTP is expired
        if (strtotime($user['otp_date']) < time()) {
            // Clear expired OTP
            clearOTP($userId);
            return false;
        }
        
        // Verify OTP (convert input to integer for comparison)
        if ($user['otp'] == intval($inputOtp)) {
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("OTP verification failed: " . $e->getMessage());
        return false;
    }
}

// Clear OTP after successful verification or expiry
function clearOTP($userId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("UPDATE users SET otp = NULL, otp_date = NULL WHERE user_id = ?");
        $stmt->execute([$userId]);
        return true;
    } catch (Exception $e) {
        error_log("OTP clearing failed: " . $e->getMessage());
        return false;
    }
}

// Change user password (after OTP verification)
function changeUserPassword($userId, $newPassword) {
    global $conn;
    
    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        
        // Clear OTP after successful password change
        clearOTP($userId);
        
        return true;
    } catch (Exception $e) {
        error_log("Password change failed: " . $e->getMessage());
        return false;
    }
}
?>
