<?php
session_start();
require_once __DIR__ . '/../public/db.php';
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

$tmb = null;
$userData = null;

$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$collection = $db->usuarios;

if (isset($_SESSION['user_id'])) {
    $userData = $collection->findOne(['_id' => new ObjectId($_SESSION['user_id'])]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['altura'] = $user->altura ?? '';
    $_SESSION['peso'] = $user->peso ?? '';
    $_SESSION['sexo'] = $user->sexo ?? '';
    $_SESSION['fecha_nacimiento'] = $user->fecha_nacimiento ?? null;

    // Harris-Benedict Formula
    if ($sexo === 'masculino') {
        $tmb = 88.36 + (13.4 * $peso) + (4.8 * $altura) - (5.7 * $edad);
    } else {
        $tmb = 447.6 + (9.2 * $peso) + (3.1 * $altura) - (4.3 * $edad);
    }
}
function calcularEdad($fechaNacimiento) {
    try {
        $fecha = new DateTime($fechaNacimiento);
        $hoy = new DateTime();
        return $hoy->diff($fecha)->y;
    } catch (Exception $e) {
        return '';
    }
}

// Calcular edad desde la sesión
$edadCalculada = isset($_SESSION['fecha_nacimiento']) ? calcularEdad($_SESSION['fecha_nacimiento']) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alimentación Saludable - Vida Saludable</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php if (isset($_SESSION['nombre'])): ?>
    <div class="dropdown position-fixed top-0 end-0 m-3">
        <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
            <img src="img/pefil.png" alt="Perfil" width="32" height="32" class="rounded-circle me-2">
            <?= htmlspecialchars($_SESSION['nombre']) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item text-danger" href="logout.php">Cerrar sesión</a></li>
        </ul>
    </div>
<?php endif; ?>

<header class="header">
    <div class="header-content">
        <h1>Vida Saludable</h1>
        <p>Descubre cómo mejorar tu salud física, mental y emocional a través de hábitos saludables.</p>
        <?php if (isset($_SESSION['nombre'])): ?>
            <p class="mt-2">👋 Bienvenido, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></p>
        <?php endif; ?>
    </div>

    <nav class="main-nav">
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="ejercicio.php">Ejercicio Físico</a></li>
        </ul>
    </nav>
</header>
<section class="highlight text-center p-4">
    <h2>¿Por qué es importante una buena alimentación?</h2>
    <p>Una alimentación equilibrada es clave para mantener la energía, prevenir enfermedades y mejorar tu calidad de vida.</p>
</section>

<main class="container my-5">
    <?php if (isset($tmb)): ?>
        <div class="alert alert-success text-center shadow-sm">
            <h2>🔢 Resultado</h2>
            <p>Tu requerimiento calórico diario estimado es:</p>
            <p class="display-4"><strong><?= round($tmb) ?> kcal</strong></p>
            <p>¡Utiliza este dato para planificar tus comidas y mantenerte saludable!</p>
        </div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm">
        <h3 class="text-center mb-3">Calcula tus calorías</h3>
        <form action="alimentacion.php" method="POST">
            <div class="mb-3">
                <label for="peso">Peso (kg):</label>
                <input type="number" name="peso" id="peso" step="0.1" class="form-control" value="<?= $userData['peso'] ?? '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="altura">Altura (cm):</label>
                <input type="number" name="altura" id="altura" class="form-control" value="<?= isset($userData['altura']) ? $userData['altura']  : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="sexo">Sexo:</label>
                <select name="sexo" id="sexo" class="form-control" required>
                    <option value="">Selecciona</option>
                    <option value="masculino" <?= (isset($userData['sexo']) && $userData['sexo'] === 'masculino') ? 'selected' : '' ?>>Masculino</option>
                    <option value="femenino" <?= (isset($userData['sexo']) && $userData['sexo'] === 'femenino') ? 'selected' : '' ?>>Femenino</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="edad">Edad:</label>
                <input type="number" name="edad" id="edad" class="form-control" value="<?= $edadCalculada ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Calcular</button>
        </form>
    </div>
</main>

<section class="bg-light text-center py-4">
    <h2>🥗 Consejos de Alimentación</h2>
    <p>"Comer sano no es una dieta, es un estilo de vida."</p>
    <p class="text-muted">- Nutricionista Vida Saludable</p>
</section>

<footer class="text-center py-3 bg-dark text-white">
    <p>&copy; 2024 Vida Saludable. Todos los derechos reservados.</p>
</footer>

</body>
</html>
