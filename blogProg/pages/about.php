<?php
	
	// Подключаемся к базе данных
	require '../db/db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>О нас</title>
	<!-- Подключаем общие стили и стили для about.php -->
	<link rel="stylesheet" href="../media/style/style_about.css">
	<link rel="stylesheet" href="../media/style/style.css">
</head>
<body>

	<!-- Блок wrapper - обёртка для всех частей страницы -->

	<div id="wrapper">

		<!-- Подключаем Header -->

		<?php require '../includes/header.php' ?>
			
			<!-- Статья о нас -->

			<article class="article_about">
				<div>
					<h2>О нас</h2>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</p>
				</div>
			</article>
		
		<!-- Подключаем Sidebar -->

		<?php require '../includes/sidebar.php' ?>

		<!-- Подключаем Footer -->
		
		<?php require '../includes/footer.php' ?>

	</div>

</body>
</html>