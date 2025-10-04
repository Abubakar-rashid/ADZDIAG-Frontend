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
  <title>Ford Radio Codes - AdzDIAG</title>
  <link id="dynamic-manifest" rel="manifest" href="/manifest.webmanifest?theme=light">
  <meta id="dynamic-theme-color" name="theme-color" content="#F6F8FB">
  <link rel="icon" type="image/x-icon" href="/dist/img/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet"/>
  <link href="/dist/css/custom.css" rel="stylesheet"/>
  <style>
    /* Navbar tweaks */
    .navbar { border-bottom: 1px solid #ddd !important; }
    #logo { height: 60px; width: auto; }

    /* Card images */
    .image-box {
      height: 180px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f9f9f9;
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-top: 10px;
    }
    .image-box img {
      max-height: 100%;
      max-width: 100%;
      object-fit: contain;
      transition: transform 0.3s ease;
      cursor: pointer;
    }
    .image-box img:hover { transform: scale(1.05); }
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
              <span class="avatar avatar-sm bg-dark text-white me-2">
                <?php
                if (!empty($_SESSION['username'])) {
                  $parts = explode(" ", $_SESSION['username']);
                  echo strtoupper(substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ""));
                } else {
                  echo "GU";
                }
                ?>
              </span>
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
                <a class="nav-link" href="dashboard.php">Dashboard</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="programkeys.php">Program Keys</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="dropdown">Additional Tools</a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="mgpektron.php">MG Pektron Remote</a>
                  <a class="dropdown-item active" href="fordradiocodes.php">Ford Radio Codes</a>
                  <a class="dropdown-item active" href="dumptools.php">Dump Tools</a>
                </div>
              </li>
              <li class="nav-item"><a class="nav-link" href="licensing.php">Licensing</a></li>
              <li class="nav-item"><a class="nav-link" href="usersetting.php">User Settings</a></li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <!-- Page Wrapper -->
    <div class="page-wrapper">
      <div class="page-header d-print-none">
        <div class="container-xl">
          <h2 class="page-title">Ford Radio Codes</h2>
        </div>
      </div>

      <div class="page-body">
        <div class="container-xl">
          <div class="card">
            <div class="card-body">
              <p>Welcome to the Ford Radio Code page.</p>
              <p>Select the radio type and enter your serial number to calculate your unlock code.</p>
              
              <div class="row">
                <!-- V Series -->
                <div class="col-md-4">
                  <div class="card h-100">
                    <div class="card-body text-center">
                      <p class="card-text">V Series</p>
                      <div class="image-box">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#vSeriesModal">
                          <img src="/vseries.png" alt="V Series">
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- M Series -->
                <div class="col-md-4">
                  <div class="card h-100">
                    <div class="card-body text-center">
                      <p class="card-text">M Series</p>
                      <div class="image-box">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#mSeriesModal">
                          <img src="/mseries.png" alt="M Series">
                        </a>
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

  <!-- V Series Modal -->
  <div class="modal modal-blur fade" id="vSeriesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">V Series Radio Unlock</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label class="form-label">Enter your Radio Serial Number</label>
              <input type="text" class="form-control" id="serialInputV" placeholder="e.g. V123456">
            </div>
            <div class="mb-3">
              <label class="form-label">Your Unlock Code</label>
              <input type="text" class="form-control" id="codeOutputV" placeholder="Result will appear here..." readonly>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="fetchCode('V')">Get Code</button>
        </div>
      </div>
    </div>
  </div>

  <!-- M Series Modal -->
  <div class="modal modal-blur fade" id="mSeriesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">M Series Radio Unlock</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label class="form-label">Enter your Radio Serial Number</label>
              <input type="text" class="form-control" id="serialInputM" placeholder="e.g. M123456">
            </div>
            <div class="mb-3">
              <label class="form-label">Your Unlock Code</label>
              <input type="text" class="form-control" id="codeOutputM" placeholder="Result will appear here..." readonly>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="fetchCode('M')">Get Code</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    function fetchCode(type) {
      let serialInput = document.getElementById('serialInput' + type);
      let codeOutput = document.getElementById('codeOutput' + type);
      let serial = serialInput.value.trim().toUpperCase();

      if (!serial) {
        codeOutput.value = "⚠️ Please enter a serial number!";
        return;
      }

      // Decide endpoint
      let endpoint;
      if (type === 'V') {
        endpoint = 'ford2.php';
        if (serial.startsWith('V')) {
          serial = serial.substring(1); // remove leading V
        }
      } else {
        endpoint = 'ford1.php';
      }

      fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'serial=' + encodeURIComponent(serial)
      })
      .then(res => res.text())
      .then(data => { codeOutput.value = data; })
      .catch(err => { codeOutput.value = "❌ Error fetching code"; });
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js" defer></script>
</body>
</html>
