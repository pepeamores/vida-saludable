<?php
session_start();
require '../autoload.php';
use MongoDB\Client;

$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;

$objetivo = $_GET['objetivo'] ?? '';
$nivel = $_GET['nivel'] ?? '';

$filtro = [];
if ($objetivo) {
    $filtro['objetivo'] = new MongoDB\BSON\Regex('^' . preg_quote($objetivo) . '$', 'i');
}
if ($nivel) {
    $filtro['nivel'] = new MongoDB\BSON\Regex('^' . preg_quote($nivel) . '$', 'i');
}

$rutinas = $db->rutinas->find($filtro);
$ejerciciosCol = $db->ejercicios;

$ejerciciosMap = [];
foreach ($ejerciciosCol->find() as $e) {
    $ejerciciosMap[(string)$e['_id']] = $e;
}
?>

<?php foreach ($rutinas as $rutina): ?>
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="card-title"><?= htmlspecialchars($rutina['titulo']) ?></h5>
      <p class="mb-1"><strong>Nivel:</strong> <?= $rutina['nivel'] ?></p>
      <p class="mb-3"><strong>Duración:</strong> <?= $rutina['duracion_total_min'] ?> minutos</p>

      <h6 class="mb-2">Ejercicios incluidos:</h6>
      <ul class="list-group">
        <?php foreach ($rutina['ejercicios'] as $id): ?>
          <?php if (isset($ejerciciosMap[$id])): ?>
            <li class="list-group-item">
              <a href="#" class="text-decoration-none" 
                onclick="mostrarModalEjercicio('<?= htmlspecialchars(addslashes($ejerciciosMap[$id]['nombre'])) ?>', '<?= htmlspecialchars(addslashes($ejerciciosMap[$id]['descripcion'] ?? 'Sin descripción.')) ?>', '<?= htmlspecialchars(addslashes($ejerciciosMap[$id]['nivel'])) ?>', '<?= htmlspecialchars(addslashes($ejerciciosMap[$id]['repeticiones'])) ?>', '<?= htmlspecialchars(addslashes($ejerciciosMap[$id]['grupo_muscular'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($ejerciciosMap[$id]['duracion_aprox_min'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($ejerciciosMap[$id]['video'] ?? '')) ?>')">
                📌 <?= htmlspecialchars($ejerciciosMap[$id]['nombre']) ?>
              </a>
            </li>
          <?php else: ?>
            <li class="list-group-item text-danger">⚠️ Ejercicio no encontrado: <?= $id ?></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endforeach; ?>
