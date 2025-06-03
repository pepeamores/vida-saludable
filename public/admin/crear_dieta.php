<?php
// crear_dieta.php
session_start();
require '../../autoload.php';
use MongoDB\Client;

// Conexión a la base de datos
$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $alimentos = $_POST['alimentos'] ?? '';
    $calorias = (int)($_POST['calorias'] ?? 0);
    $objetivo = $_POST['objetivo'] ?? '';
    $ejercicio = $_POST['ejercicio'] ?? '';
    if ($nombre && $alimentos && $objetivo && $ejercicio) {
        $db->dietas->insertOne([
            'nombre' => $nombre,
            'alimentos' => $alimentos,
            'calorias' => $calorias,
            'objetivo' => $objetivo,
            'ejercicio' => $ejercicio,
            'fecha_creacion' => new MongoDB\BSON\UTCDateTime()
        ]);
        $msg = "Dieta añadida correctamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Dieta</title>
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
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

<div class="container my-5">
    <div class="d-flex justify-content-end mb-3">
        <a href="admin.php" class="btn btn-secondary">Volver al panel</a>
    </div>
    <h2 class="mb-4">🥗 Crear Dieta</h2>
    
    <form method="post" class="card p-4 shadow-sm" style="max-width: 500px; margin: 0 auto; border-radius: 16px;">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
        <label class="form-label">Alimentos</label>
        <textarea name="alimentos" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Calorías</label>
            <input type="number" name="calorias" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Objetivo</label>
            <select name="objetivo" class="form-select" required>
                <option value="">Selecciona un objetivo</option>
                <option value="perder_peso">Perder peso</option>
                <option value="mantener_peso">Mantener peso</option>
                <option value="ganar_musculo">Ganar masa muscular</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Cantidad de ejercicio</label>
            <select name="ejercicio" class="form-select" required>
                <option value="">Selecciona una opción</option>
                <option value="sedentario">Sedentario (poco o ningún ejercicio)</option>
                <option value="ligero">Ligero (ejercicio ligero 1-3 días/semana)</option>
                <option value="moderado">Moderado (ejercicio moderado 3-5 días/semana)</option>
                <option value="intenso">Intenso (ejercicio fuerte 6-7 días/semana)</option>
            </select>
        </div>
        <button class="btn btn-success w-100">Guardar</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>