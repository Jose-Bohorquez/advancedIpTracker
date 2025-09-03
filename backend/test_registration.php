<?php
// Script de prueba para validar el registro de participantes
header('Content-Type: application/json');

// Datos de prueba
$testData = [
    'documentType' => 'CC',
    'documentNumber' => '12345678',
    'firstName' => 'Juan',
    'lastName' => 'Pérez',
    'email' => 'juan.perez@test.com',
    'phone' => '3001234567',
    'address' => 'Calle 123 #45-67',
    'nequiAccount' => '3001234567'
];

// Simular el registro
$postData = json_encode($testData);

// Configurar la solicitud POST
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $postData
    ]
]);

// Realizar la solicitud al endpoint de registro
$response = file_get_contents('http://localhost:8000/backend/register_participant.php', false, $context);

if ($response === false) {
    echo json_encode(['error' => 'Error al conectar con el endpoint de registro']);
} else {
    echo $response;
}
?>