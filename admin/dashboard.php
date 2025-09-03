<?php
/**
 * Advanced IP Tracker - Panel de Administración
 * Dashboard para visualizar datos recolectados
 */

// Configuración
define('DATA_DIR', '../data/');
define('LOGS_DIR', '../logs/');

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
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced IP Tracker - Panel de Administración</title>
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
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1em;
            color: #666;
            text-transform: uppercase;
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
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #667eea, #764ba2);
            height: 20px;
            border-radius: 10px;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8em;
            font-weight: bold;
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
        
        .device-info {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 10px;
            margin: 5px 0;
            border-radius: 0 5px 5px 0;
        }
        
        .fingerprint-info {
            background: #fce4ec;
            border-left: 4px solid #e91e63;
            padding: 10px;
            margin: 5px 0;
            border-radius: 0 5px 5px 0;
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
        
        <!-- Lista de Capturas -->
        <div class="section">
            <div class="section-header">
                <h2>📋 Capturas Recientes</h2>
            </div>
            <div class="section-content">
                <?php if (empty($dataFiles)): ?>
                    <p>No hay capturas disponibles. Los datos aparecerán aquí cuando alguien visite el enlace de tracking.</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>IP</th>
                            <th>Ubicación</th>
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
                            if (isset($data['network']['ip_details']['geolocation']['city']) && 
                                isset($data['network']['ip_details']['geolocation']['country'])) {
                                $location = $data['network']['ip_details']['geolocation']['city'] . ', ' . 
                                           $data['network']['ip_details']['geolocation']['country'];
                            }
                            $browser = $data['browser']['parsed_ua']['browser'] ?? 'Desconocido';
                            $device = $data['browser']['parsed_ua']['device'] ?? 'Desconocido';
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i:s', $file['modified']); ?></td>
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
                    
                    let html = '<div class="json-viewer">' + JSON.stringify(data, null, 2) + '</div>';
                    
                    // Agregar información destacada
                    if (data.network && data.network.client_ip) {
                        html = '<div class="ip-info"><strong>IP:</strong> ' + data.network.client_ip + '</div>' + html;
                    }
                    
                    if (data.network && data.network.ip_details && data.network.ip_details.geolocation) {
                        const geo = data.network.ip_details.geolocation;
                        html = '<div class="geo-info"><strong>Ubicación:</strong> ' + 
                               (geo.city || 'Desconocida') + ', ' + (geo.country || 'Desconocido') + 
                               ' (Lat: ' + (geo.lat || 'N/A') + ', Lon: ' + (geo.lon || 'N/A') + ')</div>' + html;
                    }
                    
                    if (data.fingerprinting) {
                        html = '<div class="fingerprint-info"><strong>Fingerprinting:</strong> Canvas, WebGL, Audio y Fuentes detectadas</div>' + html;
                    }
                    
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
</body>
</html>