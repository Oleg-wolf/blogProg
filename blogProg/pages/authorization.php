<?php
	
	// $exit ответственна за то, авторизован человек или нет
	$exit = 'no';

	// Подключение к базе данных
	$connection = mysqli_connect('localhost','root','','BlogProg');

	// Переданные переменные
	$auth = $_POST['authorization'];
	$login = $_POST['login'];
	$password = $_POST['password'];
	$error = false;

	// Если нажали кнопку авторизоваться, то:
	if(isset($auth)){

		// Проверка на существование пользователя
		$user = mysqli_query($connection, "SELECT * FROM `users` WHERE `login` = '$login' OR `email` = '$login'");
		$login_find = mysqli_fetch_assoc($user);

		if((strcasecmp($login, $login_find['login'])) !== 0){
			$error = true;
			$error_user = 'Пользователь не найден';
		}

		// Если пользователь найден, то:
		if($error == false){

			// Если верифицировав пароль пользователя из бд, он совпал с введённым, то:
			if(password_verify($password, $login_find['password'])){

				setcookie('user[login]', $login_find['login'], (time()+(30*24*60*60)), '/');
				setcookie('user[email]', $login_find['email'], (time()+(30*24*60*60)), '/');
				setcookie('user[data]', $login_find['data'], (time()+(30*24*60*60)), '/');
				setcookie('user[status]', $login_find['status'], (time()+(30*24*60*60)), '/');
				setcookie('auth', $exit, (time()+(30*24*60*60)), '/');

				// Если пользователь раньше был на сайте
				if(isset($_COOKIE['page'])){
					header('Location:..'. urldecode($_COOKIE['page']));
					exit();
				}

				// Ни разу не был на сайте
				else{
					header('Location:../index.php');
					exit();
				}

			}

			// Иначе пароли не совпадают
			else{
				$error_user = 'Неправильно введён пароль!';
			}

		}
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Авторизация</title>
	<link rel="stylesheet" href="../media/style/style_authorization.css">
</head>
<body>
	
	<section>

		<h2>Авторизация<span><?php echo $error_user ?></span></h2>
		
		<!-- Форма авторизации -->

		<form action="" method="POST">

			<p>Логин или Email</p>

			<input type="text" name="login" value="<?php if(isset($login)){ echo $login; }else{echo $_COOKIE['user']['login'];} ?>" required="required" title="Логин или Email">

			<p>Пароль<span class="forget_password"><a href="forget_password">Забыли пароль?</a></span></p>

			<input type="password" name="password" required="required" title="Пароль">
			
			<!-- Кнопка авторизации -->
			<button type="submit" name="authorization">Войти</button>

		</form>

		<p class="signup">Ещё нет аккаунта? <a href="signup.php">Зарегистрируйтесь</a></p>

	</section>

</body>
</html>