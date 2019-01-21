
<!-- Шапка. Подключается к каждой странице -->

<header>
		
	<!-- Логотип -->

	<div class="logo">
		<a href="../index.php" class="logo">
			<h1>
				Блог Программиста
				<p>Всё о IT-сфере</p>
			</h1>
		</a>
	</div>
	
	<!-- Меню -->

	<div class="menu"><p>
		
		<p><a href="/index.php">Блог</a></p>

		<p><a href="../pages/lessons.php">Уроки</a></p>

		<p><a href="../pages/about.php">О нас</a></p>

		<p><label for="search">Поиск</label></p>

		<!-- Далнейший код до конца блока обеспечивает появление и исчезновение поиска -->
		<input type="radio" name="exit" id="search" >

		<input type="radio" name="exit" id="search_exit">

		<div class="search_div">

			<form action="../pages/search.php" method="GET">

				<span class="exit">

					<label for="search_exit">
						<img src="../media/img/exit.png">
					</label>

				</span>

				<input type="search" placeholder="Поиск" class="search_div" name="search">

				<button type="search" class="search">Найти</button>

			</form>

		</div>

	</div>
	
	<?php

	// Если пользователь авторизован, то выводится логин пользователя и аватарка. По нажатию на них открывается меню управления аккаунтом
	if(isset($_COOKIE['auth']) && $_COOKIE['auth'] == 'no' && !empty($_COOKIE['user']['login'])){

	?>
	<div class="person">

		<a class="person">

			<label for="account_open">

				<span><img src="../media/img/avatar.png" alt="Аватар"></span>
				<span class="login"><?php echo $_COOKIE['user']['login']; ?></span>

			</label>

		</a>

		<input type="checkbox" id="account_open">

		<div class="account_open">

			<?php

				// Если статус admin, то:
				if($_COOKIE['user']['status'] == 'admin'){

			?>

				<a href="../pages/admin_page.php" class="first_a">
					Управление сайтом													
				</a>

				<a href="../pages/info_profile.php">
					Посмотреть профиль														
				</a>

			<?php

				}

				// Если статус не 'admin' и пользователь зарегистрирован, то:

				else{

			?>

				<a href="../pages/info_profile.php" class="first_a">
					Посмотреть профиль														
				</a>

			<?php

				}

			?>		

				<a href="../pages/my_public.php">Мои публикации</a>
				<a href="../pages/my_comments.php">Мои комментарии</a>
				<hr>
				<a href="../pages/settings_profile.php">Настройки профиля</a>
				<a href="../pages/logout.php" class="last_a">Выйти</a>

		</div>
		
	</div>

	<?php

	}
	else{
		
	?>
		
		<div class="buttons">

			<a href="../pages/signup.php" class="button">Регистрация</a>
			<a href="../pages/authorization.php" class="button">Войти</a>

		</div>

	<?php

	}

	?>

</header>