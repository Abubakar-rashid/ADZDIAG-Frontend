<?php
/**
 * Vehicle API - Returns vehicle brands and models as JSON
 * Used by programkeys.php to dynamically load vehicle data
 */

session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    // Fetch all brands
    $stmt = $conn->query("SELECT brand_id, brand_name, logo_filename FROM vehicle_brands ORDER BY brand_name");
    $brands = $stmt->fetchAll();
    
    // Fetch all models grouped by brand
    $stmt = $conn->query("
        SELECT 
            m.model_id,
            m.brand_id,
            m.model_name,
            m.model_type,
            m.procedure_name,
            m.image_filename,
            b.brand_name
        FROM vehicle_models m
        JOIN vehicle_brands b ON m.brand_id = b.brand_id
        ORDER BY b.brand_name, m.model_name
    ");
    $all_models = $stmt->fetchAll();
    
    // Group models by brand
    $models_by_brand = [];
    foreach ($all_models as $model) {
        $brand_name = $model['brand_name'];
        if (!isset($models_by_brand[$brand_name])) {
            $models_by_brand[$brand_name] = [];
        }
        $models_by_brand[$brand_name][] = [
            'model_id' => $model['model_id'],
            'name' => $model['model_name'],
            'type' => $model['model_type'],
            'procedure' => $model['procedure_name'],
            'img' => '/vehicleimages/' . $model['image_filename']
        ];
    }
    
    // Format brands for output
    $brands_output = [];
    foreach ($brands as $brand) {
        $brands_output[] = [
            'brand_id' => $brand['brand_id'],
            'name' => $brand['brand_name'],
            'logo' => '/vehicleimages/' . $brand['logo_filename']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'brands' => $brands_output,
        'models' => $models_by_brand
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
