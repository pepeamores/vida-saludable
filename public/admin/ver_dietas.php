<?php

// ver_dietas.php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header('Location: admin.php'); exit; }
require '../../autoload.php';
use MongoDB\Client;
use MongoDB\BSON\ObjectId;
$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$dietas = $db->dietas->find([], ['sort' => ['fecha_creacion' => -1]]);
function objetivo_texto($valor) {
    return [
        'perder_peso' => 'Perder peso',
        'mantener_peso' => 'Mantener peso',
        'ganar_musculo' => 'Ganar masa muscular'
    ][$valor] ?? ucfirst($valor);
}
function ejercicio_texto($valor) {
    return [
        'sedentario' => 'Sedentario',
        'ligero' => 'Ligero',
        'moderado' => 'Moderado',
        'intenso' => 'Intenso'
    ][$valor] ?? ucfirst($valor);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Dietas</title>
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <h2>📋 Dietas</h2>
    <a href="crear_dieta.php" class="btn btn-success mb-3">➕ Nueva Dieta</a>
    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Alimentos</th>
                <th>Calorías</th>
                <th>Objetivo</th>
                <th>Cantidad de ejercicio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($dietas as $dieta): ?>
            <tr>
                <td><?= htmlspecialchars($dieta['nombre']) ?></td>
                <td><?= htmlspecialchars($dieta['alimentos']) ?></td>
                <td><?= htmlspecialchars($dieta['calorias']) ?></td>
                <td><?= isset($dieta['objetivo']) ? objetivo_texto($dieta['objetivo']) : '-' ?></td>
                <td><?= isset($dieta['ejercicio']) ? ejercicio_texto($dieta['ejercicio']) : '-' ?></td>
                <td>
                    <a href="editar_dieta.php?id=<?= $dieta['_id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="eliminar_dieta.php?id=<?= $dieta['_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar dieta?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="admin.php" class="btn btn-secondary">Volver</a>
</div>
</body>
</html>