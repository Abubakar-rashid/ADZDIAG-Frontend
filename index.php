<?php
session_start();

if (isset($_SESSION['username'])) {
    // User is already logged in → send them to dashboard
    header("Location: dashboard.php");
    exit();
} else {
    // User not logged in → send them to login page
    header("Location: login.php");
    exit();
}
?>
<?php
include("config.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']); 

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Incorrect username or password.";
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <title>Login - AdzDIAG</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <style>
      body { background: #f6f8fb; }
      .login-card { max-width: 400px; margin: auto; }
    </style>
  </head>
  <body class="d-flex flex-column">
    <div class="page page-center">
      <div class="container container-tight py-4">
        <div class="text-center mb-4">
          <img src="logo.png" style="max-height: 80px;" alt="AdzDIAG Logo">
        </div>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <div class="card card-md login-card">
          <div class="card-body">
            <h2 class="h2 text-center mb-4">Sign in to your account</h2>
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="username" required>
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
        </div>

      </div>
    </div>
  </body>
</html>
