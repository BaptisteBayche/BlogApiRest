<?php 

require_once('../function/authentificationMethod.php');
require_once('../function/commonMethods.php');
require_once('../library/jwt_utils.php');

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

    //Authentification
    $auth = new authentificationMethod($login, $password);
    $connexion = $auth->isValidUser();

    //Si les identifiants sont corrects
    if ($connexion){
        $role = $auth->getRole();
        $idUser = $auth->getId();
       
        
        $headers = array('alg' => 'HS256', 'typ' => 'JWT');
        $payload = array('id' => $idUser, 'username' => $login, 'role' =>$role, 'exp' => time() + 3600);
        $jwt = generate_jwt($headers, $payload);

        deliver_response(201, "Authorized", $jwt );
    }else{
        deliver_response(401, "Unauthorized", NULL);
    }
    
}




?>