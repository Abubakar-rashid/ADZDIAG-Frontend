<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Dashboard - AdzDIAG</title>
    <link id="dynamic-manifest" rel="manifest" href="/manifest.webmanifest?theme=light">
    <meta id="dynamic-theme-color" name="theme-color" content="#F6F8FB">
    <link rel="icon" type="image/x-icon" href="/dist/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet"/>
    <link href="/dist/css/custom.css" rel="stylesheet"/>
    <style>
      /* Thinner navbar border */
      .navbar {
        border-bottom: 1px solid #ddd !important;
      }
      /* Logo size */
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

          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="nav-item me-3">
              <a href="admin.php" class="btn btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                  <circle cx="12" cy="7" r="4"/>
                  <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                  <path d="M8 15l2 2l4 -4"/>
                </svg>
                Admin Dashboard
              </a>
            </div>
          <?php endif; ?>

          <div class="nav-item dropdown">
            <a href="#" class="nav-link d-flex lh-1 text-reset" data-bs-toggle="dropdown">
              <span class="avatar avatar-sm bg-primary text-white me-2">AD</span>
              <div class="d-none d-xl-block">
                <div><?php echo $_SESSION['username'] ?? "Guest"; ?></div>
                <div class="mt-1 small text-secondary">Subscriber</div>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
              <a href="change-password.php" class="dropdown-item">Change Password</a>
              <div class="dropdown-divider"></div>
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
              <li class="nav-item active">
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
              <li class="nav-item">
                <a class="nav-link" href="licensing.php">
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
              <div class="page-pretitle">AdzDIAG</div>
              <h2 class="page-title">Dashboard</h2>
            </div>
          </div>
        </div>
      </div>

      <div class="page-body">
        <div class="container-xl">
          <div class="row">
            <div class="card">
              <div class="card-body">
                <h3 class="card-title">Welcome to AdzDIAG! 🚗🔑</h3>
                <div class="alert alert-info mt-3">
                  ℹ <strong>Notice:</strong> A tutorial video will be available soon.
                </div>
                <div class="alert alert-warning mt-3">
                  <strong>Important:</strong> Ensure your vehicle ignition is ON and stable before programming.  
                </div>

                <h4 class="mt-3">Step 1: Activate Your License</h4>
                <p>Go to the <strong>Licensing Page</strong> and redeem your license key to unlock features.</p>

                <h4 class="mt-3">Step 2: Connect Your Device</h4>
                <p>
                  Use your OBD device (e.g. VLinker, ESP32 tool) and connect it to the vehicle.
                </p>

                <h4 class="mt-3">Step 3: Start Programming</h4>
                <p>
                  Once connected, select your vehicle and begin programming keys.
                </p>
              </div>
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
