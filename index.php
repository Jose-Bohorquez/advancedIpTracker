<?php
/**
 * Archivo de redirección principal
 * Redirige automáticamente al frontend del sistema de captura
 * 
 * Herramienta Educativa de Demostración de Riesgos de Seguridad
 * Uso exclusivo para fines educativos y de concienciación
 */

// Verificar si se está accediendo desde la raíz
if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php') {
    // Redireccionar al frontend
    header('Location: /frontend/');
    exit();
}

// Si no es acceso directo, mostrar información básica
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Demostración de Seguridad</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
        }
        .logo {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        h1 {
            color: #333;
            margin-bottom: 1rem;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 0.5rem;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🔒</div>
        <h1>Sistema de Demostración de Seguridad</h1>
        <p>Herramienta educativa para demostrar riesgos de seguridad informática y técnicas de fingerprinting del navegador.</p>
        
        <a href="/frontend/" class="btn">Ir al Frontend</a>
        <a href="/admin/dashboard.php" class="btn">Panel de Administración</a>
        <a href="/admin/link-generator.php" class="btn">Generar Enlaces</a>
        
        <div class="warning">
            <strong>⚠️ Uso Educativo:</strong> Esta herramienta está diseñada exclusivamente para fines educativos y de concienciación sobre seguridad informática. Úsala de manera ética y responsable.
        </div>
    </div>
</body>
</html>