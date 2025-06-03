<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Vida Saludable</title>
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
<body>

<div class="overlay-content">
    <h1 class="mb-4">Bienvenido a Vida Saludable</h1>
    <a href="../config/login.php" class="btn btn-primary me-3">Iniciar Sesión</a>
    <a href="../config/register.php" class="btn btn-success">Registrarse</a>
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

    changeBackground(); // inicial
    setInterval(changeBackground, 5000); // cada 5 segundos
</script>

</body>
</html>
