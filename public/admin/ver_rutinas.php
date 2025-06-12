<?php
session_start();
require_once __DIR__ . '/../../autoload.php';
use MongoDB\Client;

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo "<h2 style='color:red;'>Acceso denegado.</h2>";
    exit;
}

$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$rutinas = $db->rutinas->find();
$ejerciciosCol = $db->ejercicios;

// Mapeo de ejercicios por _id para mostrar datos de ejercicios
$ejerciciosMap = [];
foreach ($ejerciciosCol->find() as $e) {
    $ejerciciosMap[(string)$e['_id']] = $e;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ver Rutinas - Vida Saludable</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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

<div class="container py-5">
  <h2 class="mb-4 text-center"><img src="../img/fuerza.png" alt="Fuerza" style="height:40px;vertical-align:middle;margin-right:7px;">Rutinas Registradas</h2>
  <div class="text-end mb-4">
    <a href="crear_rutina.php" class="btn btn-success">Crear nueva rutina</a>
  </div>
  <?php foreach ($rutinas as $rutina): ?>
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($rutina['titulo']) ?></h5>
        <p class="mb-1"><strong>Objetivo:</strong> <?= $rutina['objetivo'] ?></p>
        <p class="mb-1"><strong>Nivel:</strong> <?= $rutina['nivel'] ?></p>
        <p class="mb-3"><strong>Duración:</strong> <?= $rutina['duracion_total_min'] ?> minutos</p>

        <h6 class="mb-2">Ejercicios incluidos:</h6>
        <ul class="list-group">
          <?php foreach ($rutina['ejercicios'] as $id): ?>
            <?php if (isset($ejerciciosMap[$id])): ?>
              <li class="list-group-item">
                <strong><?= htmlspecialchars($ejerciciosMap[$id]['nombre']) ?></strong> – 
                <?= htmlspecialchars($ejerciciosMap[$id]['repeticiones']) ?> 
                (<?= $ejerciciosMap[$id]['nivel'] ?>)
              </li>
            <?php else: ?>
              <li class="list-group-item text-danger">Ejercicio no encontrado: <?= $id ?></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>

        <div class="text-end mt-3">
          <a href="editar_rutina.php?id=<?= $rutina['_id'] ?>" class="btn btn-warning">Editar rutina</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
