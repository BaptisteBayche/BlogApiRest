<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="stylesheet" href="../css/blog.css">
    <title>Blog Communautaire</title>
</head>

<body>
    <header>
        <h1>Blog Communautaire</h1>
        <nav>
            <ul>
                <li><a class="connexion-btn" href="authentificationClient.php">Connexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Formulaire pour ajouter un nouvel article -->
        <form>
            <h2>Nouvel article</h2>
            <label for="title">Titre :</label>
            <input type="text" id="title" name="title"><br>
            <label for="content">Contenu :</label>
            <textarea id="content" name="content"></textarea><br>
            <input type="submit" value="Publier">
        </form>
    </main>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        // Récupération des différents articles grace à l'api
        let token = localStorage.getItem('token');
        $.ajax({
            url: "http://localhost/blog/api/articles",
            type: "GET",
            dataType: "json",
            headers: {
                "Authorization": "Bearer " + token
            },
            success: function(data) {
                console.log(data);
                // mise en forme de l'article
                for (let i = 0; i < data.data.length; i++) {
                    let article = data.data[i];
                    let date = new Date(article.publication_date);
                    let dateFormated = date.getDate() + " " + date.toLocaleString('default', { month: 'long' }) + " " + date.getFullYear();
                    let articleHtml = `
                    <div class="article">
                        <h2>${article.title}</h2>
                        <p>${article.content}</p>
                        <div class="meta">
                            <span class="date">${dateFormated}</span>
                            <span class="author">${article.author}</span>
                            <span class="likes">${article.likes} like</span>
                            <span class="dislikes">${article.dislikes} dislike</span>
                        </div>
                    </div>
                    `;
                    // insertion de l'article dans le main
                    $('main').append(articleHtml);
                }
            },
            error: function(xhr, status, error) {
                console.log(xhr);
            }
        });
    </script>
</body>

</html>