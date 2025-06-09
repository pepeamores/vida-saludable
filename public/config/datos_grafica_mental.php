<?php

require_once __DIR__ . '/../../autoload.php';
use MongoDB\Client;
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$coleccion = $db->mental; // Cambia por el nombre real de tu colección

$registros = $coleccion->find(['usuario_id' => $_SESSION['user_id']], ['sort' => ['fecha' => 1]]);

$fechas = [];
$estres = [];
$animo = [];
$animo_num = [];

// Mapea los estados de ánimo a números para graficar
$mapaAnimo = [
    'Feliz' => 10,
    'Tranquilo' => 8,
    'Cansado' => 5,
    'Ansioso' => 4,
    'Triste' => 2,
    'Enojado' => 1
];

foreach ($registros as $r) {
    $fechas[] = isset($r['fecha']) ? date('d/m/Y', strtotime($r['fecha'])) : '';
    $estres[] = isset($r['estres']) ? (int)$r['estres'] : 0;
    $animo[] = $r['animo'] ?? '';
    $animo_num[] = isset($mapaAnimo[$r['animo']]) ? $mapaAnimo[$r['animo']] : 0;
}

header('Content-Type: application/json');
echo json_encode([
    'fechas' => $fechas,
    'estres' => $estres,
    'animo' => $animo,
    'animo_num' => $animo_num
]);