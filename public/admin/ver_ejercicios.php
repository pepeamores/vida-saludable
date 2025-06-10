<?php
session_start();
require_once __DIR__ . '/../../autoload.php';
use MongoDB\Client;

$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$ejercicios = $db->ejercicios->find();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ver Ejercicios - Admin</title>
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
    <h1>Ejercicios Guardados</h1>
    <p>Visualiza y gestiona todos los ejercicios creados en Vida Saludable.</p>
  </div>
</header>

<div class="container py-5">
  <h2 class="mb-4 text-center"><img src="../img/fuerza.png" alt="Fuerza" style="height:40px;vertical-align:middle;margin-right:7px;">Todos los Ejercicios Registrados</h2>

  <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
    <div class="text-end mb-4">
      <a href="crear_ejercicios.php" class="btn btn-success">Nuevo Ejercicio</a>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <?php foreach ($ejercicios as $ej): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($ej['nombre']) ?></h5>
            <p class="text-muted"><?= htmlspecialchars($ej['descripcion']) ?></p>
            <ul class="list-unstyled">
              <li><strong>Grupo muscular:</strong> <?= $ej['grupo_muscular'] ?></li>
              <li><strong>Nivel:</strong> <?= $ej['nivel'] ?></li>
              <li><strong>Repeticiones:</strong> <?= $ej['repeticiones'] ?></li>
              <li><strong>Duración:</strong> <?= $ej['duracion_aprox_min'] ?> min</li>
            </ul>
          </div>
          <?php if (!empty($ej['video'])): ?>
            <div class="ratio ratio-16x9">
              <iframe src="<?= $ej['video'] ?>" allowfullscreen></iframe>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
