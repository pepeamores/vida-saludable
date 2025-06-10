<?php
session_start();
require '../../autoload.php';
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    exit("Acceso denegado.");
}

$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$coleccionRutinas = $db->rutinas;
$coleccionEjercicios = $db->ejercicios;

$id = $_GET['id'] ?? null;
if (!$id) {
    exit("Rutina no especificada.");
}

// Verificar si el id es un ObjectId válido
if (preg_match('/^[a-f\d]{24}$/i', $id)) {
    $filtro = ['_id' => new ObjectId($id)];
} else {
    $filtro = ['_id' => $id]; // tratar como string simple
}

$rutina = $coleccionRutinas->findOne($filtro);
$ejercicios = $coleccionEjercicios->find([], ['sort' => ['nombre' => 1]]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateFiltro = preg_match('/^[a-f\d]{24}$/i', $id) ? ['_id' => new ObjectId($id)] : ['_id' => $id];

    $coleccionRutinas->updateOne(
        $updateFiltro,
        ['$set' => [
            'titulo' => $_POST['titulo'],
            'objetivo' => $_POST['objetivo'],
            'nivel' => $_POST['nivel'],
            'duracion_total_min' => (int)$_POST['duracion'],
            'ejercicios' => $_POST['ejercicios'] ?? []
        ]]
    );
    header("Location: ver_rutinas.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Rutina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="bg-light">

<!-- Botón Volver -->
<div class="position-fixed top-0 start-0 m-3">
    <a href="ver_rutinas.php" class="btn btn-outline-secondary">Volver</a>
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
        <h1>Editar Rutina</h1>
        <p>Modifica los datos de una rutina existente.</p>
    </div>
</header>

<div class="container py-5">
    <h2 class="mb-4">Editar Rutina</h2>

    <form method="post" class="bg-white p-4 rounded shadow">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" value="<?= htmlspecialchars($rutina['titulo']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Objetivo</label>
            <select name="objetivo" class="form-select" required>
                <?php foreach (['pérdida de peso', 'ganancia muscular', 'tonificación', 'movilidad'] as $op): ?>
                    <option value="<?= $op ?>" <?= $rutina['objetivo'] === $op ? 'selected' : '' ?>><?= $op ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Nivel</label>
            <select name="nivel" class="form-select" required>
                <?php foreach (['principiante', 'intermedio', 'avanzado'] as $nivel): ?>
                    <option value="<?= $nivel ?>" <?= $rutina['nivel'] === $nivel ? 'selected' : '' ?>><?= $nivel ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Duración total (min)</label>
            <input type="number" name="duracion" value="<?= $rutina['duracion_total_min'] ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Ejercicios incluidos</label>
            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                <?php $idsSeleccionados = array_map('strval', (array)$rutina['ejercicios']); ?>
                <?php foreach ($ejercicios as $e): ?>
                    <?php $idEj = (string)$e['_id']; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ejercicios[]" value="<?= $idEj ?>"
                            <?= in_array($idEj, $idsSeleccionados) ? 'checked' : '' ?>>
                        <label class="form-check-label">
                            <?= htmlspecialchars($e['nombre']) ?>
                            <small class="text-muted">(<?= $e['nivel'] ?> - <?= $e['grupo_muscular'] ?? 'sin grupo' ?>)</small>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="ver_rutinas.php" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>

</body>
</html>
