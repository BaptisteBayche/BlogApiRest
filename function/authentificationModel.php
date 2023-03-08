<?php 

    class authentificationMethod{
        private $linkpdo;
        private $requete = '';

        public function __construct(){
            require_once('../DB/connectionDB.php');
            $this->linkpdo = new connectionDB();
            $this->linkpdo = $this->linkpdo->getConnection();
            $this->requete = "SELECT * FROM user WHERE lower(user) = lower(:user) AND password = :password";
        }

        //Retourne Vrai si l'utilisateur existe (a les bons identifiants)
        public function authentification($user, $password){
            $req = $this->linkpdo->prepare($this->requete);
            $req->execute(array(
                'user' => $user,
                'password' => $password
            ));
            if ($req->rowCount() > 0) {
                return true;
            }
            return false;
        }

        //Recupere le role d'un utilisateur
        public function getRole($user, $password){
            $req = $this->linkpdo->prepare($this->requete);
            $req->execute(array(
                'user' => $user,
                'password' => $password
            ));
            $role = $req->fetch();
            return $role['role'];
        }

        //Recupere l'id d'un utilisateur
        public function getId($user, $password){
            $req = $this->linkpdo->prepare($this->requete);
            $req->execute(array(
                'user' => $user,
                'password' => $password
            ));
            $id = $req->fetch();
            return $id['id'];
        }

    }

?>