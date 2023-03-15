<?php 

    class authentificationMethod{
        private $linkpdo;
        private $username;
        private $resultat;
        private $password;

        public function __construct($username, $password){
            require_once('../DB/connectionDB.php');
            $this->linkpdo = connectionDB::getInstance()->getConnection();
            $this->username = $username;
            $this->password = $password;
        }

        //Recupere les informations d'un utilisateur si il existe
        public function login(){
            $req = $this->linkpdo->prepare("SELECT * FROM user WHERE lower(login) = lower(:login) AND password = :password");
            $req->execute(array(
                'login' => $this->username,
                'password' => $this->password
            ));
            if ($req->rowCount() >= 1) {
                $this->resultat = $req->fetchAll();
                return true;
            } else {
                return false;
            }
        }

        //Inscrit un utilisateur en temps que publisher
        public function signup($username, $password){
            if (count($this->isUsernameInDatabase()) >= 1) {
                return false;
            }
            $req = $this->linkpdo->prepare("INSERT INTO user (login, password, role) VALUES (:login, :password, 'publisher')");
            $req->execute(array(
                'login' => $username,
                'password' => $password
            ));
            return true;
        }

        public function isUsernameInDatabase(){
            $req = $this->linkpdo->prepare("SELECT * FROM user WHERE lower(login) = lower(:login)");
            $req->execute(array(
                'login' => $this->username
            ));
            return $req->fetchAll();
        }

        //Recupere le role d'un utilisateur
        public function getRole(){
            $role = $this->resultat;
            return $role[0]['role'];
        }

        //Recupere l'id d'un utilisateur
        public function getId(){
            $id = $this->resultat;
            return $id[0]['id_user'];
        }

    }

?>