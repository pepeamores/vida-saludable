<?php
spl_autoload_register(function ($class) {
    $prefix = 'MongoDB\\';
    $base_dir = __DIR__ . '/vendor/mongodb/mongodb/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Cargar funciones globales
require_once __DIR__ . '/vendor/mongodb/mongodb/src/functions.php';
