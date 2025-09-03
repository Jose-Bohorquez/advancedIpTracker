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
    // Validate required POST data
    if (!isset($_POST['challengeId']) || !isset($_POST['userId']) || !isset($_POST['challengeData'])) {
        throw new Exception('Datos de reto incompletos');
    }
    
    // Validate file upload
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir la imagen');
    }
    
    $challengeId = intval($_POST['challengeId']);
    $userId = $_POST['userId'];
    $challengeData = json_decode($_POST['challengeData'], true);
    $uploadedFile = $_FILES['photo'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes.');
    }
    
    // Validate file size (max 10MB)
    if ($uploadedFile['size'] > 10 * 1024 * 1024) {
        throw new Exception('El archivo es demasiado grande. Máximo 10MB.');
    }
    
    // Create directories
    $participantsDir = __DIR__ . '/participants';
    $challengesDir = $participantsDir . '/challenges_' . $userId;
    $photosDir = $challengesDir . '/photos';
    
    if (!is_dir($photosDir)) {
        if (!mkdir($photosDir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de fotos');
        }
    }
    
    // Generate unique filename
    $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
    $filename = 'challenge_' . $challengeId . '_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $extension;
    $filepath = $photosDir . '/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($uploadedFile['tmp_name'], $filepath)) {
        throw new Exception('Error al guardar la imagen');
    }
    
    // Create challenge completion record
    $completionData = [
        'challengeId' => $challengeId,
        'userId' => $userId,
        'challengeInfo' => $challengeData,
        'completionTimestamp' => date('Y-m-d H:i:s'),
        'photoFilename' => $filename,
        'photoPath' => $filepath,
        'photoSize' => $uploadedFile['size'],
        'photoType' => $mimeType,
        'originalFilename' => $uploadedFile['name'],
        'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'reward' => $challengeData['reward'] ?? 500,
        'category' => $challengeData['category'] ?? 'general'
    ];
    
    // Save challenge completion data
    $completionFile = $challengesDir . '/challenge_' . $challengeId . '_completion.json';
    if (file_put_contents($completionFile, json_encode($completionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        throw new Exception('Error al guardar los datos del reto');
    }
    
    // Update participant's progress
    updateParticipantProgress($userId, $challengeId, $challengeData['reward'] ?? 500);
    
    // Log challenge completion
    logChallengeCompletion($completionData);
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Reto completado exitosamente',
        'challengeId' => $challengeId,
        'reward' => $challengeData['reward'] ?? 500,
        'filename' => $filename,
        'completionTime' => $completionData['completionTimestamp']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => 'upload_error'
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => 'server_error'
    ]);
}

/**
 * Update participant's progress and earnings
 */
function updateParticipantProgress($userId, $challengeId, $reward) {
    $participantsDir = __DIR__ . '/participants';
    $progressFile = $participantsDir . '/progress_' . $userId . '.json';
    
    // Read existing progress or create new
    $progress = [];
    if (file_exists($progressFile)) {
        $existingProgress = file_get_contents($progressFile);
        $progress = json_decode($existingProgress, true) ?: [];
    }
    
    // Initialize if empty
    if (empty($progress)) {
        $progress = [
            'userId' => $userId,
            'totalEarnings' => 500, // Welcome bonus
            'completedChallenges' => [],
            'lastUpdate' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ];
    }
    
    // Add challenge completion
    $progress['completedChallenges'][] = [
        'challengeId' => $challengeId,
        'reward' => $reward,
        'completedAt' => date('Y-m-d H:i:s')
    ];
    
    // Update totals
    $progress['totalEarnings'] += $reward;
    $progress['lastUpdate'] = date('Y-m-d H:i:s');
    
    // Check if all challenges completed
    if (count($progress['completedChallenges']) >= 10) {
        $progress['status'] = 'completed';
        $progress['completionDate'] = date('Y-m-d H:i:s');
    }
    
    // Save updated progress
    file_put_contents($progressFile, json_encode($progress, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Log challenge completion to main log
 */
function logChallengeCompletion($completionData) {
    $participantsDir = __DIR__ . '/participants';
    $logFile = $participantsDir . '/challenges_log.json';
    
    // Read existing log or create new array
    $existingLog = [];
    if (file_exists($logFile)) {
        $existingLogContent = file_get_contents($logFile);
        $existingLog = json_decode($existingLogContent, true) ?: [];
    }
    
    // Create log entry
    $logEntry = [
        'timestamp' => $completionData['completionTimestamp'],
        'userId' => $completionData['userId'],
        'challengeId' => $completionData['challengeId'],
        'challengeTitle' => $completionData['challengeInfo']['title'] ?? 'Unknown',
        'category' => $completionData['category'],
        'reward' => $completionData['reward'],
        'photoFilename' => $completionData['photoFilename'],
        'photoSize' => $completionData['photoSize'],
        'ipAddress' => $completionData['ipAddress'],
        'userAgent' => $completionData['userAgent']
    ];
    
    // Add new entry
    $existingLog[] = $logEntry;
    
    // Save updated log
    file_put_contents($logFile, json_encode($existingLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>