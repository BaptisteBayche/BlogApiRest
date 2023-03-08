<?php

require_once('../library/jwt_utils.php');

/// Paramétrage de l'entête HTTP (pour la réponse au Client)
header("Content-Type:application/json");

/// Identification du type de méthode HTTP envoyée par le client
$http_method = $_SERVER['REQUEST_METHOD'];

// Vérification de la validité du token jwt
$bearer = get_bearer_token();
if (is_jwt_valid($bearer)) {

    switch ($http_method) {
            /// Cas de la méthode GET
        case "GET":
            /// Récupération des critères de recherche envoyés par le Client



            /// Envoi de la réponse au Client
            deliver_response(200, "Affichage de la ressource", null);
            break;

            /// Cas de la méthode POST
        case "POST":
            /// Récupération des données envoyées par le Client
            $postedData = file_get_contents('php://input');

            /// Traitement
            $postedData = json_decode($postedData, true);



            deliver_response(200, "Contact ajouté.", $matchingData);
            break;

            /// Cas de la méthode PUT
        case "PUT":
            /// Récupération des données envoyées par le Client
            $postedData = file_get_contents('php://input');
            $postedData = json_decode($postedData, true);



            /// Envoi de la réponse au Client
            deliver_response(200, "Contact mis à jour", $matchingData);
            break;

            /// Cas de la méthode DELETE
        case "DELETE":
            /// Récupération de l'identifiant de la ressource envoyé par le Client

            break;
    }
}

/// Envoi de la réponse au Client
function deliver_response($status, $status_message, $data)
{
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
