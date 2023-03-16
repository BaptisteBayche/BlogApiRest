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

    <main id="main">
        <div id="articles">

        </div>
    </main>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script>
        // Récupération du role de l'utilisateur
        function getUserRole() {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: "http://localhost/blog/api/role",
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + localStorage.getItem('token')
                    },
                    success: function(response) {
                        userRole = response.data.requestor_role; // stockage du rôle dans une variable globale
                        resolve(userRole);
                    },
                    error: function(xhr, status, error) {
                        reject(new Error('problème lors de la récupération du role'));
                    }
                });
            });
        }

        // Fonctions ayant besoin du role de l'utilisateur
        getUserRole().then(function(userRole) {
            console.log("Role : " + userRole);
            // On affiche le formulaire pour ajouter un nouvel article si l'utilisateur est publisher
            if (userRole == 'publisher') {
                let formHtml = `        
                            <form class="add-form">
                                <h2>Nouvel article</h2>
                                <label for="title">Titre :</label>
                                <input type="text" id="title" name="title" class="title-add" required><br>
                                <label for="content">Contenu :</label>
                                <textarea id="content" name="content" class="content-add" required></textarea><br>
                                <input type="submit" value="Publier">
                                <span class="info-add-article"></span>
                            </form>`;
                $('#main').append(formHtml);
            }

            // Ajout d'un article en temps que publisher
            const addForm = document.querySelector('.add-form');
            if(addForm != null) {
                const articleTitle = document.querySelector('.add-form .title-add');
                const articleContent = document.querySelector('.add-form .content-add');
                const infoAddArticle = document.querySelector('.add-form .info-add-article');
                addForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    $.ajax({
                        url: 'http://localhost/blog/API/add/article',
                        method: 'POST',
                        data: JSON.stringify({
                            title: articleTitle.value,
                            content: articleContent.value
                        }),
                        headers: {
                            "Authorization": "Bearer " + localStorage.getItem('token')
                        },
                        dataType: 'json',
                        success: function(response) {
                            getArticles();
                        },
                        error: function(response) {
                            infoAddArticle.textContent = "Erreur lors de l'ajout de l'article";
                        }
                    });
                });
            }


            getArticles();
        }).catch(function(error) {
            console.log(error);
        });

        function getArticles() {
            getUserRole().then(function(userRole) {
                // Récupération des différents articles grace à l'api
                $.ajax({
                    url: "http://localhost/blog/api/articles",
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + localStorage.getItem('token')
                    },
                    success: function(response) {

                        $('#articles').empty();
                        // mise en forme de l'article

                        switch (userRole) {
                            case 'moderator':
                                for (let i = 0; i < response.data.length; i++) {
                                    let article = response.data[i];

                                    // formatage de la date
                                    let date = new Date(article.publication_date);
                                    let dateFormated = date.getDate() + " " + date.toLocaleString('default', {
                                        month: 'long'
                                    }) + " " + date.getFullYear();

                                    // reformattage de l'heure
                                    let time = article.publication_time.split(':');
                                    article.publication_time = time[0] + "h" + time[1];

                                    // création de l'article
                                    const usersLiked = article.usersLike.map(user => user.login);
                                    const usersDisliked = article.usersDislike.map(user => user.login);
                                    let articleHtml = `
                                            <div class="article">
                                                <h2>${article.title}</h2>
                                                <p>${article.content}</p>
                                                <div class="meta">
                                                    <span class="date">Le ${dateFormated} à ${article.publication_time}</span>
                                                    <span class="author">Par ${article.author}</span>
                                                    <span class="likes" title="${usersLiked.join(', ')}">${article.nb_likes} like</span>
                                                    <span class="dislikes" title="${usersDisliked.join('<br> ')}">${article.nb_dislikes} dislike</span>
                                                    </div>
                                            </div>
                                            `;
                                    // insertion de l'article
                                    $('#articles').append(articleHtml);
                                }
                                break;
                            case 'publisher':
                                for (let i = 0; i < response.data.length; i++) {
                                    let article = response.data[i];

                                    // formatage de la date
                                    let date = new Date(article.publication_date);
                                    let dateFormated = date.getDate() + " " + date.toLocaleString('default', {
                                        month: 'long'
                                    }) + " " + date.getFullYear();

                                    // reformattage de l'heure
                                    let time = article.publication_time.split(':');
                                    article.publication_time = time[0] + "h" + time[1];

                                    // création de l'article
                                    let articleHtml = `
                                            <div class="article">
                                                <h2>${article.title}</h2>
                                                <p>${article.content}</p>
                                                <div class="meta">
                                                    <span class="date">Le ${dateFormated} à ${article.publication_time}</span>
                                                    <span class="author">Par ${article.author}</span>
                                                    <span class="likes">${article.nb_likes} like</span>
                                                    <span class="dislikes">${article.nb_dislikes} dislike</span>
                                                </div>
                                            </div>
                                            `;
                                    // insertion de l'article dans le main
                                    $('#articles').append(articleHtml);
                                }
                                break;
                            default:

                                for (let i = 0; i < response.data.length; i++) {
                                    let article = response.data[i];

                                    // formatage de la date
                                    let date = new Date(article.publication_date);
                                    let dateFormated = date.getDate() + " " + date.toLocaleString('default', {
                                        month: 'long'
                                    }) + " " + date.getFullYear();

                                    // reformattage de l'heure
                                    let time = article.publication_time.split(':');
                                    article.publication_time = time[0] + "h" + time[1];

                                    // création de l'article
                                    let articleHtml = `
                                            <div class="article">
                                                <h2>${article.title}</h2>
                                                <p>${article.content}</p>
                                                <div class="meta">
                                                    <span class="date">Le ${dateFormated} à ${article.publication_time}</span>
                                                    <span>Autres infos cachées, connectez vous ;)</span>
                                                </div>
                                            </div>
                                            `;
                                    // insertion de l'article dans le main
                                    $('#articles').append(articleHtml);
                                }
                                break;
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr);
                    }
                });
            }).catch(function(error) {
                console.log(error);
            });
        }
    </script>
</body>

</html>