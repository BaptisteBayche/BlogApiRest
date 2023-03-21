<?php

require_once('../library/jwt_utils.php');
require_once('../function/commonMethods.php');
require_once('../function/applicationMethod.php');

// Paramétrage de l'entête HTTP (pour la réponse au Client)
header("Content-Type:application/json");

// Identification du type de méthode HTTP envoyée par le client
$http_method = $_SERVER['REQUEST_METHOD'];

// Vérification de la validité du token jwt
$bearer = get_bearer_token();
if ($bearer == null || is_jwt_valid($bearer)) {

    // On récupere le payload du token jwt (qui contient l'id de l'utilisateur et son role)
    if ($bearer != null) {
        $payload = get_jwt_payload($bearer);
        $payload_id = $payload['id'];
        $payload_role = $payload['role'];
    } else {
        $payload_id = '';
        $payload_role = '';
    }
    $matchingData = array();

    switch ($http_method) {

            ///////////////////////////////////////////////////////////////////////
            ////////////////////////////// G E T //////////////////////////////////
            ///////////////////////////////////////////////////////////////////////

        case "GET":

            if (!isset($_GET["action"])) {
                deliver_response(400, "Mauvaise requête", NULL);
            }

            // Récupérer son propre role pour tu traitement dans le front
            if ($_GET["action"] === "getRole") {
                $matchingData['requestor_role'] = $payload_role;
                deliver_response(200, "Affichage de la ressource [GET - myRole]", $matchingData);

            } else if ($_GET["action"] === "myArticles") {
                // Récupérer ses propres articles
                if (!($payload_role === "publisher")) {
                    deliver_response(403, "Accès refusé : Votre role ne vous permet pas d'être auteur d'articles", NULL);
                }
                $matchingData = getArticles($payload_role, $payload_id);
                deliver_response(200, "Affichage de la ressource [GET - myArticles]", $matchingData);

            } else if ($_GET["action"] === "allArticles") {
                // Récupérer tous les articles
                $matchingData = getArticles($payload_role);
                deliver_response(200, "Affichage de la ressource [GET - allArticles]", $matchingData);

            } else {
                deliver_response(400, "Mauvaise requête : l'url d'appel n'est pas bonne", NULL);
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
                insertArticle($postedData['title'], $postedData['content'], $payload_id);
                $matchingData['title'] = $postedData['title'];
                $matchingData['content'] = $postedData['content'];
                $matchingData['id_user'] = $payload_id;

                deliver_response(200, "Post ajouté avec succès [POST - Publisher]", $matchingData);
            } else {
                // L'utilisateur n'a pas le droit d'effectuer cette action
                deliver_response(401, "Vous n'avez pas les droits nécessaires pour effectuer cette action [POST - =/ publisher]", null);
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
                if (!empty($_GET['id_article'])) {
                    $idArticle = $_GET['id_article'];
                    if (!empty($_GET['action'])) {
                        $action = $_GET['action'];
                        // like
                        if ($action === "like") {
                            $loveValue = 1;
                            $matchingData = insertLike($idArticle, $payload_id, $loveValue);
                            if ($matchingData)
                                deliver_response(200, "Like ajouté avec succès [PATCH - Publisher]", $matchingData);
                            else
                                deliver_response(401, "Erreur lors de l'ajour du like[PATCH - =/ Publisher]", null);
                            // dislike
                        } else if ($action === "dislike") {
                            $loveValue = -1;
                            $matchingData = insertLike($idArticle, $payload_id, $loveValue);
                            if ($matchingData)
                                deliver_response(200, "Dislike ajouté avec succès [PATCH - Publisher]", $matchingData);
                            else
                                deliver_response(401, "Erreur lors de l'ajour du dislike [PATCH - =/ Publisher]", null);
                            // mauvais paramètre
                        } else {
                            deliver_response(401, "Mauvais paramètre 'action' (like,dislike).", null);
                        }
                    } else {
                        // Modifier l'article
                        $matchingData = updateArticle($payload_id, $idArticle, $postedData['title'], $postedData['content']);
                        if ($matchingData)
                            deliver_response(200, "Article modifié avec succès [PATCH - Publisher]", $matchingData);
                        else
                            deliver_response(401, "Vous n'etes pas l'auteur de cette article.", null);
                    }
                }
            } else {
                // L'utilisateur n'a pas le droit d'effectuer cette action
                deliver_response(401, "Vous n'avez pas les droits nécessaires pour effectuer cette action  [PATCH - =/ Publisher]", null);
            }
            break;

            ///////////////////////////////////////////////////////////////////////
            /////////////////////////// D E L E T E ///////////////////////////////
            ///////////////////////////////////////////////////////////////////////

        case "DELETE":
            // Vérifier les droits de l'utilisateur
            if ($payload_role === "moderator") {
                // Supprimer n’importe quel article.

                $matchingData = deleteArticle($_GET["id_article"], $payload_id, $payload_role);

                // Envoi de la réponse au Client
                if ($matchingData)
                    deliver_response(200, "Ressource supprimée [DELETE - Moderator]", $matchingData);
            } else if ($payload_role === "publisher") {
                // Supprimer les articles dont il est l’auteur.

                $matchingData = deleteArticle($_GET["id_article"], $payload_id, $payload_role);

                // Envoi de la réponse au Client
                if ($matchingData)
                    deliver_response(200, "Ressource supprimée [DELETE - Publisher]", null);
                else
                    deliver_response(401, "Vous n'etes pas l'auteur de cet article [DELETE - Publisher]", null);
            } else {
                // L'utilisateur n'a pas le droit d'effectuer cette action
                deliver_response(401, "Vous n'avez pas les droits nécessaires pour effectuer cette action [DELETE - Anonymous]", null);
            }
            break;

        default:
            deliver_response(401, "Mauvaise méthode HTTP", null);
            break;
    }
} else {
    deliver_response(401, "Token invalide, veuillez vous reconnecter !", null);
}
