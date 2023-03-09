<?php 

    class applicationMethod{
        private $linkpdo;

        public function __construct($user, $password){
            require_once('../DB/connectionDB.php');
            $this->linkpdo = new connectionDB();
            $this->linkpdo = $this->linkpdo->getConnection();
        }

        

    }