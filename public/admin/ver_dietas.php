<?php

session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header('Location: admin.php'); exit; }
require '../../autoload.php';
use MongoDB\Client;
use MongoDB\BSON\ObjectId;
$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$dietas = $db->dietas->find([], ['sort' => ['fecha_creacion' => -1]]);
function objetivo_texto($valor) {
    return [
        'perder_peso' => 'Perder peso',
        'mantener_peso' => 'Mantener peso',
        'ganar_musculo' => 'Ganar masa muscular'
    ][$valor] ?? ucfirst($valor);
}
function ejercicio_texto($valor) {
    return [
        'sedentario' => 'Sedentario',
        'ligero' => 'Ligero',
        'moderado' => 'Moderado',
        'intenso' => 'Intenso'
    ][$valor] ?? ucfirst($valor);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Dietas - Vida Saludable</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="bg-light">


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
    <h1>Dietas Guardadas</h1>
    <p>Visualiza y gestiona todas las dietas creadas en Vida Saludable.</p>
  </div>
</header>

<div class="container py-5">
  <h2 class="mb-4 text-center"><img src="../img/veradmin.png" alt="Dietas" style="height:40px;vertical-align:middle;margin-right:7px;">Dietas</h2>
  <div class="table-responsive">
    <table class="table table-bordered bg-white shadow rounded" style="border-radius: 18px; overflow: hidden;">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Alimentos</th>
          <th>Calorías</th>
          <th>Objetivo</th>
          <th>Cantidad de ejercicio</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($dietas as $dieta): ?>
        <tr>
          <td><?= htmlspecialchars($dieta['nombre']) ?></td>
          <td>
                    <?php
                    if (isset($dieta['alimentos']) && $dieta['alimentos'] instanceof \MongoDB\Model\BSONArray) {
                        foreach ($dieta['alimentos'] as $alimento) {
                            $alimento = (array)$alimento;
                            echo htmlspecialchars($alimento['nombre']) . " (" . htmlspecialchars($alimento['cantidad_gramos']) . "g)<br>";
                        }
                    } elseif (is_string($dieta['alimentos'])) {
                        echo htmlspecialchars($dieta['alimentos']);
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
          <td><?= htmlspecialchars($dieta['calorias']) ?></td>
          <td><?= isset($dieta['objetivo']) ? objetivo_texto($dieta['objetivo']) : '-' ?></td>
          <td><?= isset($dieta['ejercicio']) ? ejercicio_texto($dieta['ejercicio']) : '-' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="text-end mt-4">
    <a href="crear_dieta.php" class="btn btn-success">Crear nueva dieta</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>