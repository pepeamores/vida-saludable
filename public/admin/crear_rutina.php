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
$coleccionEjercicios = $db->ejercicios;
$coleccionRutinas = $db->rutinas;

$ejercicios = $coleccionEjercicios->find([], ['sort' => ['nombre' => 1]]);
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rutina = [
        'titulo' => $_POST['titulo'],
        'objetivo' => $_POST['objetivo'],
        'nivel' => $_POST['nivel'],
        'duracion_total_min' => (int)$_POST['duracion'],
        'ejercicios' => $_POST['ejercicios'] ?? []
    ];

    $coleccionRutinas->insertOne($rutina);
    $mensaje = "Rutina creada correctamente.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Rutina</title>
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
    <h1>Crear Rutinas</h1>
    <p>Crea ejercicios en Vida Saludable.</p>
  </div>
</header>   

<div class="container mt-5">
    <h2 class="mb-4 text-center"><img src="../img/crear_rutina.png" alt="Crear Rutina" style="height:50px;vertical-align:middle;margin-right:7px;">Crear Nueva Rutina</h2>

  <?php if ($mensaje): ?>
    <div class="alert alert-success"><?= $mensaje ?></div>
  <?php endif; ?>

  <form method="post" class="bg-white p-4 rounded shadow">
    <div class="mb-3">
      <label for="titulo" class="form-label">Título de la rutina</label>
      <input type="text" name="titulo" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="objetivo" class="form-label">Objetivo</label>
      <select name="objetivo" class="form-select" required>
        <option value="pérdida de peso">Pérdida de peso</option>
        <option value="ganancia muscular">Ganancia muscular</option>
        <option value="tonificación">Tonificación</option>
        <option value="movilidad">Movilidad y flexibilidad</option>
      </select>
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
      <label for="duracion" class="form-label">Duración total (min)</label>
      <input type="number" name="duracion" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="ejercicios" class="form-label">Ejercicios incluidos</label>
      <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
        <?php foreach ($ejercicios as $e): ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ejercicios[]" value="<?= $e['_id'] ?>" id="ej<?= $e['_id'] ?>">
            <label class="form-check-label" for="ej<?= $e['_id'] ?>">
              <?= htmlspecialchars($e['nombre']) ?> 
              <small class="text-muted">(<?= $e['nivel'] ?> - <?= $e['grupo_muscular'] ?>)</small>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
      <small class="text-muted">Marca los ejercicios que formarán parte de la rutina</small>
    </div>

    <button type="submit" class="btn btn-primary">Guardar rutina</button>
    <a href="ver_ejercicios.php" class="btn btn-secondary ms-2">Volver</a>
  </form>
</div>
</body>
</html>
