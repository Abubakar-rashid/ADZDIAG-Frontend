<?php
/**
 * Vehicle Management Panel
 * Manage vehicle brands and models
 */

session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: dashboard.php");
    exit();
}

$success_message = "";
$error_message = "";

// Handle file upload for brand logo
function uploadBrandLogo($file) {
    $target_dir = "vehicleimages/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    // Check if file is an actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ["success" => false, "message" => "File is not an image."];
    }
    
    // Check file size (max 5MB)
    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "Sorry, your file is too large. Max 5MB."];
    }
    
    // Allow only PNG files
    if($imageFileType != "png") {
        return ["success" => false, "message" => "Sorry, only PNG files are allowed."];
    }
    
    // Generate filename from brand name (will be set by caller)
    $filename = basename($file["name"]);
    $target_file = $target_dir . $filename;
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "filename" => $filename];
    } else {
        return ["success" => false, "message" => "Sorry, there was an error uploading your file."];
    }
}

// Handle Add Brand
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_brand'])) {
    $brand_name = strtolower(trim($_POST['brand_name']));
    
    if (empty($brand_name)) {
        $error_message = "Brand name is required.";
    } elseif (!isset($_FILES['brand_logo']) || $_FILES['brand_logo']['error'] == UPLOAD_ERR_NO_FILE) {
        $error_message = "Brand logo is required.";
    } else {
        // Create proper filename
        $logo_filename = str_replace(' ', ' ', $brand_name) . '.png';
        
        // Temporarily rename the uploaded file to match brand name
        $temp_file = $_FILES['brand_logo'];
        $temp_file['name'] = $logo_filename;
        
        $upload_result = uploadBrandLogo($temp_file);
        
        if ($upload_result['success']) {
            try {
                $stmt = $conn->prepare("INSERT INTO vehicle_brands (brand_name, logo_filename) VALUES (?, ?)");
                $stmt->execute([$brand_name, $logo_filename]);
                $success_message = "Brand added successfully!";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'duplicate key') !== false || strpos($e->getMessage(), 'unique') !== false) {
                    $error_message = "Brand already exists.";
                } else {
                    $error_message = "Database error: " . $e->getMessage();
                }
            }
        } else {
            $error_message = $upload_result['message'];
        }
    }
}

// Handle Add Model
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_model'])) {
    $brand_id = (int)$_POST['brand_id'];
    $model_name = trim($_POST['model_name']);
    $model_type = trim($_POST['model_type']);
    $procedure_name = trim($_POST['procedure_name']);
    
    if (empty($brand_id) || empty($model_name) || empty($model_type) || empty($procedure_name)) {
        $error_message = "All fields are required for adding a model.";
    } elseif (!isset($_FILES['model_image']) || $_FILES['model_image']['error'] == UPLOAD_ERR_NO_FILE) {
        $error_message = "Model image is required.";
    } else {
        // Get brand logo filename to use as default
        $stmt = $conn->prepare("SELECT logo_filename FROM vehicle_brands WHERE brand_id = ?");
        $stmt->execute([$brand_id]);
        $brand = $stmt->fetch();
        
        if (!$brand) {
            $error_message = "Invalid brand selected.";
        } else {
            // Upload model image
            $upload_result = uploadBrandLogo($_FILES['model_image']);
            
            if ($upload_result['success']) {
                $image_filename = $upload_result['filename'];
                
                try {
                    $stmt = $conn->prepare("INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$brand_id, $model_name, $model_type, $procedure_name, $image_filename]);
                    $success_message = "Model added successfully!";
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'duplicate key') !== false || strpos($e->getMessage(), 'unique') !== false) {
                        $error_message = "Model already exists for this brand.";
                    } else {
                        $error_message = "Database error: " . $e->getMessage();
                    }
                }
            } else {
                $error_message = $upload_result['message'];
            }
        }
    }
}

// Handle Delete Brand
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_brand'])) {
    $brand_id = (int)$_POST['brand_id'];
    
    try {
        // Get brand info before deletion
        $stmt = $conn->prepare("SELECT logo_filename FROM vehicle_brands WHERE brand_id = ?");
        $stmt->execute([$brand_id]);
        $brand = $stmt->fetch();
        
        // Delete from database (models will be deleted via CASCADE)
        $stmt = $conn->prepare("DELETE FROM vehicle_brands WHERE brand_id = ?");
        $stmt->execute([$brand_id]);
        
        $success_message = "Brand and associated models deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error deleting brand: " . $e->getMessage();
    }
}

// Handle Delete Model
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_model'])) {
    $model_id = (int)$_POST['model_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM vehicle_models WHERE model_id = ?");
        $stmt->execute([$model_id]);
        $success_message = "Model deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error deleting model: " . $e->getMessage();
    }
}

// Fetch all brands
$brands = [];
try {
    $stmt = $conn->query("SELECT * FROM vehicle_brands ORDER BY brand_name");
    $brands = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error fetching brands: " . $e->getMessage();
}

// Fetch all models with brand info
$models = [];
try {
    $stmt = $conn->query("
        SELECT m.*, b.brand_name 
        FROM vehicle_models m 
        JOIN vehicle_brands b ON m.brand_id = b.brand_id 
        ORDER BY b.brand_name, m.model_name
    ");
    $models = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error fetching models: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Management - AdzDIAG Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet"/>
    <style>
        #logo { height: 60px; width: auto; }
        .navbar { border-bottom: 1px solid #ddd !important; }
        .brand-logo { width: 60px; height: 60px; object-fit: contain; }
        .model-image { width: 50px; height: 50px; object-fit: contain; }
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
                                } else { echo "AD"; }
                                ?>
                            </span>
                            <div class="d-none d-xl-block">
                                <div><?php echo htmlspecialchars($_SESSION['username'] ?? "Admin"); ?></div>
                                <div class="mt-1 small text-secondary">Administrator</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="admin.php" class="dropdown-item">User Management</a>
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
                            <li class="nav-item"><a class="nav-link" href="admin.php">User Management</a></li>
                            <li class="nav-item active"><a class="nav-link active" href="vehicle_management.php">Vehicle Management</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Add Brand Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Add New Brand</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">Brand Name</label>
                                            <input type="text" name="brand_name" class="form-control" placeholder="e.g., audi, volvo" required>
                                            <small class="form-hint">Enter brand name in lowercase</small>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">Brand Logo (PNG only)</label>
                                            <input type="file" name="brand_logo" class="form-control" accept=".png" required>
                                            <small class="form-hint">Will be saved as brandname.png</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" name="add_brand" class="btn btn-primary w-100">Add Brand</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Add Model Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Add New Model</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Select Brand</label>
                                            <select name="brand_id" class="form-select" required>
                                                <option value="">Choose...</option>
                                                <?php foreach ($brands as $brand): ?>
                                                    <option value="<?php echo $brand['brand_id']; ?>">
                                                        <?php echo htmlspecialchars(ucwords($brand['brand_name'])); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Model Name</label>
                                            <input type="text" name="model_name" class="form-control" placeholder="e.g., Focus 2005–2010" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">Model Type</label>
                                            <input type="text" name="model_type" class="form-control" placeholder="e.g., BLADED" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">Procedure Name</label>
                                            <input type="text" name="procedure_name" class="form-control" placeholder="e.g., focus_bladed" required>
                                            <small class="form-hint">JavaScript function name</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">Model Image (PNG)</label>
                                            <input type="file" name="model_image" class="form-control" accept=".png" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="add_model" class="btn btn-success">Add Model</button>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Brands -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Existing Brands (<?php echo count($brands); ?>)</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Logo</th>
                                            <th>Brand Name</th>
                                            <th>Logo Filename</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($brands as $brand): ?>
                                            <tr>
                                                <td>
                                                    <img src="/vehicleimages/<?php echo htmlspecialchars($brand['logo_filename']); ?>" 
                                                         alt="<?php echo htmlspecialchars($brand['brand_name']); ?>" 
                                                         class="brand-logo">
                                                </td>
                                                <td><?php echo htmlspecialchars(ucwords($brand['brand_name'])); ?></td>
                                                <td><?php echo htmlspecialchars($brand['logo_filename']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($brand['created_at'])); ?></td>
                                                <td>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this brand and all its models?');">
                                                        <input type="hidden" name="brand_id" value="<?php echo $brand['brand_id']; ?>">
                                                        <button type="submit" name="delete_brand" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Models -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Existing Models (<?php echo count($models); ?>)</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Brand</th>
                                            <th>Model Name</th>
                                            <th>Type</th>
                                            <th>Procedure</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($models as $model): ?>
                                            <tr>
                                                <td>
                                                    <img src="/vehicleimages/<?php echo htmlspecialchars($model['image_filename']); ?>" 
                                                         alt="<?php echo htmlspecialchars($model['model_name']); ?>" 
                                                         class="model-image">
                                                </td>
                                                <td><?php echo htmlspecialchars(ucwords($model['brand_name'])); ?></td>
                                                <td><?php echo htmlspecialchars($model['model_name']); ?></td>
                                                <td><span class="badge bg-blue"><?php echo htmlspecialchars($model['model_type']); ?></span></td>
                                                <td><code><?php echo htmlspecialchars($model['procedure_name']); ?></code></td>
                                                <td><?php echo date('M d, Y', strtotime($model['created_at'])); ?></td>
                                                <td>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this model?');">
                                                        <input type="hidden" name="model_id" value="<?php echo $model['model_id']; ?>">
                                                        <button type="submit" name="delete_model" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
</body>
</html>
