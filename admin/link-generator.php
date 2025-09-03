<?php
/**
 * Advanced IP Tracker - Generador de Enlaces
 * Herramienta para generar enlaces únicos de tracking
 */

// Configuración
define('LINKS_DIR', '../data/links/');
define('BASE_URL', 'http://localhost/advanced-ip-tracker/frontend/');

// Crear directorio si no existe
if (!file_exists(LINKS_DIR)) {
    mkdir(LINKS_DIR, 0755, true);
}

/**
 * Generar ID único para el enlace
 */
function generateLinkId() {
    return 'link_' . date('Ymd_His') . '_' . substr(md5(uniqid(rand(), true)), 0, 8);
}

/**
 * Guardar información del enlace
 */
function saveLinkInfo($linkId, $data) {
    $filepath = LINKS_DIR . $linkId . '.json';
    $linkData = [
        'id' => $linkId,
        'created_at' => date('Y-m-d H:i:s'),
        'created_timestamp' => time(),
        'campaign_name' => $data['campaign_name'] ?? 'Sin nombre',
        'description' => $data['description'] ?? '',
        'redirect_url' => $data['redirect_url'] ?? 'https://www.apple.com/iphone-15-pro/',
        'custom_message' => $data['custom_message'] ?? '¡Felicidades! Has ganado un premio',
        'custom_prize' => $data['custom_prize'] ?? 'iPhone 15 Pro GRATIS',
        'clicks' => 0,
        'unique_visitors' => [],
        'active' => true
    ];
    
    return file_put_contents($filepath, json_encode($linkData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

/**
 * Obtener lista de enlaces creados
 */
function getCreatedLinks() {
    $links = [];
    if (is_dir(LINKS_DIR)) {
        $files = scandir(LINKS_DIR);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $filepath = LINKS_DIR . $file;
                $linkData = json_decode(file_get_contents($filepath), true);
                if ($linkData) {
                    $links[] = $linkData;
                }
            }
        }
    }
    
    // Ordenar por fecha de creación (más reciente primero)
    usort($links, function($a, $b) {
        return $b['created_timestamp'] - $a['created_timestamp'];
    });
    
    return $links;
}

// Procesar formulario
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_link') {
        $linkId = generateLinkId();
        $linkData = [
            'campaign_name' => trim($_POST['campaign_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'redirect_url' => trim($_POST['redirect_url'] ?? ''),
            'custom_message' => trim($_POST['custom_message'] ?? ''),
            'custom_prize' => trim($_POST['custom_prize'] ?? '')
        ];
        
        if (saveLinkInfo($linkId, $linkData)) {
            $message = 'Enlace creado exitosamente: ' . BASE_URL . 'track.php?id=' . $linkId;
            $messageType = 'success';
        } else {
            $message = 'Error creando el enlace';
            $messageType = 'error';
        }
    }
}

$createdLinks = getCreatedLinks();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Enlaces - Advanced IP Tracker</title>
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
        
        .nav {
            margin-top: 20px;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            margin-right: 10px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        
        .nav a:hover {
            background: rgba(255,255,255,0.3);
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
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #495057;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.25);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        .link-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            transition: transform 0.3s ease;
        }
        
        .link-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .link-url {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            margin: 10px 0;
        }
        
        .link-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        
        .stat-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 0.9em;
            color: #6c757d;
        }
        
        .copy-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8em;
            margin-left: 10px;
        }
        
        .copy-btn:hover {
            background: #218838;
        }
        
        .template-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .template-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .template-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .template-card.selected {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .template-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .link-stats {
                grid-template-columns: 1fr 1fr;
            }
            
            .template-selector {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🔗 Generador de Enlaces</h1>
            <p>Crea enlaces personalizados para demostrar riesgos de seguridad</p>
            <div class="nav">
                <a href="dashboard.php">📊 Dashboard</a>
                <a href="link-generator.php">🔗 Generar Enlaces</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="alert alert-warning">
            <strong>⚠️ USO EDUCATIVO ÚNICAMENTE:</strong> Estos enlaces están diseñados para demostrar vulnerabilidades de seguridad en un entorno controlado y educativo. No los uses para actividades maliciosas.
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
            <?php if ($messageType === 'success'): ?>
            <button class="copy-btn" onclick="copyToClipboard('<?php echo BASE_URL . 'track.php?id=' . $linkId; ?>')">
                📋 Copiar Enlace
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Formulario de creación de enlaces -->
        <div class="section">
            <div class="section-header">
                <h2>🎯 Crear Nuevo Enlace de Tracking</h2>
            </div>
            <div class="section-content">
                <form method="POST">
                    <input type="hidden" name="action" value="create_link">
                    
                    <!-- Plantillas predefinidas -->
                    <div class="form-group">
                        <label>📋 Plantillas Predefinidas</label>
                        <div class="template-selector">
                            <div class="template-card" onclick="selectTemplate('prize')">
                                <div class="template-icon">🎁</div>
                                <h4>Premio/Sorteo</h4>
                                <p>iPhone, dinero, etc.</p>
                            </div>
                            <div class="template-card" onclick="selectTemplate('urgent')">
                                <div class="template-icon">⚠️</div>
                                <h4>Urgente/Seguridad</h4>
                                <p>Cuenta comprometida</p>
                            </div>
                            <div class="template-card" onclick="selectTemplate('social')">
                                <div class="template-icon">📱</div>
                                <h4>Redes Sociales</h4>
                                <p>Foto viral, video</p>
                            </div>
                            <div class="template-card" onclick="selectTemplate('work')">
                                <div class="template-icon">💼</div>
                                <h4>Trabajo/Empresa</h4>
                                <p>Documento importante</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="campaign_name">📝 Nombre de la Campaña</label>
                            <input type="text" id="campaign_name" name="campaign_name" 
                                   placeholder="Ej: Demostración Clase Seguridad" required>
                        </div>
                        <div class="form-group">
                            <label for="redirect_url">🔗 URL de Redirección</label>
                            <input type="url" id="redirect_url" name="redirect_url" 
                                   value="https://www.apple.com/iphone-15-pro/" 
                                   placeholder="https://ejemplo.com">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">📄 Descripción</label>
                        <textarea id="description" name="description" 
                                  placeholder="Describe el propósito de este enlace..."></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="custom_message">💬 Mensaje Personalizado</label>
                            <input type="text" id="custom_message" name="custom_message" 
                                   value="¡Felicidades! Has ganado un premio" 
                                   placeholder="Mensaje que verá el usuario">
                        </div>
                        <div class="form-group">
                            <label for="custom_prize">🏆 Premio/Incentivo</label>
                            <input type="text" id="custom_prize" name="custom_prize" 
                                   value="iPhone 15 Pro GRATIS" 
                                   placeholder="Qué premio o incentivo ofreces">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        🚀 Generar Enlace de Tracking
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Lista de enlaces creados -->
        <div class="section">
            <div class="section-header">
                <h2>📋 Enlaces Creados (<?php echo count($createdLinks); ?>)</h2>
            </div>
            <div class="section-content">
                <?php if (empty($createdLinks)): ?>
                    <p>No hay enlaces creados aún. Crea tu primer enlace usando el formulario de arriba.</p>
                <?php else: ?>
                    <?php foreach ($createdLinks as $link): ?>
                    <div class="link-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <div>
                                <h4>🎯 <?php echo htmlspecialchars($link['campaign_name']); ?></h4>
                                <p style="color: #6c757d; margin: 5px 0;">
                                    <?php echo htmlspecialchars($link['description']); ?>
                                </p>
                                <small style="color: #6c757d;">
                                    Creado: <?php echo $link['created_at']; ?>
                                </small>
                            </div>
                            <div>
                                <span class="btn btn-secondary" style="font-size: 0.8em; padding: 5px 10px;">
                                    <?php echo $link['active'] ? '🟢 Activo' : '🔴 Inactivo'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="link-url">
                            <strong>Enlace:</strong> <?php echo BASE_URL . 'track.php?id=' . $link['id']; ?>
                            <button class="copy-btn" onclick="copyToClipboard('<?php echo BASE_URL . 'track.php?id=' . $link['id']; ?>')">
                                📋 Copiar
                            </button>
                        </div>
                        
                        <div class="link-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $link['clicks']; ?></div>
                                <div class="stat-label">Clics Totales</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo count($link['unique_visitors']); ?></div>
                                <div class="stat-label">Visitantes Únicos</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $link['custom_prize']; ?></div>
                                <div class="stat-label">Premio Ofrecido</div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 15px;">
                            <button class="btn btn-secondary" onclick="viewLinkStats('<?php echo $link['id']; ?>')">
                                📊 Ver Estadísticas
                            </button>
                            <button class="btn btn-danger" onclick="deleteLink('<?php echo $link['id']; ?>')">
                                🗑️ Eliminar
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Plantillas predefinidas
        const templates = {
            prize: {
                message: '¡Felicidades! Has ganado un premio',
                prize: 'iPhone 15 Pro GRATIS',
                redirect: 'https://www.apple.com/iphone-15-pro/'
            },
            urgent: {
                message: '⚠️ Tu cuenta ha sido comprometida',
                prize: 'Verificación de seguridad requerida',
                redirect: 'https://support.google.com/accounts/answer/41078'
            },
            social: {
                message: '😱 ¡No vas a creer este video viral!',
                prize: 'Video exclusivo que todos están viendo',
                redirect: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
            },
            work: {
                message: '📋 Documento importante requiere tu atención',
                prize: 'Informe confidencial de la empresa',
                redirect: 'https://docs.google.com/'
            }
        };
        
        function selectTemplate(templateName) {
            // Remover selección anterior
            document.querySelectorAll('.template-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Seleccionar nueva plantilla
            event.target.closest('.template-card').classList.add('selected');
            
            // Aplicar valores de la plantilla
            const template = templates[templateName];
            if (template) {
                document.getElementById('custom_message').value = template.message;
                document.getElementById('custom_prize').value = template.prize;
                document.getElementById('redirect_url').value = template.redirect;
            }
        }
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Cambiar texto del botón temporalmente
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '✅ Copiado';
                btn.style.background = '#28a745';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '#28a745';
                }, 2000);
            }).catch(function(err) {
                alert('Error copiando al portapapeles: ' + err);
            });
        }
        
        function viewLinkStats(linkId) {
            // Redirigir al dashboard con filtro por enlace
            window.location.href = 'dashboard.php?filter_link=' + linkId;
        }
        
        function deleteLink(linkId) {
            if (confirm('¿Estás seguro de que quieres eliminar este enlace? Esta acción no se puede deshacer.')) {
                // Implementar eliminación de enlace
                fetch('?action=delete_link&id=' + linkId, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error eliminando enlace: ' + (data.error || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    alert('Error eliminando enlace: ' + error.message);
                });
            }
        }
    </script>
</body>
</html>