<?php
session_start();
require_once __DIR__ . '/db.php';
use MongoDB\Client;

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado");
}

$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$usuarios = $db->usuarios;

$userId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
$nuevaAltura = isset($_POST['altura']) ? (float)$_POST['altura'] : null;
$nuevoPeso = isset($_POST['peso']) ? (float)$_POST['peso'] : null;

// Actualizar en base de datos
$resultado = $usuarios->updateOne(
    ['_id' => $userId],
    ['$set' => ['altura' => $nuevaAltura, 'peso' => $nuevoPeso]]
);

// Actualizar en sesión también
$_SESSION['altura'] = $nuevaAltura;
$_SESSION['peso'] = $nuevoPeso;

// Redirigir de nuevo a la página de perfil o inicio
header('Location: ../index.php');
exit();
?>
