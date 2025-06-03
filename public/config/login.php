<?php
session_start();

if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: ../admin/admin.php');
    } else {
        header('Location: ../inicio.php');
    }
    exit();
}

$loginError = $_GET['error'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Vida Saludable</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
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
            text-align: center;
            color: white;
            background: rgba(0, 0, 0, 0.5);
            padding: 30px;
            border-radius: 15px;
        }

        .btn {
            min-width: 150px;
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
    <h2 class="text-center mb-4">Iniciar Sesión</h2>

    <?php if ($loginError): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <div class="mb-3">
            <label for="usuario" class="form-label">Usuario</label>
            <input type="text" name="usuario" id="usuario" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>

    <div class="text-center mt-3">
        <a href="register.php">¿No tienes cuenta? Regístrate</a>
    </div>
</div>

<script>
    const images = [
        '../img/fitness.jpg',
        '../img/yoga.jpg',
        '../img/comida.jpg'
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

<?php
require_once __DIR__ . '/db.php'; // corregido desde config

$database = new Database();
$bd = $database->getDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['usuario']);
    $password = $_POST['password'];

    $coleccion = $bd->usuarios;
    $user = $coleccion->findOne(['usuario' => $username]);

    if (!$user) {
        header('Location: login.php?error=Usuario no encontrado');
        exit();
    }

    if ($user && password_verify($password, $user->password)) {
        $_SESSION['user_id']    = (string)$user->_id;
        $_SESSION['nombre']     = $user->nombre ?? '';
        $_SESSION['apellidos']  = $user->apellidos ?? '';
        $_SESSION['fecha_nacimiento'] = $user->fecha_nacimiento ?? null;
        $_SESSION['altura']     = $user->altura ?? '';
        $_SESSION['peso']       = $user->peso ?? '';
        $_SESSION['sexo']       = $user->sexo ?? '';
        $_SESSION['email']      = $user->email ?? '';
        $_SESSION['usuario']    = $user->usuario ?? '';
        $_SESSION['rol']        = $user->rol ?? 'usuario';
        $_SESSION['activo']     = $user->activo ?? true;

        if ($_SESSION['rol'] === 'admin') {
            header('Location: ../admin/admin.php');
        } else {
            header('Location: ../inicio.php');
        }
        exit();
    } else {
        header('Location: login.php?error=Credenciales inválidas');
        exit();
    }
}
?>
