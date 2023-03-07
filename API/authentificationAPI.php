<?php 

require_once('../function/authentificationModel.php');

/// Paramétrage de l'entête HTTP (pour la réponse au Client)
header("Content-Type:application/json");

    
/// Identification du type de méthode HTTP envoyée par le client
$http_method = $_SERVER['REQUEST_METHOD'];
//Seulement method post 
if ($http_method == "POST"){
    /// Récupération des données envoyées par le client
    $data = json_decode(file_get_contents('php://input'), true);
    $login = $data['login'];
    $password = $data['password'];
    $auth = new authentificationModel();
    $auth->authentification($login, $password);
}

?>