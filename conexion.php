<?php
require_once __DIR__ . '/autoload.php';

use MongoDB\Client;

try {
    $cliente = new Client("mongodb://localhost:27017");
    $bd = $cliente->vida_saludable;

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
