<?php
/**
 * Advanced IP Tracker - Panel de Administración
 * Dashboard para visualizar datos recolectados
 */

// Configuración
define('DATA_DIR', '../data/');
define('LOGS_DIR', '../logs/');
define('PARTICIPANTS_DIR', '../participants/');
define('CHALLENGES_DIR', '../challenges/');
define('PHOTOS_DIR', '../photos/');

/**
 * Obtener lista de archivos de datos
 */
function getDataFiles() {
    $files = [];
    if (is_dir(DATA_DIR)) {
        $fileList = scandir(DATA_DIR);
        foreach ($fileList as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $filepath = DATA_DIR . $file;
                $files[] = [
                    'filename' => $file,
                    'size' => filesize($filepath),
                    'modified' => filemtime($filepath),
                    'data' => json_decode(file_get_contents($filepath), true)
                ];
            }
        }
    }
    
    // Ordenar por fecha de modificación (más reciente primero)
    usort($files, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
    
    return $files;
}

/**
 * Obtener lista de participantes del sistema Nequi
 */
function getNequiParticipants() {
    $participants = [];
    if (is_dir(PARTICIPANTS_DIR)) {
        $fileList = scandir(PARTICIPANTS_DIR);
        foreach ($fileList as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $filepath = PARTICIPANTS_DIR . $file;
                $data = json_decode(file_get_contents($filepath), true);
                if ($data) {
                    $participants[] = [
                        'filename' => $file,
                        'size' => filesize($filepath),
                        'modified' => filemtime($filepath),
                        'data' => $data
                    ];
                }
            }
        }
    }
    
    // Ordenar por fecha de modificación (más reciente primero)
    usort($participants, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
    
    return $participants;
}

/**
 * Obtener estadísticas de participantes Nequi
 */
function getNequiStats($participants) {
    $stats = [
        'total_participants' => count($participants),
        'total_earnings' => 0,
        'completed_challenges' => 0,
        'locations' => [],
        'document_types' => [],
        'registration_dates' => [],
        'popular_challenges' => [],
        'regions' => [],
        'earnings_distribution' => [
            '0-50000' => 0,
            '50001-100000' => 0,
            '100001-200000' => 0,
            '200001-500000' => 0,
            '500001+' => 0
        ],
        'challenge_completion_rate' => [],
        'gps_precision_stats' => [
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'ip_only' => 0
        ]
    ];
    
    foreach ($participants as $participant) {
        $data = $participant['data'];
        
        // Ganancias totales
        if (isset($data['current_earnings'])) {
            $earnings = $data['current_earnings'];
            $stats['total_earnings'] += $earnings;
            
            // Distribución de ganancias
            if ($earnings <= 50000) {
                $stats['earnings_distribution']['0-50000']++;
            } elseif ($earnings <= 100000) {
                $stats['earnings_distribution']['50001-100000']++;
            } elseif ($earnings <= 200000) {
                $stats['earnings_distribution']['100001-200000']++;
            } elseif ($earnings <= 500000) {
                $stats['earnings_distribution']['200001-500000']++;
            } else {
                $stats['earnings_distribution']['500001+']++;
            }
        }
        
        // Retos completados y populares
        if (isset($data['completed_challenges'])) {
            $completedCount = count($data['completed_challenges']);
            $stats['completed_challenges'] += $completedCount;
            
            // Tasa de completado de retos
            $completionRate = round(($completedCount / 10) * 100);
            $rateRange = '';
            if ($completionRate == 0) $rateRange = '0%';
            elseif ($completionRate <= 25) $rateRange = '1-25%';
            elseif ($completionRate <= 50) $rateRange = '26-50%';
            elseif ($completionRate <= 75) $rateRange = '51-75%';
            elseif ($completionRate <= 99) $rateRange = '76-99%';
            else $rateRange = '100%';
            
            $stats['challenge_completion_rate'][$rateRange] = ($stats['challenge_completion_rate'][$rateRange] ?? 0) + 1;
            
            // Retos más populares
            foreach ($data['completed_challenges'] as $challenge) {
                if (isset($challenge['challenge_id'])) {
                    $challengeId = $challenge['challenge_id'];
                    $stats['popular_challenges'][$challengeId] = ($stats['popular_challenges'][$challengeId] ?? 0) + 1;
                }
            }
        } else {
            $stats['challenge_completion_rate']['0%'] = ($stats['challenge_completion_rate']['0%'] ?? 0) + 1;
        }
        
        // Ubicaciones y regiones
        if (isset($data['address'])) {
            $location = $data['address'];
            $stats['locations'][$location] = ($stats['locations'][$location] ?? 0) + 1;
            
            // Extraer región (asumiendo formato "Ciudad, Departamento")
            $parts = explode(',', $location);
            if (count($parts) >= 2) {
                $region = trim($parts[count($parts) - 1]); // Último elemento como región
                $stats['regions'][$region] = ($stats['regions'][$region] ?? 0) + 1;
            } else {
                $stats['regions']['Otros'] = ($stats['regions']['Otros'] ?? 0) + 1;
            }
        }
        
        // Estadísticas de precisión GPS
        $hasPrecisionData = false;
        
        // Verificar GPS en additional_data
        if (isset($data['additional_data']['locationData']['accuracy'])) {
            $accuracy = $data['additional_data']['locationData']['accuracy'];
            $hasPrecisionData = true;
            
            if ($accuracy < 100) {
                $stats['gps_precision_stats']['high']++;
            } elseif ($accuracy < 1000) {
                $stats['gps_precision_stats']['medium']++;
            } else {
                $stats['gps_precision_stats']['low']++;
            }
        }
        // Verificar formato anterior de geolocalización
        elseif (isset($data['geolocation']['coordinates']['gps']['accuracy'])) {
            $accuracy = $data['geolocation']['coordinates']['gps']['accuracy'];
            $hasPrecisionData = true;
            
            if ($accuracy < 100) {
                $stats['gps_precision_stats']['high']++;
            } elseif ($accuracy < 1000) {
                $stats['gps_precision_stats']['medium']++;
            } else {
                $stats['gps_precision_stats']['low']++;
            }
        }
        
        if (!$hasPrecisionData) {
            $stats['gps_precision_stats']['ip_only']++;
        }
        
        // Tipos de documento
        if (isset($data['document_type'])) {
            $docType = $data['document_type'];
            $stats['document_types'][$docType] = ($stats['document_types'][$docType] ?? 0) + 1;
        }
        
        // Fechas de registro
        if (isset($data['timestamp'])) {
            $date = date('Y-m-d', strtotime($data['timestamp']));
            $stats['registration_dates'][$date] = ($stats['registration_dates'][$date] ?? 0) + 1;
        }
    }
    
    // Ordenar retos populares por frecuencia
    if (!empty($stats['popular_challenges'])) {
        arsort($stats['popular_challenges']);
    }
    
    // Ordenar regiones por cantidad
    if (!empty($stats['regions'])) {
        arsort($stats['regions']);
    }
    
    // Calcular tasa promedio de completado
    $totalParticipants = $stats['total_participants'];
    if ($totalParticipants > 0) {
        $totalCompletionRate = 0;
        foreach ($stats['challenge_completion_rate'] as $range => $count) {
            if ($range === '0%') {
                $totalCompletionRate += 0 * $count;
            } elseif ($range === '1-25%') {
                $totalCompletionRate += 12.5 * $count;
            } elseif ($range === '26-50%') {
                $totalCompletionRate += 37.5 * $count;
            } elseif ($range === '51-75%') {
                $totalCompletionRate += 62.5 * $count;
            } elseif ($range === '76-99%') {
                $totalCompletionRate += 87.5 * $count;
            } elseif ($range === '100%') {
                $totalCompletionRate += 100 * $count;
            }
        }
        $stats['avg_completion_rate'] = $totalCompletionRate / $totalParticipants;
    } else {
        $stats['avg_completion_rate'] = 0;
    }
    
    // Calcular porcentajes de precisión GPS
    $totalGpsEntries = $stats['gps_precision_stats']['high'] + $stats['gps_precision_stats']['medium'] + $stats['gps_precision_stats']['low'] + $stats['gps_precision_stats']['ip_only'];
    if ($totalGpsEntries > 0) {
        $stats['gps_precision'] = [
            'high' => ($stats['gps_precision_stats']['high'] / $totalGpsEntries) * 100,
            'medium' => ($stats['gps_precision_stats']['medium'] / $totalGpsEntries) * 100,
            'low' => ($stats['gps_precision_stats']['low'] / $totalGpsEntries) * 100,
            'ip_only' => ($stats['gps_precision_stats']['ip_only'] / $totalGpsEntries) * 100
        ];
    } else {
        $stats['gps_precision'] = [
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'ip_only' => 0
        ];
    }
    
    return $stats;
}

/**
 * Obtener estadísticas generales
 */
function getStats($files) {
    $stats = [
        'total_captures' => count($files),
        'unique_ips' => [],
        'browsers' => [],
        'operating_systems' => [],
        'countries' => [],
        'devices' => [],
        'total_size' => 0,
        'date_range' => ['first' => null, 'last' => null]
    ];
    
    foreach ($files as $file) {
        $data = $file['data'];
        $stats['total_size'] += $file['size'];
        
        // IPs únicas
        if (isset($data['network']['client_ip'])) {
            $stats['unique_ips'][$data['network']['client_ip']] = true;
        }
        
        // Navegadores
        if (isset($data['browser']['parsed_ua']['browser'])) {
            $browser = $data['browser']['parsed_ua']['browser'];
            $stats['browsers'][$browser] = ($stats['browsers'][$browser] ?? 0) + 1;
        }
        
        // Sistemas operativos
        if (isset($data['browser']['parsed_ua']['os'])) {
            $os = $data['browser']['parsed_ua']['os'];
            $stats['operating_systems'][$os] = ($stats['operating_systems'][$os] ?? 0) + 1;
        }
        
        // Países
        if (isset($data['network']['ip_details']['geolocation']['country'])) {
            $country = $data['network']['ip_details']['geolocation']['country'];
            $stats['countries'][$country] = ($stats['countries'][$country] ?? 0) + 1;
        }
        
        // Dispositivos
        if (isset($data['browser']['parsed_ua']['device'])) {
            $device = $data['browser']['parsed_ua']['device'];
            $stats['devices'][$device] = ($stats['devices'][$device] ?? 0) + 1;
        }
        
        // Rango de fechas
        $timestamp = $file['modified'];
        if ($stats['date_range']['first'] === null || $timestamp < $stats['date_range']['first']) {
            $stats['date_range']['first'] = $timestamp;
        }
        if ($stats['date_range']['last'] === null || $timestamp > $stats['date_range']['last']) {
            $stats['date_range']['last'] = $timestamp;
        }
    }
    
    $stats['unique_ips'] = count($stats['unique_ips']);
    
    return $stats;
}

// Obtener datos
$dataFiles = getDataFiles();
$stats = getStats($dataFiles);
$nequiParticipants = getNequiParticipants();
$nequiStats = getNequiStats($nequiParticipants);

// Manejar acciones AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_file_data':
            if (isset($_GET['filename'])) {
                $filename = basename($_GET['filename']);
                $filepath = DATA_DIR . $filename;
                if (file_exists($filepath)) {
                    echo file_get_contents($filepath);
                } else {
                    echo json_encode(['error' => 'Archivo no encontrado']);
                }
            }
            exit;
            
        case 'delete_file':
            if (isset($_GET['filename'])) {
                $filename = basename($_GET['filename']);
                $filepath = DATA_DIR . $filename;
                if (file_exists($filepath) && unlink($filepath)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'No se pudo eliminar el archivo']);
                }
            }
            exit;
            
        case 'get_stats':
            echo json_encode($stats);
            exit;
            
        case 'get_nequi_participant':
            if (isset($_GET['filename'])) {
                $filename = basename($_GET['filename']);
                $filepath = PARTICIPANTS_DIR . $filename;
                if (file_exists($filepath)) {
                    echo file_get_contents($filepath);
                } else {
                    echo json_encode(['error' => 'Participante no encontrado']);
                }
            }
            exit;
            
        case 'get_nequi_stats':
            echo json_encode($nequiStats);
            exit;
            
        case 'delete_nequi_participant':
            if (isset($_GET['filename'])) {
                $filename = basename($_GET['filename']);
                $filepath = PARTICIPANTS_DIR . $filename;
                if (file_exists($filepath) && unlink($filepath)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'No se pudo eliminar el participante']);
                }
            }
            exit;
            
        case 'delete_photo':
            if (isset($_GET['filename'])) {
                $filename = basename($_GET['filename']);
                $filepath = PHOTOS_DIR . '/' . $filename;
                if (file_exists($filepath) && unlink($filepath)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'No se pudo eliminar la foto']);
                }
            } else {
                echo json_encode(['error' => 'Nombre de archivo no especificado']);
            }
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced IP Tracker - Panel de Administración</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #667eea;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-number {
            color: #764ba2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-label {
            font-size: 1.1em;
            color: #666;
            text-transform: uppercase;
            transition: color 0.3s ease;
        }
        
        .stat-card:hover .stat-label {
            color: #333;
            letter-spacing: 1px;
        }
        
        .section {
            background: white;
            margin: 30px 0;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .section-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .section-header h2 {
            color: #495057;
            font-size: 1.5em;
        }
        
        .section-content {
            padding: 20px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .json-viewer {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        .chart-container {
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .progress-bar {
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 5px 0;
            height: 20px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #667eea, #764ba2);
            height: 100%;
            border-radius: 10px;
            transition: width 0.6s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8em;
            font-weight: bold;
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .ip-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 10px;
            margin: 5px 0;
            border-radius: 0 5px 5px 0;
        }
        
        .geo-info {
            background: #e8f5e8;
            border-left: 4px solid #4caf50;
            padding: 10px;
            margin: 5px 0;
            border-radius: 0 5px 5px 0;
        }
        
        .maps-link {
            color: #007bff !important;
            text-decoration: none;
            font-size: 0.9em;
            margin-top: 8px;
            display: inline-block;
            padding: 5px 10px;
            background: #f0f8ff;
            border-radius: 4px;
            border: 1px solid #b3d9ff;
            transition: all 0.3s ease;
        }
        
        .maps-link:hover {
            background: #007bff;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,123,255,0.3);
        }
        
        .device-info {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 10px;
            margin: 5px 0;
            border-radius: 0 5px 5px 0;
        }
        
        .fingerprint-info {
            background: #fce4ec;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 10px;
            }
            
            .header h1 {
                font-size: 1.8em;
            }
            
            .header p {
                font-size: 1em;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin: 20px 0;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2em;
            }
            
            .section {
                margin: 20px 0;
            }
            
            .section-header {
                padding: 15px;
            }
            
            .section-header h2 {
                font-size: 1.3em;
            }
            
            .section-content {
                padding: 15px;
            }
            
            .data-table {
                font-size: 0.9em;
            }
            
            .data-table th,
            .data-table td {
                padding: 8px 6px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 0.8em;
                margin: 1px;
            }
            
            .modal-content {
                margin: 10% auto;
                padding: 15px;
                width: 95%;
            }
            
            .json-viewer {
                font-size: 0.8em;
                padding: 10px;
            }
            
            .progress-fill {
                font-size: 0.7em;
            }
            
            .maps-link {
                font-size: 0.8em;
                padding: 4px 8px;
            }
            
            .log-entry {
                margin-bottom: 10px;
                padding: 8px;
            }
            
            .log-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .log-details {
                font-size: 0.8em;
            }
            
            .logs-container {
                max-height: 300px;
                padding: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.5em;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-number {
                font-size: 1.8em;
            }
            
            .section-header h2 {
                font-size: 1.2em;
            }
            
            .data-table {
                font-size: 0.8em;
            }
            
            .data-table th,
            .data-table td {
                padding: 6px 4px;
            }
            
            .btn {
                padding: 5px 10px;
                font-size: 0.75em;
            }
            
            .modal-content {
                margin: 5% auto;
                padding: 10px;
                width: 98%;
            }
            
            .json-viewer {
                font-size: 0.75em;
                padding: 8px;
            }
            
            .log-entry {
                padding: 6px;
            }
            
            .log-details {
                font-size: 0.75em;
            }
        }
        
        /* Mejoras para tablas en móviles */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .data-table {
                min-width: 600px;
            }
        }
        
        /* Mejoras para el mapa */
        @media (max-width: 768px) {
            #nequiMap {
                height: 300px !important;
            }
        }
        
        @media (max-width: 480px) {
            #nequiMap {
                height: 250px !important;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .data-table {
                font-size: 0.8em;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
            
            /* Logs responsivos */
            .logs-container {
                max-height: 300px !important;
                padding: 10px !important;
            }
            
            .log-entry {
                margin-bottom: 10px !important;
                padding: 8px !important;
            }
            
            .log-header {
                flex-direction: column !important;
                align-items: flex-start !important;
            }
            
            .log-details {
                font-size: 0.8em !important;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 5px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .stat-card {
                padding: 10px;
            }
            
            .data-table {
                font-size: 0.7em;
            }
            
            .data-table th,
            .data-table td {
                padding: 6px 2px;
            }
            
            /* Logs muy pequeños */
            .logs-container {
                max-height: 250px !important;
                padding: 8px !important;
            }
            
            .log-entry {
                margin-bottom: 8px !important;
                padding: 6px !important;
            }
            
            .log-details {
                font-size: 0.75em !important;
            }
            
            .log-type {
                font-size: 0.9em !important;
            }
            
            .log-time {
                font-size: 0.8em !important;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🔍 Advanced IP Tracker</h1>
            <p>Panel de Administración - Herramienta Educativa de Seguridad</p>
        </div>
    </div>
    
    <div class="container">
        <div class="alert alert-warning">
            <strong>⚠️ ADVERTENCIA EDUCATIVA:</strong> Esta herramienta está diseñada exclusivamente para fines educativos y de concienciación sobre seguridad. Los datos mostrados aquí demuestran la información que puede ser recolectada cuando los usuarios hacen clic en enlaces sospechosos. Úsala responsablemente.
        </div>
        
        <!-- Estadísticas Generales -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_captures']; ?></div>
                <div class="stat-label">Capturas Totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['unique_ips']; ?></div>
                <div class="stat-label">IPs Únicas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_size'] / 1024, 1); ?> KB</div>
                <div class="stat-label">Datos Recolectados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($stats['browsers']); ?></div>
                <div class="stat-label">Navegadores Detectados</div>
            </div>
        </div>
        
        <!-- Estadísticas Sistema Nequi -->
        <?php if ($nequiStats['total_participants'] > 0): ?>
        <div class="section">
            <div class="section-header">
                <h2>💰 Sistema de Promociones Nequi</h2>
            </div>
            <div class="section-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $nequiStats['total_participants']; ?></div>
                        <div class="stat-label">Participantes Registrados</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$<?php echo number_format($nequiStats['total_earnings']); ?></div>
                        <div class="stat-label">Ganancias Acumuladas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $nequiStats['completed_challenges']; ?></div>
                        <div class="stat-label">Retos Completados</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($nequiStats['locations']); ?></div>
                        <div class="stat-label">Ubicaciones Únicas</div>
                    </div>
                </div>
                
                <!-- Estadísticas Detalladas de Nequi -->
                <div class="stats-grid" style="margin-top: 20px;">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($nequiStats['regions']); ?></div>
                        <div class="stat-label">Regiones Activas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($nequiStats['avg_completion_rate'], 1); ?>%</div>
                        <div class="stat-label">Tasa Promedio Completado</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($nequiStats['popular_challenges']); ?></div>
                        <div class="stat-label">Retos Populares</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($nequiStats['gps_precision']['high'], 1); ?>%</div>
                        <div class="stat-label">Precisión GPS Alta</div>
                    </div>
                </div>
                
                <!-- Análisis Regional -->
                <?php if (!empty($nequiStats['regions'])): ?>
                <div style="margin-top: 30px;">
                    <h3>📍 Análisis por Regiones</h3>
                    <?php 
                    $maxRegionCount = max($nequiStats['regions']);
                    foreach ($nequiStats['regions'] as $region => $count): 
                        $percentage = ($maxRegionCount > 0) ? ($count / $maxRegionCount) * 100 : 0;
                        $totalPercentage = ($nequiStats['total_participants'] > 0) ? round(($count / $nequiStats['total_participants']) * 100, 1) : 0;
                    ?>
                    <div style="margin: 10px 0;">
                        <strong><?php echo htmlspecialchars($region); ?></strong> (<?php echo $count; ?> participantes - <?php echo $totalPercentage; ?>%)
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Retos Más Populares -->
                <?php if (!empty($nequiStats['popular_challenges'])): ?>
                <div style="margin-top: 30px;">
                    <h3>🏆 Retos Más Populares</h3>
                    <?php 
                    $maxChallengeCount = max($nequiStats['popular_challenges']);
                    foreach ($nequiStats['popular_challenges'] as $challenge => $count): 
                        $percentage = ($maxChallengeCount > 0) ? ($count / $maxChallengeCount) * 100 : 0;
                        $totalPercentage = ($nequiStats['completed_challenges'] > 0) ? round(($count / $nequiStats['completed_challenges']) * 100, 1) : 0;
                    ?>
                    <div style="margin: 10px 0;">
                        <strong><?php echo htmlspecialchars($challenge); ?></strong> (<?php echo $count; ?> completados - <?php echo $totalPercentage; ?>%)
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Distribución de Ganancias -->
                <?php if (!empty($nequiStats['earnings_distribution'])): ?>
                <div style="margin-top: 30px;">
                    <h3>💵 Distribución de Ganancias</h3>
                    <?php 
                    $maxEarningsCount = max($nequiStats['earnings_distribution']);
                    foreach ($nequiStats['earnings_distribution'] as $range => $count): 
                        $percentage = ($maxEarningsCount > 0) ? ($count / $maxEarningsCount) * 100 : 0;
                        $totalPercentage = ($nequiStats['total_participants'] > 0) ? round(($count / $nequiStats['total_participants']) * 100, 1) : 0;
                    ?>
                    <div style="margin: 10px 0;">
                        <strong><?php echo htmlspecialchars($range); ?></strong> (<?php echo $count; ?> participantes - <?php echo $totalPercentage; ?>%)
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Gráficos de Estadísticas -->
        <?php if (!empty($stats['browsers'])): ?>
        <div class="section">
            <div class="section-header">
                <h2>📊 Distribución de Navegadores</h2>
            </div>
            <div class="section-content">
                <?php 
                $maxCount = max($stats['browsers']);
                foreach ($stats['browsers'] as $browser => $count): 
                    $percentage = ($maxCount > 0) ? ($count / $maxCount) * 100 : 0;
                    $totalPercentage = ($stats['total_captures'] > 0) ? round(($count / $stats['total_captures']) * 100, 1) : 0;
                ?>
                <div style="margin: 10px 0;">
                    <strong><?php echo htmlspecialchars($browser); ?></strong> (<?php echo $count; ?> capturas)
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%">
                            <?php echo $totalPercentage; ?>%
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($stats['operating_systems'])): ?>
        <div class="section">
            <div class="section-header">
                <h2>💻 Sistemas Operativos</h2>
            </div>
            <div class="section-content">
                <?php 
                $maxCount = max($stats['operating_systems']);
                foreach ($stats['operating_systems'] as $os => $count): 
                    $percentage = ($maxCount > 0) ? ($count / $maxCount) * 100 : 0;
                    $totalPercentage = ($stats['total_captures'] > 0) ? round(($count / $stats['total_captures']) * 100, 1) : 0;
                ?>
                <div style="margin: 10px 0;">
                    <strong><?php echo htmlspecialchars($os); ?></strong> (<?php echo $count; ?> capturas)
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%">
                            <?php echo $totalPercentage; ?>%
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Ubicaciones de Participantes Nequi -->
        <?php if (!empty($nequiStats['locations'])): ?>
        <div class="section">
            <div class="section-header">
                <h2>📍 Ubicaciones de Participantes Nequi</h2>
            </div>
            <div class="section-content">
                <?php 
                $maxCount = max($nequiStats['locations']);
                foreach ($nequiStats['locations'] as $location => $count): 
                    $percentage = ($maxCount > 0) ? ($count / $maxCount) * 100 : 0;
                    $totalPercentage = ($nequiStats['total_participants'] > 0) ? round(($count / $nequiStats['total_participants']) * 100, 1) : 0;
                ?>
                <div style="margin: 10px 0;">
                    <strong><?php echo htmlspecialchars($location); ?></strong> (<?php echo $count; ?> participantes)
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%">
                            <?php echo $totalPercentage; ?>%
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Lista de Participantes Nequi -->
        <?php if (!empty($nequiParticipants)): ?>
        <div class="section">
            <div class="section-header">
                <h2>👥 Participantes del Sistema Nequi</h2>
            </div>
            <div class="section-content">
                <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha Registro</th>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Ubicación</th>
                            <th>Ganancias</th>
                            <th>Retos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nequiParticipants as $participant): 
                            $data = $participant['data'];
                            $completedChallenges = isset($data['completed_challenges']) ? count($data['completed_challenges']) : 0;
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($data['timestamp'])); ?></td>
                            <td><?php echo htmlspecialchars($data['name']); ?></td>
                            <td><?php echo htmlspecialchars($data['document_type'] . ': ' . $data['document_number']); ?></td>
                            <td><?php echo htmlspecialchars($data['email']); ?></td>
                            <td><?php echo htmlspecialchars($data['phone']); ?></td>
                            <td><?php echo htmlspecialchars($data['address']); ?></td>
                            <td>$<?php echo number_format($data['current_earnings']); ?></td>
                            <td><?php echo $completedChallenges; ?>/10</td>
                            <td>
                                <button class="btn btn-info" onclick="viewNequiParticipant('<?php echo $participant['filename']; ?>')" title="Ver detalles">
                                    👁️
                                </button>
                                <button class="btn btn-danger" onclick="deleteNequiParticipant('<?php echo $participant['filename']; ?>')" title="Eliminar">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Fotos de Retos Completados -->
        <?php 
        $challengePhotos = [];
        $participantsDir = __DIR__ . '/../participants';
        
        if (is_dir($participantsDir)) {
            // Buscar en todos los directorios de participantes
            $participantDirs = glob($participantsDir . '/challenges_*', GLOB_ONLYDIR);
            
            foreach ($participantDirs as $participantDir) {
                $photosDir = $participantDir . '/photos';
                if (is_dir($photosDir)) {
                    $photoFiles = glob($photosDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                    
                    foreach ($photoFiles as $photoFile) {
                        $filename = basename($photoFile);
                        $participantId = basename($participantDir);
                        
                        // Extraer información del nombre del archivo: challenge_X_date_time_uniqid.ext
                        if (preg_match('/^challenge_(\d+)_([\d\-_]+)_([a-f0-9]+)\.(jpg|jpeg|png|gif|webp)$/i', $filename, $matches)) {
                            $challengePhotos[] = [
                                'filename' => $filename,
                                'participant_id' => str_replace('challenges_', '', $participantId),
                                'challenge_id' => $matches[1],
                                'timestamp' => filemtime($photoFile),
                                'extension' => $matches[4],
                                'path' => $photoFile,
                                'relative_path' => '../participants/' . basename($participantDir) . '/photos/' . $filename
                            ];
                        }
                    }
                }
            }
            
            // Ordenar por timestamp descendente
            usort($challengePhotos, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });
        }
        ?>
        
        <?php if (!empty($challengePhotos)): ?>
        <div class="section">
            <div class="section-header">
                <h2>📸 Fotos de Retos Completados</h2>
            </div>
            <div class="section-content">
                <div class="photos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <?php foreach (array_slice($challengePhotos, 0, 12) as $photo): ?>
                    <div class="photo-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <img src="<?php echo htmlspecialchars($photo['relative_path']); ?>" 
                             alt="Reto <?php echo $photo['challenge_id']; ?>" 
                             style="width: 100%; height: 150px; object-fit: cover; cursor: pointer;"
                             onclick="viewPhotoDetails('<?php echo htmlspecialchars($photo['relative_path']); ?>', '<?php echo $photo['participant_id']; ?>', '<?php echo $photo['challenge_id']; ?>', '<?php echo $photo['timestamp']; ?>')">
                        <div style="padding: 10px;">
                            <p style="margin: 0; font-size: 12px; color: #666;">
                                <strong>Reto:</strong> <?php echo $photo['challenge_id']; ?><br>
                                <strong>Participante:</strong> <?php echo substr($photo['participant_id'], 0, 10); ?>...<br>
                                <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', $photo['timestamp']); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($challengePhotos) > 12): ?>
                <p style="text-align: center; color: #666; font-style: italic;">
                    Mostrando las 12 fotos más recientes de <?php echo count($challengePhotos); ?> total.
                </p>
                <?php endif; ?>
                
                <table class="data-table" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Participante</th>
                            <th>Reto</th>
                            <th>Archivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($challengePhotos as $photo): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', $photo['timestamp']); ?></td>
                            <td><?php echo htmlspecialchars(substr($photo['participant_id'], 0, 15)); ?>...</td>
                            <td>Reto <?php echo $photo['challenge_id']; ?></td>
                            <td><?php echo htmlspecialchars($photo['filename']); ?></td>
                            <td>
                                <button class="btn btn-info" onclick="viewPhotoDetails('<?php echo htmlspecialchars($photo['relative_path']); ?>', '<?php echo $photo['participant_id']; ?>', '<?php echo $photo['challenge_id']; ?>', '<?php echo $photo['timestamp']; ?>')" title="Ver foto">
                                    👁️
                                </button>
                                <button class="btn btn-danger" onclick="deletePhoto('<?php echo htmlspecialchars($photo['path']); ?>')" title="Eliminar foto">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mapa de Ubicaciones de Participantes Nequi -->
        <?php if (!empty($nequiParticipants)): ?>
        <div class="section">
            <div class="section-header">
                <h2>🗺️ Mapa de Ubicaciones - Participantes Nequi</h2>
            </div>
            <div class="section-content">
                <div id="nequi-map" style="height: 500px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>
                <div style="margin-top: 10px; font-size: 12px; color: #666;">
                    <p><strong>Leyenda:</strong></p>
                    <p>🎯 Verde: GPS de alta precisión (&lt;100m) | 📍 Azul: GPS de precisión media (&lt;1km) | 🌐 Rojo: Ubicación aproximada por IP</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Logs de Actividad Nequi -->
        <?php if ($nequiStats['total_participants'] > 0): ?>
        <div class="section">
            <div class="section-header">
                <h2>📝 Logs de Actividad - Sistema Nequi</h2>
            </div>
            <div class="section-content">
                <?php
                // Leer logs de registro
                $registrationsLog = [];
                $registrationsLogFile = __DIR__ . '/../participants/registrations_log.json';
                if (file_exists($registrationsLogFile)) {
                    $registrationsLogContent = file_get_contents($registrationsLogFile);
                    $registrationsLog = json_decode($registrationsLogContent, true) ?: [];
                }
                
                // Leer logs de retos completados
                $challengesLog = [];
                $challengesLogFile = __DIR__ . '/../participants/challenges_log.json';
                if (file_exists($challengesLogFile)) {
                    $challengesLogContent = file_get_contents($challengesLogFile);
                    $challengesLog = json_decode($challengesLogContent, true) ?: [];
                }
                
                // Combinar y ordenar logs por timestamp
                $allLogs = [];
                
                // Agregar logs de registro
                foreach ($registrationsLog as $log) {
                    $allLogs[] = [
                        'type' => 'registration',
                        'timestamp' => $log['timestamp'],
                        'data' => $log
                    ];
                }
                
                // Agregar logs de retos
                foreach ($challengesLog as $log) {
                    $allLogs[] = [
                        'type' => 'challenge',
                        'timestamp' => $log['timestamp'],
                        'data' => $log
                    ];
                }
                
                // Ordenar por timestamp descendente (más recientes primero)
                usort($allLogs, function($a, $b) {
                    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
                });
                
                // Mostrar solo los últimos 20 logs
                $recentLogs = array_slice($allLogs, 0, 20);
                ?>
                
                <?php if (empty($recentLogs)): ?>
                    <p>No hay actividad registrada aún.</p>
                <?php else: ?>
                <div class="logs-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 15px;">
                    <?php foreach ($recentLogs as $log): ?>
                    <div class="log-entry" style="margin-bottom: 15px; padding: 10px; border-left: 4px solid <?php echo $log['type'] === 'registration' ? '#28a745' : '#007bff'; ?>; background-color: #f8f9fa; border-radius: 4px;">
                        <div class="log-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <span class="log-type" style="font-weight: bold; color: <?php echo $log['type'] === 'registration' ? '#28a745' : '#007bff'; ?>;">
                                <?php if ($log['type'] === 'registration'): ?>
                                    👤 Nuevo Registro
                                <?php else: ?>
                                    🏆 Reto Completado
                                <?php endif; ?>
                            </span>
                            <span class="log-time" style="color: #6c757d; font-size: 0.9em;">
                                <?php echo date('d/m/Y H:i:s', strtotime($log['timestamp'])); ?>
                            </span>
                        </div>
                        <div class="log-details" style="font-size: 0.9em;">
                            <?php if ($log['type'] === 'registration'): ?>
                                <strong><?php echo htmlspecialchars($log['data']['name']); ?></strong><br>
                                📧 <?php echo htmlspecialchars($log['data']['email']); ?><br>
                                📱 <?php echo htmlspecialchars($log['data']['phone']); ?><br>
                                📍 <?php echo isset($log['data']['location']['latitude']) ? 'GPS: ' . $log['data']['location']['latitude'] . ', ' . $log['data']['location']['longitude'] : 'Ubicación no disponible'; ?><br>
                                🌐 IP: <?php echo htmlspecialchars($log['data']['ipAddress']); ?>
                            <?php else: ?>
                                <strong><?php echo htmlspecialchars($log['data']['challengeTitle']); ?></strong><br>
                                👤 Usuario: <?php echo htmlspecialchars($log['data']['userId']); ?><br>
                                💰 Recompensa: $<?php echo number_format($log['data']['reward']); ?><br>
                                📂 Categoría: <?php echo htmlspecialchars($log['data']['category']); ?><br>
                                <?php if (isset($log['data']['photoFilename'])): ?>
                                    📸 Foto: <?php echo htmlspecialchars($log['data']['photoFilename']); ?><br>
                                <?php elseif (isset($log['data']['textResponse'])): ?>
                                    📝 Respuesta: <?php echo htmlspecialchars(substr($log['data']['textResponse'], 0, 50)) . (strlen($log['data']['textResponse']) > 50 ? '...' : ''); ?><br>
                                <?php elseif (isset($log['data']['selectedOption'])): ?>
                                    ✅ Opción: <?php echo htmlspecialchars($log['data']['selectedOption']); ?><br>
                                <?php endif; ?>
                                🌐 IP: <?php echo htmlspecialchars($log['data']['ipAddress']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="margin-top: 15px; text-align: center;">
                    <small style="color: #6c757d;">Mostrando los últimos <?php echo count($recentLogs); ?> eventos de actividad</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Lista de Capturas -->
        <div class="section">
            <div class="section-header">
                <h2>📋 Capturas Recientes</h2>
            </div>
            <div class="section-content">
                <?php if (empty($dataFiles)): ?>
                    <p>No hay capturas disponibles. Los datos aparecerán aquí cuando alguien visite el enlace de tracking.</p>
                <?php else: ?>
                <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>IP</th>
                            <th>Ubicación</th>
                            <th>Precisión</th>
                            <th>Navegador</th>
                            <th>Dispositivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataFiles as $file): 
                            $data = $file['data'];
                            $ip = $data['network']['client_ip'] ?? 'Desconocida';
                            $location = 'Desconocida';
                            $precision = '🌐 Solo IP';
                            
                            // Verificar si hay geolocalización GPS en additional_data
                            if (isset($data['additional_data']['locationData']) && 
                                isset($data['additional_data']['locationData']['latitude']) && 
                                isset($data['additional_data']['locationData']['longitude'])) {
                                
                                $gps = $data['additional_data']['locationData'];
                                $location = "GPS: {$gps['latitude']}, {$gps['longitude']}";
                                
                                // Determinar precisión basada en accuracy
                                $accuracy = $gps['accuracy'] ?? 1000;
                                if ($accuracy < 10) {
                                    $precision = '🎯 Muy Alta (<10m)';
                                } elseif ($accuracy < 100) {
                                    $precision = '🎯 Alta (<100m)';
                                } elseif ($accuracy < 1000) {
                                    $precision = '📍 Media (<1km)';
                                } else {
                                    $precision = '📍 Baja (>1km)';
                                }
                            }
                            // Verificar si hay geolocalización procesada (formato anterior)
                            elseif (isset($data['geolocation']['coordinates'])) {
                                $geoData = $data['geolocation'];
                                
                                // Determinar la mejor ubicación disponible
                                if (isset($geoData['coordinates']['gps']) && isset($geoData['validation']['gps']) && $geoData['validation']['gps'] === 'valid') {
                                    $gps = $geoData['coordinates']['gps'];
                                    $location = "GPS: {$gps['latitude']}, {$gps['longitude']}";
                                    
                                    // Determinar precisión basada en accuracy
                                    $accuracy = $gps['accuracy'];
                                    if ($accuracy < 10) {
                                        $precision = '🎯 Muy Alta (<10m)';
                                    } elseif ($accuracy < 100) {
                                        $precision = '🎯 Alta (<100m)';
                                    } elseif ($accuracy < 1000) {
                                        $precision = '📍 Media (<1km)';
                                    } else {
                                        $precision = '📍 Baja (>1km)';
                                    }
                                } elseif (isset($geoData['coordinates']['ip_services']) && !empty($geoData['coordinates']['ip_services'])) {
                                    $ipService = $geoData['coordinates']['ip_services'][0];
                                    $location = $ipService['city'] . ', ' . $ipService['country'];
                                    $precision = '🌐 IP Múltiple';
                                }
                            } elseif (isset($data['geolocation']['advanced_geolocation'])) {
                                // Compatibilidad con formato anterior
                                $advGeo = $data['geolocation']['advanced_geolocation'];
                                
                                if (isset($advGeo['coordinates']['gps'])) {
                                    $gps = $advGeo['coordinates']['gps'];
                                    $location = "GPS: {$gps['latitude']}, {$gps['longitude']}";
                                    
                                    $accuracy = $gps['accuracy'];
                                    if ($accuracy < 10) {
                                        $precision = '🎯 Muy Alta (<10m)';
                                    } elseif ($accuracy < 100) {
                                        $precision = '🎯 Alta (<100m)';
                                    } elseif ($accuracy < 1000) {
                                        $precision = '📍 Media (<1km)';
                                    } else {
                                        $precision = '📍 Baja (>1km)';
                                    }
                                } elseif (isset($advGeo['coordinates']['ip_services']) && !empty($advGeo['coordinates']['ip_services'])) {
                                    $ipService = $advGeo['coordinates']['ip_services'][0];
                                    $location = $ipService['city'] . ', ' . $ipService['country'];
                                    $precision = '🌐 IP Múltiple';
                                }
                            } elseif (isset($data['network']['ip_details']['geolocation']['city']) && 
                                      isset($data['network']['ip_details']['geolocation']['country'])) {
                                $location = $data['network']['ip_details']['geolocation']['city'] . ', ' . 
                                           $data['network']['ip_details']['geolocation']['country'];
                            }
                            
                            $browser = $data['browser']['parsed_ua']['browser'] ?? 'Desconocido';
                            $device = $data['browser']['parsed_ua']['device'] ?? 'Desconocido';
                        ?>
                        <tr>
                            <td><?php 
                                // Configurar zona horaria de Bogotá, Colombia
                                $bogotaTimezone = new DateTimeZone('America/Bogota');
                                $dateTime = new DateTime('@' . $file['modified']);
                                $dateTime->setTimezone($bogotaTimezone);
                                echo $dateTime->format('d/m/Y H:i:s') . ' (COT)';
                            ?></td>
                            <td>
                                <div class="ip-info">
                                    <strong><?php echo htmlspecialchars($ip); ?></strong>
                                </div>
                            </td>
                            <td>
                                <div class="geo-info">
                                    <?php echo htmlspecialchars($location); ?>
                                </div>
                            </td>
                            <td>
                                <div class="precision-info" style="font-size: 0.9em;">
                                    <?php echo $precision; ?>
                                </div>
                            </td>
                            <td>
                                <div class="device-info">
                                    <?php echo htmlspecialchars($browser); ?>
                                </div>
                            </td>
                            <td>
                                <div class="device-info">
                                    <?php echo htmlspecialchars($device); ?>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-info" onclick="viewDetails('<?php echo $file['filename']; ?>')">
                                    👁️ Ver Detalles
                                </button>
                                <button class="btn btn-danger" onclick="deleteFile('<?php echo $file['filename']; ?>')">
                                    🗑️ Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal para ver detalles -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>🔍 Detalles Completos de la Captura</h2>
            <div id="detailsContent"></div>
        </div>
    </div>
    
    <script>
        function viewDetails(filename) {
            fetch(`?action=get_file_data&filename=${filename}`)
                .then(response => response.json())
                .then(data => {
                    const modal = document.getElementById('detailsModal');
                    const content = document.getElementById('detailsContent');
                    
                    let html = '';
                    
                    // Información básica
                    if (data.network && data.network.client_ip) {
                        html += '<div class="ip-info"><strong>🌐 IP:</strong> ' + data.network.client_ip + '</div>';
                    }
                    
                    // Información de Fingerprinting Avanzado
                    if (data.fingerprinting) {
                        html += '<div style="background: #f0f8ff; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #2196F3;">';
                        html += '<h3>🔍 Fingerprinting del Navegador</h3>';
                        
                        if (data.fingerprinting.canvas && data.fingerprinting.canvas !== 'unavailable') {
                            html += '<div style="margin: 10px 0;">';
                            html += '<strong>🎨 Canvas Fingerprint:</strong><br>';
                            html += '<code style="background: #f5f5f5; padding: 5px; border-radius: 3px; font-size: 0.9em; word-break: break-all;">';
                            html += data.fingerprinting.canvas.substring(0, 100) + (data.fingerprinting.canvas.length > 100 ? '...' : '');
                            html += '</code>';
                            html += '</div>';
                        }
                        
                        if (data.fingerprinting.webgl && data.fingerprinting.webgl !== 'unavailable') {
                            html += '<div style="margin: 10px 0;">';
                            html += '<strong>🎮 WebGL Fingerprint:</strong><br>';
                            html += '<code style="background: #f5f5f5; padding: 5px; border-radius: 3px; font-size: 0.9em; word-break: break-all;">';
                            html += data.fingerprinting.webgl.substring(0, 100) + (data.fingerprinting.webgl.length > 100 ? '...' : '');
                            html += '</code>';
                            html += '</div>';
                        }
                        
                        if (data.fingerprinting.audio && data.fingerprinting.audio !== 'unavailable') {
                            html += '<div style="margin: 10px 0;">';
                            html += '<strong>🔊 Audio Fingerprint:</strong><br>';
                            html += '<code style="background: #f5f5f5; padding: 5px; border-radius: 3px; font-size: 0.9em; word-break: break-all;">';
                            html += data.fingerprinting.audio.substring(0, 100) + (data.fingerprinting.audio.length > 100 ? '...' : '');
                            html += '</code>';
                            html += '</div>';
                        }
                        
                        if (data.fingerprinting.fonts && Array.isArray(data.fingerprinting.fonts) && data.fingerprinting.fonts.length > 0) {
                            html += '<div style="margin: 10px 0;">';
                            html += '<strong>🔤 Fuentes Detectadas (' + data.fingerprinting.fonts.length + '):</strong><br>';
                            html += '<div style="max-height: 100px; overflow-y: auto; background: #f9f9f9; padding: 8px; border-radius: 3px; font-size: 0.85em;">';
                            html += data.fingerprinting.fonts.slice(0, 20).join(', ');
                            if (data.fingerprinting.fonts.length > 20) {
                                html += ' ... y ' + (data.fingerprinting.fonts.length - 20) + ' más';
                            }
                            html += '</div>';
                            html += '</div>';
                        }
                        
                        html += '</div>';
                    }
                    
                    // Información de Hardware Avanzado
                    if (data.device) {
                        html += '<div style="background: #e8f5e8; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #4CAF50;">';
                        html += '<h3>💻 Información de Hardware</h3>';
                        
                        if (data.device.hardware_concurrency) {
                            html += '<p><strong>🔧 CPU Cores:</strong> ' + data.device.hardware_concurrency + '</p>';
                        }
                        
                        if (data.device.device_memory && data.device.device_memory !== 'unknown') {
                            html += '<p><strong>🧠 Memoria RAM:</strong> ' + data.device.device_memory + ' GB</p>';
                        }
                        
                        html += '<p><strong>📱 Resolución:</strong> ' + (data.device.screen_width || 0) + 'x' + (data.device.screen_height || 0) + '</p>';
                        html += '<p><strong>🎨 Profundidad de Color:</strong> ' + (data.device.screen_color_depth || 'N/A') + ' bits</p>';
                        html += '<p><strong>🖼️ Ventana:</strong> ' + (data.device.window_width || 0) + 'x' + (data.device.window_height || 0) + '</p>';
                        
                        html += '</div>';
                    }
                    
                    // Información de Sensores y Batería
                    if (data.device_sensors && data.device_sensors !== 'unavailable') {
                        html += '<div style="background: #fff3e0; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #FF9800;">';
                        html += '<h3>📱 Sensores del Dispositivo</h3>';
                        
                        if (typeof data.device_sensors === 'object') {
                            if (data.device_sensors.accelerometer) {
                                html += '<p><strong>📐 Acelerómetro:</strong> Disponible</p>';
                            }
                            if (data.device_sensors.gyroscope) {
                                html += '<p><strong>🌀 Giroscopio:</strong> Disponible</p>';
                            }
                            if (data.device_sensors.magnetometer) {
                                html += '<p><strong>🧭 Magnetómetro:</strong> Disponible</p>';
                            }
                        }
                        
                        html += '</div>';
                    }
                    
                    if (data.battery && data.battery !== 'unavailable') {
                        html += '<div style="background: #f3e5f5; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #9C27B0;">';
                        html += '<h3>🔋 Estado de la Batería</h3>';
                        
                        if (typeof data.battery === 'object') {
                            if (data.battery.level !== undefined) {
                                const batteryPercent = Math.round(data.battery.level * 100);
                                html += '<p><strong>Nivel:</strong> ' + batteryPercent + '%</p>';
                            }
                            if (data.battery.charging !== undefined) {
                                html += '<p><strong>Estado:</strong> ' + (data.battery.charging ? 'Cargando' : 'Descargando') + '</p>';
                            }
                            if (data.battery.chargingTime !== undefined && data.battery.chargingTime !== Infinity) {
                                html += '<p><strong>Tiempo de Carga:</strong> ' + Math.round(data.battery.chargingTime / 60) + ' minutos</p>';
                            }
                            if (data.battery.dischargingTime !== undefined && data.battery.dischargingTime !== Infinity) {
                                html += '<p><strong>Tiempo de Descarga:</strong> ' + Math.round(data.battery.dischargingTime / 60) + ' minutos</p>';
                            }
                        }
                        
                        html += '</div>';
                    }
                    
                    // Información de zona horaria
                    if (data.timezone) {
                        html += '<div class="geo-info"><strong>🕐 Zona Horaria:</strong> ' + data.timezone.timezone + ' (Offset: ' + data.timezone.timezone_offset + ')</div>';
                        
                        // Información avanzada de zona horaria si está disponible
                        if (data.timezone.timezone_advanced) {
                            const tzAdv = data.timezone.timezone_advanced;
                            html += '<div style="background: #f0f8ff; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                            html += '<h4>🌍 Configuración Regional Avanzada</h4>';
                            
                            if (tzAdv.regional) {
                                const regional = tzAdv.regional;
                                html += '<p><strong>Idioma:</strong> ' + (regional.locale || 'N/A') + '</p>';
                                if (regional.languages && Array.isArray(regional.languages)) {
                                    html += '<p><strong>Idiomas:</strong> ' + regional.languages.join(', ') + '</p>';
                                }
                                html += '<p><strong>Formato de Fecha:</strong> ' + (regional.dateFormat || 'N/A') + '</p>';
                                html += '<p><strong>Formato de Hora:</strong> ' + (regional.timeFormat || 'N/A') + '</p>';
                                if (regional.numberFormat) {
                                    html += '<p><strong>Formato Numérico:</strong> Decimal: "' + (regional.numberFormat.decimal || 'N/A') + '", Miles: "' + (regional.numberFormat.thousands || 'N/A') + '"</p>';
                                }
                            }
                            
                            if (tzAdv.validation) {
                                const validation = tzAdv.validation;
                                html += '<p><strong>Horario de Verano:</strong> ' + (validation.dstActive ? 'Activo' : 'Inactivo') + '</p>';
                                if (validation.timeConsistency !== undefined) {
                                    html += '<p><strong>Consistencia Temporal:</strong> ' + (validation.timeConsistency ? 'Válida' : 'Inconsistente') + '</p>';
                                }
                                if (validation.serverTimeDiff !== undefined) {
                                    html += '<p><strong>Diferencia con Servidor:</strong> ' + Math.round(validation.serverTimeDiff / 1000 * 100) / 100 + ' segundos</p>';
                                }
                            }
                            
                            if (tzAdv.advanced) {
                                const advanced = tzAdv.advanced;
                                html += '<p><strong>Calendario:</strong> ' + (advanced.calendar || 'N/A') + '</p>';
                                html += '<p><strong>Ciclo de Hora:</strong> ' + (advanced.hourCycle || 'N/A') + '</p>';
                                if (advanced.weekStart !== undefined) {
                                    const weekDays = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                    html += '<p><strong>Primer Día de Semana:</strong> ' + (weekDays[advanced.weekStart] || 'N/A') + '</p>';
                                }
                            }
                            
                            html += '</div>';
                        }
                    }
                    
                    // Información de geolocalización GPS desde additional_data
                    if (data.additional_data && data.additional_data.locationData) {
                        const gps = data.additional_data.locationData;
                        let geoHtml = '<div class="geo-info"><h3>📍 Geolocalización GPS</h3>';
                        
                        // Determinar precisión
                        const accuracy = gps.accuracy || 1000;
                        let precisionLabel = '📍 Baja (>1km)';
                        if (accuracy < 10) {
                            precisionLabel = '🎯 Muy Alta (<10m)';
                        } else if (accuracy < 100) {
                            precisionLabel = '🎯 Alta (<100m)';
                        } else if (accuracy < 1000) {
                            precisionLabel = '📍 Media (<1km)';
                        }
                        
                        geoHtml += '<p><strong>Precisión:</strong> ' + precisionLabel + '</p>';
                        geoHtml += '<p><strong>Método:</strong> GPS del dispositivo</p>';
                        
                        // Coordenadas GPS
                        geoHtml += '<div style="background: #e8f5e8; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                        geoHtml += '<h4>🛰️ Coordenadas GPS</h4>';
                        geoHtml += '<p><strong>Latitud:</strong> ' + gps.latitude + '</p>';
                        geoHtml += '<p><strong>Longitud:</strong> ' + gps.longitude + '</p>';
                        geoHtml += '<p><strong>Precisión:</strong> ±' + accuracy + ' metros</p>';
                        if (gps.altitude) geoHtml += '<p><strong>Altitud:</strong> ' + gps.altitude + ' m</p>';
                        if (gps.speed) geoHtml += '<p><strong>Velocidad:</strong> ' + gps.speed + ' m/s</p>';
                        
                        // Enlace a Google Maps con coordenadas GPS
                        const mapsUrl = `https://www.google.com/maps?q=${gps.latitude},${gps.longitude}`;
                        geoHtml += '<a href="' + mapsUrl + '" target="_blank" class="maps-link">🗺️ Ver ubicación GPS en Google Maps</a>';
                        geoHtml += '</div>';
                        
                        geoHtml += '</div>';
                        html += geoHtml;
                    }
                    // Información de geolocalización avanzada (formato anterior)
                    else if (data.geolocation && data.geolocation.advanced_geolocation) {
                        const advGeo = data.geolocation.advanced_geolocation;
                        let geoHtml = '<div class="geo-info"><h3>📍 Geolocalización Avanzada</h3>';
                        
                        // Mostrar precisión estimada
                        if (advGeo.estimated_precision) {
                            const precisionLabels = {
                                'very_high': '🎯 Muy Alta (GPS <10m)',
                                'high': '🎯 Alta (GPS <100m)',
                                'medium': '📍 Media (GPS <1km)',
                                'low': '📍 Baja (GPS >1km)',
                                'ip_only': '🌐 Solo IP (aprox.)'
                            };
                            geoHtml += '<p><strong>Precisión:</strong> ' + (precisionLabels[advGeo.estimated_precision] || advGeo.estimated_precision) + '</p>';
                        }
                        
                        // Mostrar métodos utilizados
                        if (advGeo.methods_used && advGeo.methods_used.length > 0) {
                            geoHtml += '<p><strong>Métodos:</strong> ' + advGeo.methods_used.join(', ').toUpperCase() + '</p>';
                        }
                        
                        // Coordenadas GPS si están disponibles
                        if (advGeo.coordinates && advGeo.coordinates.gps) {
                            const gps = advGeo.coordinates.gps;
                            geoHtml += '<div style="background: #e8f5e8; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                            geoHtml += '<h4>🛰️ Coordenadas GPS</h4>';
                            geoHtml += '<p><strong>Latitud:</strong> ' + gps.latitude + '</p>';
                            geoHtml += '<p><strong>Longitud:</strong> ' + gps.longitude + '</p>';
                            geoHtml += '<p><strong>Precisión:</strong> ±' + gps.accuracy + ' metros</p>';
                            if (gps.altitude) geoHtml += '<p><strong>Altitud:</strong> ' + gps.altitude + ' m</p>';
                            if (gps.speed) geoHtml += '<p><strong>Velocidad:</strong> ' + gps.speed + ' m/s</p>';
                            
                            // Enlace a Google Maps con coordenadas GPS
                            const mapsUrl = `https://www.google.com/maps?q=${gps.latitude},${gps.longitude}`;
                            geoHtml += '<a href="' + mapsUrl + '" target="_blank" class="maps-link">🗺️ Ver ubicación GPS en Google Maps</a>';
                            geoHtml += '</div>';
                        }
                        
                        // Información de servicios IP
                        if (advGeo.coordinates && advGeo.coordinates.ip_services && advGeo.coordinates.ip_services.length > 0) {
                            geoHtml += '<div style="background: #fff3e0; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                            geoHtml += '<h4>🌐 Geolocalización por IP</h4>';
                            advGeo.coordinates.ip_services.forEach(service => {
                                geoHtml += '<div style="margin: 5px 0; padding: 5px; border-left: 3px solid #ff9800;">';
                                geoHtml += '<strong>' + service.service + ':</strong> ';
                                geoHtml += service.city + ', ' + service.region + ', ' + service.country;
                                if (service.latitude && service.longitude) {
                                    geoHtml += ' (' + service.latitude + ', ' + service.longitude + ')';
                                }
                                geoHtml += '<br><small>ISP: ' + service.isp + '</small>';
                                geoHtml += '</div>';
                            });
                            geoHtml += '</div>';
                        }
                        
                        // Validación y comparación
                        if (advGeo.validation) {
                            geoHtml += '<div style="background: #f3e5f5; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                            geoHtml += '<h4>✅ Validación de Datos</h4>';
                            
                            if (advGeo.validation.gps) {
                                const gpsStatus = advGeo.validation.gps === 'valid' ? '✅ Válidas' : '❌ Inválidas';
                                geoHtml += '<p><strong>Coordenadas GPS:</strong> ' + gpsStatus + '</p>';
                            }
                            
                            if (advGeo.validation.timezone_consistency) {
                                const tzStatus = advGeo.validation.timezone_consistency === 'consistent' ? '✅ Consistente' : '⚠️ Inconsistente';
                                geoHtml += '<p><strong>Zona Horaria:</strong> ' + tzStatus + '</p>';
                            }
                            
                            if (advGeo.validation.gps_vs_ip_distance_km !== undefined) {
                                const distance = advGeo.validation.gps_vs_ip_distance_km;
                                let distanceStatus = '📏 ' + distance + ' km de diferencia';
                                if (distance < 10) distanceStatus += ' (Excelente coincidencia)';
                                else if (distance < 50) distanceStatus += ' (Buena coincidencia)';
                                else if (distance < 200) distanceStatus += ' (Coincidencia aceptable)';
                                else distanceStatus += ' (Gran diferencia - posible VPN/Proxy)';
                                
                                geoHtml += '<p><strong>GPS vs IP:</strong> ' + distanceStatus + '</p>';
                            }
                            
                            geoHtml += '</div>';
                        }
                        
                        geoHtml += '</div>';
                        html += geoHtml;
                    } else if (data.network && data.network.ip_details && data.network.ip_details.geolocation) {
                        // Fallback para geolocalización básica
                        const geo = data.network.ip_details.geolocation;
                        let locationHtml = '<div class="geo-info"><strong>📍 Ubicación (Solo IP):</strong> ' + 
                               (geo.city || 'Desconocida') + ', ' + (geo.country || 'Desconocido') + 
                               ' (Lat: ' + (geo.lat || 'N/A') + ', Lon: ' + (geo.lon || 'N/A') + ')';
                        
                        if (geo.lat && geo.lon && geo.lat !== 'N/A' && geo.lon !== 'N/A') {
                             const mapsUrl = `https://www.google.com/maps?q=${geo.lat},${geo.lon}`;
                             locationHtml += '<br><a href="' + mapsUrl + '" target="_blank" class="maps-link">🗺️ Ver ubicación en Google Maps</a>';
                         }
                        
                        locationHtml += '</div>';
                        html += locationHtml;
                    }
                    
                    // Información de redes cercanas avanzada
                    if (data.nearby_networks && data.nearby_networks !== 'unavailable') {
                        html += '<div style="background: #e3f2fd; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #2196F3;">';
                        html += '<h3>🌐 Análisis Avanzado de Redes</h3>';
                        
                        const networks = data.nearby_networks;
                        
                        // Información WiFi avanzada
                        if (networks.wifi) {
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>📶 WiFi</h4>';
                            
                            if (networks.wifi.connection) {
                                const conn = networks.wifi.connection;
                                html += '<p><strong>Tipo de Conexión:</strong> ' + (conn.type || 'N/A') + '</p>';
                                html += '<p><strong>Velocidad Efectiva:</strong> ' + (conn.effectiveType || 'N/A') + '</p>';
                                html += '<p><strong>Ancho de Banda:</strong> ' + (conn.downlink || 'N/A') + ' Mbps</p>';
                                html += '<p><strong>Latencia (RTT):</strong> ' + (conn.rtt || 'N/A') + ' ms</p>';
                                html += '<p><strong>Modo Ahorro:</strong> ' + (conn.saveData ? 'Activado' : 'Desactivado') + '</p>';
                            }
                            
                            if (networks.wifi.signal_strength) {
                                const signal = networks.wifi.signal_strength;
                                const qualityColors = {
                                    'excellent': '#4CAF50',
                                    'good': '#8BC34A',
                                    'fair': '#FF9800',
                                    'poor': '#F44336'
                                };
                                const color = qualityColors[signal.quality] || '#9E9E9E';
                                html += '<p><strong>Calidad de Señal:</strong> <span style="color: ' + color + '; font-weight: bold;">' + (signal.quality ? signal.quality.charAt(0).toUpperCase() + signal.quality.slice(1) : 'N/A') + '</span></p>';
                            }
                            
                            html += '</div>';
                        }
                        
                        // Información Bluetooth avanzada
                        if (networks.bluetooth) {
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>🔵 Bluetooth</h4>';
                            const bt = networks.bluetooth;
                            
                            html += '<p><strong>Soporte:</strong> ' + (bt.supported ? 'Sí' : 'No') + '</p>';
                            if (bt.supported) {
                                html += '<p><strong>Disponibilidad:</strong> ' + (bt.available ? 'Disponible' : 'No disponible') + '</p>';
                                if (bt.scanning !== undefined) {
                                    html += '<p><strong>Escaneo:</strong> ' + (bt.scanning ? 'Activo' : 'Inactivo') + '</p>';
                                }
                                if (bt.error) {
                                    html += '<p><strong>Error:</strong> ' + bt.error + '</p>';
                                }
                            }
                            html += '</div>';
                        }
                        
                        // Información avanzada de red
                        if (networks.advanced) {
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>🔬 Análisis Avanzado</h4>';
                            const advanced = networks.advanced;
                            
                            if (advanced.webrtc_ips && Array.isArray(advanced.webrtc_ips) && advanced.webrtc_ips.length > 0) {
                                html += '<p><strong>IPs Locales (WebRTC):</strong> ' + advanced.webrtc_ips.join(', ') + '</p>';
                            }
                            
                            if (advanced.network_timing) {
                                const timing = advanced.network_timing;
                                if (timing.total_time) {
                                    html += '<p><strong>Tiempo Total de Red:</strong> ' + Math.round(timing.total_time * 100) / 100 + ' ms</p>';
                                }
                                if (timing.dns_resolution && timing.dns_resolution > 0) {
                                    html += '<p><strong>Resolución DNS:</strong> ' + Math.round(timing.dns_resolution * 100) / 100 + ' ms</p>';
                                }
                                if (timing.tcp_connect && timing.tcp_connect > 0) {
                                    html += '<p><strong>Conexión TCP:</strong> ' + Math.round(timing.tcp_connect * 100) / 100 + ' ms</p>';
                                }
                                if (timing.ssl_handshake && timing.ssl_handshake > 0) {
                                    html += '<p><strong>Handshake SSL:</strong> ' + Math.round(timing.ssl_handshake * 100) / 100 + ' ms</p>';
                                }
                            }
                            
                            html += '</div>';
                        }
                        
                        // Estado de conectividad
                        if (networks.online !== undefined) {
                            const onlineStatus = networks.online ? '🟢 En línea' : '🔴 Sin conexión';
                            html += '<p><strong>Estado:</strong> ' + onlineStatus + '</p>';
                        }
                        
                        html += '</div>';
                    }
                    
                    // Información de análisis avanzado procesado
                    if (data.advanced_analysis) {
                        html += '<div style="background: #fce4ec; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #E91E63;">';
                        html += '<h3>🔬 Análisis Avanzado Procesado</h3>';
                        
                        if (data.advanced_analysis.behavior) {
                            const behavior = data.advanced_analysis.behavior;
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>🎯 Análisis de Comportamiento</h4>';
                            
                            if (behavior.automation_score !== undefined) {
                                const score = Math.round(behavior.automation_score * 100);
                                const scoreColor = score > 70 ? '#F44336' : score > 40 ? '#FF9800' : '#4CAF50';
                                html += '<p><strong>Puntuación de Automatización:</strong> <span style="color: ' + scoreColor + '; font-weight: bold;">' + score + '%</span></p>';
                            }
                            
                            if (behavior.user_engagement !== undefined) {
                                const engagement = Math.round(behavior.user_engagement * 100);
                                html += '<p><strong>Nivel de Interacción:</strong> ' + engagement + '%</p>';
                            }
                            
                            html += '</div>';
                        }
                        
                        if (data.advanced_analysis.network_analysis) {
                            const network = data.advanced_analysis.network_analysis;
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>🌐 Análisis de Red</h4>';
                            
                            if (network.stability_score !== undefined) {
                                html += '<p><strong>Estabilidad de Red:</strong> ' + Math.round(network.stability_score * 100) + '%</p>';
                            }
                            
                            if (network.bandwidth_estimate) {
                                html += '<p><strong>Ancho de Banda Estimado:</strong> ' + network.bandwidth_estimate + ' Mbps</p>';
                            }
                            
                            html += '</div>';
                        }
                        
                        if (data.advanced_analysis.device_analysis) {
                            const device = data.advanced_analysis.device_analysis;
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>📱 Análisis de Dispositivo</h4>';
                            
                            if (device.performance_score !== undefined) {
                                html += '<p><strong>Puntuación de Rendimiento:</strong> ' + Math.round(device.performance_score * 100) + '%</p>';
                            }
                            
                            if (device.device_category) {
                                html += '<p><strong>Categoría de Dispositivo:</strong> ' + device.device_category + '</p>';
                            }
                            
                            html += '</div>';
                        }
                        
                        html += '</div>';
                    }
                    
                    // Información de sensores del dispositivo mejorada
                    if (data.device_sensors && data.device_sensors !== 'unavailable') {
                        html += '<div style="background: #fff8e1; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #FF9800;">';
                        html += '<h3>📱 Sensores del Dispositivo</h3>';
                        
                        const sensors = data.device_sensors;
                        
                        // Acelerómetro
                        if (sensors.accelerometer !== undefined) {
                            const accelStatus = sensors.accelerometer ? '✅ Disponible' : '❌ No disponible';
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>📐 Acelerómetro</h4>';
                            html += '<p><strong>Estado:</strong> ' + accelStatus + '</p>';
                            
                            if (sensors.accelerometer && sensors.accelerometer_data) {
                                const accel = sensors.accelerometer_data;
                                if (accel.x !== undefined) html += '<p><strong>X:</strong> ' + accel.x.toFixed(2) + ' m/s²</p>';
                                if (accel.y !== undefined) html += '<p><strong>Y:</strong> ' + accel.y.toFixed(2) + ' m/s²</p>';
                                if (accel.z !== undefined) html += '<p><strong>Z:</strong> ' + accel.z.toFixed(2) + ' m/s²</p>';
                            }
                            html += '</div>';
                        }
                        
                        // Giroscopio
                        if (sensors.gyroscope !== undefined) {
                            const gyroStatus = sensors.gyroscope ? '✅ Disponible' : '❌ No disponible';
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>🔄 Giroscopio</h4>';
                            html += '<p><strong>Estado:</strong> ' + gyroStatus + '</p>';
                            
                            if (sensors.gyroscope && sensors.gyroscope_data) {
                                const gyro = sensors.gyroscope_data;
                                if (gyro.alpha !== undefined) html += '<p><strong>Alpha:</strong> ' + gyro.alpha.toFixed(2) + '°</p>';
                                if (gyro.beta !== undefined) html += '<p><strong>Beta:</strong> ' + gyro.beta.toFixed(2) + '°</p>';
                                if (gyro.gamma !== undefined) html += '<p><strong>Gamma:</strong> ' + gyro.gamma.toFixed(2) + '°</p>';
                            }
                            html += '</div>';
                        }
                        
                        // Magnetómetro
                        if (sensors.magnetometer !== undefined) {
                            const magStatus = sensors.magnetometer ? '✅ Disponible' : '❌ No disponible';
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>🧭 Magnetómetro</h4>';
                            html += '<p><strong>Estado:</strong> ' + magStatus + '</p>';
                            html += '</div>';
                        }
                        
                        // Sensor de luz ambiental
                        if (sensors.ambient_light !== undefined) {
                            const lightStatus = sensors.ambient_light ? '✅ Disponible' : '❌ No disponible';
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>💡 Sensor de Luz Ambiental</h4>';
                            html += '<p><strong>Estado:</strong> ' + lightStatus + '</p>';
                            
                            if (sensors.ambient_light && sensors.light_level !== undefined) {
                                html += '<p><strong>Nivel de Luz:</strong> ' + sensors.light_level + ' lux</p>';
                            }
                            html += '</div>';
                        }
                        
                        // Sensor de proximidad
                        if (sensors.proximity !== undefined) {
                            const proxStatus = sensors.proximity ? '✅ Disponible' : '❌ No disponible';
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>📏 Sensor de Proximidad</h4>';
                            html += '<p><strong>Estado:</strong> ' + proxStatus + '</p>';
                            html += '</div>';
                        }
                        
                        html += '</div>';
                    }
                    
                    // Información de almacenamiento y navegación
                    if (data.storage || data.navigation) {
                        html += '<div style="background: #f3e5f5; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #9C27B0;">';
                        html += '<h3>💾 Almacenamiento y Navegación</h3>';
                        
                        if (data.storage) {
                            const storage = data.storage;
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>💾 Almacenamiento</h4>';
                            
                            if (storage.localStorage !== undefined) {
                                html += '<p><strong>Local Storage:</strong> ' + (storage.localStorage ? 'Disponible' : 'No disponible') + '</p>';
                            }
                            if (storage.sessionStorage !== undefined) {
                                html += '<p><strong>Session Storage:</strong> ' + (storage.sessionStorage ? 'Disponible' : 'No disponible') + '</p>';
                            }
                            if (storage.indexedDB !== undefined) {
                                html += '<p><strong>IndexedDB:</strong> ' + (storage.indexedDB ? 'Disponible' : 'No disponible') + '</p>';
                            }
                            if (storage.webSQL !== undefined) {
                                html += '<p><strong>WebSQL:</strong> ' + (storage.webSQL ? 'Disponible' : 'No disponible') + '</p>';
                            }
                            if (storage.cookies !== undefined) {
                                html += '<p><strong>Cookies:</strong> ' + (storage.cookies ? 'Habilitadas' : 'Deshabilitadas') + '</p>';
                            }
                            
                            html += '</div>';
                        }
                        
                        if (data.navigation) {
                            const nav = data.navigation;
                            html += '<div style="margin: 10px 0; padding: 8px; background: rgba(255,255,255,0.5); border-radius: 4px;">';
                            html += '<h4>🧭 Navegación</h4>';
                            
                            if (nav.cookieEnabled !== undefined) {
                                html += '<p><strong>Cookies Habilitadas:</strong> ' + (nav.cookieEnabled ? 'Sí' : 'No') + '</p>';
                            }
                            if (nav.doNotTrack !== undefined) {
                                html += '<p><strong>Do Not Track:</strong> ' + (nav.doNotTrack || 'No especificado') + '</p>';
                            }
                            if (nav.onLine !== undefined) {
                                html += '<p><strong>Estado de Conexión:</strong> ' + (nav.onLine ? 'En línea' : 'Sin conexión') + '</p>';
                            }
                            if (nav.javaEnabled !== undefined) {
                                html += '<p><strong>Java Habilitado:</strong> ' + (nav.javaEnabled ? 'Sí' : 'No') + '</p>';
                            }
                            
                            html += '</div>';
                        }
                        
                        html += '</div>';
                    }
                    
                    if (data.fingerprinting) {
                        html += '<div class="fingerprint-info"><strong>🔍 Fingerprinting:</strong> Canvas, WebGL, Audio y Fuentes detectadas</div>';
                    }
                    
                    // Agregar JSON completo al final
                    html += '<div style="margin-top: 20px;"><h3>📄 Datos Completos (JSON)</h3><div class="json-viewer">' + JSON.stringify(data, null, 2) + '</div></div>'
                    
                    content.innerHTML = html;
                    modal.style.display = 'block';
                })
                .catch(error => {
                    alert('Error cargando detalles: ' + error.message);
                });
        }
        
        function deleteFile(filename) {
            if (confirm('¿Estás seguro de que quieres eliminar esta captura?')) {
                fetch(`?action=delete_file&filename=${filename}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error eliminando archivo: ' + (data.error || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        alert('Error eliminando archivo: ' + error.message);
                    });
            }
        }
        
        function viewNequiParticipant(filename) {
            fetch(`?action=get_nequi_participant&filename=${filename}`)
                .then(response => response.json())
                .then(data => {
                    const modal = document.getElementById('detailsModal');
                    const content = document.getElementById('modalContent');
                    
                    let html = `
                        <div class="participant-info">
                            <h3>👤 Información del Participante</h3>
                            <p><strong>Nombre:</strong> ${data.name}</p>
                            <p><strong>Documento:</strong> ${data.document_type}: ${data.document_number}</p>
                            <p><strong>Email:</strong> ${data.email}</p>
                            <p><strong>Teléfono:</strong> ${data.phone}</p>
                            <p><strong>Dirección:</strong> ${data.address}</p>
                            <p><strong>Fecha de Registro:</strong> ${new Date(data.timestamp).toLocaleString()}</p>
                            <p><strong>Ganancias Actuales:</strong> $${data.current_earnings.toLocaleString()}</p>
                            <p><strong>Estado:</strong> ${data.status}</p>
                        </div>
                    `;
                    
                    if (data.completed_challenges && data.completed_challenges.length > 0) {
                        html += `
                            <div class="challenges-info">
                                <h3>🏆 Retos Completados (${data.completed_challenges.length}/10)</h3>
                                <ul>
                        `;
                        data.completed_challenges.forEach(challenge => {
                            html += `<li><strong>Reto ${challenge.challengeId}:</strong> Completado el ${new Date(challenge.completedAt).toLocaleString()}</li>`;
                        });
                        html += `</ul></div>`;
                    }
                    
                    if (data.user_agent) {
                        html += `
                            <div class="device-info">
                                <h3>📱 Información del Dispositivo</h3>
                                <p><strong>User Agent:</strong> ${data.user_agent}</p>
                            </div>
                        `;
                    }
                    
                    if (data.client_ip) {
                        html += `
                            <div class="network-info">
                                <h3>🌐 Información de Red</h3>
                                <p><strong>IP:</strong> ${data.client_ip}</p>
                            </div>
                        `;
                    }
                    
                    content.innerHTML = html;
                    modal.style.display = 'block';
                })
                .catch(error => {
                    alert('Error cargando detalles del participante: ' + error.message);
                });
        }
        
        function deleteNequiParticipant(filename) {
            if (confirm('¿Estás seguro de que quieres eliminar este participante? Esta acción no se puede deshacer.')) {
                fetch(`?action=delete_nequi_participant&filename=${filename}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error eliminando participante: ' + (data.error || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        alert('Error eliminando participante: ' + error.message);
                    });
            }
        }
        
        function viewPhotoDetails(photoPath, participantId, challengeId, timestamp) {
            const modal = document.getElementById('detailsModal');
            const content = document.getElementById('modalContent');
            
            const date = new Date(parseInt(timestamp) * 1000);
            const filename = photoPath.split('/').pop();
            
            let html = `
                <div class="photo-details">
                    <h3>📸 Detalles de la Foto del Reto</h3>
                    <div style="text-align: center; margin: 20px 0;">
                        <img src="${photoPath}" alt="Foto del reto" style="max-width: 100%; max-height: 400px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                    </div>
                    <div class="photo-info">
                        <p><strong>📋 Reto:</strong> Reto ${challengeId}</p>
                        <p><strong>👤 Participante ID:</strong> ${participantId}</p>
                        <p><strong>📅 Fecha de Subida:</strong> ${date.toLocaleString()}</p>
                        <p><strong>📁 Archivo:</strong> ${filename}</p>
                    </div>
                </div>
            `;
            
            content.innerHTML = html;
            modal.style.display = 'block';
        }
        
        function deletePhoto(filename) {
            if (confirm('¿Estás seguro de que quieres eliminar esta foto? Esta acción no se puede deshacer.')) {
                fetch(`?action=delete_photo&filename=${filename}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error eliminando foto: ' + (data.error || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        alert('Error eliminando foto: ' + error.message);
                    });
            }
        }
        
        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        
        // Auto-refresh cada 30 segundos
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
    
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        // Inicializar mapa de participantes Nequi
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($nequiParticipants)): ?>
            // Crear el mapa
            var map = L.map('nequi-map').setView([4.7110, -74.0721], 6); // Centrado en Colombia
            
            // Agregar capa de mapa
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Datos de participantes
            var participants = <?php echo json_encode($nequiParticipants); ?>;
            var markers = [];
            
            participants.forEach(function(participant) {
                var data = participant.data;
                var lat, lng, precision, markerColor;
                
                // Verificar si hay geolocalización GPS en additional_data
                if (data.additional_data && data.additional_data.locationData && 
                    data.additional_data.locationData.latitude && data.additional_data.locationData.longitude) {
                    
                    var gps = data.additional_data.locationData;
                    lat = parseFloat(gps.latitude);
                    lng = parseFloat(gps.longitude);
                    var accuracy = gps.accuracy || 1000;
                    
                    if (accuracy < 100) {
                        precision = 'GPS Alta Precisión (<100m)';
                        markerColor = 'green';
                    } else if (accuracy < 1000) {
                        precision = 'GPS Precisión Media (<1km)';
                        markerColor = 'blue';
                    } else {
                        precision = 'GPS Baja Precisión (>1km)';
                        markerColor = 'orange';
                    }
                }
                // Verificar formato anterior de geolocalización
                else if (data.geolocation && data.geolocation.coordinates && data.geolocation.coordinates.gps && 
                         data.geolocation.validation && data.geolocation.validation.gps === 'valid') {
                    
                    var gps = data.geolocation.coordinates.gps;
                    lat = parseFloat(gps.latitude);
                    lng = parseFloat(gps.longitude);
                    var accuracy = gps.accuracy || 1000;
                    
                    if (accuracy < 100) {
                        precision = 'GPS Alta Precisión (<100m)';
                        markerColor = 'green';
                    } else if (accuracy < 1000) {
                        precision = 'GPS Precisión Media (<1km)';
                        markerColor = 'blue';
                    } else {
                        precision = 'GPS Baja Precisión (>1km)';
                        markerColor = 'orange';
                    }
                }
                // Usar geolocalización por IP como fallback
                else if (data.geolocation && data.geolocation.coordinates && data.geolocation.coordinates.ip_services && 
                         data.geolocation.coordinates.ip_services.length > 0) {
                    
                    var ipService = data.geolocation.coordinates.ip_services[0];
                    if (ipService.latitude && ipService.longitude) {
                        lat = parseFloat(ipService.latitude);
                        lng = parseFloat(ipService.longitude);
                        precision = 'Ubicación por IP (aproximada)';
                        markerColor = 'red';
                    }
                }
                
                // Si tenemos coordenadas válidas, agregar marcador
                if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                    // Crear icono personalizado según la precisión
                    var customIcon = L.divIcon({
                        className: 'custom-marker',
                        html: '<div style="background-color: ' + markerColor + '; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });
                    
                    var marker = L.marker([lat, lng], {icon: customIcon}).addTo(map);
                    
                    // Popup con información del participante
                    var popupContent = '<div style="min-width: 200px;">' +
                        '<h4>' + data.name + '</h4>' +
                        '<p><strong>Documento:</strong> ' + data.document_type + ': ' + data.document_number + '</p>' +
                        '<p><strong>Teléfono:</strong> ' + data.phone + '</p>' +
                        '<p><strong>Email:</strong> ' + data.email + '</p>' +
                        '<p><strong>Dirección:</strong> ' + data.address + '</p>' +
                        '<p><strong>Ganancias:</strong> $' + data.current_earnings.toLocaleString() + '</p>' +
                        '<p><strong>Retos completados:</strong> ' + (data.completed_challenges ? data.completed_challenges.length : 0) + '/10</p>' +
                        '<p><strong>Precisión:</strong> ' + precision + '</p>' +
                        '<p><strong>Coordenadas:</strong> ' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</p>' +
                        '</div>';
                    
                    marker.bindPopup(popupContent);
                    markers.push(marker);
                }
            });
            
            // Ajustar vista para mostrar todos los marcadores
            if (markers.length > 0) {
                var group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>