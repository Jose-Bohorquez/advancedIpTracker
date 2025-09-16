<?php
/**
 * Procesador Avanzado de Datos de Seguimiento
 * Maneja y procesa todos los datos recopilados por el sistema de fingerprinting avanzado
 */

class AdvancedDataProcessor {
    private $db;
    private $logFile;
    
    public function __construct($database = null) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/logs/advanced_tracking.log';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Procesa datos de comportamiento del usuario
     */
    public function processBehaviorData($data) {
        $processed = [
            'session_id' => $data['sessionId'] ?? '',
            'mouse_patterns' => $this->analyzeMouse($data['mouseData'] ?? []),
            'keyboard_patterns' => $this->analyzeKeyboard($data['keyboardData'] ?? []),
            'scroll_behavior' => $this->analyzeScroll($data['scrollData'] ?? []),
            'interaction_timeline' => $data['interactionTimeline'] ?? [],
            'automation_score' => $this->calculateAutomationScore($data),
            'user_engagement' => $this->calculateEngagement($data),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->logData('behavior', $processed);
        return $processed;
    }
    
    /**
     * Procesa datos de red y conexión
     */
    public function processNetworkData($data) {
        $processed = [
            'session_id' => $data['sessionId'] ?? '',
            'connection_type' => $data['connectionType'] ?? 'unknown',
            'effective_type' => $data['effectiveType'] ?? 'unknown',
            'downlink' => $data['downlink'] ?? 0,
            'rtt' => $data['rtt'] ?? 0,
            'latency_avg' => $data['latencyAvg'] ?? 0,
            'stability_score' => $data['stabilityScore'] ?? 0,
            'bandwidth_estimate' => $data['bandwidthEstimate'] ?? 0,
            'network_fingerprint' => $data['networkFingerprint'] ?? '',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->logData('network', $processed);
        return $processed;
    }
    
    /**
     * Procesa datos de dispositivo y hardware
     */
    public function processDeviceData($data) {
        $processed = [
            'session_id' => $data['sessionId'] ?? '',
            'device_fingerprint' => $data['deviceFingerprint'] ?? '',
            'hardware_info' => [
                'cpu_cores' => $data['hardwareConcurrency'] ?? 0,
                'memory' => $data['deviceMemory'] ?? 0,
                'platform' => $data['platform'] ?? '',
                'architecture' => $data['architecture'] ?? ''
            ],
            'screen_info' => [
                'resolution' => $data['screenResolution'] ?? '',
                'color_depth' => $data['colorDepth'] ?? 0,
                'pixel_ratio' => $data['pixelRatio'] ?? 1
            ],
            'browser_info' => [
                'user_agent' => $data['userAgent'] ?? '',
                'language' => $data['language'] ?? '',
                'timezone' => $data['timezone'] ?? '',
                'cookies_enabled' => $data['cookieEnabled'] ?? false
            ],
            'capabilities' => $data['capabilities'] ?? [],
            'sensors' => $data['sensors'] ?? [],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->logData('device', $processed);
        return $processed;
    }
    
    /**
     * Procesa datos de ubicación avanzados
     */
    public function processLocationData($data) {
        $processed = [
            'session_id' => $data['sessionId'] ?? '',
            'coordinates' => [
                'latitude' => $data['latitude'] ?? 0,
                'longitude' => $data['longitude'] ?? 0,
                'accuracy' => $data['accuracy'] ?? 0,
                'altitude' => $data['altitude'] ?? null,
                'heading' => $data['heading'] ?? null,
                'speed' => $data['speed'] ?? null
            ],
            'location_method' => $data['method'] ?? 'unknown',
            'ip_location' => $data['ipLocation'] ?? [],
            'timezone_info' => $data['timezoneInfo'] ?? [],
            'movement_pattern' => $this->analyzeMovement($data),
            'location_consistency' => $this->checkLocationConsistency($data),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->logData('location', $processed);
        return $processed;
    }
    
    /**
     * Analiza patrones de movimiento del mouse
     */
    private function analyzeMouse($mouseData) {
        if (empty($mouseData)) return [];
        
        $patterns = [
            'total_movements' => count($mouseData),
            'avg_speed' => 0,
            'direction_changes' => 0,
            'click_patterns' => [],
            'dwell_times' => []
        ];
        
        // Calcular velocidad promedio y cambios de dirección
        for ($i = 1; $i < count($mouseData); $i++) {
            $prev = $mouseData[$i-1];
            $curr = $mouseData[$i];
            
            $distance = sqrt(pow($curr['x'] - $prev['x'], 2) + pow($curr['y'] - $prev['y'], 2));
            $time = $curr['timestamp'] - $prev['timestamp'];
            
            if ($time > 0) {
                $patterns['avg_speed'] += $distance / $time;
            }
        }
        
        if (count($mouseData) > 1) {
            $patterns['avg_speed'] /= (count($mouseData) - 1);
        }
        
        return $patterns;
    }
    
    /**
     * Analiza patrones de teclado
     */
    private function analyzeKeyboard($keyboardData) {
        if (empty($keyboardData)) return [];
        
        $patterns = [
            'total_keystrokes' => count($keyboardData),
            'avg_typing_speed' => 0,
            'pause_patterns' => [],
            'rhythm_consistency' => 0
        ];
        
        // Calcular velocidad de escritura
        $intervals = [];
        for ($i = 1; $i < count($keyboardData); $i++) {
            $interval = $keyboardData[$i]['timestamp'] - $keyboardData[$i-1]['timestamp'];
            $intervals[] = $interval;
        }
        
        if (!empty($intervals)) {
            $patterns['avg_typing_speed'] = 1000 / (array_sum($intervals) / count($intervals));
            $patterns['rhythm_consistency'] = $this->calculateVariance($intervals);
        }
        
        return $patterns;
    }
    
    /**
     * Analiza patrones de scroll
     */
    private function analyzeScroll($scrollData) {
        if (empty($scrollData)) return [];
        
        return [
            'total_scrolls' => count($scrollData),
            'scroll_depth' => max(array_column($scrollData, 'y')),
            'scroll_speed' => $this->calculateScrollSpeed($scrollData),
            'reading_pattern' => $this->analyzeReadingPattern($scrollData)
        ];
    }
    
    /**
     * Calcula puntuación de automatización
     */
    private function calculateAutomationScore($data) {
        $score = 0;
        
        // Factores que indican automatización
        if (isset($data['mouseData']) && $this->hasRobotMousePattern($data['mouseData'])) {
            $score += 30;
        }
        
        if (isset($data['keyboardData']) && $this->hasRobotTypingPattern($data['keyboardData'])) {
            $score += 40;
        }
        
        if (isset($data['interactionTimeline']) && $this->hasSuspiciousTiming($data['interactionTimeline'])) {
            $score += 30;
        }
        
        return min($score, 100);
    }
    
    /**
     * Calcula nivel de engagement del usuario
     */
    private function calculateEngagement($data) {
        $engagement = [
            'focus_time' => $data['focusTime'] ?? 0,
            'interaction_frequency' => 0,
            'page_exploration' => 0,
            'form_completion_rate' => 0
        ];
        
        // Calcular frecuencia de interacción
        $totalInteractions = 0;
        if (isset($data['mouseData'])) $totalInteractions += count($data['mouseData']);
        if (isset($data['keyboardData'])) $totalInteractions += count($data['keyboardData']);
        if (isset($data['scrollData'])) $totalInteractions += count($data['scrollData']);
        
        $sessionDuration = $data['sessionDuration'] ?? 1;
        $engagement['interaction_frequency'] = $totalInteractions / ($sessionDuration / 1000);
        
        return $engagement;
    }
    
    /**
     * Analiza patrones de movimiento geográfico
     */
    private function analyzeMovement($locationData) {
        // Implementar análisis de movimiento geográfico
        return [
            'is_stationary' => true,
            'movement_speed' => 0,
            'location_jumps' => 0
        ];
    }
    
    /**
     * Verifica consistencia de ubicación
     */
    private function checkLocationConsistency($locationData) {
        // Comparar ubicación GPS vs IP vs timezone
        return [
            'gps_ip_match' => true,
            'timezone_match' => true,
            'consistency_score' => 100
        ];
    }
    
    /**
     * Detecta patrones de mouse robóticos
     */
    private function hasRobotMousePattern($mouseData) {
        if (count($mouseData) < 10) return false;
        
        // Buscar movimientos perfectamente lineales o repetitivos
        $linearMovements = 0;
        for ($i = 2; $i < count($mouseData); $i++) {
            $p1 = $mouseData[$i-2];
            $p2 = $mouseData[$i-1];
            $p3 = $mouseData[$i];
            
            // Verificar si los puntos están en línea recta
            $slope1 = ($p2['y'] - $p1['y']) / ($p2['x'] - $p1['x'] + 0.001);
            $slope2 = ($p3['y'] - $p2['y']) / ($p3['x'] - $p2['x'] + 0.001);
            
            if (abs($slope1 - $slope2) < 0.1) {
                $linearMovements++;
            }
        }
        
        return ($linearMovements / count($mouseData)) > 0.8;
    }
    
    /**
     * Detecta patrones de escritura robóticos
     */
    private function hasRobotTypingPattern($keyboardData) {
        if (count($keyboardData) < 5) return false;
        
        $intervals = [];
        for ($i = 1; $i < count($keyboardData); $i++) {
            $intervals[] = $keyboardData[$i]['timestamp'] - $keyboardData[$i-1]['timestamp'];
        }
        
        // Intervalos demasiado consistentes indican automatización
        $variance = $this->calculateVariance($intervals);
        return $variance < 10; // Muy poca variación
    }
    
    /**
     * Detecta timing sospechoso en interacciones
     */
    private function hasSuspiciousTiming($timeline) {
        // Buscar patrones de timing no humanos
        return false; // Implementar lógica específica
    }
    
    /**
     * Calcula varianza de un array
     */
    private function calculateVariance($values) {
        if (empty($values)) return 0;
        
        $mean = array_sum($values) / count($values);
        $variance = 0;
        
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        return $variance / count($values);
    }
    
    /**
     * Calcula velocidad de scroll
     */
    private function calculateScrollSpeed($scrollData) {
        if (count($scrollData) < 2) return 0;
        
        $totalDistance = 0;
        $totalTime = 0;
        
        for ($i = 1; $i < count($scrollData); $i++) {
            $distance = abs($scrollData[$i]['y'] - $scrollData[$i-1]['y']);
            $time = $scrollData[$i]['timestamp'] - $scrollData[$i-1]['timestamp'];
            
            $totalDistance += $distance;
            $totalTime += $time;
        }
        
        return $totalTime > 0 ? $totalDistance / $totalTime : 0;
    }
    
    /**
     * Analiza patrones de lectura
     */
    private function analyzeReadingPattern($scrollData) {
        // Implementar análisis de patrones de lectura
        return [
            'reading_speed' => 'normal',
            'attention_areas' => [],
            'skip_patterns' => []
        ];
    }
    
    /**
     * Registra datos en log
     */
    private function logData($type, $data) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'data' => $data
        ];
        
        file_put_contents($this->logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Genera reporte de análisis
     */
    public function generateAnalysisReport($sessionId) {
        // Implementar generación de reportes
        return [
            'session_id' => $sessionId,
            'risk_score' => 0,
            'automation_probability' => 0,
            'user_authenticity' => 100,
            'recommendations' => []
        ];
    }
}