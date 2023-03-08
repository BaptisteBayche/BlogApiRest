<?php 

require_once('../function/authentificationModel.php');
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
    $auth = new authentificationMethod();
    $connexion = $auth->authentification($login, $password);

    //Si les identifiants sont corrects
    if ($connexion){
        $role = $auth->getRole($login, $password);
        $idUser = $auth->getId($login, $password);
        $headers = array('alg' => 'HS256', 'typ' => 'JWT');
        $payload = array('id' => $idUser, 'username' => $login, 'role' =>$role, 'exp' => time() + 3600);
        $jwt = generate_jwt($headers, $payload, "pouet");

        deliver_response(201, $jwt, NULL);
    }else{
        deliver_response(401, "Unauthorized", NULL);
    }
    
}


function deliver_response($status, $status_message, $data){
    /// Paramétrage de l'entête HTTP, suite
    header("HTTP/1.1 $status $status_message");
    
    /// Paramétrage de la réponse retournée
    $response['status'] = $status;
    $response['status_message'] = $status_message;
    $response['data'] = $data;
    
    /// Mapping de la réponse au format JSON
    $json_response = json_encode($response);
    echo $json_response;
}

?>