<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obtener timestamp del servidor con información adicional
$serverTime = [
    'timestamp' => date('c'), // ISO 8601 format
    'unix_timestamp' => time(),
    'timezone' => date_default_timezone_get(),
    'offset' => date('P'),
    'dst_active' => date('I') == 1,
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_time' => date('Y-m-d H:i:s'),
        'utc_time' => gmdate('Y-m-d H:i:s')
    ]
];

echo json_encode($serverTime);
?>