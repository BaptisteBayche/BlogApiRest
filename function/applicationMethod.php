<?php

require_once('../DB/connectionDB.php');
// Consultation des articles

function getArticles($role = null)
{
    // Connexion à la base de données
    $db = new connectionDB();
    $linkpdo = $db->getConnection();

    // Récupération des articles selon le rôle de l'utilisateur

    switch ($role) {
        case "moderator":
            $sql = "SELECT a.*, u.login as author FROM article as a, user as u WHERE a.id_user = u.id_user";
            $result = $linkpdo->query($sql);
            $articles = $result->fetchAll(PDO::FETCH_ASSOC);

            // On ajoute au tableau le nombre de like ainsi que le nombre de dislike ainsi que la liste des utilisateurs ayant liké ou disliké l'article
            foreach ($articles as $key => $article) {
                $id_article = $article['id_article'];
                // Nombre de likes et de dislikes
                $articles[$key]['nb_likes'] = getLikeNumber($id_article)['nb_likes'];
                $articles[$key]['nb_dislikes'] = getDislikeNumber($id_article)['nb_dislikes'];
                // Liste des utilisateurs ayant liké ou disliké l'article
                $userLikeList = getUserLike($id_article);
                $articles[$key]['usersLike'] = $userLikeList['usersLike'];
                $articles[$key]['usersDislike'] = $userLikeList['usersDislike'];
            }
            break;
        case "publisher":
            $sql = "SELECT a.*, u.login as author FROM article as a, user as u WHERE a.id_user = u.id_user";
            $result = $linkpdo->query($sql);
            $articles = $result->fetchAll(PDO::FETCH_ASSOC);

            // On ajoute au tableau le nombre de like ainsi que le nombre de dislike ainsi que la liste des utilisateurs ayant liké ou disliké l'article
            foreach ($articles as $key => $article) {
                $id_article = $article['id_article'];
                // Nombre de likes et de dislikes
                $articles[$key]['nb_likes'] = getLikeNumber($id_article)['nb_likes'];
                $articles[$key]['nb_dislikes'] = getDislikeNumber($id_article)['nb_dislikes'];
            }
            break;
        default:
            $sql = "SELECT id_user, title, content, publication_date, publication_time FROM article";
            $result = $linkpdo->query($sql);
            $articles = $result->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    $db->closeConnection();
    return $articles;
}

function getLikeNumber($id_article)
{
    // Connexion à la base de données
    $db = new connectionDB();
    $linkpdo = $db->getConnection();

    // Récupération du nombre de likes
    $sql = "SELECT COUNT(*) AS nb_likes FROM love WHERE id_article = :id_article and love = 1";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article
    ));
    $nb_likes = $result->fetch(PDO::FETCH_ASSOC);
    $db->closeConnection();
    return $nb_likes;
}

function getDislikeNumber($id_article)
{
    // Connexion à la base de données
    $db = new connectionDB();
    $linkpdo = $db->getConnection();

    // Récupération du nombre de dislikes
    $sql = "SELECT COUNT(*) AS nb_dislikes FROM love WHERE id_article = :id_article and love = -1";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article
    ));
    $nb_dislikes = $result->fetch(PDO::FETCH_ASSOC);
    $db->closeConnection();
    return $nb_dislikes;
}

function getUserLike($id_article)
{
    // Connexion à la base de données
    $db = new connectionDB();
    $linkpdo = $db->getConnection();

    // Récupération des utilisateurs ayant liké l'article
    $sql = "SELECT login FROM love as l, user as u WHERE l.id_article = :id_article and l.id_user = u.id_user and love = 1";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article
    ));
    $usersLike = $result->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des utilisateurs ayant disliké l'article
    $sql = "SELECT login FROM love as l, user as u WHERE l.id_article = :id_article and l.id_user = u.id_user and love = -1";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article
    ));
    $db->closeConnection();
    $usersDislike = $result->fetchAll(PDO::FETCH_ASSOC);

    // Fusion des deux tableaux
    $userLikeList['usersLike'] = $usersLike;
    $userLikeList['usersDislike'] = $usersDislike;
    return $userLikeList;
}

function insertArticle($title, $content, $id_user)
{
    // Connexion à la base de données
    $db = new connectionDB();
    $linkpdo = $db->getConnection();

    // Insertion de l'article
    $sql = "INSERT INTO article (title, content, publication_date, publication_time, id_user) VALUES (:title, :content, now(), now(), :id_user)";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'title' => $title,
        'content' => $content,
        'id_user' => $id_user
    ));
    $db->closeConnection();
}

function insertLike($id_article, $id_user, $love)
{

    // Insertion du like ou du dislike
    if (userAlreadyLikedOrDisliked($id_user, $id_article)) {
        updateLike($id_user, $id_article, $love);
    } else {
        // Connexion à la base de données
        $db = new connectionDB();
        $linkpdo = $db->getConnection();
        $sql = "INSERT INTO love (id_article,id_user, love) VALUES (:id_article,:id_user, :love)";
        $result = $linkpdo->prepare($sql);
        $result->execute(array(
            'id_user' => $id_user,
            'id_article' => $id_article,
            'love' => $love
        ));
        $db->closeConnection();
    }
}

function updateLike($id_user, $id_article, $like) {
    // Connexion à la base de données
    $db = new connectionDB();
    $linkpdo = $db->getConnection();

    // Mise à jour du like
    $sql = "UPDATE love SET love = :like WHERE id_article = :id_article and id_user = :id_user";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article,
        'id_user' => $id_user,
        'like' => $like
    ));
    $db->closeConnection();
}

function userAlreadyLikedOrDisliked($id_user, $id_article)
{
    // Connexion à la base de données
    $db = new connectionDB();
    $linkpdo = $db->getConnection();

    // Vérification si l'utilisateur a déjà liké l'article
    $sql = "SELECT COUNT(*) FROM love WHERE id_article = :id_article and id_user = :id_user and love is not null";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article,
        'id_user' => $id_user
    ));
    $nb_likes = $result->fetch(PDO::FETCH_ASSOC);
    if ($nb_likes['COUNT(*)'] == 0) {
        return false;
    } else {
        return true;
    }
    $db->closeConnection();
}

?>
