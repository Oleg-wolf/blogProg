<?php
	
	// Если не авторизован, то перенаправляет на главную

	if(empty($_COOKIE['auth'])||$_COOKIE['auth'] == 'yes'||!isset($_COOKIE['user']['login'])){
		header('Location:../index.php');
		exit();
	}

	// Подключаем файл с подключением к базе данных

	require '../db/db.php';

	// Данные для следущих действий(Смены email, login, password)

	$login = urldecode($_COOKIE['user']['login']);

	$info_user = mysqli_query($connection, "SELECT * FROM `users` WHERE `login` = '$login' OR `email` = '$login'");
	$inf_us = mysqli_fetch_assoc($info_user);

	$old_email = $inf_us['email'];
	$old_login = $inf_us['login'];

	// Кнопка сохранения изменений
	$save_settings = $_POST['save_settings'];

	if(isset($save_settings)){

		$error = false;
		$new_email = $_POST['new_email'];
		$new_login = $_POST['new_login'];
		$old_password = $_POST['old_password'];
		$new_password = $_POST['new_password'];
		$repeat_new_password = $_POST['repeat_new_password'];

		// Если пользователь отправил пустую форму выводит, что ничего не изменено

		if($new_email == '' && $new_login == '' && $old_password == '' && $new_password == '' && $repeat_new_password == ''){
			$success = 'Ваши данные не изменены';
		}

		else{

			// Если есть переданные данные в форме, то сначала реализуем проверки

			if($new_email == ''){
				$new_email = $old_email;
			}

			else{

				if(strlen($new_email) > 40){
					$error = true;
					$error_new_email = 'Максимальная длина Email - 40 символов!';
				}

			}

			if($new_login == ''){
				$new_login = $old_login;
			}

			else{

				if(strlen($new_login) < 3 || strlen($new_login) > 25 || 
					strpbrk($new_login, '.,-_?!`~@\'\\></";:[]{}+=*()&^%$#№|') != false){
					$error = true;
					$error_login = 'Только буквы (A-Z a-z) и цифры (0-9), не меньше 3 и не больше 25 символов!';
				}

			}

			if(($old_password == '' && $new_password == '' && $repeat_new_password == '') || ($new_password == '' && $repeat_new_password == '')){
				$hash = true;
			}

			elseif(password_verify($old_password, $inf_us['password'])){

				if(strlen($new_password) < 8 || strlen($new_password) > 40){
					$error = true;
					$error_new_password = 'Не меньше 8 и не больше 40 символов!';
				}

				if($repeat_new_password != $new_password){
					$error = true;
					$error_repeat_password = 'Пароли не совпадают!';
				}

			}

			else{
				$error = true;
				$error_old_password = 'Неправильный пароль';
			}

			// Если без ошибок, то преступает к следущему шагу - проверка на уникальность

			if($error == false){

				$unique_login = mysqli_query($connection, "SELECT `login` FROM `users` WHERE `login` = '$new_login' AND `login` != '$new_login'");

				if((mysqli_num_rows($unique_login)) > 0){
					$error = true;
					$error_new_login = 'Такой логин уже существует!';
				}

				$unique_email = mysqli_query($connection, "SELECT `email` FROM `users` WHERE `email` = '$new_email' AND `email` != '$new_email'");

				if((mysqli_num_rows($unique_email)) > 0){
					$error = true;
					$error_new_email = 'Пользователь с таким email уже зарегистрирован!';
				}

			}
			
			// Если без ошибок, изменяем данные пользователя, на введённые им

			if($error == false){

				if(!isset($hash)){ 
					$password_hash = password_hash($new_password, PASSWORD_DEFAULT); 
				}

				else{
					$password_hash = $inf_us['password'];
				}

					mysqli_query($connection, "UPDATE `users` SET `login` = '$new_login', `email` = '$new_email', `password` = '$password_hash', `data` = '".$inf_us['data']."', `status` = '".$inf_us['status']."' WHERE `id` = '".$inf_us['id']."'");
					setcookie('user[login]', $new_login, (time()+(30*24*60*60)), '/');
					setcookie('user[email]', $new_email, (time()+(30*24*60*60)), '/');
					$success = 'Настройка аккаунта прошла успешно! Ваши данные изменены!';

			}
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Настройка аккаунта</title>
	<!-- Подключаем стили для страницы index.php и общие стили(стили шапки, подвала, сайдбара. Они везде одинаковые)-->
	<link rel="stylesheet" href="../media/style/style_settings_profile.css">
	<link rel="stylesheet" href="../media/style/style.css">
</head>
<body>
	
	<!-- Скрипт согласия) -->
	<script>
		function changeFile(){
			if(confirm('Вы действительно хотите изменить настройки вашего аккаунта?')){
				return true;
			}
			else{
				return false;
			}
		}
	</script>
	
	<!-- Блок wrapper - обёртка для всех частей страницы -->

	<div id="wrapper">
		
		<!-- Подключаем Header -->

		<?php require '../includes/header.php' ?>
		
		<!-- Секция Content - Основная часть сайта -->
		<section class="content">
			
			<?php 
				if(isset($success)){
			?>
					<section class="add_comment">
						<div class="comments_prohibited">
							<p><?php echo $success; ?></p>
						</div>
					</section>
			<?php
				}
			?>
		
			<h2>Настройки аккаунта</h2>

			<section class="articles">

			<!-- Форма для изменения данных пользователя -->
			<form action="" method="POST">

				<!-- Список из блоков для изменения e-mail, логина, пароля -->
				<ul>

					<!-- Блок для изменения e-mail -->

					<li>
						<details <?php if($error == true){ echo 'open="open"'; } ?>>

							<summary>

								<label for="open_email">
									Изменить e-mail			
								</label>
								<input type="checkbox" <?php if($error == true){ echo 'checked="checked"'; } ?> name="open" id="open_email" >
								<div class="open">
									<span>									
										>
									</span>
								</div>

							</summary>

							<p class="first_p">Текущий e-mail</p>

							<input type="email" name="old_email" readonly="readonly" value="<?php echo $old_email; ?>">

							<p>Новый e-mail <span><?php echo $error_new_email; ?></span></p>

							<input type="email" name="new_email" class="last_input" value="<?php echo $new_email; ?>">

						</details>

					</li>

					<!-- Блок для изменения логина -->

					<li>

						<details <?php if($error == true){ echo 'open="open"'; } ?>>

							<summary>

								<label for="open_login">
									Изменить логин			
								</label>
								<input type="checkbox" name="open" id="open_login" <?php if($error == true){ echo 'checked="checked"'; } ?>>
								<div class="open">
									<span>									
										>
									</span>
								</div>

							</summary>

							<p class="first_p">Текущий логин</p>

							<input type="text" name="old_login" readonly="readonly" value="<?php echo $old_login; ?>">

							<p>Новый логин <span><?php echo $error_new_login; ?></span></p>

							<input type="text" name="new_login" class="last_input" value="<?php echo $new_login; ?>">

						</details>

					</li>

					<!-- Блок для изменения пароля -->

					<li>

						<details <?php if($error == true){ echo 'open="open"'; } ?>>

							<summary>

								<label for="open_password">
									Изменить пароль			
								</label>
								<input type="checkbox" name="open" id="open_password" <?php if($error == true){ echo 'checked="checked"'; } ?>>
								<div class="open">
									<span>									
										>
									</span>
								</div>

							</summary>

							<p class="first_p">Текущий пароль <span><?php echo $error_old_password; ?></span></p>

							<input type="password" name="old_password" value="<?php echo $old_password; ?>">

							<p>Новый пароль <span><?php echo $error_new_password; ?></span></p>
							<input type="password" name="new_password" value="<?php echo $new_password; ?>">

							<p>Новый пароль ещё раз <span><?php echo $error_repeat_password; ?></span></p>

							<input type="password" name="repeat_new_password" class="last_input" value="<?php echo $repeat_new_password; ?>">

						</details>

					</li>

				</ul>

				<!-- Кнопка отправки формы -->
				<button onclick="return changeFile();" type="submit" name="save_settings">Сохранить настройки</button>

			</form>

			</section>

		</section>

		<!-- Подключаем Сайдбар -->

		<?php require '../includes/sidebar.php' ?>
			

		<!-- Подключаем Footer -->
		<?php require '../includes/footer.php' ?>

	</div>

</body>

</html>