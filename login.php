<?php
// Start output buffering to catch any accidental output
ob_start();
session_start();
require_once 'config.php';

$error_message = "";
$debug_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = "⚠ Please enter both username and password.";
    } else {
        try {
            // ✅ Fetch role and activation status as well
            $stmt = $conn->prepare("SELECT user_id, username, email, password, role, is_activated FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if account is activated
                if (!$user['is_activated']) {
                    $error_message = "⚠ Your account is not yet activated. Please wait for activation by the admin.";
                } else {
                    // Login successful - clear any output buffer
                    ob_clean();
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['logged_in'] = true;

                    // ✅ Save role in session
                    $_SESSION['role'] = $user['role'];
                    
                    // Multiple redirect methods for better compatibility
                    if (headers_sent($filename, $linenum)) {
                        $error_message = "⚠ Headers already sent in $filename on line $linenum. Please check for output before this script.";
                    } else {
                        // Try absolute URL first
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                        $host = $_SERVER['HTTP_HOST'];
                        $script_path = dirname($_SERVER['SCRIPT_NAME']);
                        
                        // All users go to the regular dashboard
                        $redirect_url = $protocol . '://' . $host . $script_path . '/dashboard.php';
                        
                        header("Location: " . $redirect_url, true, 302);
                        header("Cache-Control: no-cache, must-revalidate");
                        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                        
                        // JavaScript fallback if header redirect fails
                        echo "<script>window.location.href = '" . htmlspecialchars($redirect_url) . "';</script>";
                        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($redirect_url) . "'></noscript>";
                        exit();
                    }
                }
            } else {
                $error_message = "⚠ Invalid username or password.";
                // Debug info for troubleshooting
                if ($user) {
                    $debug_message = "User found but password verification failed.";
                } else {
                    $debug_message = "User not found in database.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "⚠ Login failed. Please try again later.";
            // Log the error for debugging
            error_log("Login error: " . $e->getMessage());
            $debug_message = "Database error occurred.";
        }
    }
}

// End output buffering
ob_end_flush();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login - AdzDIAG</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
  <style>
    @import url('https://rsms.me/inter/inter.css');
    :root {
      --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, sans-serif;
    }
    body { font-feature-settings: "cv03", "cv04", "cv11"; }

    .login-logo {
      width: 250px;
      height: auto;
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
      background: transparent !important;
    }
  </style>
</head>
<body class="d-flex flex-column">
  <div class="page page-center">
    <div class="container container-tight py-4">
      <div class="text-center mb-4">
        <img src="/logo2.png" alt="AdzDIAG" class="login-logo">
      </div>
      <div class="card card-md">
        <div class="card-body">
          <h2 class="h2 text-center mb-4">Sign in to your account</h2>
          
          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
              <?= $error_message ?>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($debug_message) && isset($_GET['debug'])): ?>
            <div class="alert alert-info">
              Debug: <?= $debug_message ?>
            </div>
          <?php endif; ?>
          
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <div class="form-footer">
              <button type="submit" class="btn btn-primary w-100">Sign in</button>
            </div>
          </form>
        </div>
      <div class="text-center text-muted mt-3">
        Not Registered? <a href="registrationpage.php">Sign Up Here!</a>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
</body>
</html>
