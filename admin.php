<?php
/**
 * Admin Panel - User Management
 * 
 * Features:
 * - Activate/Deactivate user accounts
 * - Delete user accounts (with safeguards)
 * - Send activation emails automatically when activating users
 * 
 * Email Notifications:
 * - When an admin activates a user account, an email is automatically sent to the user
 * - Email includes welcome message and account access information
 * - Email configuration is handled in config.php using PHPMailer
 */

// Start session and check authentication
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Check if user has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect non-admin users to dashboard with error message
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: dashboard.php");
    exit();
}

$users = [];
$error_message = "";
$success_message = "";

// Handle activation/deactivation/deletion requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'activate') {
            // Get user information before activation
            $stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user_info = $stmt->fetch();
            
            // Update user activation status
            $stmt = $conn->prepare("UPDATE users SET is_activated = true WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Send activation email to user
            if ($user_info && $user_info['email']) {
                $email_result = sendAccountActivationEmail($user_info['email'], $user_info['username']);
                if ($email_result === true) {
                    $success_message = "User account has been activated successfully and activation email sent to " . htmlspecialchars($user_info['email']) . ".";
                } else {
                    // Try backup method using OTP infrastructure
                    error_log("Main activation email failed, trying backup method");
                    $backup_result = sendActivationEmailViaOTP($user_info['email'], $user_info['username']);
                    if ($backup_result === true) {
                        $success_message = "User account has been activated successfully and activation email sent to " . htmlspecialchars($user_info['email']) . " (via backup method).";
                    } else {
                        // Show specific error message
                        $error_details = is_string($email_result) ? $email_result : "Unknown email error";
                        $success_message = "User account has been activated successfully, but there was an issue sending the activation email: " . htmlspecialchars($error_details);
                    }
                }
            } else {
                $success_message = "User account has been activated successfully.";
            }
        } elseif ($action === 'deactivate') {
            $stmt = $conn->prepare("UPDATE users SET is_activated = false WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $success_message = "User account has been deactivated successfully.";
        } elseif ($action === 'delete') {
            // Prevent admin from deleting themselves
            if ($user_id == $_SESSION['user_id']) {
                $error_message = "You cannot delete your own account.";
            } else {
                // Check if this is the only admin account
                $stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin' AND user_id != ?");
                $stmt->execute([$user_id]);
                $admin_count = $stmt->fetch()['admin_count'];
                
                // Get user info to check if it's an admin
                $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user_role = $stmt->fetch()['role'];
                
                if ($user_role === 'admin' && $admin_count < 1) {
                    $error_message = "Cannot delete the last admin account. At least one admin account must remain.";
                } else {
                    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $success_message = "User account has been deleted successfully.";
                }
            }
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
        error_log("Admin panel database error: " . $e->getMessage());
    } catch (Exception $e) {
        $error_message = "An error occurred: " . $e->getMessage();
        error_log("Admin panel general error: " . $e->getMessage());
    }
}

// Fetch all users from database
try {
    $stmt = $conn->prepare("SELECT user_id, username, email, role, created_at, is_activated FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error fetching users: " . $e->getMessage();
    error_log("Admin panel error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Admin Panel - User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/icons-sprite.svg" rel="stylesheet"/>
    <style>
        .navbar {
            border-bottom: 1px solid #ddd !important;
        }
        #logo {
            height: 60px;
            width: auto;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .admin-badge {
            background-color: #dc3545;
            color: white;
        }
        .member-badge {
            background-color: #6c757d;
            color: white;
        }
        .user-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .btn-list {
            gap: 0.25rem;
        }
        .btn-list .btn {
            margin-bottom: 0;
        }
        .table td {
            vertical-align: middle;
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
                            <div class="page-pretitle">Admin Panel</div>
                            <h2 class="page-title">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/>
                                </svg>
                                User Management
                            </h2>
                        </div>
                        <div class="col-auto ms-auto d-print-none">
                            <div class="btn-list">
                                <a href="test_email.php" class="btn btn-outline-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z"/>
                                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                                        <polyline points="3,7 12,13 21,7"/>
                                    </svg>
                                    Test Email
                                </a>
                                <a href="dashboard.php" class="btn btn-outline-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z"/>
                                        <polyline points="9,6 15,12 9,18"/>
                                    </svg>
                                    Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page body -->
                <div class="page-body">
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
                                    <?php if (strpos($success_message, 'activation email sent') !== false): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z"/>
                                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                                            <polyline points="3,7 12,13 21,7"/>
                                        </svg>
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z"/>
                                            <path d="M5 12l5 5l10 -10"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h4 class="alert-title">Success!</h4>
                                    <div class="text-muted"><?php echo $success_message; ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <div class="card user-table">
                                <div class="card-header">
                                    <h3 class="card-title">All Users</h3>
                                    <div class="card-actions">
                                        <span class="badge bg-blue"><?php echo count($users); ?> Total Users</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table">
                                        <thead>
                                            <tr>
                                                <th>User ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($users)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z"/>
                                                            <circle cx="12" cy="12" r="9"/>
                                                            <line x1="9" y1="9" x2="9.01" y2="9"/>
                                                            <line x1="15" y1="9" x2="15.01" y2="9"/>
                                                            <path d="M8 13a4 4 0 1 0 8 0h-8z"/>
                                                        </svg>
                                                        <div>No users found</div>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($users as $user): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="text-muted">#<?php echo htmlspecialchars($user['user_id']); ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex py-1 align-items-center">
                                                                <span class="avatar me-2">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"/>
                                                                        <circle cx="12" cy="7" r="4"/>
                                                                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                                                                    </svg>
                                                                </span>
                                                                <div class="flex-fill">
                                                                    <div class="font-weight-medium"><?php echo htmlspecialchars($user['username']); ?></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="text-muted"><?php echo htmlspecialchars($user['email']); ?></div>
                                                        </td>
                                                        <td>
                                                            <?php if ($user['role'] === 'admin'): ?>
                                                                <span class="badge admin-badge role-badge">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"/>
                                                                        <path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/>
                                                                    </svg>
                                                                    Admin
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge member-badge role-badge">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"/>
                                                                        <circle cx="12" cy="7" r="4"/>
                                                                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                                                                    </svg>
                                                                    <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($user['is_activated']): ?>
                                                                <span class="badge bg-success">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"/>
                                                                        <path d="M5 12l5 5l10 -10"/>
                                                                    </svg>
                                                                    Active
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger" title="User will receive an activation email when activated">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"/>
                                                                        <circle cx="12" cy="12" r="9"/>
                                                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                                                        <line x1="9" y1="9" x2="15" y2="15"/>
                                                                    </svg>
                                                                    Inactive
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-1" width="12" height="12" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"/>
                                                                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                                                                        <polyline points="3,7 12,13 21,7"/>
                                                                    </svg>
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="text-muted">
                                                                <?php 
                                                                if ($user['created_at']) {
                                                                    $date = new DateTime($user['created_at']);
                                                                    echo $date->format('M d, Y \a\t g:i A');
                                                                } else {
                                                                    echo 'N/A';
                                                                }
                                                                ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list flex-nowrap">
                                                                <?php if ($user['is_activated']): ?>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                                        <input type="hidden" name="action" value="deactivate">
                                                                        <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                                <path stroke="none" d="M0 0h24v24H0z"/>
                                                                                <circle cx="12" cy="12" r="9"/>
                                                                                <line x1="15" y1="9" x2="9" y2="15"/>
                                                                                <line x1="9" y1="9" x2="15" y2="15"/>
                                                                            </svg>
                                                                            Deactivate
                                                                        </button>
                                                                    </form>
                                                                <?php else: ?>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                                        <input type="hidden" name="action" value="activate">
                                                                        <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Are you sure you want to activate this user?\n\nNote: An activation email will be sent to the user.')">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                                <path stroke="none" d="M0 0h24v24H0z"/>
                                                                                <path d="M5 12l5 5l10 -10"/>
                                                                            </svg>
                                                                            Activate & Email
                                                                        </button>
                                                                    </form>
                                                                <?php endif; ?>
                                                                
                                                                <!-- Delete Button (only show if not current user) -->
                                                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                                        <input type="hidden" name="action" value="delete">
                                                                        <button type="submit" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirmDelete('<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>')">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                                <path stroke="none" d="M0 0h24v24H0z"/>
                                                                                <line x1="4" y1="7" x2="20" y2="7"/>
                                                                                <line x1="10" y1="11" x2="10" y2="17"/>
                                                                                <line x1="14" y1="11" x2="14" y2="17"/>
                                                                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                                                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
                                                                            </svg>
                                                                            Delete
                                                                        </button>
                                                                    </form>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
    <script>
        // Enhanced delete confirmation
        function confirmDelete(username, email) {
            const message = `⚠️ DANGER: Permanent User Deletion\n\n` +
                          `You are about to permanently delete:\n` +
                          `Username: ${username}\n` +
                          `Email: ${email}\n\n` +
                          `This action is IRREVERSIBLE and will:\n` +
                          `• Permanently remove the user account\n` +
                          `• Delete all associated user data\n` +
                          `• Cannot be undone\n\n` +
                          `Are you absolutely certain you want to proceed?`;
            
            return confirm(message);
        }
    </script>
</body>
</html>