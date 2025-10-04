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
  <title>MG Pektron Remote Tool - AdzDIAG</title>
  <link id="dynamic-manifest" rel="manifest" href="/manifest.webmanifest?theme=light">
  <meta id="dynamic-theme-color" name="theme-color" content="#F6F8FB">
  <link rel="icon" type="image/x-icon" href="/dist/img/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet"/>
  <link href="/dist/css/custom.css" rel="stylesheet"/>
  <style>
    #logo { height: 60px; width: auto; }
    .navbar { border-bottom: 1px solid #ddd !important; }
    .decoded-box { margin-top:20px; }
    .decoded-box h5 { margin-bottom: 15px; }
    .decoded-box .list-group-item { font-family: monospace; }
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
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="dropdown">Additional Tools</a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="fordradiocodes.php">Ford Radio Codes</a>
                  <a class="dropdown-item" href="dumptools.php">Dump Tool</a>
                  <a class="dropdown-item active" href="mgpektron.php">MG Pektron Remote</a>
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
          <h2 class="page-title">MG Pektron SCU 93CL66A</h2>
        </div>
      </div>

      <!-- Page Body -->
      <div class="page-body">
        <div class="container-xl">
          <div class="card">
            <div class="card-body">

              <p>Load <strong>93CL66A EEPROM File</strong> – the software will detect all remotes coded to the PEKTRON ECU. 
              Pektron remotes are <strong>20 characters long</strong>.</p>

              <p>Enter your 20 characters from the barcode of the key and select <strong>Add Remote</strong>. 
              The software will add the remote to EEPROM. Once you have added the remote, click <strong>Save EEPROM File</strong> 
              and write back to the PEKTRON ECU; the remote will work instantly.</p>

              <p>If you want to delete existing remotes from the PEKTRON unit, select <strong>Delete All Remotes</strong>. 
              You will get a new EEPROM file with all remotes cleared — reload that file and you can add new remotes.</p>

              <div class="mb-3">
                <label class="form-label">Upload EEPROM File (512 bytes only)</label>
                <input type="file" id="eepromFile" class="form-control" accept=".bin,.eep,.hex">
              </div>

              <div id="statusBox" class="alert alert-secondary">No file loaded.</div>

              <!-- Decoded Info -->
              <div id="decodedBox" class="decoded-box" style="display:none;">
                <h5>Decoded Information</h5>
                <ul class="list-group" id="decodedList"></ul>

                <!-- Add Remote + Action Buttons -->
                <div class="mt-3">
                  <label class="form-label">Add Remote Barcode (20 characters)</label>
                  <div class="input-group mb-3">
                    <input type="text" id="remoteInput" class="form-control" maxlength="20" placeholder="Enter 20-char barcode">
                    <button id="addRemoteBtn" class="btn btn-primary">Add Remote</button>
                    <button id="deleteBtn" class="btn btn-danger" disabled>Delete All Remotes</button>
                    <button id="saveBtn" class="btn btn-success" disabled>Save New EEPROM</button>
                  </div>
                  <div id="remoteStatus" class="form-text text-danger mt-1"></div>
                </div>
              </div>

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

  <!-- External decoder -->
  <script src="mg9878.js"></script>

  <script>
    const fileInput = document.getElementById("eepromFile");
    const statusBox = document.getElementById("statusBox");
    const deleteBtn = document.getElementById("deleteBtn");
    const saveBtn = document.getElementById("saveBtn");
    const decodedBox = document.getElementById("decodedBox");
    const decodedList = document.getElementById("decodedList");
    const remoteInput = document.getElementById("remoteInput");
    const addRemoteBtn = document.getElementById("addRemoteBtn");
    const remoteStatus = document.getElementById("remoteStatus");

    let decodedData = null;

    fileInput.addEventListener("change", function() {
      const file = this.files[0];
      if (!file) return;

      if (file.size !== 512) {
        statusBox.className = "alert alert-danger";
        statusBox.textContent = "❌ Invalid file size. Expected 512 bytes.";
        deleteBtn.disabled = true;
        saveBtn.disabled = true;
        decodedBox.style.display = "none";
        return;
      }

      const reader = new FileReader();
      reader.onload = function(e) {
        const buffer = new Uint8Array(e.target.result);
        try {
          decodedData = decodeMgEeprom(buffer); // from mg9878.js
          statusBox.className = "alert alert-success";
          statusBox.textContent = "✅ EEPROM decoded successfully.";

          decodedList.innerHTML = "";
          if (decodedData.VIN) {
            decodedList.innerHTML += `<li class="list-group-item"><strong>VIN:</strong> ${decodedData.VIN}</li>`;
          }
          if (decodedData.EKA) {
            decodedList.innerHTML += `<li class="list-group-item"><strong>EKA:</strong> ${decodedData.EKA}</li>`;
          }

          // Always show 4 remotes, even if blank
          for (let i = 0; i < 4; i++) {
            if (decodedData.Remotes && decodedData.Remotes[i]) {
              decodedList.innerHTML += `<li class="list-group-item"><strong>${decodedData.Remotes[i].label}:</strong> ${decodedData.Remotes[i].data}</li>`;
            } else {
              decodedList.innerHTML += `<li class="list-group-item"><strong>Remote ${i+1}:</strong> (empty)</li>`;
            }
          }

          decodedBox.style.display = "block";
          deleteBtn.disabled = false;
          saveBtn.disabled = false;
        } catch (err) {
          statusBox.className = "alert alert-danger";
          statusBox.textContent = "❌ Error: " + err.message;
          decodedBox.style.display = "none";
          deleteBtn.disabled = true;
          saveBtn.disabled = true;
        }
      };
      reader.readAsArrayBuffer(file);
    });

    // Add Remote handler
    addRemoteBtn.addEventListener("click", () => {
      const val = remoteInput.value.trim();
      if (val.length !== 20) {
        remoteStatus.textContent = "⚠️ Barcode must be exactly 20 characters.";
        return;
      }
      remoteStatus.textContent = "";

      const idx = (decodedData?.Remotes?.length || 0) + 1;
      decodedList.innerHTML += `<li class="list-group-item"><strong>Remote ${idx} (Added):</strong> ${val}</li>`;

      if (decodedData) {
        decodedData.Remotes.push({ label: `Remote ${idx}`, data: val });
      }
      remoteInput.value = "";
      saveBtn.disabled = false;
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js" defer></script>
</body>
</html>
