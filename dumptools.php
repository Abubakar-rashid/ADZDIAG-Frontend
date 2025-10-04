<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Dump Tool - AdzDIAG</title>
  <link id="dynamic-manifest" rel="manifest" href="/manifest.webmanifest?theme=light">
  <meta id="dynamic-theme-color" name="theme-color" content="#F6F8FB">
  <link rel="icon" type="image/x-icon" href="/dist/img/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet"/>
  <link href="/dist/css/custom.css" rel="stylesheet"/>
  <style>
    #logo { height: 60px; width: auto; }
    .navbar { border-bottom: 1px solid #ddd !important; }
  </style>
</head>
<body>
  <div class="page">

    <!-- Top Navbar -->
    <header class="navbar navbar-expand-md d-print-none">
      <div class="container-xl">
        <h1 class="navbar-brand navbar-brand-autodark pe-0 pe-md-3">
          <a href="/"><img src="/logo.png" alt="AdzDIAG" id="logo"></a>
        </h1>
        <div class="navbar-nav flex-row order-md-last">
          <div class="nav-item dropdown">
            <a href="#" class="nav-link d-flex lh-1 text-reset" data-bs-toggle="dropdown">
              <span class="avatar avatar-sm bg-dark text-white me-2">
                <?php
                if (!empty($_SESSION['username'])) {
                  $parts = explode(" ", $_SESSION['username']);
                  echo strtoupper(substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ""));
                } else { echo "GU"; }
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
              <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
              <li class="nav-item"><a class="nav-link" href="programkeys.php">Program Keys</a></li>
              <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="dropdown">Additional Tools</a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="mgpektron.php">MG Pektron Remote</a>
                  <a class="dropdown-item" href="fordradiocodes.php">Ford Radio Codes</a>
                  <a class="dropdown-item active" href="dumptool.php">Dump Tool</a>
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
          <h2 class="page-title">Dump Tool</h2>
        </div>
      </div>

      <!-- Page Body -->
      <div class="page-body">
        <div class="container-xl">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title">Select Vehicle & Unit</h3>
              <p>Select the vehicle brand and then the unit/module available for that brand.</p>

              <form method="post" action="process_dump.php" enctype="multipart/form-data" id="dumpForm">
                <div class="row">
                  <!-- Brand -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Vehicle Brand</label>
                    <select class="form-select" name="brand" id="brandSelect" required>
                      <option value="">-- Select Brand --</option>
                      <option value="ford">Ford</option>
                      <option value="peugeot">Peugeot</option>
                      <option value="renault">Renault</option>
                      <option value="vw">Volkswagen</option>
                      <option value="audi">Audi</option>
                      <option value="bmw">BMW</option>
                      <option value="mercedes">Mercedes</option>
                      <option value="vauxhallopel">Vauxhall/Opel</option>
                    </select>
                  </div>
                  <!-- Unit -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Unit / Module</label>
                    <select class="form-select" name="unit" id="unitSelect" required>
                      <option value="">-- Select Module --</option>
                    </select>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Upload Dump File</label>
                  <input type="file" name="dumpfile" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Upload & Process</button>
              </form>

              <hr class="my-4">
              <h4>Results</h4>
              <div class="alert alert-info" id="resultsBox">Results will be displayed here after processing.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <footer class="footer footer-transparent d-print-none">
        <div class="container-xl text-center">
          <p>© 2025 AdzDIAG. All rights reserved.</p>
        </div>
      </footer>
    </div>
  </div>

  <script>
    // Brand -> Module mapping
    const modulesByBrand = {
      ford: ["BSI", "ECU", "Cluster", "Radio"],
      peugeot: ["BSI", "ECU", "ABS", "Cluster"],
      renault: ["UCH", "ECU", "ABS", "Cluster"],
      vw: ["ECU", "Cluster", "Immo Box", "ABS"],
      audi: ["ECU", "Cluster", "MMI", "ABS"],
      bmw: ["CAS", "ECU", "KOMBI", "FEM"],
      mercedes: ["ECU", "EZS", "Cluster", "Gearbox"],
      vauxhallopel: ["Astra H IPC", "Corsa D IPC", "Astra H BCM", "Astra J BCM"]
    };

    // Map module name -> JS file in /dumpdecoder/
    const moduleToFile = {
      "astra h ipc": "astraH.js",
      "corsa d ipc": "corsaD.js",
      "astra h bcm": "astraHBCM.js",
      "astra j bcm": "astraJBCM.js",
      "bsi": "peugeotBSI.js",
      "uch": "renaultUCH.js"
      // add more mappings here
    };

    // Populate unit dropdown
    document.getElementById("brandSelect").addEventListener("change", function() {
      const brand = this.value;
      const unitSelect = document.getElementById("unitSelect");
      unitSelect.innerHTML = '<option value="">-- Select Module --</option>';
      if (modulesByBrand[brand]) {
        modulesByBrand[brand].forEach(unit => {
          let opt = document.createElement("option");
          opt.value = unit.toLowerCase();
          opt.textContent = unit;
          unitSelect.appendChild(opt);
        });
      }
    });

    // When a module is selected, load its decoder script dynamically
    document.getElementById("unitSelect").addEventListener("change", function() {
      const moduleName = this.value.toLowerCase();
      const resultsBox = document.getElementById("resultsBox");

      if (moduleToFile[moduleName]) {
        const filename = "/dumpdecoder/" + moduleToFile[moduleName];

        // Load JS dynamically
        const script = document.createElement("script");
        script.src = filename;
        script.onload = function() {
          // convention: function name = <modulename without spaces>Decode
          const fnName = moduleName.replace(/\s+/g, '') + "Decode";
          if (typeof window[fnName] === "function") {
            resultsBox.innerHTML = "Running decoder for " + moduleName + "...";
            window[fnName]();
          } else {
            resultsBox.innerHTML = "Decoder script loaded but function not found: " + fnName;
          }
        };
        script.onerror = function() {
          resultsBox.innerHTML = "Decoder script not found: " + filename;
        };
        document.body.appendChild(script);
      } else {
        resultsBox.innerHTML = "No decoder mapped for: " + moduleName;
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js" defer></script>
</body>
</html>
