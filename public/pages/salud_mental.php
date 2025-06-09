<?php
session_start();
require_once __DIR__ . '/../../autoload.php';
use MongoDB\Client;
$resultado = null;
$ejercicio = '';
$duracion = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animo = $_POST['animo'];
    $estres = (int)$_POST['estres'];
    if ($estres <= 3) {
        $resultado = "¡Buen trabajo! Tu nivel de estrés es bajo. Mantén tus hábitos saludables.";
        $ejercicio = "Respiración consciente";
        $duracion = 60;
    } elseif ($estres <= 7) {
        $resultado = "Tu nivel de estrés es moderado. Te recomendamos relajarte un poco.";
        $ejercicio = "Respiración profunda";
        $duracion = 120;
    } else {
        $resultado = "Tu nivel de estrés es alto. Haz una pausa y realiza este ejercicio.";
        $ejercicio = "Meditación guiada";
        $duracion = 180;
    }
    
if (isset($_SESSION['user_id'])) {
        $client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $db->mental->insertOne([
            'usuario_id' => $_SESSION['user_id'],
            'fecha' => date('Y-m-d'),
            'animo' => $animo,
            'estres' => $estres
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Salud Mental</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .temporizador-box {
        display: inline-block;
        min-width: 200px;
        min-height: 100px;
        background: #e3f2fd;
        border: 2px solid #2196f3;
        border-radius: 16px;
        padding: 20px 30px;
        margin-top: 10px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(33,150,243,0.08);
    }
    .temporizador-num {
        font-size: 2.8rem;
        font-weight: bold;
        color: #1565c0;
        margin-bottom: 10px;
    }
    .temporizador-mensaje {
        font-size: 2rem;
        font-weight: bold;
        color: #388e3c;
    }
    </style>
    <script>
    let intervalo, subIntervalo, timeoutRespiracion;

function iniciarTemporizador(segundos) {
    let tiempo = segundos;
    const display = document.getElementById('temporizador');
    const mensaje = document.getElementById('mensaje-respiracion');
    display.textContent = tiempo + 's';
    mensaje.textContent = '';
    clearInterval(intervalo);
    clearInterval(subIntervalo);
    clearTimeout(timeoutRespiracion);

    function cicloRespiracion() {
        mensaje.textContent = 'Inspira';
        mensaje.style.color = '#1976d2';
        timeoutRespiracion = setTimeout(() => {
            mensaje.textContent = 'Expira';
            mensaje.style.color = '#d32f2f';
        }, 4000);
    }

    cicloRespiracion();
    subIntervalo = setInterval(cicloRespiracion, 8000);

    intervalo = setInterval(() => {
        tiempo--;
        display.textContent = tiempo + 's';
        if (tiempo <= 0) {
            clearInterval(intervalo);
            clearInterval(subIntervalo);
            clearTimeout(timeoutRespiracion);
            display.textContent = '¡Tiempo terminado!';
            mensaje.textContent = '';
        }
    }, 1000);
}

function cancelarTemporizador() {
    clearInterval(intervalo);
    clearInterval(subIntervalo);
    clearTimeout(timeoutRespiracion);
    document.getElementById('temporizador').textContent = '';
    document.getElementById('mensaje-respiracion').textContent = '';
}


    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartMental = null;

function mostrarGraficaMental() {
    document.getElementById('seccion-grafica-mental').style.display = 'block';
    fetch('../config/datos_grafica_mental.php')
        .then(res => res.json())
        .then(data => {
            if (chartMental) chartMental.destroy();
            chartMental = new Chart(document.getElementById('graficaMental'), {
                type: 'bar',
                data: {
                    labels: data.fechas,
                    datasets: [
                        {
                            label: 'Nivel de Estrés',
                            data: data.estres,
                            backgroundColor: 'rgba(255,99,132,0.6)'
                        },
                    ]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true, max: 10 }
                    }
                }
            });
        });
}
function toggleGraficaMental() {
    const seccion = document.getElementById('seccion-grafica-mental');
    const btn = document.getElementById('btn-grafica-mental');
    const icono = '<img src="../img/graficas1.png" alt="Gráfica" style="height:24px;vertical-align:middle;margin-right:7px;">';
    if (seccion.style.display === 'block') {
        seccion.style.display = 'none';
        btn.innerHTML = icono + 'Mostrar gráfica';
        if (window.chartMental) {
            window.chartMental.destroy();
            window.chartMental = null;
        }    
    } else {
        seccion.style.display = 'block';
        btn.innerHTML = icono + 'Ocultar gráfica';
        if (!window.chartMental || window.chartMental._destroyed) {
            mostrarGraficaMental();
        }
    }
}
</script>
</head>
<body class="bg-light">

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
        <h1>Salud Mental</h1>
        <p>Cuida tu mente y emociones con ejercicios y consejos prácticos.</p>
    </div>
    <nav class="main-nav">
        <ul>
            <li><a href="../inicio.php">Inicio</a></li>
            <li><a href="alimentacion.php">Alimentación Saludable</a></li>
            <li><a href="ejercicio.php">Ejercicio Físico</a></li>
        </ul>
    </nav>
</header>

<section class="as highlight d-flex flex-wrap justify-content-center align-items-center my-5 p-4" style="max-width: 900px; margin: 0 auto;">
    <img src="../img/mental_health.jpg" alt="Salud Mental" class="img-fluid" style="max-width: 420px; border-radius: 14px;">
    <div class="highlight-text ms-4 mt-4 mt-md-0" style="max-width: 320px; padding: 1.2rem;">
        <h2 style="font-size:1.5rem;">Bienestar emocional y mental</h2>
        <p style="font-size:1.05rem;">Aprende técnicas para reducir el estrés, mejorar tu ánimo y cuidar tu salud mental cada día.</p>
    </div>
</section>

<main class="container my-5">
    <section id="encuesta">
        <h2 class="mb-4 text-center">Encuesta rápida</h2>
        <form method="post" class="bg-white p-4 rounded shadow highlight" style="max-width: 500px; margin: 0 auto;">
            <div class="mb-3">
                <label class="form-label">¿Cómo te sientes hoy?</label>
                <select name="animo" class="form-select" required>
                    <option value="Feliz">Feliz</option>
                    <option value="Tranquilo">Tranquilo</option>
                    <option value="Cansado">Cansado</option>
                    <option value="Ansioso">Ansioso</option>
                    <option value="Triste">Triste</option>
                    <option value="Enojado">Enojado</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">¿Cuánto estrés sientes? (0 = nada, 10 = mucho)</label>
                <input type="number" name="estres" min="0" max="10" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-info w-100">Enviar</button>
        </form>
    </section>

    <?php if ($resultado): ?>
    <section class="mt-5">
        
        <div class="alert alert-success text-center" style="max-width: 500px; margin: 0 auto;">
            <p><?= htmlspecialchars($resultado); ?></p>
            <h3>Ejercicio recomendado: <?= htmlspecialchars($ejercicio); ?></h3>
            <button class="btn btn-info mt-2" onclick="iniciarTemporizador(<?= $duracion; ?>)">Iniciar temporizador (<?= $duracion; ?>s)</button>
            <button class="btn btn-danger mt-2" onclick="cancelarTemporizador()">Cancelar</button>
            <div id="recuadro-temporizador" class="temporizador-box mt-3">
                <div id="temporizador" class="temporizador-num"></div>
                <div id="mensaje-respiracion" class="temporizador-mensaje"></div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>
<div class="container my-4 text-center">
    <button class="btn btn-info" id="btn-grafica-mental" onclick="toggleGraficaMental()">
        <img src="../img/graficas1.png" alt="Gráfica" style="height:24px;vertical-align:middle;margin-right:7px;">
        Mostrar gráfica
    </button>
</div>

<section class="container my-5" id="seccion-grafica-mental" style="display:none;">
    <h2 class="mb-4 text-center">Estrés por día</h2>
    <canvas id="graficaMental"></canvas>
</section>

<footer class="text-center py-3 bg-dark text-white mt-5">
    <p>&copy; 2025 Vida Saludable. Todos los derechos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>