<?php

require_once('../DB/connectionDB.php');

// Consultation des articles
function getArticles($role = null, $idUser = null, $onlyFromUser = false)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
    $linkpdo = $db->getConnection();

    // Récupération des articles selon le rôle de l'utilisateur

    switch ($role) {
        case "moderator":
            $sql = "SELECT a.*, u.login as author FROM article as a, user as u WHERE a.id_user = u.id_user order by publication_date desc, publication_time desc";
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
            $articles = [];
            if ($onlyFromUser == true) {
                // Récupération des articles de l'utilisateur
                $sql = "SELECT a.*, u.login as author FROM article as a, user as u WHERE a.id_user = u.id_user and a.id_user = :id_user order by publication_date desc, publication_time desc";
                $result = $linkpdo->prepare($sql);
                $result->bindParam(':id_user', $idUser);
                $result->execute();
                $articles = $result->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Récupération de tous les articles
                $sql = "SELECT a.*, u.login as author FROM article as a, user as u WHERE a.id_user = u.id_user order by publication_date desc, publication_time desc";
                $result = $linkpdo->query($sql);
                $articles = $result->fetchAll(PDO::FETCH_ASSOC);
            }

            // On ajoute au tableau le nombre de like ainsi que le nombre de dislike ainsi que la liste des utilisateurs ayant liké ou disliké l'article
            foreach ($articles as $key => $article) {
                $id_article = $article['id_article'];
                // Nombre de likes et de dislikes
                $articles[$key]['nb_likes'] = getLikeNumber($id_article)['nb_likes'];
                $articles[$key]['nb_dislikes'] = getDislikeNumber($id_article)['nb_dislikes'];
                // On récupère si l'utilisateur a liké ou disliké l'article
                $articles[$key]['user_like_value'] = userAlreadyLikedOrDisliked($idUser, $id_article);
            }
            break;
        default:
            $sql = "SELECT id_user, title, content, publication_date, publication_time FROM article order by publication_date desc, publication_time desc";
            $result = $linkpdo->query($sql);
            $articles = $result->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    return $articles;
}

function userAlreadyLikedOrDisliked($idUser, $idArticle)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
    $linkpdo = $db->getConnection();

    // Vérification si l'utilisateur a déjà liké l'article
    $sql = "SELECT love.love FROM love WHERE id_article = :id_article and id_user = :id_user and love is not null";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $idArticle,
        'id_user' => $idUser
    ));
    if ($result->rowCount() == 0) {
        return null;
    } else {
        $result = $result->fetch(PDO::FETCH_ASSOC);
        return $result['love'];
    }
}

function getLikeNumber($id_article)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
    $linkpdo = $db->getConnection();

    // Récupération du nombre de likes
    $sql = "SELECT COUNT(*) AS nb_likes FROM love WHERE id_article = :id_article and love = 1";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article
    ));
    $nb_likes = $result->fetch(PDO::FETCH_ASSOC);

    return $nb_likes;
}

function getDislikeNumber($id_article)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
    $linkpdo = $db->getConnection();

    // Récupération du nombre de dislikes
    $sql = "SELECT COUNT(*) AS nb_dislikes FROM love WHERE id_article = :id_article and love = -1";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article
    ));
    $nb_dislikes = $result->fetch(PDO::FETCH_ASSOC);

    return $nb_dislikes;
}

function getUserLike($id_article)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
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

    $usersDislike = $result->fetchAll(PDO::FETCH_ASSOC);

    // Fusion des deux tableaux
    $userLikeList['usersLike'] = $usersLike;
    $userLikeList['usersDislike'] = $usersDislike;
    return $userLikeList;
}

function insertArticle($title, $content, $id_user)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
    $linkpdo = $db->getConnection();

    // Insertion de l'article
    $sql = "INSERT INTO article (title, content, publication_date, publication_time, id_user) VALUES (:title, :content, now(), now(), :id_user)";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'title' => $title,
        'content' => $content,
        'id_user' => $id_user
    ));
}

function insertLike($id_article, $id_user, $love)
{

    // Insertion du like ou du dislike
    if (userAlreadyLikedOrDisliked($id_user, $id_article) != null) {
        return updateLike($id_user, $id_article, $love);
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
        return $love;
    }
}

function updateLike($id_user, $id_article, $love)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
    $linkpdo = $db->getConnection();

    // Mise à jour du like
    // On récupère le like actuel
    $sql = "SELECT love FROM love WHERE id_article = :id_article and id_user = :id_user";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article,
        'id_user' => $id_user
    ));
    $currentLove = $result->fetch(PDO::FETCH_ASSOC)['love'];

    // Si le like actuel est le même que le nouveau like, on supprime le like/dislike
    if ($currentLove == $love) {
        $sql = "DELETE FROM love WHERE id_article = :id_article and id_user = :id_user";
        $result = $linkpdo->prepare($sql);
        $result->execute(array(
            'id_article' => $id_article,
            'id_user' => $id_user
        ));
        return 0;
    } else {
        $sql = "UPDATE love SET love = :like WHERE id_article = :id_article and id_user = :id_user";
        $result = $linkpdo->prepare($sql);
        $result->execute(array(
            'id_article' => $id_article,
            'id_user' => $id_user,
            'like' => $love
        ));
    }
    return $love;
}

function deleteArticle($id_article, $id_user, $role = null)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
    $linkpdo = $db->getConnection();

    // Suppression de l'article
    switch ($role) {
        case "moderator":
            clearArticleInLoveTable($id_article);
            $sql = "DELETE FROM article WHERE id_article = :id_article";
            $result = $linkpdo->prepare($sql);
            $result->execute(array(
                'id_article' => $id_article
            ));
            return $result->rowCount();
        case "publisher":
            clearArticleInLoveTable($id_article);
            $sql = "DELETE FROM article WHERE id_article = :id_article and id_user = :id_user";
            $result = $linkpdo->prepare($sql);
            $result->execute(array(
                'id_article' => $id_article,
                'id_user' => $id_user
            ));
            return $result->rowCount();
        case 'default':
            return false;
    }
}

function clearArticleInLoveTable($id_article)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
    $linkpdo = $db->getConnection();

    // Suppression des likes et dislikes
    $sql = "DELETE FROM love WHERE id_article = :id_article";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article
    ));
}

function updateArticle($id_user, $id_article, $title = null, $content = null)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
    $linkpdo = $db->getConnection();

    // Mise à jour de l'article
    if (!verifyOwner($id_user, $id_article)) {
        return -1;
    }

    if ($title == null && $content == null) {
        return -2;
    } else if ($title == null) {
        $sql = "UPDATE article SET content = :content WHERE id_article = :id_article and id_user = :id_user";
        $result = $linkpdo->prepare($sql);
        $result->execute(array(
            'id_article' => $id_article,
            'content' => $content,
            'id_user' => $id_user
        ));
        return $result->rowCount();
    } else if ($content == null) {
        $sql = "UPDATE article SET title = :title WHERE id_article = :id_article and id_user = :id_user";
        $result = $linkpdo->prepare($sql);
        $result->execute(array(
            'id_article' => $id_article,
            'title' => $title,
            'id_user' => $id_user
        ));
        return $result->rowCount();
    } else {
        $sql = "UPDATE article SET title = :title, content = :content WHERE id_article = :id_article and id_user = :id_user";
        $result = $linkpdo->prepare($sql);
        $result->execute(array(
            'id_article' => $id_article,
            'title' => $title,
            'content' => $content,
            'id_user' => $id_user
        ));
        return $result->rowCount();
    }
}

function verifyOwner($id_user, $id_article)
{
    // Connexion à la base de données
    $db = connectionDB::getInstance();
    $linkpdo = $db->getConnection();

    // Vérification si l'utilisateur est bien le propriétaire de l'article
    $sql = "SELECT COUNT(*) FROM article WHERE id_article = :id_article and id_user = :id_user";
    $result = $linkpdo->prepare($sql);
    $result->execute(array(
        'id_article' => $id_article,
        'id_user' => $id_user
    ));
    $nb_articles = $result->fetch(PDO::FETCH_ASSOC);

    if ($nb_articles['COUNT(*)'] == 0) {
        return false;
    } else {
        return true;
    }
}
