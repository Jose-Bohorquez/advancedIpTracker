<?php
/**
 * API Endpoints para consulta y análisis de datos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'advanced-data-processor.php';

// Configuración
define('DATA_DIR', '../data/');
define('LOGS_DIR', 'logs/');

/**
 * Obtener lista de sesiones
 */
function getSessions($limit = 50, $offset = 0) {
    $dataDir = DATA_DIR;
    $sessions = [];
    
    if (!is_dir($dataDir)) {
        return ['sessions' => [], 'total' => 0];
    }
    
    $files = glob($dataDir . '*.json');
    rsort($files); // Más recientes primero
    
    $total = count($files);
    $files = array_slice($files, $offset, $limit);
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data) {
            // Extraer información de ubicación
            $location = 'Desconocido';
            if (isset($data['network']['ip_details']['geolocation'])) {
                $geo = $data['network']['ip_details']['geolocation'];
                $city = $geo['city'] ?? '';
                $country = $geo['country'] ?? '';
                if ($city && $country) {
                    $location = $city . ', ' . $country;
                } elseif ($country) {
                    $location = $country;
                }
            } elseif (isset($data['geolocation']['city'])) {
                $location = $data['geolocation']['city'];
            }
            
            // Verificar si tiene datos avanzados
            $hasAdvancedData = isset($data['advanced_analysis']) || 
                              isset($data['fingerprinting']) || 
                              isset($data['additional_data']);
            
            $sessions[] = [
                'session_id' => $data['session_id'] ?? basename($file, '.json'),
                'timestamp' => $data['timestamp'] ?? 'unknown',
                'client_ip' => $data['network']['client_ip'] ?? 'unknown',
                'user_agent' => $data['browser']['user_agent'] ?? 'unknown',
                'location' => $location,
                'filename' => basename($file),
                'has_advanced_data' => $hasAdvancedData,
                'file_size' => filesize($file),
                'browser_info' => [
                    'browser' => $data['browser']['parsed_ua']['browser'] ?? 'Desconocido',
                    'version' => $data['browser']['parsed_ua']['version'] ?? '',
                    'os' => $data['browser']['parsed_ua']['os'] ?? 'Desconocido',
                    'device' => $data['browser']['parsed_ua']['device'] ?? 'Desconocido'
                ]
            ];
        }
    }
    
    return ['sessions' => $sessions, 'total' => $total];
}

/**
 * Obtener detalles de una sesión específica
 */
function getSessionDetails($sessionId) {
    $dataDir = DATA_DIR;
    
    // Buscar por ID de sesión en los archivos
    $files = glob($dataDir . 'capture_*.json');
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && isset($data['session_id']) && $data['session_id'] === $sessionId) {
            // Agregar metadatos del archivo
            $data['_metadata'] = [
                'file_name' => basename($file),
                'file_size' => filesize($file),
                'file_modified' => date('Y-m-d H:i:s', filemtime($file)),
                'data_structure_version' => '2.0'
            ];
            
            return $data;
        }
    }
    
    // Si no se encuentra por session_id, buscar por nombre de archivo
    $possibleFiles = [
        $dataDir . $sessionId . '.json',
        $dataDir . 'capture_' . $sessionId . '.json'
    ];
    
    foreach ($possibleFiles as $file) {
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $data['_metadata'] = [
                    'file_name' => basename($file),
                    'file_size' => filesize($file),
                    'file_modified' => date('Y-m-d H:i:s', filemtime($file)),
                    'data_structure_version' => '2.0'
                ];
                return $data;
            }
        }
    }
    
    return ['error' => 'Sesión no encontrada'];
}

/**
 * Generar análisis de riesgo para una sesión
 */
function generateRiskAnalysis($sessionId) {
    $sessionData = getSessionDetails($sessionId);
    
    if (isset($sessionData['error'])) {
        return $sessionData;
    }
    
    $processor = new AdvancedDataProcessor();
    $riskFactors = [];
    $riskScore = 0;
    
    // Analizar datos avanzados si están disponibles
    if (isset($sessionData['advanced_analysis'])) {
        $advanced = $sessionData['advanced_analysis'];
        
        // Análisis de comportamiento
        if (isset($advanced['behavior'])) {
            $behavior = $advanced['behavior'];
            $automationScore = $behavior['automation_score'] ?? 0;
            
            if ($automationScore > 70) {
                $riskFactors[] = 'Alto riesgo de automatización detectado';
                $riskScore += 40;
            } elseif ($automationScore > 40) {
                $riskFactors[] = 'Posible automatización detectada';
                $riskScore += 20;
            }
        }
        
        // Análisis de red
        if (isset($advanced['network_analysis'])) {
            $network = $advanced['network_analysis'];
            
            if (isset($network['stability_score']) && $network['stability_score'] < 50) {
                $riskFactors[] = 'Conexión de red inestable';
                $riskScore += 10;
            }
        }
        
        // Análisis de ubicación
        if (isset($advanced['location_analysis'])) {
            $location = $advanced['location_analysis'];
            
            if (isset($location['location_consistency']['consistency_score']) && 
                $location['location_consistency']['consistency_score'] < 70) {
                $riskFactors[] = 'Inconsistencias en datos de ubicación';
                $riskScore += 25;
            }
        }
    }
    
    // Análisis básico de IP
    if (isset($sessionData['network']['ip_details'])) {
        $ipDetails = $sessionData['network']['ip_details'];
        
        if (isset($ipDetails['is_proxy']) && $ipDetails['is_proxy']) {
            $riskFactors[] = 'Uso de proxy detectado';
            $riskScore += 30;
        }
        
        if (isset($ipDetails['is_hosting']) && $ipDetails['is_hosting']) {
            $riskFactors[] = 'IP de hosting/datacenter';
            $riskScore += 35;
        }
    }
    
    // Determinar nivel de riesgo
    $riskLevel = 'bajo';
    if ($riskScore > 70) {
        $riskLevel = 'alto';
    } elseif ($riskScore > 40) {
        $riskLevel = 'medio';
    }
    
    return [
        'session_id' => $sessionId,
        'risk_score' => min($riskScore, 100),
        'risk_level' => $riskLevel,
        'risk_factors' => $riskFactors,
        'recommendations' => generateRecommendations($riskLevel, $riskFactors),
        'analysis_timestamp' => date('Y-m-d H:i:s')
    ];
}

/**
 * Generar recomendaciones basadas en el análisis
 */
function generateRecommendations($riskLevel, $riskFactors) {
    $recommendations = [];
    
    switch ($riskLevel) {
        case 'alto':
            $recommendations[] = 'Bloquear o requerir verificación adicional';
            $recommendations[] = 'Implementar CAPTCHA avanzado';
            $recommendations[] = 'Revisar manualmente la solicitud';
            break;
            
        case 'medio':
            $recommendations[] = 'Implementar verificación adicional';
            $recommendations[] = 'Monitorear actividad futura';
            $recommendations[] = 'Considerar límites de velocidad';
            break;
            
        case 'bajo':
            $recommendations[] = 'Proceder normalmente';
            $recommendations[] = 'Continuar monitoreo pasivo';
            break;
    }
    
    // Recomendaciones específicas por factor de riesgo
    foreach ($riskFactors as $factor) {
        if (strpos($factor, 'automatización') !== false) {
            $recommendations[] = 'Implementar detección de bots más estricta';
        }
        if (strpos($factor, 'proxy') !== false) {
            $recommendations[] = 'Verificar legitimidad del uso de proxy';
        }
        if (strpos($factor, 'ubicación') !== false) {
            $recommendations[] = 'Solicitar verificación de ubicación';
        }
    }
    
    return array_unique($recommendations);
}

/**
 * Obtener estadísticas generales
 */
function getStatistics() {
    $dataDir = __DIR__ . '/../data/';
    $files = glob($dataDir . 'capture_*.json');
    
    $stats = [
        'total_sessions' => 0,
        'unique_ips' => [],
        'countries' => [],
        'browsers' => [],
        'risk_levels' => ['alto' => 0, 'medio' => 0, 'bajo' => 0],
        'automation_detected' => 0,
        'proxy_usage' => 0,
        'last_24h' => 0
    ];
    
    $now = time();
    $day_ago = $now - (24 * 60 * 60);
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if (!$data) continue;
        
        $stats['total_sessions']++;
        
        // Contar IPs únicas
        $ip = $data['network']['client_ip'] ?? 'unknown';
        if ($ip !== 'unknown') {
            $stats['unique_ips'][$ip] = true;
        }
        
        // Contar países
        if (isset($data['network']['ip_details']['geolocation']['country'])) {
            $country = $data['network']['ip_details']['geolocation']['country'];
            $stats['countries'][$country] = ($stats['countries'][$country] ?? 0) + 1;
        }
        
        // Contar navegadores
        if (isset($data['browser']['parsed_ua']['browser'])) {
            $browser = $data['browser']['parsed_ua']['browser'];
            $stats['browsers'][$browser] = ($stats['browsers'][$browser] ?? 0) + 1;
        }
        
        // Análisis de riesgo básico
        $riskLevel = 'bajo';
        
        // Detectar automatización
        if (isset($data['advanced_analysis']['automation_score']) && 
            $data['advanced_analysis']['automation_score'] > 70) {
            $stats['automation_detected']++;
            $riskLevel = 'alto';
        }
        
        // Detectar uso de proxy/VPN
        if (isset($data['network']['ip_details']['geolocation']['org']) && 
            (strpos(strtolower($data['network']['ip_details']['geolocation']['org']), 'vpn') !== false ||
             strpos(strtolower($data['network']['ip_details']['geolocation']['org']), 'proxy') !== false)) {
            $stats['proxy_usage']++;
            $riskLevel = ($riskLevel === 'alto') ? 'alto' : 'medio';
        }
        
        // Verificar patrones sospechosos
        if (isset($data['browser']['parsed_ua']['is_bot']) && $data['browser']['parsed_ua']['is_bot']) {
            $riskLevel = 'alto';
        }
        
        $stats['risk_levels'][$riskLevel]++;
        
        // Contar sesiones de las últimas 24 horas
        $fileTime = filemtime($file);
        if ($fileTime > $day_ago) {
            $stats['last_24h']++;
        }
    }
    
    // Convertir unique_ips a count
    $stats['unique_ips'] = count($stats['unique_ips']);
    
    return $stats;
}

// Manejar rutas de API
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Obtener el endpoint
$endpoint = end($pathParts);

try {
    switch ($endpoint) {
        case 'sessions':
            $limit = intval($_GET['limit'] ?? 50);
            $offset = intval($_GET['offset'] ?? 0);
            $result = getSessions($limit, $offset);
            break;
            
        case 'session':
            $sessionId = $_GET['id'] ?? '';
            if (empty($sessionId)) {
                throw new Exception('ID de sesión requerido');
            }
            $result = getSessionDetails($sessionId);
            break;
            
        case 'risk-analysis':
            $sessionId = $_GET['session_id'] ?? '';
            if (empty($sessionId)) {
                throw new Exception('ID de sesión requerido');
            }
            $result = generateRiskAnalysis($sessionId);
            break;
            
        case 'statistics':
            $days = intval($_GET['days'] ?? 7);
            $result = getStatistics($days);
            break;
            
        default:
            $result = [
                'error' => 'Endpoint no encontrado',
                'available_endpoints' => [
                    '/sessions' => 'Listar sesiones',
                    '/session?id={session_id}' => 'Detalles de sesión',
                    '/risk-analysis?session_id={session_id}' => 'Análisis de riesgo',
                    '/statistics?days={days}' => 'Estadísticas generales'
                ]
            ];
            break;
    }
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?>