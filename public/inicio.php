<?php
session_start();

function calcularEdadDesdeTexto($fechaTexto) {
    try {
        $fecha = new DateTime($fechaTexto);
        $hoy = new DateTime();
        return $hoy->diff($fecha)->y;
    } catch (Exception $e) {
        return 'N/A';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vida Saludable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>
<?php if (isset($_SESSION['nombre'])): ?>
    <div class="dropdown position-fixed top-0 end-0 m-3">
        <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
            <img src="../img/pefil.png" alt="Perfil" width="32" height="32" class="rounded-circle me-2">
            <?= htmlspecialchars($_SESSION['nombre']) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item text-danger" href="/logout.php">Cerrar sesión</a></li>
        </ul>
    </div>
<?php endif; ?>

<header class="header">
    <div class="header-content">
        <h1>Vida Saludable</h1>
        <p>Descubre cómo mejorar tu salud física, mental y emocional a través de hábitos saludables.</p>
        <?php if (isset($_SESSION['nombre'])): ?>
            <p class="mt-2">Bienvenido, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></p>
        <?php endif; ?>
    </div>

    <nav class="main-nav">
        <ul>
            <li><a href="../pages/alimentacion.php">Alimentación Saludable</a></li>
            <li><a href="../pages/ejercicio.php">Ejercicio Físico</a></li>
            <li><a href="../pages/salud_mental.php">Salud Mental</a></li>
        </ul>
    </nav>
</header>

<section class="interactive-sections">
    <section class=" as highlight d-flex flex-wrap justify-content-center align-items-center">
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-info text-start bg-white p-4 rounded shadow me-4" style="max-width: 300px;">
            <h4 class="text-success mb-3"><img src="../img/perfil2.png" alt="Perfil2" style="height:40px;width:40px;vertical-align:middle;margin-right:7px;">Tu perfil</h4>
            <form method="POST" action="../config/actualizar_datos.php" id="form-perfil">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellidos']) ?></p>
            <p><strong>Edad:</strong> <?= isset($_SESSION['fecha_nacimiento']) ? calcularEdadDesdeTexto($_SESSION['fecha_nacimiento']) . ' años' : 'No especificada' ?></p>

            <div class="mb-2">
                <label><strong>Altura:</strong></label>
                <p id="texto-altura"><?= htmlspecialchars($_SESSION['altura']) ?> m</p>
                <input type="number" step="0.01" name="altura" class="form-control d-none" value="<?= htmlspecialchars($_SESSION['altura']) ?>" required>
            </div>

            <div class="mb-2">
                <label><strong>Peso:</strong></label>
                <p id="texto-peso"><?= htmlspecialchars($_SESSION['peso']) ?> kg</p>
                <input type="number" step="0.1" name="peso" class="form-control d-none" value="<?= htmlspecialchars($_SESSION['peso']) ?>" required>
            </div>

            <p><strong>Sexo:</strong> <?= htmlspecialchars($_SESSION['sexo']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email']) ?></p>
            <p><strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['usuario']) ?></p>

            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-editar">Editar</button>
            <button type="submit" class="btn btn-primary btn-sm d-none" id="btn-guardar">Guardar</button>
            </form>
        </div>
        <?php endif; ?>

        <img src="../img/health.png" alt="Vida saludable" class="img-fluid" style="max-width: 400px; border-radius: 10px;">

        <div class="highlight-text ms-4 mt-4 mt-md-0">
            <h2>Alimentate bien desde HOY</h2>
            <p>La clave de una vida plena y larga es cuidar tanto del cuerpo como de la mente. Aquí aprenderás cómo hacerlo.</p>
            <a href="../pages/alimentacion.php" class="cta-button btn btn-success">Descubre Más</a>
        </div>
    </section>

    <div class="row mt-5 justify-content-center">
    <div class="col-md-6 mb-4 mb-md-0">
        <section class="as highlight d-flex flex-wrap justify-content-center align-items-center h-100 p-4" style="max-width: 650px;">
            <img src="../img/fitness.jpg" alt="Ejercicio Físico" class="img-fluid" style="max-width: 440px; border-radius: 12px;">
            <div class="highlight-text ms-3 mt-4 mt-md-0" style="max-width: 230px; padding: 0.7rem;">
                <h2 style="font-size:1.5rem;">¡Actívate con Ejercicio Físico!</h2>
                <p style="font-size:1.05rem;">Conoce las mejores rutinas para mantenerte en forma, mejorar tu salud cardiovascular y fortalecer tus músculos.</p>
                <a href="../pages/ejercicio.php" class="cta-button btn btn-success">Ver Rutinas</a>
            </div>
        </section>
    </div>
    <div class="col-md-6">
        <section class="as highlight d-flex flex-wrap justify-content-center align-items-center h-100 p-4" style="max-width: 650px;">
            <img src="../img/yoga.jpg" alt="Salud Mental" class="img-fluid" style="max-width: 900px; border-radius: 12px;">
            <div class="highlight-text ms-3 mt-4 mt-md-0" style="max-width: 230px; padding: 0.7rem;">
                <h2 style="font-size:1.5rem;">Salud Mental</h2>
                <p style="font-size:1.05rem;">Aprende técnicas para cuidar de tu bienestar mental y emocional.</p>
                <a href="../pages/salud_mental.php" class="cta-button btn btn-success">Descubre Más</a>
            </div>
        </section>
    </div>
</div>
</section>

<?php if (isset($_SESSION['user_id'])): ?>
<section class="container my-5">
  <h3 class="text-center mb-4"><img src="../img/valoracion_pagina.png" alt="Valoracion" style="height:45px;vertical-align:middle;margin-right:7px;">Danos tu opinión</h3>
  <form method="post" action="../config/guardar_opinion.php" class="bg-white p-4 rounded shadow-sm">
    <div class="mb-3">
      <label class="form-label">Valoración general (1 a 10)</label>
      <input type="number" name="puntuacion" min="1" max="10" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Comentario</label>
      <textarea name="comentario" rows="3" class="form-control" placeholder="¿En qué podemos mejorar?" required></textarea>
    </div>
    <button class="btn btn-primary">Enviar opinión</button>
  </form>
</section>
<?php endif; ?>



<footer>
    <section class="testimonios">
    <h2>Testimonios</h2>
    <div id="testimony-slider">
        <p class="testimony-text">"Cambiar mis hábitos alimenticios me ha hecho sentir con más energía y vitalidad."</p>
        <p class="testimony-author">- Ana Pérez</p>
    </div>
    </section>
    <p>&copy; 2025 Vida Saludable. Todos los derechos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSection(id) {
        const el = document.getElementById(id);
        el.style.display = el.style.display === 'block' ? 'none' : 'block';
    }

    document.getElementById('theme-toggle')?.addEventListener('click', () => {
        document.body.classList.toggle('dark-theme');
    });

    document.getElementById('btn-editar')?.addEventListener('click', function() {
        document.querySelector('input[name="altura"]').classList.remove('d-none');
        document.querySelector('input[name="peso"]').classList.remove('d-none');
        document.getElementById('texto-altura').classList.add('d-none');
        document.getElementById('texto-peso').classList.add('d-none');

        this.classList.add('d-none');
        document.getElementById('btn-guardar').classList.remove('d-none');
    });
</script>
</body>
</html>
