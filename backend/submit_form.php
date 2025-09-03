<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $requiredFields = ['documentType', 'documentNumber', 'firstName', 'lastName', 'email', 'phone', 'address'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Add timestamp and additional metadata
    $data['timestamp'] = date('Y-m-d H:i:s');
    $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $data['ip_address_server'] = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $data['form_type'] = 'consulta_beneficio';
    $data['submission_id'] = uniqid('CB_', true);
    
    // Create data directory if it doesn't exist
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Generate filename with timestamp
    $filename = 'consulta_beneficio_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.json';
    $filepath = $dataDir . '/' . $filename;
    
    // Save data to JSON file
    $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($filepath, $jsonData) === false) {
        throw new Exception('Failed to save data');
    }
    
    // Log successful submission
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'submission_id' => $data['submission_id'],
        'document' => $data['documentType'] . ' ' . $data['documentNumber'],
        'name' => $data['firstName'] . ' ' . $data['lastName'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'location' => isset($data['latitude']) ? $data['latitude'] . ',' . $data['longitude'] : 'Not provided',
        'file' => $filename
    ];
    
    $logFile = $dataDir . '/submissions_log.json';
    $existingLog = [];
    if (file_exists($logFile)) {
        $existingLog = json_decode(file_get_contents($logFile), true) ?? [];
    }
    $existingLog[] = $logEntry;
    file_put_contents($logFile, json_encode($existingLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Data submitted successfully',
        'submission_id' => $data['submission_id'],
        'timestamp' => $data['timestamp']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>