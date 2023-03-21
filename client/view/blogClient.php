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
        <span class="info"></span>
    </main>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script>
        // Récupération du role de l'utilisateur
        function getUserRole() {
            return new Promise(function(resolve, reject) {
                if (localStorage.getItem('token') == null) {
                    resolve("anonymous");
                } else {
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
                }
            });
        }

        // Fonctions ayant besoin du role de l'utilisateur
        getUserRole().then(function(userRole) {
            console.log("Role : " + userRole);
            if (userRole == 'anonymous') {
                $('.connexion-btn').text('Connexion');
            } else {
                $('.connexion-btn').text('Déconnexion');
            }
            // On affiche le formulaire pour ajouter un nouvel article si l'utilisateur est publisher
            if (userRole == 'publisher') {
                let formHtml = `        
                            <form class="add-form">
                                <h2>Nouvel article</h2>
                                <label for="title">Titre :</label>
                                <input type="text" maxlength="50" id="title" name="title" class="title-add" required><br>
                                <label for="content">Contenu :</label>
                                <textarea id="content"  maxlength="256" name="content" class="content-add" required></textarea><br>
                                <input type="submit" value="Publier">
                                <span class="info-add-article"></span>
                            </form>`;
                $('#main').append(formHtml);
            }

            // Ajout d'un article en temps que publisher
            const addForm = document.querySelector('.add-form');
            if (addForm != null) {
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
                let headers = {};
                if (localStorage.getItem('token') != null) {
                    headers = {
                        "Authorization": "Bearer " + localStorage.getItem('token')
                    };
                }
                $.ajax({
                    url: "http://localhost/blog/api/articles",
                    type: "GET",
                    dataType: "json",
                    headers: headers,
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
                                                <span style="display:none;">${article.id_article}</span>
                                                <p class="content" onClick="editText(this)">${article.content}</p>
                                                <div class="meta">
                                                    <span class="date">Le ${dateFormated} à ${article.publication_time}</span>
                                                    <span class="author">Par ${article.author}</span>
                                                    <span class="likes" title="${usersLiked.join(', ')}">${article.nb_likes} like</span>
                                                    <span class="dislikes" title="${usersDisliked.join('<br> ')}">${article.nb_dislikes} dislike</span>
                                                    <span class="delete" onClick="deleteArticle(${article.id_article})"><a href="#/">Supprimer</a></span>
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

                                    let colorLike = "#999";
                                    let colorDislike = "#999";
                                    if (article.user_like_value == 1) {
                                        colorLike = "#1acc57";
                                        colorDislike = "#999";
                                    } else if (article.user_like_value == -1) {
                                        colorLike = "#999";
                                        colorDislike = "#f22c2b";
                                    }
                                    let articleHtml = `
                                            <div class="article">
                                                <h2>${article.title}</h2>
                                                <span style="display:none;">${article.id_article}</span>
                                                <p class="content" onClick="editText(this)">${article.content}</p>
                                                <div class="meta">
                                                    <span class="date">Le ${dateFormated} à ${article.publication_time}</span>
                                                    <span class="author">Par ${article.author}</span>
                                                    <span class="likes" style="color:${colorLike};" onClick="likeArticle(this, ${article.id_article})"><a>${article.nb_likes} like</a></span>
                                                    <span class="dislikes" style="color:${colorDislike};" onClick="dislikeArticle(this, ${article.id_article})"><a>${article.nb_dislikes} dislike</a></span>
                                                    <span class="like-value" style="display: flex;">${article.user_like_value}</span>
                                                    <span class="delete" onClick="deleteArticle(${article.id_article})"><a href="#/">Supprimer</a></span>
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
                                                <span style="display:none;">${article.id_article}</span>
                                                <p class="content" onClick="editText(this)">${article.content}</p>
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

        function deleteArticle(idArticle) {
            
            $.ajax({
                url: "http://localhost/blog/api/delete/article/" + idArticle,
                type: "DELETE",
                dataType: "json",
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem('token')
                },
                success: function(response) {
                    afficherMessage("Article supprimé");
                    getArticles();
                },
                error: function(xhr, status, error) {
                    afficherMessage("Erreur lors de la suppression de l'article");
                },

            });

        }

        function likeArticle(span, idArticle) {

            $.ajax({
                url: "http://localhost/blog/api/like/article/" + idArticle,
                type: "PATCH",
                dataType: "json",
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem('token')
                },
                success: function(response) {
                    spanAsLike = span.nextElementSibling.nextElementSibling;
                    asLike = spanAsLike.innerHTML;
                    if (response.data.likeValue == 0) {
                        span.children[0].innerHTML = (parseInt(span.children[0].innerHTML.split(" ")[0]) - 1) + " like";
                        span.style.color = "#999";
                        span.nextElementSibling.style.color = "#999";
                        afficherMessage("Like retiré");
                        spanAsLike.innerHTML = 0;
                    } else if (response.data.likeValue == 1) {
                        span.children[0].innerHTML = (parseInt(span.children[0].innerHTML.split(" ")[0]) + 1) + " like";
                        span.style.color = "#1acc57";
                        span.nextElementSibling.style.color = "#999";
                        if (asLike == -1) {
                            let likeValue = span.nextElementSibling.children[0].innerHTML.split(" ")[0];
                            span.nextElementSibling.children[0].innerHTML = (likeValue - 1) + " dislike";
                        }
                        afficherMessage("Article liké");
                        spanAsLike.innerHTML = 1;
                    }
                },
                error: function(xhr, status, error) {
                    afficherMessage("Erreur lors du like de l'article");
                },
            });
        }

        function dislikeArticle(span, idArticle, asLike) {

            $.ajax({
                url: "http://localhost/blog/api/dislike/article/" + idArticle,
                type: "PATCH",
                dataType: "json",
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem('token')
                },
                success: function(response) {
                    spanAsLike = span.nextElementSibling;
                    asLike = spanAsLike.innerHTML;
                    if (response.data.likeValue == 0) {
                        span.children[0].innerHTML = (parseInt(span.children[0].innerHTML.split(" ")[0]) - 1) + " dislike";
                        span.style.color = "#999";
                        afficherMessage("Dislike retiré");
                        spanAsLike.innerHTML = 0;
                    } else {
                        span.children[0].innerHTML = (parseInt(span.children[0].innerHTML.split(" ")[0]) + 1) + " dislike";
                        span.style.color = "#f22c2b";
                        span.previousElementSibling.style.color = "#999";
                        if (asLike == 1) {
                            let likeValue = span.previousElementSibling.children[0].innerHTML.split(" ")[0];
                            span.previousElementSibling.children[0].innerHTML = (likeValue - 1) + " like";
                        }
                        afficherMessage("Article disliké");
                        spanAsLike.innerHTML = -1;
                    }

                },
                error: function(xhr, status, error) {
                    afficherMessage("Erreur lors du dislike de l'article");
                },
            });
        }

        function afficherMessage(message) {
            $(".info").val(message);
        }

        function editText(text) {
            // Créer un champ de texte
            var input = document.createElement("input");
            input.type = "text";
            input.value = text.textContent;

            // Remplacer le texte par le champ de texte
            text.replaceWith(input);

            // Ajouter un écouteur d'événements pour le clic en dehors du champ de texte
            input.addEventListener("blur", validEditText);
        }

        function editArticle(content, idArticle) {
            $.ajax({
                url: "http://localhost/blog/api/modify/article/" + idArticle,
                type: "PATCH",
                dataType: "json",
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem('token')
                },
                data: JSON.stringify({
                    content: content
                }),
                success: function(response) {
                    afficherMessage("Article modifié");

                },
            });
        }


        function validEditText() {
            // Créer une balise span pour contenir le texte édité
            var p = document.createElement("p");
            p.className = "content";
            p.onclick = function() {
                editText(this);
            };
            text = document.querySelector("input[type='text']");


            p.textContent = text.value;
            idArticle = text.previousElementSibling.textContent;


            editArticle(text.value, idArticle);
            // Remplacer le champ de texte par la balise span
            document.querySelector("input[type='text']").replaceWith(p);

            // Ajouter un écouteur d'événements pour le clic sur la balise span
            p.addEventListener("click", editText);
        }
    </script>
</body>

</html>