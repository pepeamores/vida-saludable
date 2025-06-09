<?php
session_start();
require_once __DIR__ . '/../config/db.php';
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
    $peso = isset($_POST['peso']) ? floatval($_POST['peso']) : null;
    $altura = isset($_POST['altura']) ? floatval($_POST['altura']) : null;
    $sexo = $_POST['sexo'] ?? '';
    $edad = isset($_POST['edad']) ? intval($_POST['edad']) : null;

    $_SESSION['peso'] = $peso;
    $_SESSION['altura'] = $altura;
    $_SESSION['sexo'] = $sexo;

    // Harris-Benedict Formula
    if ($sexo === 'masculino') {
        $tmb = 88.36 + (13.4 * $peso) + (4.8 * $altura) - (5.7 * $edad);
    } elseif ($sexo === 'femenino') {
        $tmb = 447.6 + (9.2 * $peso) + (3.1 * $altura) - (4.3 * $edad);
    }
    $factor = 1.2;
    if ($_POST['ejercicio'] === 'sedentario') $factor = 1.2; 
    if ($_POST['ejercicio'] === 'ligero') $factor = 1.375;
    if ($_POST['ejercicio'] === 'moderado') $factor = 1.55;
    if ($_POST['ejercicio'] === 'intenso') $factor = 1.725;

    $calorias = $tmb * $factor;

    // Ajuste según objetivo
    if ($_POST['objetivo'] === 'perder_peso') {
        $calorias -= 500; 
    } elseif ($_POST['objetivo'] === 'ganar_musculo') {
        $calorias += 300; 
    }
    $tmb = $calorias; 

    // Buscar dietas recomendadas según objetivo y ejercicio
    $dietasRecomendadas = $db->dietas->find([
        'objetivo' => $_POST['objetivo'],
        'ejercicio' => $_POST['ejercicio']
    ]);

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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Alimentación Saludable - Vida Saludable</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php if (isset($_SESSION['nombre'])): ?>
    <div class="dropdown position-fixed top-0 end-0 m-3">
        <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
            <img src="../img/pefil.png" alt="Perfil" width="32" height="32" class="rounded-circle me-2">
            <?= htmlspecialchars($_SESSION['nombre']) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item text-danger" href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </div>
<?php endif; ?>

<header class="header text-center">
  <div class="header-content">
    <h1>Alimentación Saludable</h1>
    <p>Descubre cómo calcular tus calorías diarias y obtener recomendaciones de dietas saludables.</p>
  </div>
  <nav class="main-nav">
    <ul>
      <li><a href="../inicio.php">Inicio</a></li>            
      <li><a href="ejercicio.php">Ejercicio Físico</a></li>
      <li><a href="salud_mental.php">Salud Mental</a></li>
    </ul>
  </nav>
</header>


<main class="container my-5">
    <?php if (isset($tmb)): ?>
        <div id="resultado-calorias" class="alert alert-success text-center shadow-sm mb-4">
            <h2>Resultado</h2>
            <p>Tu requerimiento calórico diario estimado es:</p>
            <p class="display-4"><strong><?= round($tmb) ?> kcal</strong></p>
            <p>¡Utiliza este dato para planificar tus comidas y mantenerte saludable!</p>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const resultado = document.getElementById('resultado-calorias');
                if (resultado) {
                    resultado.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                // Abrir el acordeón automáticamente
                const collapse = document.getElementById('calculadoraCollapse');
                if (collapse) {
                    new bootstrap.Collapse(collapse, { show: true });
                }
            });
        </script>
    <?php endif; ?>

    <div class="accordion" id="calculadoraAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingCalculadora">
                <button class="accordion-button <?= (isset($tmb) || $_SERVER['REQUEST_METHOD'] !== 'POST') ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#calculadoraCollapse" aria-expanded="true" aria-controls="calculadoraCollapse">
                    Calcula tus calorías
                </button>
            </h2>
            <div id="calculadoraCollapse" class="accordion-collapse collapse show" aria-labelledby="headingCalculadora" data-bs-parent="#calculadoraAccordion">
                <div class="accordion-body">
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
                            <input type="number" name="edad" id="edad" class="form-control" value="<?= $edadCalculada !== '' ? $edadCalculada : (isset($_POST['edad']) ? intval($_POST['edad']) : '') ?>"<?= $edadCalculada !== '' ? 'readonly' : '' ?>required>
                        </div>
                        <div class="mb-3">
                            <label for="objetivo">¿Cuál es tu objetivo?</label>
                            <select name="objetivo" id="objetivo" class="form-control" required>
                                <option value="">Selecciona</option>
                                <option value="perder_peso">Bajar de peso</option>
                                <option value="mantener_peso">Mantener el peso</option>
                                <option value="ganar_musculo">Ganar masa muscular</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="ejercicio">Cantidad de ejercicio semanal:</label>
                            <select name="ejercicio" id="ejercicio" class="form-control" required>
                                <option value="">Selecciona</option>
                                <option value="sedentario">Sedentario (poco o ningún ejercicio)</option>
                                <option value="ligero">Ligero (1-3 días/semana)</option>
                                <option value="moderado">Moderado (3-5 días/semana)</option>
                                <option value="intenso">Intenso (6-7 días/semana)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Calcular</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Mostrar dietas recomendadas más cercanas a las calorías calculadas
    if (isset($tmb) && isset($dietasRecomendadas)) {
        // Ordenar dietas por cercanía de calorías
        $dietasArray = iterator_to_array($dietasRecomendadas);
        usort($dietasArray, function($a, $b) use ($tmb) {
            return abs($a['calorias'] - $tmb) <=> abs($b['calorias'] - $tmb);
        });
        if (count($dietasArray) > 0) {
            echo '<div class="mt-5">';
            echo '<h3 class="text-center mb-3"> Dietas recomendadas según tus calorías</h3>';
            echo '<div class="row justify-content-center">';
            foreach (array_slice($dietasArray, 0, 6) as $dieta) { // Muestra las 3 más cercanas
                echo '<div class="col-md-4 mb-3">';
                echo '<div class="card shadow-sm h-100">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">'.htmlspecialchars($dieta['nombre']).'</h5>';
                echo '<p><strong>Calorías:</strong> '.htmlspecialchars($dieta['calorias']).' kcal</p>';
                
                echo '<p><strong>Alimentos:</strong></p><ul class="list-unstyled small">';
                    if (isset($dieta['alimentos']) && $dieta['alimentos'] instanceof \MongoDB\Model\BSONArray) {
                        foreach ($dieta['alimentos'] as $alimento) {
                            $alimento = (array)$alimento;
                            echo '<li>• ' . htmlspecialchars($alimento['nombre']) . ' (' . htmlspecialchars($alimento['cantidad_gramos']) . 'g)</li>';
                        }       
                    } elseif (is_string($dieta['alimentos'])) {
                        echo '<li>' . htmlspecialchars($dieta['alimentos']) . '</li>';
                    } else {
                        echo '<li>No disponible</li>';
                }
                    echo '</ul>';

                echo '</div></div></div>';
            }
            echo '</div></div>';
        } else {
            echo '<div class="alert alert-warning text-center mt-4">No se encontraron dietas para tu objetivo y nivel de ejercicio.</div>';
        }
    }
    ?>
</main>



<footer class="text-center py-3 bg-dark text-white">
    <p>&copy; 2025 Vida Saludable. Todos los derechos reservados.</p>
</footer>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</html>
