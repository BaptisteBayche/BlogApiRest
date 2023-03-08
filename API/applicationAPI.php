<?php

require_once('../library/jwt_utils.php');

// Paramétrage de l'entête HTTP (pour la réponse au Client)
header("Content-Type:application/json");

// Identification du type de méthode HTTP envoyée par le client
$http_method = $_SERVER['REQUEST_METHOD'];

// Vérification de la validité du token jwt
$bearer = get_bearer_token();
if (is_jwt_valid($bearer)) {

    // On récupere le payload du token jwt (qui contient l'id de l'utilisateur et son role)
    $payload = get_jwt_payload($bearer);
    $payload_id = $payload['id'];
    $payload_role = $payload['role'];

    switch ($http_method) {

            ///////////////////////////////////////////////////////////////////////
            ////////////////////////////// G E T //////////////////////////////////
            ///////////////////////////////////////////////////////////////////////

        case "GET":
            // Vérifier les droits de l'utilisateur
            if ($payload_role === "moderator") {
                // Consulter n’importe quel article. Un utilisateur moderator doit accéder à l’ensemble des
                // informations décrivant un article : auteur, date de publication, contenu, liste des
                // utilisateurs ayant liké l’article, nombre total de like, liste des utilisateurs ayant disliké
                // l’article, nombre total de dislike.

                // Envoi de la réponse au Client
                deliver_response(200, "Affichage de la ressource [Moderator]", $matchingData);
            } else if ($payload_role === "publisher") {
                // ○ Consulter ses propres messages.
                // ○ Consulter les messages publiés par les autres utilisateurs. Un utilisateur publisher doit
                //      accéder aux informations suivantes relatives à un article : auteur, date de publication,
                //      contenu, nombre total de like, nombre total de dislike.

                // Envoi de la réponse au Client
                deliver_response(200, "Affichage de la ressource [Publisher]", $matchingData);
            } else {
                // Consulter les messages existants. Seules les informations suivantes doivent être
                // disponibles : auteur, date de publication, contenu.

                // Envoi de la réponse au Client
                deliver_response(200, "Affichage de la ressource [anonymous]", $matchingData);
            }
            break;


            ///////////////////////////////////////////////////////////////////////
            ////////////////////////////// P O S T ////////////////////////////////
            ///////////////////////////////////////////////////////////////////////

        case "POST":
            // Vérifier les droits de l'utilisateur
            if ($payload_role === "publisher") {
                //Poster un nouvel article.

                // Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                $postedData = json_decode($postedData, true);

                // Traitement

                deliver_response(200, "Post ajouté avec succès.", $matchingData);
            } else {
                // L'utilisateur n'a pas le droit d'effectuer cette action
                deliver_response(401, "Vous n'avez pas les droits nécessaires pour effectuer cette action.", null);
            }
            break;

            ///////////////////////////////////////////////////////////////////////
            ////////////////////////////// P A T C H //////////////////////////////
            ///////////////////////////////////////////////////////////////////////

        case "PATCH":
            // Vérifier les droits de l'utilisateur
            if ($payload_role === "publisher") {
                // Modifier un article existant.
                // Liker/disliker les articles publiés par les autres utilisateurs.

                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                $postedData = json_decode($postedData, true);

                // Traitement

                // if (action === "like") {
                //     // Liker l'article
                // } else if (action === "dislike") {
                //     // Disliker l'article
                // } else {
                //     // Modifier l'article
                // }

                // Envoi de la réponse au Client
                deliver_response(200, "Article mis à jour", $matchingData);
            } else {
                // L'utilisateur n'a pas le droit d'effectuer cette action
                deliver_response(401, "Vous n'avez pas les droits nécessaires pour effectuer cette action.", null);
            }
            break;

            ///////////////////////////////////////////////////////////////////////
            /////////////////////////// D E L E T E ///////////////////////////////
            ///////////////////////////////////////////////////////////////////////

        case "DELETE":
            // Vérifier les droits de l'utilisateur
            if ($payload_role === "moderator") {
                // Supprimer n’importe quel article.

                // Traitement

                // Envoi de la réponse au Client
                deliver_response(200, "Ressource supprimée", null);
            } else if ($payload_role === "publisher") {
                // Supprimer les articles dont il est l’auteur.

                // Traitement

                // Envoi de la réponse au Client
                deliver_response(200, "Ressource supprimée", null);
            } else {
                // L'utilisateur n'a pas le droit d'effectuer cette action
                deliver_response(401, "Vous n'avez pas les droits nécessaires pour effectuer cette action.", null);
            }
            break;
    }
}

// Envoi de la réponse au Client
function deliver_response($status, $status_message, $data)
{
    // Paramétrage de l'entête HTTP, suite
    header("HTTP/1.1 $status $status_message");

    // Paramétrage de la réponse retournée
    $response['status'] = $status;
    $response['status_message'] = $status_message;
    $response['data'] = $data;

    // Mapping de la réponse au format JSON
    $json_response = json_encode($response);
    echo $json_response;
}
