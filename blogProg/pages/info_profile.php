<?php

 	 // Если пользователь не авторизован, то перенаправляет на главную
	if(empty($_COOKIE['auth'])||$_COOKIE['auth'] == 'yes'){
		header('Location:../index.php');
		exit();
	}

	// Подключаем файл с подключением к базе данных
	require '../db/db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $_COOKIE['user']['login']; ?></title>
	<!-- Подключаем стили для страницы index.php и общие стили(стили шапки, подвала, сайдбара. Они везде одинаковые)-->
	<link rel="stylesheet" href="../media/style/style_info_profile.css">
	<link rel="stylesheet" href="../media/style/style.css">
</head>
<body>
	
	<!-- Блок wrapper - обёртка для всех частей страницы -->

	<div id="wrapper">
		
		<!-- Подключаем Header -->

		<?php require '../includes/header.php' ?>
		
		<!-- Секция Content - Основная часть сайта -->

		<section class="content">

			<!-- Имя пользователя(Логин) -->
			<h2><?php echo $_COOKIE['user']['login']; ?></h2>

			<section class="articles">

				<!-- Информация о пользователе -->
				<div class="info_profile">

					<h3>Информация</h3>

					<table>
						<!-- Когда был зарегистрирован -->
						<tr>
							<td class="input">Зарегистрирован</td>
							<td class="value"><?php echo $_COOKIE['user']['data'] ?></td>
						</tr>
						<!-- Последняя активность(Фейковая) -->
						<tr>
							<td class="input">Последняя активность</td>
							<td class="value"><?php echo $_COOKIE['last'] ?></td>
						</tr>
						<!-- Логин пользователя -->
						<tr>
							<td class="input">Логин</td>
							<td class="value"><?php echo $_COOKIE['user']['login'] ?></td>
						</tr>
						<!-- Адрес электронной почты пользователя -->
						<tr>
							<td class="input">Адрес электронной почты</td>
							<td class="value"><?php echo $_COOKIE['user']['email'] ?></td>
						</tr>
						<!-- Аватарка(у всех одинаковая) -->
						<tr>
							<td class="input">Аватарка</td>
							<td class="value"><img src="../media/img/avatar.png"></td>
						</tr>
						<!-- Количество опубликованных статей у пользователя -->
						<tr>
							<td class="input">Количество публикаций</td>
							<td class="value">
								<?php $number_my_articles = mysqli_query($connection, "SELECT `id` FROM `articles` WHERE `author`='".$_COOKIE['user']['login']."'");
									$number = mysqli_num_rows($number_my_articles);
									echo $number;
								?>							
							</td>
						</tr>
						<!-- Количество комментариев у пользователя -->
						<tr>
							<td class="input">Количество комментариев</td>
							<td class="value">
								<?php $number_my_comments = mysqli_query($connection, "SELECT `id` FROM `comments` WHERE `author`='".$_COOKIE['user']['login']."'");
									$number = mysqli_num_rows($number_my_comments);
									echo $number;
								?></td>
						</tr>
					</table>

				</div>

			</section>
			
		</section>	
		
		<?php require '../includes/sidebar.php' ?>
			
			<!-- Подключаем Footer -->

		<?php require '../includes/footer.php' ?>

	</div>

</body>

</html>