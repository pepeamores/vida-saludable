<?php
session_start();

// Compatibilidad con PHP < 8.1
if (!function_exists('array_is_list')) {
    function array_is_list(array $arr): bool {
        if ($arr === []) return true;
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}

require_once __DIR__ . '/../../autoload.php';
use MongoDB\Client;


// Verificar acceso solo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo "<h2 style='color:red;'>Acceso denegado. Solo administradores pueden ver esta página.</h2>";
    exit;
}

// Conexión
$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;

$totalUsuarios = $db->usuarios->countDocuments();
$totalRutinas = $db->rutinas->countDocuments();
$totalEjercicios = $db->ejercicios->countDocuments();
$totalRegistrosCalendario = $db->calendario->countDocuments();

// Activos esta semana
$fechaInicioSemana = (new DateTime())->modify('monday this week')->format('Y-m-d');
$usuariosActivosSemana = $db->calendario->distinct('usuario_id', ['semana_inicio' => ['$gte' => $fechaInicioSemana]]);
$usuariosActivosCount = count($usuariosActivosSemana);

// Nuevos en el último mes
$fechaMesAtras = (new DateTime('-1 month'))->format(DateTime::ATOM); // ISO 8601

// En la consulta:
$usuariosNuevos = $db->usuarios->countDocuments([
    'fecha_creacion' => ['$gte' => $fechaMesAtras]
]);
// Promedio entrenamientos
$totalEntrenamientos = 0;
$usuariosUnicos = [];
foreach ($db->calendario->find() as $registro) {
    $usuariosUnicos[$registro['usuario_id']] = true;
    foreach ($registro['dias'] as $dia) {
        if (!empty($dia['entrenado'])) $totalEntrenamientos++;
    }
}
$promedioEntrenamientos = $totalEntrenamientos > 0 ? round($totalEntrenamientos / max(count($usuariosUnicos), 1), 2) : 0;

// Inactivos
$fechaLimite = (new DateTime())->modify('-14 days')->format('Y-m-d');
$usuariosConActividad = $db->calendario->distinct('usuario_id', ['semana_inicio' => ['$gte' => $fechaLimite]]);
$usuariosTotales = $db->usuarios->distinct('_id');
$usuariosInactivos = array_diff($usuariosTotales, $usuariosConActividad);
$usuariosInactivosCount = count($usuariosInactivos);

// Opiniones y media
$opinionesCursor = $db->opiniones->find([], ['sort' => ['fecha' => -1]]);
$opiniones = iterator_to_array($opinionesCursor);
$mediaOpiniones = count($opiniones) > 0 ? round(array_sum(array_column($opiniones, 'puntuacion')) / count($opiniones), 2) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Control - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/style.css">
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
  <h1 class="text-center mb-4">🎛 Panel de Control del Administrador</h1>

  <div class="d-flex justify-content-center gap-3 mb-5 flex-wrap">
    <a href="crear_ejercicios.php" class="btn btn-success">➕ Crear Ejercicio</a>
    <a href="crear_dieta.php" class="btn btn-success">➕ Crear Dietas</a>
    <a href="../admin/crear_rutina.php" class="btn btn-primary">📝 Crear Rutina</a>
    <a href="ver_ejercicios.php" class="btn btn-warning">📋 Ver Ejercicios</a>
    <a href="ver_rutinas.php" class="btn btn-info">📋 Ver Rutinas</a>
    <a href="ver_dietas.php" class="btn btn-info">📋 Ver Dietas</a>
  </div>

  <div class="row text-center">
    <div class="col-md-3 mb-3"><div class="card p-3 shadow-sm"><h5>👥 Total Usuarios</h5><p class="display-6"><?= $totalUsuarios ?></p></div></div>
    <div class="col-md-3 mb-3"><div class="card p-3 shadow-sm"><h5>📚 Total Rutinas</h5><p class="display-6"><?= $totalRutinas ?></p></div></div>
    <div class="col-md-3 mb-3"><div class="card p-3 shadow-sm"><h5>💪 Total Ejercicios</h5><p class="display-6"><?= $totalEjercicios ?></p></div></div>
    <div class="col-md-3 mb-3"><div class="card p-3 shadow-sm"><h5>📅 Entradas en Calendario</h5><p class="display-6"><?= $totalRegistrosCalendario ?></p></div></div>
  </div>

  <div class="row text-center mt-4">
    <div class="col-md-3 mb-3"><div class="card p-3 shadow-sm"><h5>✅ Activos Esta Semana</h5><p class="display-6"><?= $usuariosActivosCount ?></p></div></div>
    <div class="col-md-3 mb-3"><div class="card p-3 shadow-sm"><h5>📈 Promedio Entrenamientos</h5><p class="display-6"><?= $promedioEntrenamientos ?></p></div></div>
    <div class="col-md-3 mb-3"><div class="card p-3 shadow-sm"><h5>⭐ Valoración Promedio</h5><p class="display-6"><?= $mediaOpiniones ?> / 10</p></div></div>
    <div class="col-md-3 mb-3"><div class="card p-3 shadow-sm"><h5>😴 Inactivos (14 días)</h5><p class="display-6"><?= $usuariosInactivosCount ?></p></div></div>
  </div>

  <div class="mt-5">
    <h3 class="text-center mb-4">💬 Opiniones de usuarios</h3>
    <div class="list-group">
      <?php foreach ($opiniones as $op): ?>
        <div class="list-group-item">
          <h6><?= htmlspecialchars($op['nombre']) ?>
          <small class="text-muted">(<?= date('d/m/Y', $op['fecha']->toDateTime()->getTimestamp()) ?>)</small></h6>
          <p class="mb-1">📈 Valoración: <strong><?= $op['puntuacion'] ?>/10</strong><br><?= htmlspecialchars($op['comentario']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
