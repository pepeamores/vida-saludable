<?php
session_start();
require_once __DIR__ . '/../../autoload.php'; // Ajusta la ruta a Composer si estás en config/
use MongoDB\Client;

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado.");
}

$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$coleccion = $db->calendario;

// Fechas de la semana actual
$lunes = new DateTime();
$lunes->modify('monday this week');
$domingo = clone $lunes;
$domingo->modify('+6 days');

// Estructura del documento
$diasNombres = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
$dias = [];

foreach ($diasNombres as $index => $nombre) {
    $entrenado = isset($_POST['entrenado'][$index]) ? true : false;
    $nota = trim($_POST['notas'][$index] ?? '');
    $dias[$nombre] = [
        'entrenado' => $entrenado,
        'notas' => $nota
    ];
}

$registro = [
    'usuario_id' => $_SESSION['user_id'],
    'semana_inicio' => $lunes->format('Y-m-d'),
    'semana_fin' => $domingo->format('Y-m-d'),
    'dias' => $dias
];

// Inserta en la colección
$coleccion->insertOne($registro);

// Redirige a ejercicio.php (ajusta si está en otra ruta)
header("Location: ../pages/ejercicio.php?mensaje=semana_guardada");
exit;
