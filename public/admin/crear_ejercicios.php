<?php
session_start();
require '../../autoload.php';
use MongoDB\Client;

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo "<h2 style='color:red;'>Acceso denegado. Solo administradores pueden ver esta página.</h2>";
    exit;
}

$mensaje = "";

// Función para convertir URL a formato embebido
function convertirAEmbed($url) {
    if (strpos($url, 'youtube.com/watch?v=') !== false) {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        return "https://www.youtube.com/embed/" . ($params['v'] ?? '');
    }
    if (strpos($url, 'youtu.be/') !== false) {
        $parts = explode('/', $url);
        return "https://www.youtube.com/embed/" . end($parts);
    }
    return $url; // Si ya es embed o inválido
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
    $db = $client->vida_saludable;
    $coleccion = $db->ejercicios;

    $videoEmbed = convertirAEmbed(trim($_POST['video']));

    $ejercicio = [
        'nombre' => $_POST['nombre'],
        'grupo_muscular' => $_POST['grupo'],
        'nivel' => $_POST['nivel'],
        'descripcion' => $_POST['descripcion'],
        'duracion_aprox_min' => (int)$_POST['duracion'],
        'repeticiones' => $_POST['repeticiones'],
        'video' => $videoEmbed
    ];

    try {
        $coleccion->insertOne($ejercicio);
        $mensaje = "✅ Ejercicio insertado correctamente.";
    } catch (Exception $e) {
        $mensaje = "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Ejercicio - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/style.css">
</head>
<body class="bg-light">
<!-- Botón Volver -->
<div class="position-fixed top-0 start-0 m-3">
  <a href="admin.php" class="btn btn-primary">Volver</a>
</div>
<?php if (isset($_SESSION['nombre'])): ?>
  <div class="dropdown position-fixed top-0 end-0 m-3">
    <button class="btn btn-light dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
      <img src="../img/pefil.png" width="32" height="32" class="rounded-circle me-2">
      <?= htmlspecialchars($_SESSION['nombre']) ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item text-danger" href="../logout.php">Cerrar sesión</a></li>
    </ul>
  </div>
<?php endif; ?>

<header class="header text-center">
  <div class="header-content">
    <h1>Rutinas Guardadas</h1>
    <p>Visualiza y gestiona todas las rutinas creadas en Vida Saludable.</p>

  </div>
</header>

<div class="container mt-5">
    <h2 class="mb-4">📝 Crear Nuevo Ejercicio</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-info"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="post" class="bg-white p-4 rounded shadow">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del ejercicio</label>
            <input type="text" class="form-control" name="nombre" required>
        </div>

        <div class="mb-3">
            <label for="grupo" class="form-label">Grupo muscular</label>
            <input type="text" class="form-control" name="grupo" required>
        </div>

        <div class="mb-3">
            <label for="nivel" class="form-label">Nivel</label>
            <select name="nivel" class="form-select" required>
                <option value="principiante">Principiante</option>
                <option value="intermedio">Intermedio</option>
                <option value="avanzado">Avanzado</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label for="duracion" class="form-label">Duración aproximada (minutos)</label>
            <input type="number" class="form-control" name="duracion" required>
        </div>

        <div class="mb-3">
            <label for="repeticiones" class="form-label">Repeticiones</label>
            <input type="text" class="form-control" name="repeticiones" required>
        </div>

        <div class="mb-3">
            <label for="video" class="form-label">URL del video</label>
            <input type="url" class="form-control" name="video">
        </div>

        <button type="submit" class="btn btn-primary">Guardar ejercicio</button>
        <a href="../inicio.php" class="btn btn-secondary ms-2">Volver</a>
    </form>
</div>

</body>
</html>
