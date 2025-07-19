<?php
header('Content-Type: application/json');

// Health check response
$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0.0',
    'checks' => []
];

// Database check
try {
    require_once 'config/database.php';
    $result = mysqli_query($conn, "SELECT 1");
    if ($result) {
        $health['checks']['database'] = 'healthy';
    } else {
        $health['checks']['database'] = 'unhealthy';
        $health['status'] = 'unhealthy';
    }
} catch (Exception $e) {
    $health['checks']['database'] = 'unhealthy';
    $health['status'] = 'unhealthy';
}

// File system check
$upload_dir = getenv('UPLOAD_PATH') ?: 'uploads/';
if (is_dir($upload_dir) && is_writable($upload_dir)) {
    $health['checks']['filesystem'] = 'healthy';
} else {
    $health['checks']['filesystem'] = 'unhealthy';
    $health['status'] = 'unhealthy';
}

// Session check
if (session_status() === PHP_SESSION_ACTIVE) {
    $health['checks']['sessions'] = 'healthy';
} else {
    $health['checks']['sessions'] = 'unhealthy';
    $health['status'] = 'unhealthy';
}

// Memory usage
$memory_usage = memory_get_usage(true);
$memory_limit = ini_get('memory_limit');
$health['checks']['memory'] = [
    'usage' => $memory_usage,
    'limit' => $memory_limit,
    'status' => $memory_usage < (1024 * 1024 * 128) ? 'healthy' : 'warning' // 128MB threshold
];

// Response code
http_response_code($health['status'] === 'healthy' ? 200 : 503);

echo json_encode($health, JSON_PRETTY_PRINT);
?> 