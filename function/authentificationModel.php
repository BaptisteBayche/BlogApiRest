<?php 

    class authentificationMethod{
        private $linkpdo;

        public function __construct(){
            require_once('../DB/connectionDB.php');
            $this->linkpdo = new connectionDB();
            $this->linkpdo = $this->linkpdo->getConnection();
        }

        public function authentification($user, $password){
            $req = $this->linkpdo->prepare('SELECT * FROM user WHERE lower(user) = lower(:user) AND password = :password');
            $req->execute(array(
                'user' => $user,
                'password' => $password
            ));
            if ($req->rowCount() > 0) {
                return true;
            }
            return false;
        }

        generate
    
    }

?>