<?php 

    class authentificationMethod{
        private $linkpdo;
        private $resultat;


        public function __construct($user, $password){
            require_once('../DB/connectionDB.php');
            $this->linkpdo = new connectionDB();
            $this->linkpdo = $this->linkpdo->getConnection();
            $this->resultat = $this->authentification($user, $password);
        }

        //Recupere les informations d'un utilisateur si il existe
        public function authentification($user, $password){
            $req = $this->linkpdo->prepare("SELECT * FROM user WHERE lower(login) = lower(:login) AND password = :password");
            $req->execute(array(
                'login' => $user,
                'password' => $password
            ));
            return $req->fetchAll();
        }
        
        
        //Retourne Vrai si l'utilisateur existe (a les bons identifiants)
        public function isValidUser(){
            $req = $this->resultat;
            if (count($req) >= 1) {
                return true;
            }
            return false;
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