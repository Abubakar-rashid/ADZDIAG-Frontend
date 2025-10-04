<?php
// Start session and check authentication
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Clear any existing OTP when user first visits the page (fresh start)
if (!isset($_POST['action'])) {
    clearOTP($_SESSION['user_id']);
}

$error_message = "";
$success_message = "";
$step = "request"; // request, verify, change

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'request_otp':
                // Generate and send OTP (always creates a new one)
                if (generateAndSendOTP($_SESSION['user_id'])) {
                    $success_message = "A new OTP has been sent to your email address. Please check your inbox and spam folder.";
                    $step = "verify";
                } else {
                    $error_message = "Failed to send OTP. Please try again.";
                }
                break;
                
            case 'verify_otp':
                $otp = trim($_POST['otp'] ?? '');
                if (empty($otp)) {
                    $error_message = "Please enter the OTP.";
                    $step = "verify";
                } elseif (!preg_match('/^\d{6}$/', $otp)) {
                    $error_message = "OTP must be a 6-digit number.";
                    $step = "verify";
                } elseif (verifyOTP($_SESSION['user_id'], $otp)) {
                    $success_message = "OTP verified successfully. You can now change your password.";
                    $step = "change";
                } else {
                    $error_message = "Invalid or expired OTP. Please try again.";
                    $step = "verify";
                }
                break;
                
            case 'change_password':
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if (empty($newPassword) || empty($confirmPassword)) {
                    $error_message = "Please fill in all fields.";
                    $step = "change";
                } elseif ($newPassword !== $confirmPassword) {
                    $error_message = "Passwords do not match.";
                    $step = "change";
                } elseif (strlen($newPassword) < 6) {
                    $error_message = "Password must be at least 6 characters long.";
                    $step = "change";
                } else {
                    if (changeUserPassword($_SESSION['user_id'], $newPassword)) {
                        $success_message = "Password changed successfully!";
                        $step = "complete";
                    } else {
                        $error_message = "Failed to change password. Please try again.";
                        $step = "change";
                    }
                }
                break;
        }
    }
}

// Since we clear OTP on page load, we always start fresh
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Change Password - AdzDIAG</title>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet"/>
    <style>
        .navbar {
            border-bottom: 1px solid #ddd !important;
        }
        #logo {
            height: 60px;
            width: auto;
        }
        .otp-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 10px;
            font-weight: bold;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 20px;
            font-weight: bold;
            color: #6c757d;
            background-color: #f8f9fa;
        }
        .step.active {
            background-color: #007bff;
            color: white;
        }
        .step.completed {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Top Navbar -->
        <header class="navbar navbar-expand-md d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                    <a href="dashboard.php">
                        <img src="logo.png" width="110" height="32" alt="AdzDIAG" class="navbar-brand-image" id="logo">
                    </a>
                </h1>
                <div class="navbar-nav flex-row order-md-last">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z"/>
                                    <circle cx="12" cy="7" r="4"/>
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                                </svg>
                            </span>
                            <div class="d-none d-xl-block ps-2">
                                <div><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                                <div class="mt-1 small text-muted"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="dashboard.php" class="dropdown-item">Dashboard</a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="admin.php" class="dropdown-item">Admin Panel</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-wrapper">
            <div class="container-xl">
                <!-- Page header -->
                <div class="page-header d-print-none">
                    <div class="row align-items-center">
                        <div class="col">
                            <h2 class="page-title">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z"/>
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <circle cx="12" cy="16" r="1"/>
                                    <path d="M7 11v-4a5 5 0 0 1 10 0v4"/>
                                </svg>
                                Change Password
                            </h2>
                        </div>
                        <div class="col-auto ms-auto d-print-none">
                            <a href="dashboard.php" class="btn btn-outline-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z"/>
                                    <polyline points="15,18 9,12 15,6"/>
                                </svg>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Page body -->
                <div class="page-body">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <!-- Step Indicator -->
                            <div class="step-indicator">
                                <div class="step <?php echo ($step === 'request') ? 'active' : (in_array($step, ['verify', 'change', 'complete']) ? 'completed' : ''); ?>">1. Request OTP</div>
                                <div class="step <?php echo ($step === 'verify') ? 'active' : (in_array($step, ['change', 'complete']) ? 'completed' : ''); ?>">2. Verify OTP</div>
                                <div class="step <?php echo ($step === 'change') ? 'active' : ($step === 'complete' ? 'completed' : ''); ?>">3. New Password</div>
                            </div>

                            <?php if ($error_message): ?>
                                <div class="alert alert-danger" role="alert">
                                    <div class="d-flex">
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z"/>
                                                <circle cx="12" cy="12" r="9"/>
                                                <line x1="12" y1="8" x2="12" y2="12"/>
                                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="alert-title">Error!</h4>
                                            <div class="text-muted"><?php echo htmlspecialchars($error_message); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($success_message): ?>
                                <div class="alert alert-success" role="alert">
                                    <div class="d-flex">
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z"/>
                                                <path d="M5 12l5 5l10 -10"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="alert-title">Success!</h4>
                                            <div class="text-muted"><?php echo htmlspecialchars($success_message); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Step 1: Request OTP -->
                            <?php if ($step === 'request'): ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Request Password Change</h3>
                                    </div>
                                    <div class="card-body">
                                        <p>To change your password, we'll send a verification code to your registered email address.</p>
                                        <p><strong>Email:</strong> <?php 
                                            try {
                                                $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
                                                $stmt->execute([$_SESSION['user_id']]);
                                                $user = $stmt->fetch();
                                                echo htmlspecialchars($user['email']);
                                            } catch (Exception $e) {
                                                echo "Error loading email";
                                            }
                                        ?></p>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="request_otp">
                                            <button type="submit" class="btn btn-primary">Send OTP</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Step 2: Verify OTP -->
                            <?php if ($step === 'verify'): ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Verify OTP</h3>
                                    </div>
                                    <div class="card-body">
                                        <p>Enter the 6-digit verification code sent to your email:</p>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="verify_otp">
                                            <div class="mb-3">
                                                <label class="form-label">OTP Code</label>
                                                <input type="text" name="otp" class="form-control otp-input" maxlength="6" pattern="[0-9]{6}" required autocomplete="off">
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">Verify OTP</button>
                                            </div>
                                        </form>
                                        <form method="POST" class="mt-2">
                                            <input type="hidden" name="action" value="request_otp">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm">Resend OTP</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Step 3: Change Password -->
                            <?php if ($step === 'change'): ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Set New Password</h3>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="change_password">
                                            <div class="mb-3">
                                                <label class="form-label">New Password</label>
                                                <input type="password" name="new_password" class="form-control" required minlength="6">
                                                <div class="form-hint">Password must be at least 6 characters long.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Confirm New Password</label>
                                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                                            </div>
                                            <button type="submit" class="btn btn-success">Change Password</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Step 4: Complete -->
                            <?php if ($step === 'complete'): ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Password Changed Successfully</h3>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-success" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z"/>
                                                <circle cx="12" cy="12" r="9"/>
                                                <path d="M9 12l2 2 4-4"/>
                                            </svg>
                                        </div>
                                        <h4>Your password has been changed successfully!</h4>
                                        <p class="text-muted">You can now use your new password to log in.</p>
                                        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
    <script>
        // Auto-format OTP input
        const otpInput = document.querySelector('.otp-input');
        if (otpInput) {
            otpInput.addEventListener('input', function(e) {
                // Only allow digits
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    </script>
</body>
</html>