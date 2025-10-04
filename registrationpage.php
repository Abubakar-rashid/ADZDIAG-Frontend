<?php
session_start();
require_once 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm  = $_POST["confirm"];

    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $message = "⚠️ All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "⚠️ Invalid email address.";
    } elseif ($password !== $confirm) {
        $message = "⚠️ Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $message = "⚠️ Password must be at least 8 characters.";
    } else {
        try {
            // Check if username already exists
            $checkUsername = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $checkUsername->execute([$username]);
            
            if ($checkUsername->rowCount() > 0) {
                $message = "⚠️ Username already exists. Please choose a different username.";
            } else {
                // Check if email already exists
                $checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                $checkEmail->execute([$email]);
                
                if ($checkEmail->rowCount() > 0) {
                    $message = "⚠️ Email already registered. Please use a different email.";
                } else {
                    // Hash the password for security
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user into database
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $email, $hashedPassword]);
                    
                    $message = "✅ Registration successful! You can now <a href='login.php'>login</a>.";
                }
            }
        } catch (PDOException $e) {
            $message = "⚠️ Registration failed. Please try again later.";
            // Log the error for debugging (in production, log to file instead)
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Register - AdzDIAG</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
  <style>
    @import url('https://rsms.me/inter/inter.css');
    :root {
      --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, sans-serif;
    }
    body { font-feature-settings: "cv03", "cv04", "cv11"; }

    .register-logo {
      width: 200px;
      height: auto;
      margin-top: 60px;   /* ⬅️ pushes logo further down */
      margin-bottom: 30px;
    }
  </style>
</head>
<body class="d-flex flex-column">
  <div class="page page-center">
    <div class="container container-tight py-4">
      <div class="text-center">
        <img src="/logo2.png" alt="AdzDIAG" class="register-logo">
      </div>
      <div class="card card-md">
        <div class="card-body">
          <h2 class="h2 text-center mb-4">Create an Account</h2>

          <?php if (!empty($message)): ?>
            <div class="alert <?= strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger' ?>">
              <?= $message ?>
            </div>
          <?php endif; ?>

          <form method="post">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" placeholder="Enter a username" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email address</label>
              <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" placeholder="********" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm" class="form-control" placeholder="********" required>
            </div>
            <div class="form-footer">
              <button type="submit" class="btn btn-primary w-100">Register</button>
            </div>
          </form>
        </div>
      </div>
      <div class="text-center text-muted mt-3">
        Already have an account? <a href="login.php">Sign in</a>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
</body>
</html>
