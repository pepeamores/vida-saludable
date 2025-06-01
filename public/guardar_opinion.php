<?php
require '../autoload.php'; // Asegúrate de que la ruta sea correcta si lo mueves de carpeta
use MongoDB\Client;
session_start();

// Solo permitir si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $puntuacion = isset($_POST['puntuacion']) ? (int)$_POST['puntuacion'] : null;
    $comentario = trim($_POST['comentario'] ?? '');

    // Validaciones básicas
    if ($puntuacion < 1 || $puntuacion > 10 || empty($comentario)) {
        die("Datos inválidos.");
    }

    // Conexión a MongoDB
    $client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
    $db = $client->vida_saludable;

    // Documento que se insertará
    $opinion = [
        'usuario_id' => $_SESSION['user_id'],
        'nombre'     => $_SESSION['nombre'] ?? 'Anónimo',
        'puntuacion' => $puntuacion,
        'comentario' => $comentario,
        'fecha'      => new MongoDB\BSON\UTCDateTime()
    ];

    try {
        $db->opiniones->insertOne($opinion);
        header("Location: index.php?mensaje=Gracias por tu opinión");
        exit();
    } catch (Exception $e) {
        echo "Error al guardar la opinión: " . $e->getMessage();
    }
} else {
    echo "Método no permitido.";
}
?>
