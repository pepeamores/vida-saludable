<?php
require_once __DIR__ . '/../../autoload.php'; // Carga Composer

class Database {
    private $client;
    private $db;

    public function __construct() {
        $uri = "mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/";
        $this->client = new MongoDB\Client($uri);
        $this->db = $this->client->selectDatabase("vida_saludable");
    }

    public function getDb() {
        return $this->db;
    }
}
?>
