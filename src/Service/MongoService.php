<?php

namespace App\Service;

use MongoDB\Client;

class MongoService
{
    private $db;

    public function __construct()
    {
        $uri = $_ENV['MONGODB_URI'];
        $dbName = $_ENV['MONGODB_DB'];
        $client = new Client($uri);
        $this->db = $client->selectDatabase($dbName);
    }

    public function getDb()
    {
        return $this->db;
    }

    // Puedes agregar funciones específicas para obtener colecciones
    public function getColeccion(string $nombre)
    {
        return $this->db->$nombre;
    }
}