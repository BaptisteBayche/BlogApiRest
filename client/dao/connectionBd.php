<?php

// Singleton de connexion à la base de données mysql
class connectionBd {

    private static $instance = null;
    private $linkpdo;

    private function __construct() {
        $this->linkpdo = new PDO('mysql:host=localhost;dbname=blog;charset=utf8', 'root', '');
    }

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new connectionBd();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->linkpdo;
    }

    public function closeConnection() {
        $this->linkpdo = null;
    }

}