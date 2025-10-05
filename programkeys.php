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
  <title>Program Keys - AdzDIAG</title>
  <link id="dynamic-manifest" rel="manifest" href="/manifest.webmanifest?theme=light">
  <meta id="dynamic-theme-color" name="theme-color" content="#F6F8FB">
  <link rel="icon" type="image/x-icon" href="/dist/img/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet"/>
  <style>
    #logo { height: 60px; width: auto; }
    .navbar { border-bottom: 1px solid #ddd !important; }
    .status-disconnected { color: red; font-weight: bold; }
    .status-connected { color: green; font-weight: bold; }
    .hidden { display: none; }
    .workspace { display: flex; gap: 20px; margin-top: 20px; }
    #connectionLogs {
      flex: 3; height: 400px; background: #f8f9fa;
      border: 1px solid #ddd; border-radius: 6px;
      padding: 10px; font-family: monospace; font-size: 14px;
      overflow-y: auto; white-space: pre-wrap;
    }
    .controls { flex: 1; display: flex; flex-direction: column; gap: 10px; }
    .controls button { padding: 6px 10px; font-size: 14px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px; margin-top: 20px; }
    .box {
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px;
      transition: transform 0.3s ease; cursor: pointer; padding: 10px; text-align: center;
    }
    .box img { max-height: 80px; max-width: 90%; margin-bottom: 10px; object-fit: contain; }
    .box:hover { transform: scale(1.05); border-color: #007bff; }
    .model-text { font-weight: bold; }
    .model-sub { font-size: 0.9rem; color: #555; }
  </style>
</head>
<body>
  <div class="page">

    <!-- Navbar -->
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
                <div class="mt-1">Interface Status: <span id="interfaceStatus" class="status-disconnected">Disconnected</span></div>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
              <a href="logout.php" class="dropdown-item">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Menu -->
    <header class="navbar-expand-md">
      <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
          <div class="container-xl">
            <ul class="navbar-nav mx-auto">
              <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
              <li class="nav-item active"><a class="nav-link active" href="programkeys.php">Program Keys</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Additional Tools</a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="mgpektron.php">MG Pektron Remote</a>
                  <a class="dropdown-item" href="fordradiocodes.php">Ford Radio Codes</a>
                  <a class="dropdown-item" href="dumptools.php">Dump Tool</a>
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
      <div class="page-body">
        <div class="container-xl">
          <div class="card">
            <div class="card-body">

              <!-- Step 1: Brand selection -->
              <div id="brandSelection">
                <h3 class="card-title">Select a Vehicle Brand</h3>
                <div class="grid" id="brandGrid"></div>
              </div>

              <!-- Step 2: Models -->
              <div id="modelSelection" class="hidden">
                <button class="btn btn-secondary mb-3" onclick="goBack()">← Back to Brands</button>
                <h3 class="card-title" id="modelsTitle"></h3>
                <div class="grid" id="modelsGrid"></div>
              </div>

              <!-- Step 3: Vehicle Connection -->
              <div id="vehicleActions" class="hidden">
                <button class="btn btn-secondary mb-3" onclick="goBackToModels()">← Back to Models</button>
                <div class="workspace">
                  <div id="connectionLogs">[ Waiting for connection... ]</div>
                  <div class="controls">
                    <button id="connectBtn" class="btn btn-primary" onclick="connectToVehicle()">Connect to Vehicle</button>
                    <button id="addKeyBtn" class="btn btn-success" disabled>Add Key</button>
                    <button id="deleteKeysBtn" class="btn btn-danger" disabled>Delete Keys</button>
                    <button id="disconnectBtn" class="btn btn-warning" onclick="disconnectVehicle()" disabled>Disconnect</button>
                  </div>
                </div>
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

  <script>
    // ===== Vehicle Data (loaded from database) =====
    let allBrands = [];
    let brandModels = {};
    let brandsData = []; // Store full brand data including logos

    // Load vehicle data from API
    async function loadVehicleData() {
      try {
        const response = await fetch('api_vehicles.php');
        const data = await response.json();
        
        if (data.success) {
          // Store brands data
          brandsData = data.brands;
          allBrands = data.brands.map(b => b.name);
          brandModels = data.models;
          
          // Build the brand grid
          buildBrandGrid();
        } else {
          console.error('Error loading vehicle data:', data.error);
          document.getElementById('brandGrid').innerHTML = '<p class="text-danger">Error loading vehicle data. Please refresh the page.</p>';
        }
      } catch (error) {
        console.error('Error fetching vehicle data:', error);
        document.getElementById('brandGrid').innerHTML = '<p class="text-danger">Error connecting to server. Please refresh the page.</p>';
      }
    }

    // ===== Command helper =====
    async function sendCommand(writer, logs, cmd, delay = 300) {
      logs.textContent += `\nTX: ${cmd}`;
      await writer.write(new TextEncoder().encode(cmd + "\r"));
      await new Promise(r => setTimeout(r, delay));
    }

    // ===== Procedures =====
    const procedures = {
      focusmk2_bladed: async (writer, reader, logs) => {
        logs.textContent += "\n[ 🚗 Initialising ELM327... ]";
        await sendCommand(writer, logs, "ATZ", 3000);
        await sendCommand(writer, logs, "ATE0", 200);
        await sendCommand(writer, logs, "ATL0", 200);
        await sendCommand(writer, logs, "ATS0", 200);
        await sendCommand(writer, logs, "ATH1", 200);
        await sendCommand(writer, logs, "ATSP6", 200);
        await sendCommand(writer, logs, "ATSH726", 200);
        await sendCommand(writer, logs, "ATCRA72E", 200);
        logs.textContent += "\n✅ ELM327 init complete";

        logs.textContent += "\n[ 🚀 Reading ECU info... ]";
        await sendCommand(writer, logs, "10 01", 300);
        await sendCommand(writer, logs, "22 F1 90", 300);
        await sendCommand(writer, logs, "22 F1 13", 300);
        logs.textContent += "\n✅ Sequence complete";
      },
      fiesta_bladed: async (writer, reader, logs) => {
        logs.textContent += "\n[ 🚀 Fiesta procedure placeholder ]";
      },
      bmw_cas3: async (writer, reader, logs) => {
        logs.textContent += "\n[ 🚀 BMW CAS3 procedure placeholder ]";
      },
      toyota_corolla: async (writer, reader, logs) => {
        logs.textContent += "\n[ 🚀 Toyota Corolla procedure placeholder ]";
      }
    };

    // ===== UI handling =====
    function showModels(brand) {
      document.getElementById('brandSelection').classList.add('hidden');
      document.getElementById('modelSelection').classList.remove('hidden');
      const title = document.getElementById('modelsTitle');
      const grid = document.getElementById('modelsGrid');
      title.innerText = brand.toUpperCase() + " Models";
      grid.innerHTML = "";
      if (brandModels[brand]) {
        brandModels[brand].forEach(model => {
          let div = document.createElement("div");
          div.className = "box";
          div.onclick = () => showVehicleActions(model.procedure);
          div.innerHTML = `
            <img src="${model.img}" alt="${model.name}">
            <div class="model-text">${model.name}</div>
            <div class="model-sub">${model.type}</div>
          `;
          grid.appendChild(div);
        });
      } else {
        grid.innerHTML = "<p>No models added yet.</p>";
      }
    }

    function goBack() {
      document.getElementById('modelSelection').classList.add('hidden');
      document.getElementById('brandSelection').classList.remove('hidden');
    }

    let currentProcedure = null;
    function showVehicleActions(procName) {
      document.getElementById('modelSelection').classList.add('hidden');
      document.getElementById('vehicleActions').classList.remove('hidden');
      document.getElementById('connectionLogs').textContent = "[ Waiting for connection... ]";
      currentProcedure = procedures[procName] || null;
    }

    function goBackToModels() {
      document.getElementById('vehicleActions').classList.add('hidden');
      document.getElementById('modelSelection').classList.remove('hidden');
    }

    // ===== Web Serial =====
    let port = null, writer, reader, isConnected = false;

    async function connectToVehicle() {
      const logs = document.getElementById("connectionLogs");
      const status = document.getElementById("interfaceStatus");
      const addKeyBtn = document.getElementById("addKeyBtn");
      const deleteKeysBtn = document.getElementById("deleteKeysBtn");
      const disconnectBtn = document.getElementById("disconnectBtn");

      try {
        if (!port) {
          port = await navigator.serial.requestPort();
          await port.open({ baudRate: 115200 });
          writer = port.writable.getWriter();
          reader = port.readable.getReader();
          logs.textContent += "\n[ ✅ Interface connected ]";
          status.textContent = "Connected"; status.className = "status-connected";
          isConnected = true;
          addKeyBtn.disabled = false; deleteKeysBtn.disabled = false; disconnectBtn.disabled = false;
          readLoop();

          // run init + DID automatically
          if (currentProcedure) {
            logs.textContent += "\n[ 🚀 Running init + DID sequence... ]";
            await currentProcedure(writer, reader, logs);
          }
        }
      } catch (err) { logs.textContent += "\n❌ Error: " + err.message; }
    }

    async function readLoop() {
      const logs = document.getElementById("connectionLogs");
      try {
        while (port && port.readable) {
          const { value, done } = await reader.read();
          if (done) break;
          if (value) {
            logs.textContent += "\n" + new TextDecoder().decode(value);
            logs.scrollTop = logs.scrollHeight;
          }
        }
      } catch (err) { logs.textContent += "\n❌ Read error: " + err.message; }
    }

    async function disconnectVehicle() {
      const logs = document.getElementById("connectionLogs");
      const status = document.getElementById("interfaceStatus");
      try {
        if (reader) { await reader.releaseLock(); reader = null; }
        if (writer) { await writer.releaseLock(); writer = null; }
        if (port) { await port.close(); port = null; }
        logs.textContent += "\n[ 🔌 Interface disconnected ]";
        status.textContent = "Disconnected"; status.className = "status-disconnected";
        isConnected = false;
      } catch (err) { logs.textContent += "\n❌ Error: " + err.message; }
    }

    // ===== Buttons =====
    document.getElementById("addKeyBtn").addEventListener("click", () => {
      const logs = document.getElementById("connectionLogs");
      if (currentProcedure) {
        logs.textContent += "\n[ 🚀 Running Add Key procedure... ]";
        currentProcedure(writer, reader, logs);
      } else {
        logs.textContent += "\n❌ No procedure loaded.";
      }
    });

    document.getElementById("deleteKeysBtn").addEventListener("click", () => {
      const logs = document.getElementById("connectionLogs");
      logs.textContent += "\n❌ Delete Keys procedure not yet implemented.";
    });

    // ===== Build brand grid =====
    function buildBrandGrid() {
      const brandGrid = document.getElementById('brandGrid');
      brandGrid.innerHTML = ''; // Clear existing content
      
      brandsData.forEach(brand => {
        let div = document.createElement("div");
        div.className = "box";
        div.onclick = () => showModels(brand.name);
        div.innerHTML = `<img src="${brand.logo}" alt="${brand.name}">`;
        brandGrid.appendChild(div);
      });
    }

    // ===== Load data on page load =====
    window.onload = () => {
      loadVehicleData();
    };
  </script>
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js" defer></script>
</body>
</html>
