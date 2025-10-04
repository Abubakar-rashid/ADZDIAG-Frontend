<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['username'])) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Licensing - AdzDIAG</title>
  <link id="dynamic-manifest" rel="manifest" href="/manifest.webmanifest?theme=light">
  <meta id="dynamic-theme-color" name="theme-color" content="#F6F8FB">
  <link rel="icon" type="image/x-icon" href="/dist/img/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet"/>
  <link href="/dist/css/custom.css" rel="stylesheet"/>
  <style>
    .navbar {
      border-bottom: 1px solid #ddd !important;
    }
    #logo {
      height: 60px;
      width: auto;
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
          <a href="/">
            <img src="/logo.png" alt="AdzDIAG" id="logo">
          </a>
        </h1>
        <div class="navbar-nav flex-row order-md-last">
          <div class="nav-item dropdown">
            <a href="#" class="nav-link d-flex lh-1 text-reset" data-bs-toggle="dropdown">
              <span class="avatar avatar-sm bg-primary text-white me-2">AD</span>
              <div class="d-none d-xl-block">
                <div><?php echo $_SESSION['username'] ?? "Guest"; ?></div>
                <div class="mt-1 small text-secondary">Subscriber</div>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
              <a href="logout.php" class="dropdown-item">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Menu Bar -->
    <header class="navbar-expand-md">
      <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
          <div class="container-xl">
            <ul class="navbar-nav mx-auto">
              <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                  <span class="nav-link-title">Dashboard</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="programkeys.php">Program Keys</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Additional Tools</a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="mgpektron.php">MG Pektron Remote</a>
                  <a class="dropdown-item" href="fordradiocodes.php">Ford Radio Codes</a>
                <a class="dropdown-item" href="dumptools.php">Dump Tools</a>
                </div>
              </li>
              <li class="nav-item active">
                <a class="nav-link active" href="licensing.php">
                  <span class="nav-link-title">Licensing</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="usersetting.php">
                  <span class="nav-link-title">User Settings</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <!-- Page Wrapper -->
    <div class="page-wrapper">
      <div class="page-header d-print-none">
        <div class="container-xl">
          <div class="row g-2 align-items-center">
            <div class="col">
              <div class="page-pretitle"></div>
              <h2 class="page-title">Licensing</h2>
            </div>
          </div>
        </div>
      </div>

      <div class="page-body">
        <div class="container-xl">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title">Activate Your License</h3>
              <p>Enter your license key below to activate AdzDIAG features.</p>

              <form method="post" action="activate_license.php">
                <div class="mb-3">
                  <label class="form-label">License Key</label>
                  <input type="text" name="license_key" class="form-control" placeholder="Enter your license key here" required>
                </div>
                <button type="submit" class="btn btn-primary">Activate</button>
              </form>

              <hr class="my-4">

              <h4>Current License Status</h4>
              <div class="alert alert-info mt-2">
                <?php
                // Example: replace with real logic
                echo isset($_SESSION['license_status']) ? $_SESSION['license_status'] : "No license activated yet.";
                ?>
              </div>

              <h4>Manage License</h4>
              <a href="deactivate_license.php" class="btn btn-danger">Deactivate License</a>
            </div>
          </div>
        </div>
      </div>

      <footer class="footer footer-transparent d-print-none">
        <div class="container-xl text-center">
          <p>© 2025 AdzDIAG. All rights reserved.</p>
        </div>
      </footer>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js" defer></script>
</body>
</html>
