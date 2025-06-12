<?php 
session_start(); 
$recomendacionTexto = '';
if (isset($_SESSION['altura']) && isset($_SESSION['peso'])) {
    $alturaM = floatval($_SESSION['altura']); // en metros
    $peso = floatval($_SESSION['peso']); // en kg

    if ($alturaM > 0) {
        $imc = $peso / ($alturaM * $alturaM);
        $opcion = '';

        if ($imc < 18.5) {
            $opcion = 'ganancia muscular';
        } elseif ($imc >= 18.5 && $imc < 25) {
            $opcion = 'tonificación';
        } else {
            $opcion = 'pérdida de peso';
        }

        $recomendacionTexto = "Según tu altura ({$alturaM} m) y tu peso ({$peso} kg), te recomendamos que te enfoques en la siguiente opción: <strong>{$opcion}</strong>.";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ejercicio Físico - Vida Saludable</title>
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
    <h1>Ejercicio Físico</h1>
    <p>Transforma tu cuerpo y mente con rutinas adaptadas a tus metas.</p>
  </div>
  <nav class="main-nav">
    <ul>
      <li><a href="../inicio.php">Inicio</a></li>            
      <li><a href="alimentacion.php">Alimentación Saludable</a></li>
      <li><a href="salud_mental.php">Salud Mental</a></li>
    </ul>
  </nav>
</header>


<section class="container my-5 text-center">
  <h2 class="mb-4">Selecciona tu objetivo</h2>
  <div class="d-flex justify-content-center flex-wrap gap-3 mb-4" id="objetivos-btns">
  <button class="btn btn-white border objetivo-btn" onclick="mostrarRecomendacion('pérdida de peso', this)">
    <img src="../img/perdida_peso.png" alt="Pérdida de peso" style="height: 22px; vertical-align: middle; margin-right: 7px;">
    Pérdida de peso
  </button>
  <button class="btn btn-white border objetivo-btn" onclick="mostrarRecomendacion('ganancia muscular', this)">
    <img src="../img/fuerza.png" alt="Ganancia muscular" style="height: 22px; vertical-align: middle; margin-right: 7px;">
    Ganancia muscular
  </button>
  <button class="btn btn-white border objetivo-btn" onclick="mostrarRecomendacion('tonificación', this)">
    <img src="../img/fuerza.png" alt="Tonificación" style="height: 22px; vertical-align: middle; margin-right: 7px;">
    Tonificación
  </button>
  <button class="btn btn-white border objetivo-btn" onclick="mostrarRecomendacion('movilidad', this)">
    <img src="../img/flexibilidad.png" alt="Movilidad" style="height: 22px; vertical-align: middle; margin-right: 7px;">
    Movilidad y flexibilidad
  </button>
  </div>
  <div class="mb-3">
    <label for="filtro-nivel" class="form-label">Filtrar por nivel:</label>
    <select id="filtro-nivel" class="form-select w-auto d-inline-block" onchange="filtrarPorNivel()">
      <option value="">Todos</option>
      <option value="principiante">Principiante</option>
      <option value="intermedio">Intermedio</option>
      <option value="avanzado">Avanzado</option>
    </select>
  </div>
</section>

<section class="container my-5" id="rutinas-container"></section>

<section class="container my-5">
  <h2 class="mb-4"><img src="../img/calendario.png" alt="Calendario" style="height: 37px; vertical-align: middle; margin-right: 7px;"> Tu calendario de entrenamiento</h2>
  <form method="post" action="../config/guardar_calendario.php">
    <table class="table table-bordered table-hover bg-white shadow rounded" style="border-radius: 18px; overflow: hidden;">
      <thead class="table-light">
        <tr><th>Día</th><th>¿Entrenaste?</th><th>¿Qué hiciste?</th></tr>
      </thead>
      <tbody>
        <?php
        $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
        foreach ($dias as $i => $dia): ?>
        <tr>
          <td><strong><?= $dia ?></strong></td>
          <td class="text-center">
            <input type="checkbox" class="form-check-input" name="entrenado[<?= $i ?>]" value="1">
          </td>
          <td>
            <select name="notas[<?= $i ?>]" class="form-select" >
              <option value="">Selecciona</option>
              <option value="pecho">Pecho</option>
              <option value="hombros">Hombros</option>
              <option value="abdominales">Abdominales</option>
              <option value="cardio">Cardio</option>
              <option value="biceps">Bíceps</option>
              <option value="triceps">Tríceps</option>
              <option value="piernas">Piernas</option>
              <option value="espalda">Espalda</option>
              <option value="otros">Otros</option>
            </select>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="text-end">
      <button class="btn btn-success" type="submit"> Guardar semana</button>
    </div>
  </form>
</section>

<div class="container my-4 text-center">
  <button class="btn btn-info" id="btn-graficas-ejercicio" onclick="toggleGraficasEjercicio()">
    <img src="../img/graficas1.png" alt="Gráfica" style="height:24px;vertical-align:middle;margin-right:7px;">
    Mostrar gráficas
  </button>
</div>

<section class="container my-5" id="seccion-graficas" style="display:none;">
  <h2 class="mb-4 text-center">Análisis de tu Progreso</h2>
  <div class="row">
    <div class="col-md-6 mb-4">
      <canvas id="graficaDiasSemana"></canvas>
    </div>
    <div class="col-md-6 mb-4">
      <canvas id="graficaTiposEntrenamiento"></canvas>
    </div>
  </div>
</section>

<div class="modal fade" id="modalEjercicio" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEjercicioTitulo">Ejercicio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalEjercicioContenido">Cargando...</div>
    </div>
  </div>
</div>



<footer>
    <p>&copy; 2025 Vida Saludable. Todos los derechos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let objetivoSeleccionado = '';

function mostrarRecomendacion(objetivo, btn) {
  objetivoSeleccionado = objetivo;
  const nivel = document.getElementById('filtro-nivel').value;
  cargarRutinas(objetivoSeleccionado, nivel);
  document.querySelectorAll('.objetivo-btn').forEach(b => b.classList.remove('selected'));
  if(btn) btn.classList.add('selected');
}

function filtrarPorNivel() {
  const nivel = document.getElementById('filtro-nivel').value;
  if (objetivoSeleccionado) {
    cargarRutinas(objetivoSeleccionado, nivel);
  }
}

function mostrarModalEjercicio(nombre, descripcion, nivel, repeticiones, grupo_muscular, duracion, video) {
  const titulo = document.getElementById('modalEjercicioTitulo');
  const contenido = document.getElementById('modalEjercicioContenido');

  titulo.textContent = nombre;
  contenido.innerHTML = `
    <p><strong>Descripción:</strong> ${descripcion}</p>
    <p><strong>Nivel:</strong> ${nivel}</p>
    <p><strong>Grupo muscular:</strong> ${grupo_muscular}</p>
    <p><strong>Repeticiones:</strong> ${repeticiones}</p>
    <p><strong>Duración aproximada:</strong> ${duracion} minutos</p>
    <div class="ratio ratio-16x9 mt-3">
        <iframe src="${video}" title="Video del ejercicio" allowfullscreen></iframe>
    </div>
  `;

  new bootstrap.Modal(document.getElementById('modalEjercicio')).show();
}

function cargarRutinas(objetivo, nivel) {
  let url = `../config/cargar_rutinas.php?objetivo=${encodeURIComponent(objetivo)}`;
  if (nivel) url += `&nivel=${encodeURIComponent(nivel)}`;
  fetch(url).then(res => res.text()).then(html => {
    document.getElementById('rutinas-container').innerHTML = html;
  });
}



function mostrarGraficas() {
  document.getElementById('seccion-graficas').style.display = 'block';
  fetch('../config/datos_graficas.php').then(res => res.json()).then(data => {
    new Chart(document.getElementById('graficaDiasSemana'), {
      type: 'bar',
      data: { labels: data.diasEntrenados.map(d => d.semana), datasets: [{
        label: 'Días entrenados',
        data: data.diasEntrenados.map(d => d.entrenamientos),
        backgroundColor: 'rgba(54, 162, 235, 0.7)'
      }] },
      options: { scales: { y: { beginAtZero: true, max: 7 } } }
    });
    new Chart(document.getElementById('graficaTiposEntrenamiento'), {
      type: 'pie',
      data: { labels: Object.keys(data.tiposEntrenamiento), datasets: [{
        data: Object.values(data.tiposEntrenamiento),
        backgroundColor: ['#ff6384','#36a2eb','#ffce56','#4caf50','#9c27b0']
      }] }
    });
    new Chart(document.getElementById('graficaFrecuenciaDia'), {
      type: 'line',
      data: { labels: Object.keys(data.frecuenciaPorDia), datasets: [{
        label: 'Frecuencia',
        data: Object.values(data.frecuenciaPorDia),
        borderColor: 'rgba(255, 99, 132, 1)',
        tension: 0.3,
        fill: false
      }] },
      options: { scales: { y: { beginAtZero: true } } }
    });
  });
}

function toggleGraficasEjercicio() {
  const seccion = document.getElementById('seccion-graficas');
  const btn = document.getElementById('btn-graficas-ejercicio');
  const icono = '<img src="../img/graficas1.png" alt="Gráfica" style="height:24px;vertical-align:middle;margin-right:7px;">';
  if (seccion.style.display === 'block') {
    seccion.style.display = 'none';
    btn.innerHTML = icono + 'Mostrar gráficas';
    if (chartDiasSemana) { chartDiasSemana.destroy(); chartDiasSemana = null; }
    if (chartTiposEntrenamiento) { chartTiposEntrenamiento.destroy(); chartTiposEntrenamiento = null; }
  } else {
    seccion.style.display = 'block';
    btn.innerHTML = icono + 'Ocultar gráficas';
    mostrarGraficas();
  }
}
function toggleSelect(i) {
  const check = document.getElementById('entrenado' + i);
  const select = document.getElementById('tipo_ejercicio' + i);
  if (check.checked) {
    select.disabled = false;
    select.required = true;
  } else {
    select.disabled = true;
    select.required = false;
    select.value = "";
  }
}
// Inicializa el estado de los selects al cargar la página
window.onload = function() {
  for (let i = 0; i < 7; i++) {
    toggleSelect(i);
  }
}
</script>

</body>
</html>
