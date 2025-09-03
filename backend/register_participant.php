<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos JSON inválidos');
    }
    
    // Validate required fields
    $requiredFields = ['documentType', 'documentNumber', 'firstName', 'lastName', 'email', 'phone', 'address'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo {$field} es requerido");
        }
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Formato de email inválido');
    }
    
    // Validate phone number (Colombian format)
    if (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
        throw new Exception('Número de teléfono debe tener 10 dígitos');
    }
    
    // Validate document number
    if (!preg_match('/^[0-9A-Z]{6,15}$/', $data['documentNumber'])) {
        throw new Exception('Número de documento inválido');
    }
    
    // Add metadata
    $participantData = [
        'participantId' => uniqid('NEQUI_', true),
        'registrationTimestamp' => date('Y-m-d H:i:s'),
        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'formType' => 'nequi_registration',
        'status' => 'registered',
        'totalEarnings' => 500, // Welcome bonus
        'completedChallenges' => 0,
        'registrationData' => $data
    ];
    
    // Create participants directory if it doesn't exist
    $participantsDir = __DIR__ . '/participants';
    if (!is_dir($participantsDir)) {
        if (!mkdir($participantsDir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de participantes');
        }
    }
    
    // Save participant data to individual file
    $filename = $participantsDir . '/participant_' . $data['documentNumber'] . '_' . date('Y-m-d_H-i-s') . '.json';
    if (file_put_contents($filename, json_encode($participantData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        throw new Exception('Error al guardar los datos del participante');
    }
    
    // Log registration to main log file
    $logFile = $participantsDir . '/registrations_log.json';
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'participantId' => $participantData['participantId'],
        'documentNumber' => $data['documentNumber'],
        'name' => $data['firstName'] . ' ' . $data['lastName'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'location' => [
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'accuracy' => $data['accuracy'] ?? null
        ],
        'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ];
    
    // Read existing log or create new array
    $existingLog = [];
    if (file_exists($logFile)) {
        $existingLogContent = file_get_contents($logFile);
        $existingLog = json_decode($existingLogContent, true) ?: [];
    }
    
    // Add new entry
    $existingLog[] = $logEntry;
    
    // Save updated log
    if (file_put_contents($logFile, json_encode($existingLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        // Log error but don't fail the registration
        error_log('Failed to update registrations log');
    }
    
    // Create challenges directory for this participant
    $challengesDir = $participantsDir . '/challenges_' . $data['documentNumber'];
    if (!is_dir($challengesDir)) {
        mkdir($challengesDir, 0755, true);
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Registro exitoso',
        'participantId' => $participantData['participantId'],
        'welcomeBonus' => 500,
        'data' => [
            'name' => $data['firstName'] . ' ' . $data['lastName'],
            'email' => $data['email'],
            'registrationDate' => $participantData['registrationTimestamp']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => 'registration_error'
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => 'server_error'
    ]);
}
?>