<?php
/**
 * Database Setup Script
 * Run this file in your browser to create the vehicle management tables
 * URL: https://www.app.adzdiag.co.uk/setup_vehicle_database.php
 */

// Include database configuration
require_once 'config.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Admin authentication (optional but recommended)
session_start();
$require_admin = true; // Set to false if you want to run without login

if ($require_admin) {
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        die("Error: You must be logged in to run this setup script.");
    }
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        die("Error: You must be an admin to run this setup script.");
    }
}

$results = [];
$errors = [];

try {
    // Start transaction
    $conn->beginTransaction();
    
    // 1. Create vehicle_brands table
    $results[] = "Creating vehicle_brands table...";
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_brands (
        brand_id SERIAL PRIMARY KEY,
        brand_name VARCHAR(100) UNIQUE NOT NULL,
        logo_filename VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    $results[] = "✓ vehicle_brands table created successfully";
    
    // 2. Create vehicle_models table
    $results[] = "Creating vehicle_models table...";
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_models (
        model_id SERIAL PRIMARY KEY,
        brand_id INTEGER NOT NULL REFERENCES vehicle_brands(brand_id) ON DELETE CASCADE,
        model_name VARCHAR(255) NOT NULL,
        model_type VARCHAR(100) NOT NULL,
        procedure_name VARCHAR(255) NOT NULL,
        image_filename VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(brand_id, model_name)
    )";
    $conn->exec($sql);
    $results[] = "✓ vehicle_models table created successfully";
    
    // 3. Create index
    $results[] = "Creating indexes...";
    $sql = "CREATE INDEX IF NOT EXISTS idx_models_brand ON vehicle_models(brand_id)";
    $conn->exec($sql);
    $results[] = "✓ Indexes created successfully";
    
    // 4. Insert brands
    $results[] = "Inserting vehicle brands...";
    $brands = [
        'abarth', 'alfa romeo', 'bmw', 'chevrolet', 'chrysler', 'citroen', 'dacia', 'dodge',
        'fiat', 'ford', 'holden', 'hyundai', 'jaguar', 'jeep', 'kia', 'lancia', 'land rover',
        'maserati', 'mazda', 'mercedes', 'mg', 'nissan', 'peugeot', 'renault', 'smart',
        'suzuki', 'toyota', 'vauxhall'
    ];
    
    $stmt = $conn->prepare("INSERT INTO vehicle_brands (brand_name, logo_filename) VALUES (?, ?) ON CONFLICT (brand_name) DO NOTHING");
    $brands_inserted = 0;
    foreach ($brands as $brand) {
        $logo_filename = $brand . '.png';
        $stmt->execute([$brand, $logo_filename]);
        if ($stmt->rowCount() > 0) {
            $brands_inserted++;
        }
    }
    $results[] = "✓ Inserted $brands_inserted brands";
    
    // 5. Insert example models
    $results[] = "Inserting example vehicle models...";
    
    // Ford models
    $stmt = $conn->prepare("
        INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
        SELECT b.brand_id, ?, ?, ?, ?
        FROM vehicle_brands b WHERE b.brand_name = ?
        ON CONFLICT (brand_id, model_name) DO NOTHING
    ");
    
    $models = [
        ['Focus 2005–2010', 'BLADED', 'focusmk2_bladed', 'ford.png', 'ford'],
        ['Fiesta 2002–2008', 'BLADED', 'fiesta_bladed', 'ford.png', 'ford'],
        ['E90 CAS3', 'Module', 'bmw_cas3', 'bmw.png', 'bmw'],
        ['Corolla 2008–2012', 'Bladed', 'toyota_corolla', 'toyota.png', 'toyota']
    ];
    
    $models_inserted = 0;
    foreach ($models as $model) {
        $stmt->execute($model);
        if ($stmt->rowCount() > 0) {
            $models_inserted++;
        }
    }
    $results[] = "✓ Inserted $models_inserted models";
    
    // Commit transaction
    $conn->commit();
    $results[] = "✓ All changes committed successfully";
    
    // Get counts
    $brand_count = $conn->query("SELECT COUNT(*) FROM vehicle_brands")->fetchColumn();
    $model_count = $conn->query("SELECT COUNT(*) FROM vehicle_models")->fetchColumn();
    
    $results[] = "";
    $results[] = "=== SETUP COMPLETE ===";
    $results[] = "Total Brands: $brand_count";
    $results[] = "Total Models: $model_count";
    $results[] = "";
    $results[] = "You can now access the Vehicle Management panel at:";
    $results[] = "https://www.app.adzdiag.co.uk/vehicle_management.php";
    
} catch (PDOException $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $errors[] = "Database Error: " . $e->getMessage();
    $errors[] = "Code: " . $e->getCode();
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $errors[] = "Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Database Setup</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #1e1e1e;
            color: #00ff00;
        }
        h1 {
            color: #00ff00;
            border-bottom: 2px solid #00ff00;
            padding-bottom: 10px;
        }
        .success {
            background: #001a00;
            border-left: 4px solid #00ff00;
            padding: 15px;
            margin: 10px 0;
        }
        .error {
            background: #1a0000;
            border-left: 4px solid #ff0000;
            padding: 15px;
            margin: 10px 0;
            color: #ff0000;
        }
        .result {
            margin: 5px 0;
            padding: 5px;
        }
        .highlight {
            color: #ffff00;
            font-weight: bold;
        }
        a {
            color: #00ffff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #00ff00;
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .button:hover {
            background: #00cc00;
        }
    </style>
</head>
<body>
    <h1>🚗 Vehicle Management Database Setup</h1>
    
    <?php if (empty($errors)): ?>
        <div class="success">
            <h2>✓ Setup Successful!</h2>
            <?php foreach ($results as $result): ?>
                <div class="result"><?php echo htmlspecialchars($result); ?></div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="vehicle_management.php" class="button">Open Vehicle Management</a>
            <a href="programkeys.php" class="button">Open Program Keys</a>
            <a href="admin.php" class="button">Admin Panel</a>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #002200; border: 1px solid #00ff00;">
            <h3>Next Steps:</h3>
            <ol>
                <li>Go to <a href="vehicle_management.php">Vehicle Management</a></li>
                <li>Add new brands and models</li>
                <li>Upload vehicle images</li>
                <li>Add procedure code to programkeys.php</li>
                <li>Test in <a href="programkeys.php">Program Keys</a></li>
            </ol>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #220000; border: 1px solid #ff0000;">
            <h3>⚠️ Security Notice:</h3>
            <p>For security reasons, you should <strong>DELETE THIS FILE</strong> after setup is complete:</p>
            <p class="highlight">setup_vehicle_database.php</p>
        </div>
        
    <?php else: ?>
        <div class="error">
            <h2>✗ Setup Failed</h2>
            <?php foreach ($errors as $error): ?>
                <div class="result"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #220000; border: 1px solid #ff0000;">
            <h3>Troubleshooting:</h3>
            <ul>
                <li>Check that config.php has correct database credentials</li>
                <li>Verify PostgreSQL server is running and accessible</li>
                <li>Ensure database user has CREATE TABLE permissions</li>
                <li>Check the error message above for specific details</li>
            </ul>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="setup_vehicle_database.php" class="button">Try Again</a>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px; text-align: center; color: #666;">
        <small>AdzDIAG Vehicle Management System © 2025</small>
    </div>
</body>
</html>
