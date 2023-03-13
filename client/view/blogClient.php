<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Communautaire</title>
</head>

<body>
    <main>
        <h1>Blog Communautaire</h1>
        <h2>Articles</h2>
        <div id="articles"></div>
        <h2>Commentaires</h2>
        <div id="comments"></div>
        <h2>Formulaire d'ajout d'article</h2>
        <form id="formArticle">
            <label for="title">Titre</label>
            <input type="text" name="title" id="title">
            <label for="content">Contenu</label>
            <textarea name="content" id="content" cols="30" rows="10"></textarea>
            <input type="submit" value="Envoyer">
        </form>
        <h2>Formulaire d'ajout de commentaire</h2>
        <form id="formComment">
            <label for="content">Contenu</label>
            <textarea name="content" id="content" cols="30" rows="10"></textarea>
            <input type="submit" value="Envoyer">
        </form>

    </main>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Récupération des articles
        $.ajax({
            url: 'http://localhost:8080/blog/api/articles',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log(data);
                for (let i = 0; i < data.length; i++) {
                    $('#articles').append('<div id="article' + data[i].id_article + '"></div>');
                    $('#article' + data[i].id_article).append('<h3>' + data[i].title + '</h3>');
                    $('#article' + data[i].id_article).append('<p>' + data[i].content + '</p>');
                    $('#article' + data[i].id_article).append('<p>Nombre de likes : ' + data[i].nb_like + '</p>');
                    $('#article' + data[i].id_article).append('<p>Nombre de dislikes : ' + data[i].nb_dislike + '</p>');
                    $('#article' + data[i].id_article).append('<p>Auteur : ' + data[i].username + '</p>');
                    $('#article' + data[i].id_article).append('<p>Date de publication : ' + data[i].publication_date + '</p>');
                    $('#article' + data[i].id_article).append('<p>Heure de publication : ' + data[i].publication_time + '</p>');
                    $('#article' + data[i].id_article).append('<button id="like' + data[i].id_article + '">Like</button>');
                    $('#article' + data[i].id_article).append('<button id="dislike' + data[i].id_article + '">Dislike</button>');
                    $('#article' + data[i].id_article).append('<button id="comment' + data[i].id_article + '">Commenter</button>');
                    $('#article' + data[i].id_article).append('<div id="comments' + data[i].id_article + '"></div>');
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    </script>
</body>

</html>