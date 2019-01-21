<?php
	
	// Подключаемся к базе данных
	require '../db/db.php';

	// Старт сессий
	session_start();

	// Присваиваем переменным переданные данные
	$to = 'vagaytsev2001@mail.ru';
	$email = $_POST['email'];
	$subject = $_POST['subject'];
	$message = $_POST['message'];
	$send = $_POST['send'];

	// Создаём сессии и присваиваем им значения переданных данных
	$_SESSION['email'] = $email;
	$_SESSION['subject'] = $subject;
	$_SESSION['message'] = $message;

	//Преобразуем переданные данные в специальные HTML-сущности
	$email = htmlspecialchars($email);
	$subject = htmlspecialchars($subject);
	$message = htmlspecialchars($message);

	// Создаём переменные, которые пригодятся для проверки на введённые данные и отправку
	$error = '';
	$success = '';

	// Если пользователь нажал кнопку отправки, то проверяем ввёл ли он e-mail, тему сообщения и сообщение. Если одно из полей не заполнено, то выводит сообщение "Введите незаполненное поле!". Если всё в порядке и все поля заполнены, то отправляет форму на Email админа и выводит "Сообщение успешно отправлено!".
	if(isset($send)){

		if($email == ''){
			$error = 'Введите Email!';
		}

		elseif(strlen($subject) == 0){
			$error = 'Введите тему сообщения!';
		}

		elseif(strlen($message) == 0){
			$error = 'Введите сообщение!';
		}

		else{

			$error = '';
			$success = 'Сообщение успешно отправлено!';
			$subject = "=?utf-8?B?".base64_encode($subject)."?=";
			$headers = "From: $email\r\nReply-to: $to\r\nContent-type:text-plain; charset=utf-8\r\n";
			mail($to, $subject, $message, $headers);

		}
		
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Обратная связь</title>
	<link rel="stylesheet" href="../media/style/style.css">
	<link rel="stylesheet" href="../media/style/style_feedback.css">
</head>
<body>
	
	<!-- Блок wrapper - обёртка для всех частей сайта -->

	<div id="wrapper">
		
		<!-- Подключаем Header -->

		<?php require '../includes/header.php' ?>

		<!-- Блок Content - Основная часть сайта -->

		<div class="content">
			
			<!-- Форма обратной связи -->

			<div class="form">

				<h2>Хотите связаться с нами? Тогда заполните форму и нажмите кнопку "Отправить".</h2>
				
				<!-- Форма Обратной связи -->
				<form action='' method="POST">

					<?php 

						if($error){
							echo '<span class="error">'.$error.'</span>';
						}

						else{
							echo '<span class="success">'.$success.'</span>';
						}

					?>

					<p><input type="email" name="email" placeholder="Введите Email" value="<?php echo $_SESSION['email']; ?>"></p>

					<p><input type="text" name="subject" placeholder="Введите тему сообщения" value="<?php echo $_SESSION['subject']; ?>"></p>

					<p><textarea name="message" placeholder="Введите сообщение" ><?php echo $_SESSION['message']; ?></textarea></p>

					<p><button type="submit" name="send">Отправить сообщение</button></p>

				</form>

			</div>

		</div>
		
		<!-- Подключаем Сайдбар -->

		<?php require '../includes/sidebar.php' ?>
		
		<!-- Подключаем Footer -->

		<?php require '../includes/footer.php' ?>

	</div>

</body>

</html>