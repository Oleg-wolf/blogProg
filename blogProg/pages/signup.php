<?php
	
	// $exit ответственна за то, авторизован человек или нет 
	$exit = 'no';

	// Подключение к базе данных
	$connection = mysqli_connect('localhost','root','','BlogProg');

	// Переданные переменные
	$signup = $_POST['signup'];
	$login = $_POST['login'];
	$email = $_POST['email'];
	$password = $_POST['password'];
	$repeat_password = $_POST['repeat_password'];
	$terms_of_use = $_POST['terms_of_use'];
	$echo_characters = $_POST['echo_characters'];
	$characters = $_POST['characters'];

	$error = false;

	// Если нажали кнопку зарегистрироваться, то:
	if(isset($signup)){

		// Обрезка пробелов
		$login = trim($login);
		$password = trim($password);
		$email = trim($email);
		$repeat_password = trim($repeat_password);
		$characters = trim($characters);

		// Проверка логина
		if(strlen($login) < 3 || strlen($login) > 25 || 
			strpbrk($login, '.,-_?!`~@\'\\></";:[]{}+=*()&^%$#№|') != false){
			$error = true;
			$error_login = 'Только буквы (A-Z a-z) и цифры (0-9), не меньше 3 и не больше 25 символов!';
		}

		// Проверка email
		if(strlen($email) > 40){
			$error = true;
			$error_email = 'Максимальная длина Email - 40 символов!';
		}

		// Проверка пароля
		if(strlen($password) < 8 || strlen($password) > 40){
			$error = true;
			$error_password = 'Не меньше 8 и не больше 40 символов!';
		}

		// Проверка совпадения пароля и повторения пароля
		if($repeat_password != $password){
			$error = true;
			$error_password = 'Пароли не совпадают!';
		}

		// Проверка на робота)
		if((strnatcasecmp($characters, $echo_characters)) != 0){
			$error = true;
			$error_characters = 'Cимволы не совпадают!';
		}

		// Если нет ошибок приступает к этой части
		if($error == false){

			// Проверка уникальности логина
			$unique_login = mysqli_query($connection, "SELECT `login` FROM `users` WHERE `login` = '$login'");
			if((mysqli_num_rows($unique_login)) > 0){
				$error = true;
				$error_login = 'Такой логин уже существует!';
			}

			// Проверка уникальности Email
			$unique_email = mysqli_query($connection, "SELECT `email` FROM `users` WHERE `email` = '$email'");
			if((mysqli_num_rows($unique_email)) > 0){
				$error = true;
				$error_email = 'Пользователь с таким email уже зарегистрирован!';
			}

			// Если всё в порядке, то регистрируем пользователя
			if($error == false){

				// Хэшируем пароль
				$password_hash = password_hash($password, PASSWORD_DEFAULT);

				// Создание даты регистрации
				$data = date('d.m.Y');

				// Создание статуса. Если Admin, то статус admin
				if(strcasecmp($login, 'admin') == 0){
					$status = 'admin';
				}

				// Статус user
				else{
					$status = 'user';
				}

				// Добавление пользователя в базу данных
				$add_user = mysqli_query($connection, "INSERT INTO `users` (`login`, `email`, `password`, `data`, `status`) VALUES ('$login', '$email', '$password_hash', '$data', '$status')");

				setcookie('user[login]', $login, (time()+(30*24*60*60)), '/');				
				setcookie('user[email]', $email, (time()+(30*24*60*60)), '/');
				setcookie('user[data]', $data, (time()+(30*24*60*60)), '/');
				setcookie('user[status]', $status, (time()+(30*24*60*60)), '/');
				setcookie('auth', $exit, (time()+(30*24*60*60)), '/');

				// Если пользователь раньше был на сайте
				if(isset($_COOKIE['page'])){
					header('Location:'. urldecode($_COOKIE['page']));
					exit();
				}

				// Ни разу не был на сайте 
				else{
					header('Location:../index.php');
					exit();
				}
					
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Регистрация</title>
	<link rel="stylesheet" href="../media/style/style_signup.css">
</head>
<body>

	<section>

		<h2>Регистрация</h2>

		<!-- Форма регистрации -->

		<form action="" method="POST">

			<p>Логин <span><?php echo $error_login ?></span></p>

			<input type="text" name="login" required="required" title="Логин" value="<?php echo $login ?>">

			<p>Email <span><?php echo $error_email ?></span></p>

			<input type="email" name="email" required="required" title="Email" value="<?php echo $email ?>">

			<p>Пароль</p>

			<input type="password" name="password" required="required" title="Пароль" minlength="4">
			<p>Пароль ещё раз <span><?php echo $error_password ?></span></p>

			<input type="password" name="repeat_password" required="required" title="Повторите пароль" minlength="4">

			<p class="terms_of_use"><label title="Я принимаю условия пользовательского соглашения"><input type="checkbox" name="terms_of_use" required="required" title="Я принимаю условия пользовательского соглашения">Я принимаю условия <a href="terms_of_use" title="Читать пользовательское соглашение"></label>Пользовательского соглашения</a></p>
			
			<?php

				// Создание проверки на робота

				$characters = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');
				shuffle($characters);
				$arr = array_rand($characters, 5);
				$i = 0;
				for($j = 0; $j < 5; $j++){
					$string .= $characters[$arr[$j]];
				}

			?>

			<input type="text" value="<?php echo $string; ?>" class="characters" name="echo_characters"  readonly="readonly" id="characters">

			<a href="#characters"><button class="characters">Обновить картинку</button></a>

			<p class="characters">Введите символы с картинки <span><?php echo $error_characters ?></span></p>

			<input type="text" name="characters">

			<!-- Кнопка регистрации -->

			<button type="submit" name="signup" class="signup">Зарегистрироваться</button>

		</form>

		<p class="authorization">Уже зарегистрированы? <a href="../pages/authorization.php">Войдите в аккаунт</a></p>

	</section>

</body>
</html>