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

            if (isset($_GET["action"])) {
                $action = $_GET["action"];
                switch ($action) {
                    case "getRole":
                        // Récupérer son propre role
                        $matchingData['requestor_role'] = $payload_role;
                        deliver_response(200, "Affichage de la ressource [GET - getRole]", $matchingData);
                        break;
                    case "getId":
                        // Récupérer son propre id
                        $matchingData['requestor_id'] = $payload_id;
                        deliver_response(200, "Affichage de la ressource [GET - getId]", $matchingData);
                        break;

                    case "myArticles":
                        // Récupérer ses propres articles
                        if (!($payload_role === "publisher")) {
                            deliver_response(403, "Accès refusé : Votre role ne vous permet pas d'être auteur d'articles", NULL);
                            break;
                        }
                        $matchingData = getArticles($payload_role, $payload_id, true);
                        deliver_response(200, "Affichage de la ressource [GET - myArticles]", $matchingData);
                        break;
                    case "allArticles":
                        // Récupérer tous les articles
                        $matchingData = getArticles($payload_role, $payload_id);
                        deliver_response(200, "Affichage de la ressource [GET - allArticles]", $matchingData);
                        break;

                    default:
                        // L'url n'existe pas / L'action n'existe pas
                        deliver_response(400, "Mauvaise requête : l'url d'appel n'est pas bonne", NULL);
                        break;
                }
            } else {
                // L'url n'existe pas / N'est pas entièrement définie
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

                deliver_response(201, "Post ajouté avec succès [POST - Publisher]", $matchingData);
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
                            $matchingData['likeValue'] = insertLike($idArticle, $payload_id, $loveValue);
                            if ($matchingData)
                                deliver_response(200, "Action effectuée avec succès [PATCH - Publisher]", $matchingData);
                            else
                                deliver_response(401, "Erreur lors de l'ajout du like[PATCH - =/ Publisher]", null);
                            // dislike
                        } else if ($action === "dislike") {
                            $loveValue = -1;
                            $matchingData['likeValue'] = insertLike($idArticle, $payload_id, $loveValue);
                            if ($matchingData)
                                deliver_response(200, "Action effectuée avec succès [PATCH - Publisher]", $matchingData);
                            else
                                deliver_response(401, "Erreur lors de l'ajout du dislike [PATCH - =/ Publisher]", null);
                            // mauvais paramètre
                        } else {
                            deliver_response(401, "Mauvais paramètre 'action' (like,dislike).", null);
                        }
                    } else {
                        // Modifier l'article
                        $status = updateArticle($payload_id, $idArticle, $postedData['title'], $postedData['content']);
                        if ($status == 1) {
                            $matchingData['title'] = $postedData['title'];
                            $matchingData['content'] = $postedData['content'];
                            $matchingData['id_user'] = $payload_id;
                            deliver_response(200, "Article modifié avec succès [PATCH - Publisher]", $matchingData);
                        } else if ($status == -1) {
                            deliver_response(403, "Vous n'etes pas l'auteur de cette article.", null);
                        } else if ($status == -2) {
                            deliver_response(401, "Valeurs vide (body : title et content null en même temps)", null);
                        } else if ($status == 0) {
                            deliver_response(401, "L'article n'existe pas ou n'a pas subit de changements.", null);
                        } else {
                            deliver_response(401, "Erreur lors de la modification de l'article.", null);
                        }
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
                if ($matchingData == 1)
                    deliver_response(200, "Ressource supprimée [DELETE - Moderator]", null);
                else if ($matchingData == 0)
                    deliver_response(401, "Ressource inexistante [DELETE - Moderator]", null);
            } else if ($payload_role === "publisher") {
                // Supprimer les articles dont il est l’auteur.
                $matchingData = deleteArticle($_GET["id_article"], $payload_id, $payload_role);
                if ($matchingData == 1)
                    deliver_response(200, "Ressource supprimée [DELETE - Publisher]", null);
                else if ($matchingData == 0)
                    deliver_response(403, "Vous n'etes pas l'auteur de cet article ou article inexistant [DELETE - Publisher]", null);
            } else {
                // L'utilisateur n'a pas le droit d'effectuer cette action
                deliver_response(403, "Vous n'avez pas les droits nécessaires pour effectuer cette action [DELETE - Anonymous]", null);
            }
            break;

        default:
            //Methode non supportée par l'API
            deliver_response(405, "Mauvaise méthode HTTP", null);
            break;
    }
} else {
    // Token invalide ou expiré
    deliver_response(401, "Token invalide, veuillez vous reconnecter !", null);
}
