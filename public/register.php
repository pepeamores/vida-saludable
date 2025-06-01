<?php

require_once __DIR__ . '/../public/db.php';
use MongoDB\Client;

$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$usuarios = $db->usuarios;

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre'     => $_POST['nombre'],
        'apellidos'  => $_POST['apellidos'],
        'fecha_nacimiento' => $_POST['fecha_nacimiento'],
        'altura' => $_POST['altura'],
        'peso'       => (float)$_POST['peso'],
        'sexo'       => $_POST['sexo'],
        'email'      => $_POST['email'],
        'usuario'    => $_POST['usuario'],
        'password'   => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'rol'        => 'usuario',
        'activo'     => true,
        'fecha_registro' => new MongoDB\BSON\UTCDateTime()
    ];

    // Verificar si el usuario o email ya existen
    $existe = $usuarios->findOne([
        '$or' => [
            ['email' => $data['email']],
            ['usuario' => $data['usuario']]
        ]
    ]);

    if ($existe) {
        $mensaje = "⚠️ El correo o usuario ya están registrados.";
    } else {
        $usuarios->insertOne($data);
        $mensaje = "✅ Registro exitoso. Ahora puedes iniciar sesión.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Vida Saludable</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: background-image 1s ease-in-out;
        }

        .overlay-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            max-width: 450px;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 15px;
            color: white;
        }

        .form-control {
            margin-bottom: 15px;
        }

        .message {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="overlay-content">
    <h2 class="text-center mb-4">Registro de Usuario</h2>
    <?php if ($mensaje): ?>
        <div class="alert <?= str_contains($mensaje, '✅') ? 'alert-success' : 'alert-warning' ?> message">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
        <input type="text" name="apellidos" class="form-control" placeholder="Apellidos" required>
        <input type="date" name="fecha_nacimiento" class="form-control" placeholder="Fecha de nacimiento" required>
        <input type="number" step="0.01" name="altura" class="form-control" placeholder="Altura (cm)" required>
        <input type="number" step="0.1" name="peso" class="form-control" placeholder="Peso (kg)" required>
        <select name="sexo" class="form-control" required>
            <option value="">Selecciona tu sexo</option>
            <option value="masculino">Masculino</option>
            <option value="femenino">Femenino</option>
        </select>
        <input type="email" name="email" class="form-control" placeholder="Correo electrónico" required>
        <input type="text" name="usuario" class="form-control" placeholder="Nombre de usuario" required>
        <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
        <button type="submit" class="btn btn-success w-100">Registrarse</button>
    </form>
    <div class="text-center mt-3">
        <a href="login.php" class="text-white">¿Ya tienes cuenta? Inicia sesión</a>
    </div>
</div>

<!-- Carrusel de fondo -->
<script>
    const images = [
        'img/fitness.jpg',
        'img/yoga.jpg',
        'img/comida.jpg'
    ];

    let index = 0;

    function changeBackground() {
        document.body.style.backgroundImage = `url(${images[index]})`;
        index = (index + 1) % images.length;
    }

    changeBackground();
    setInterval(changeBackground, 5000);
</script>

</body>
</html>
