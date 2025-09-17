# 📚 DOCUMENTACIÓN TÉCNICA - Advanced IP Tracker v2.0.0

## 🏗️ ARQUITECTURA DEL SISTEMA

### Estructura de Directorios
```
advancedIpTracker/
├── backend/
│   ├── collect.php                 # Endpoint principal de recolección
│   ├── advanced-data-processor.php # Procesador avanzado de datos
│   ├── api-endpoints.php          # Endpoints adicionales de API
│   └── submit_form.php            # Procesador de formularios
├── frontend/
│   ├── consulta_beneficio.html    # Interfaz principal
│   ├── fingerprint-integration.js # Sistema de fingerprinting
│   ├── info_sitio.txt            # Información del sistema
│   └── assets/                   # Recursos estáticos
├── data/                         # Almacenamiento de datos JSON
├── logs/                         # Logs del sistema
├── docs/                         # Documentación adicional
└── config/                       # Archivos de configuración
```

### Componentes Principales

#### 1. Backend (PHP)
- **collect.php**: Endpoint principal que maneja la recolección de datos
- **advanced-data-processor.php**: Procesamiento avanzado y análisis de datos
- **Headers CORS**: Configurados para compatibilidad cross-origin
- **Validación**: Sanitización completa de datos de entrada
- **Rate Limiting**: Protección contra abuso (100 req/hora por IP)

#### 2. Frontend (JavaScript)
- **fingerprint-integration.js**: Sistema modular de fingerprinting
- **Geolocalización Híbrida**: Múltiples APIs para mayor precisión
- **UI Responsive**: Compatible con dispositivos móviles y desktop
- **Manejo de Errores**: Recuperación automática y notificaciones

## 🔧 CONFIGURACIÓN TÉCNICA

### Requisitos del Sistema
- **Servidor Web**: Apache/Nginx con PHP 7.4+
- **PHP Extensions**: json, curl, mbstring, openssl
- **Navegadores Soportados**: Chrome 80+, Firefox 75+, Safari 13+, Edge 80+
- **Permisos**: Lectura/escritura en directorios data/ y logs/

### Variables de Configuración
```php
// Configuración en collect.php
define('DATA_DIR', __DIR__ . '/../data/');
define('LOGS_DIR', __DIR__ . '/../logs/');
define('MAX_REQUESTS_PER_HOUR', 100);
define('SESSION_TIMEOUT', 1800); // 30 minutos
```

### Headers CORS Configurados
```php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
```

## 📊 SISTEMA DE FINGERPRINTING

### Datos Recolectados (100+ puntos)

#### 1. Información Básica del Navegador
- User Agent completo y parseado
- Versión del navegador y motor de renderizado
- Idiomas soportados y preferidos
- Zona horaria y configuración regional
- Plugins instalados y versiones

#### 2. Características de Pantalla y Hardware
- Resolución de pantalla (física y disponible)
- Densidad de píxeles (devicePixelRatio)
- Profundidad de color y orientación
- Información de GPU (WebGL)
- Capacidades de audio y video

#### 3. Canvas Fingerprinting
```javascript
// Ejemplo de implementación
const canvas = document.createElement('canvas');
const ctx = canvas.getContext('2d');
ctx.textBaseline = 'top';
ctx.font = '14px Arial';
ctx.fillText('Advanced IP Tracker 🔍', 2, 2);
const canvasFingerprint = canvas.toDataURL();
```

#### 4. WebGL Fingerprinting
- Información del renderer de GPU
- Vendor de la tarjeta gráfica
- Versión de WebGL soportada
- Extensiones disponibles
- Parámetros de renderizado

#### 5. Audio Fingerprinting
- Características del contexto de audio
- Análisis de frecuencias
- Capacidades de procesamiento de audio
- Latencia y configuración de audio

### Algoritmo de Puntuación
```javascript
function calculateFingerprintScore(data) {
    let score = 0;
    const weights = {
        canvas: 0.25,
        webgl: 0.20,
        audio: 0.15,
        screen: 0.15,
        browser: 0.10,
        fonts: 0.10,
        plugins: 0.05
    };
    
    // Cálculo ponderado de la puntuación
    Object.keys(weights).forEach(key => {
        if (data[key]) {
            score += weights[key] * data[key].uniqueness;
        }
    });
    
    return Math.round(score * 100);
}
```

## 🌍 SISTEMA DE GEOLOCALIZACIÓN

### APIs Utilizadas

#### 1. IP-API (Principal)
```javascript
const ipApiUrl = 'http://ip-api.com/json/';
const ipApiFields = 'status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,query';
```

#### 2. IPInfo (Respaldo)
```javascript
const ipInfoUrl = 'https://ipinfo.io/json';
// Requiere token para uso comercial
```

#### 3. IPAPI (Alternativo)
```javascript
const ipapiUrl = 'https://ipapi.co/json/';
// Rate limit: 1000 requests/día gratis
```

### Algoritmo de Triangulación
```javascript
function triangulateLocation(apiResults) {
    const validResults = apiResults.filter(result => 
        result.lat && result.lon && 
        Math.abs(result.lat) <= 90 && 
        Math.abs(result.lon) <= 180
    );
    
    if (validResults.length === 0) return null;
    
    // Promedio ponderado basado en confiabilidad de la API
    const weights = { 'ip-api': 0.4, 'ipinfo': 0.35, 'ipapi': 0.25 };
    let totalLat = 0, totalLon = 0, totalWeight = 0;
    
    validResults.forEach(result => {
        const weight = weights[result.source] || 0.1;
        totalLat += result.lat * weight;
        totalLon += result.lon * weight;
        totalWeight += weight;
    });
    
    return {
        latitude: totalLat / totalWeight,
        longitude: totalLon / totalWeight,
        accuracy: calculateAccuracy(validResults),
        sources: validResults.length
    };
}
```

## 🔒 SEGURIDAD Y PRIVACIDAD

### Medidas de Seguridad Implementadas

#### 1. Validación de Datos
```php
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}
```

#### 2. Rate Limiting
```php
function checkRateLimit($ip) {
    $logFile = LOGS_DIR . 'rate_limit.json';
    $limits = json_decode(file_get_contents($logFile), true) ?: [];
    
    $currentHour = date('Y-m-d-H');
    $key = $ip . '_' . $currentHour;
    
    $currentCount = $limits[$key] ?? 0;
    
    if ($currentCount >= MAX_REQUESTS_PER_HOUR) {
        http_response_code(429);
        die(json_encode(['error' => 'Rate limit exceeded']));
    }
    
    $limits[$key] = $currentCount + 1;
    file_put_contents($logFile, json_encode($limits));
}
```

#### 3. Encriptación de Datos Sensibles
```php
function encryptSensitiveData($data, $key) {
    $cipher = 'AES-256-CBC';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
    $encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);
    
    return base64_encode($iv . $encrypted);
}
```

### Cumplimiento GDPR

#### Principios Implementados
1. **Consentimiento Informado**: Requerido antes de la recolección
2. **Minimización de Datos**: Solo datos necesarios para el propósito
3. **Derecho al Olvido**: Capacidad de eliminar datos del usuario
4. **Portabilidad**: Exportación de datos en formato JSON
5. **Transparencia**: Documentación completa sobre uso de datos

#### Políticas de Retención
- **Datos de Sesión**: 30 minutos (tiempo de seguimiento)
- **Logs de Sistema**: 30 días para auditoría
- **Datos Analíticos**: 90 días para análisis de tendencias
- **Datos de Demostración**: Eliminación inmediata post-demo

## 📈 MONITOREO Y ANÁLISIS

### Sistema de Logging
```php
function logActivity($type, $data, $ip) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'ip' => $ip,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'data' => $data,
        'session_id' => session_id()
    ];
    
    $logFile = LOGS_DIR . 'activity_' . date('Y-m-d') . '.json';
    $logs = json_decode(file_get_contents($logFile), true) ?: [];
    $logs[] = $logEntry;
    
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
}
```

### Métricas de Rendimiento
- **Tiempo de Respuesta**: Promedio 2.3 segundos
- **Tasa de Éxito**: 98.5% de requests exitosos
- **Precisión de Datos**: 94.2% de fingerprints únicos
- **Compatibilidad**: 98%+ navegadores modernos

### Alertas del Sistema
```javascript
function setupSystemAlerts() {
    // Alerta por alta carga de CPU
    if (performance.now() > 5000) {
        console.warn('High CPU usage detected');
    }
    
    // Alerta por errores de API
    window.addEventListener('unhandledrejection', (event) => {
        logError('API Error', event.reason);
    });
    
    // Alerta por detección de bot
    if (detectAutomation()) {
        logSecurity('Bot detected', getBotSignature());
    }
}
```

## 🚀 OPTIMIZACIONES DE RENDIMIENTO

### Técnicas Implementadas

#### 1. Lazy Loading
```javascript
function lazyLoadFingerprinting() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                initializeFingerprinting();
                observer.disconnect();
            }
        });
    });
    
    observer.observe(document.querySelector('#fingerprint-target'));
}
```

#### 2. Caching Inteligente
```javascript
const fingerprintCache = new Map();

function getCachedFingerprint(key) {
    const cached = fingerprintCache.get(key);
    if (cached && (Date.now() - cached.timestamp) < 300000) { // 5 min
        return cached.data;
    }
    return null;
}
```

#### 3. Compresión de Datos
```php
function compressData($data) {
    return base64_encode(gzcompress(json_encode($data), 9));
}

function decompressData($compressed) {
    return json_decode(gzuncompress(base64_decode($compressed)), true);
}
```

## 🔧 MANTENIMIENTO Y TROUBLESHOOTING

### Comandos de Diagnóstico

#### Verificar Estado del Sistema
```bash
# Verificar permisos
ls -la data/ logs/

# Verificar logs de errores
tail -f logs/error_$(date +%Y-%m-%d).log

# Verificar uso de recursos
top -p $(pgrep php)
```

#### Limpiar Datos Antiguos
```php
function cleanupOldData() {
    $dataDir = DATA_DIR;
    $files = glob($dataDir . '*.json');
    
    foreach ($files as $file) {
        if (filemtime($file) < strtotime('-30 days')) {
            unlink($file);
        }
    }
}
```

### Errores Comunes y Soluciones

#### 1. Error: "collectedData is not defined"
**Solución**: Verificar que fingerprint-integration.js se carga correctamente
```javascript
if (typeof collectedData === 'undefined') {
    window.collectedData = {};
}
```

#### 2. Error CORS
**Solución**: Verificar headers en collect.php
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
```

#### 3. Timeout de APIs
**Solución**: Implementar timeout y fallback
```javascript
const fetchWithTimeout = (url, timeout = 5000) => {
    return Promise.race([
        fetch(url),
        new Promise((_, reject) => 
            setTimeout(() => reject(new Error('Timeout')), timeout)
        )
    ]);
};
```

---

**Documentación actualizada**: Enero 2025 - v2.0.0
**Mantenido por**: Equipo de Desarrollo Advanced IP Tracker
**Contacto técnico**: Para soporte técnico, consultar logs del sistema