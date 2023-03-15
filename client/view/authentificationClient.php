<!DOCTYPE html>
<html>

<head>
	<title>Page de connexion et d'inscription</title>
	<link rel="stylesheet" type="text/css" href="../css/auth.css">
</head>

<body>

	<!-- Page de connexion -->
	<div class="login-page">
		<div class="form connection-form">
			<h1>Connection</h1>
			<form class="login-form" action="#" method="post">
				<input type="text" placeholder="Nom d'utilisateur" required />
				<input type="password" placeholder="Mot de passe" required />
				<button>Se connecter</button>
				<span class="error"></span>
				<p class="message">Pas encore inscrit? <a href="#">Créer un compte</a></p>
			</form>
		</div>
	</div>

	<!-- Page d'inscription -->
	<div class="register-page hidden">
		<div class="form signup-form">
			<h1>Inscription</h1>
			<form class="register-form" action="#" method="post">
				<input type="text" placeholder="Nom d'utilisateur" required />
				<input type="password" placeholder="Mot de passe" required />
				<input class="password-confirm" type="password" placeholder="Confirmer le mot de passe" required />
				<button>S'inscrire</button>
				<span class="error"></span>
				<p class="message">Déjà inscrit? <a href="#">Se connecter</a></p>
			</form>
		</div>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
	<script>
		// Script pour faire basculer entre les deux pages 
		const loginForm = document.querySelector('.login-page');
		const registerForm = document.querySelector('.register-page');
		const loginLink = document.querySelector('.message a');
		const registerLink = document.querySelector('.register-page .message a');

		loginLink.addEventListener('click', function() {
			loginForm.classList.toggle('hidden');
			registerForm.classList.toggle('hidden');
		});

		registerLink.addEventListener('click', function() {
			loginForm.classList.toggle('hidden');
			registerForm.classList.toggle('hidden');
		});


		// Script pour faire fonctionner le formulaire de connexion
		const connectionForm = document.querySelector('.connection-form');
		const loginButton = document.querySelector('.login-page .login-form button');
		const loginUsername = document.querySelector('.login-page .login-form input[type="text"]');
		const loginPassword = document.querySelector('.login-page .login-form input[type="password"]');
		const loginError = document.querySelector('.login-page .login-form .error');

		connectionForm.addEventListener('submit', function(event) {
			event.preventDefault();
			$.ajax({
				url: 'http://localhost/blog/API/auth/login',
				method: 'POST',
				data: JSON.stringify({
					login: loginUsername.value,
					password: loginPassword.value
				}),
				dataType: 'json',
				success: function(response) {
					//stockage du jeton dans le stockage localStorage
					localStorage.setItem('token', response.data);
					console.log(response.data);
					//redirection vers la page du blog
					document.location.href = "blogClient.php";
				},
				error: function() {
					loginError.textContent = "Nom d'utilisateur ou mot de passe incorrect";
					$(loginPassword).val('');
				}
			});
		});


		// Script pour faire fonctionner le formulaire d'incription
		const signupForm = document.querySelector('.signup-form');
		const registerButton = document.querySelector('.register-page .register-form button');
		const registerUsername = document.querySelector('.register-page .register-form input[type="text"]');
		const registerPassword = document.querySelector('.register-page .register-form input[type="password"]');
		const registerConfirmPassword = document.querySelector('.register-page .register-form .password-confirm');
		const registerError = document.querySelector('.register-page .register-form .error');

		signupForm.addEventListener('submit', function(event) {
			event.preventDefault();
			if (registerPassword.value !== registerConfirmPassword.value) {
				registerError.textContent = "Les mots de passe ne correspondent pas";
				$(registerPassword).val('');
				$(registerConfirmPassword).val('');
			} else {
				$.ajax({
					url: 'http://localhost/blog/API/auth/signup',
					method: 'POST',
					data: JSON.stringify({
						login: registerUsername.value,
						password: registerPassword.value
					}),
					dataType: 'json',
					success: function(response) {
						loginForm.classList.toggle('hidden');
						registerForm.classList.toggle('hidden');
						$('input').val('');
						loginError.textContent = "Inscription réussie, vous pouvez vous connecter";
					},
					error: function() {
						registerError.textContent = "Nom d'utilisateur déjà utilisé";
					}
				});
			}
		});

	</script>
</body>

</html>