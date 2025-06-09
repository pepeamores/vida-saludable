<?php
require_once __DIR__ . '/../../autoload.php';
use MongoDB\Client;
session_start();

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['error' => 'No autenticado']);
  exit;
}

$client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
$db = $client->vida_saludable;
$calendario = $db->calendario->find(['usuario_id' => $_SESSION['user_id']]);

$diasEntrenados = [];
$frecuenciaPorDia = array_fill_keys(['lunes','martes','miércoles','jueves','viernes','sábado','domingo'], 0);
$tiposEntrenamiento = [];

foreach ($calendario as $semana) {
  $entrenadosSemana = 0;
  foreach ($semana['dias'] as $dia => $datos) {
    if (!empty($datos['entrenado']) && $datos['entrenado']) {
      $entrenadosSemana++;
      $frecuenciaPorDia[$dia]++;

      $nota = strtolower(trim($datos['notas'] ?? 'otro'));
      $tipos = preg_split('/[,;]+/', $nota);
      foreach ($tipos as $tipo) {
        $tipo = trim($tipo);
        if ($tipo !== '') {
          $tiposEntrenamiento[$tipo] = ($tiposEntrenamiento[$tipo] ?? 0) + 1;
        }
      }
    }
  }
  $diasEntrenados[] = [
    'semana' => $semana['semana_inicio'],
    'entrenamientos' => $entrenadosSemana
  ];
}

header('Content-Type: application/json');
echo json_encode([
  'diasEntrenados' => $diasEntrenados,
  'frecuenciaPorDia' => $frecuenciaPorDia,
  'tiposEntrenamiento' => $tiposEntrenamiento
]);