<?php

//Connexion à la base de données en singleton
class connectionDB
{
    private static $instance = null;
    private $linkpdo;

    public function __construct()
    {
        ///Connexion au serveur MySQL avec PDO
        //IP du serveur MySQL si vous souhaitez vous connecter
        //89.116.147.154
        $server = '89.116.147.154';
        $login  = 'u743447366_blogRest';
        $mdp    = 'W9zPe@0o';
        $db     = 'u743447366_apiRestBlog';

        try {
            $this->linkpdo = new PDO("mysql:host=$server;dbname=$db", $login, $mdp);
            $this->linkpdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        ///Capture des erreurs éventuelles
        catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new connectionDB();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->linkpdo;
    }

    public function closeConnection()
    {
        $this->linkpdo = null;
    }
}
