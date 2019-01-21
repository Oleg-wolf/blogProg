<?php
	
	// Проверка на то, что зашёл точно админ
	if($_COOKIE['auth'] != 'no' || strcasecmp($_COOKIE['user']['login'], 'admin') != 0 || $_COOKIE['user']['status'] != 'admin'||empty($_COOKIE['auth'])){
		header('Location:../index.php');
		exit();
	}
	
	// Подключение к бд
	require '../db/db.php';

	// Старт сессии
	session_start();
	
	// По умочанию в сортировке выбранно:
	$header_content = 'Последние записи';
	$sorting = 'articles';
	$articles_select = 'new';

	// Если нажали кнопку сохранить изменения, то обновляет информацию в бд
	if(isset($_POST['save_changes'])){

		$id_stat_select = mysqli_query($connection, "SELECT `id` FROM `articles`");

		while($id_stat_sel = mysqli_fetch_assoc($id_stat_select)['id']){

			if(isset($_POST[$id_stat_sel]) && $_POST[$id_stat_sel] == 'public'){
				mysqli_query($connection, "UPDATE `articles` SET `status` = '".$_POST[$id_stat_sel]."', `data_public` ='".date('d.m.Y')."' WHERE `id` = '$id_stat_sel'");
			}

			if(isset($_POST[$id_stat_sel]) && $_POST[$id_stat_sel] != 'public'){
				mysqli_query($connection, "UPDATE `articles` SET `status` = '".$_POST[$id_stat_sel]."' WHERE `id` = '$id_stat_sel'");
			}

		}

	}

	// Если нажали кнопку "Применить", то сортирует
	if(isset($_POST['apply'])){
		$apply = true;
		$sorting = $_POST['sorting'];
		unset($_GET['page']);

		// Проверяем, чему равна группа сортировки
		switch($sorting){

			// Если сортировка по статьям, то проверяем какие именно статьи были запрошены
			case 'articles':

				$_SESSION['sorting'] = 'articles';
				$articles_select = $_POST['articles_select'];

				switch($articles_select){
					case 'new':
						$header_content = "Последние записи";
						$_SESSION['select'] = 'new';
						break;
					case 'old':
						$header_content = "Первые записи";
						$_SESSION['select'] = 'old';
						break;
					case 'popular':
						$header_content = "Самые популярные статьи";
						$_SESSION['select'] = 'popular';
						break;
					case 'public':
						$header_content = "Последние опубликованные статьи";
						$_SESSION['select'] = 'public';
						break;
					case 'waiting_moderation':
						$header_content = "Ожидают модерации";
						$_SESSION['select'] = 'waiting_moderation';
						break;
					case 'edited':
						$header_content = "Редактируются";
						$_SESSION['select'] = 'edited';
						break;
					case 'no_public':
						$header_content = "Неопубликованные статьи";
						$_SESSION['select'] = 'no_public';
						break;
				}
				break;

				// Если по комментариям, определяем, какие именно были запрошены на вывод
			case 'comments':

				$_SESSION['sorting'] = 'comments';				
				$comments_select = $_POST['comments_select'];

				// Считаем количество комментов
				$number_today_comments = mysqli_query($connection, "SELECT `id` FROM `comments` WHERE `data` = '".date('d.m.Y')."'");
				$num_today_com = mysqli_num_rows($number_today_comments);

				$number_comments = mysqli_query($connection, "SELECT `id` FROM `comments`");
				$num_com = mysqli_num_rows($number_comments);

				switch($comments_select){
					case 'new':
						$header_content = "Последние комментарии";
						$_SESSION['select'] = 'new';
						break;
					case 'old':
						$header_content = "Первые комментарии";
						$_SESSION['select'] = 'old';
						break;
				}
				break;

				// Если по пользователям, то опредёляем каким именно
			case 'users':

				$_SESSION['sorting'] = 'users';
				$users_select = $_POST['users_select'];

				// Подсчёт пользователей
				$number_today_users = mysqli_query($connection, "SELECT `id` FROM `users` WHERE `data` ='".date('d.m.Y')."'");
				$num_today_users = mysqli_num_rows($number_today_users);

				$number_users = mysqli_query($connection, "SELECT `id` FROM `users`");
				$num_users = mysqli_num_rows($number_users);

				switch($users_select){
					case 'new':
						$header_content = "Последние зарегистрированные пользователи";
						$_SESSION['select'] = 'new';
						break;
					case 'old':
						$header_content = "Первые зарегистрированные пользователи";
						$_SESSION['select'] = 'old';
						break;
				}
				break;
		}		
	}

	// Если сессия сортировки не пустая, то смотрим, что в ней и определяем, что выводить
	if(isset($_SESSION['sorting'])){
		$apply = true;
		$sorting = $_SESSION['sorting'];

		switch($sorting){

			// Если сессия сортировки по статьям, то проверяем, каким именно
			case 'articles':

				switch($_SESSION['select']){
					case 'new':
						$header_content = 'Последние записи';
						$articles_select = 'new';
						break;
					case 'old':
						$header_content = 'Первые записи';
						$articles_select = 'old';
						break;
					case 'popular':
						$header_content = 'Самые популярные записи';
						$articles_select = 'popular';
						break;
					case 'public':
						$header_content = 'Последние опубликованные записи';
						$articles_select = 'public';
						break;
					case 'waiting_moderation':
						$header_content = 'Ожидают модерации';
						$articles_select = 'waiting_moderation';
						break;
					case 'edited':
						$header_content = 'Редактируются';
						$articles_select = 'edited';
						break;
					case 'no_public':
						$header_content = 'Неопубликованные статьи';
						$articles_select = 'no_public';
						break;
				}
				break;

			// Если сессия сортировки по комментариям, то проверяем, каким именно
			case 'comments':

				switch($_SESSION['select']){
					case 'new':
						$header_content = 'Последние комментарии';
						$comments_select = 'new';
						break;
					case 'old':
						$header_content = 'Первые комментарии';
						$comments_select = 'old';
						break;
				}

				// Подсчёт комментов
				$number_today_comments = mysqli_query($connection, "SELECT `id` FROM `comments` WHERE `data` = '".date('d.m.Y')."'");
				$num_today_com = mysqli_num_rows($number_today_comments);

				$number_comments = mysqli_query($connection, "SELECT `id` FROM `comments`");
				$num_com = mysqli_num_rows($number_comments);

				// Организация Пагинации
				if(!empty($num_com)){

					if($num_com/8 > 1){

						$pagination = true;
						$pages = array();
						$i = 0;
						$num_comments = $num_com;
						while($num_comments > 0){
							$num_comments = $num_comments - 8;			
							$pages[$i] = $i;
							$i++;
						}

						if(empty($_GET['page'])){

							if($comments_select == 'new'){
								$articles = mysqli_query($connection, "SELECT * FROM `comments` ORDER BY `id` DESC LIMIT 8");
							}

							else{
								$articles = mysqli_query($connection, "SELECT * FROM `comments` ORDER BY `id` LIMIT 8");
							}

						}

					}
					else{

						if($comments_select == 'new'){
							$articles = mysqli_query($connection, "SELECT * FROM `comments` ORDER BY `id` DESC LIMIT 8");
						}

						else{
							$articles = mysqli_query($connection, "SELECT * FROM `comments` ORDER BY `id` LIMIT 8");
						}

					}

				}
				if(isset($_GET['page']) && $_GET['page'] != false){

					$page = $_GET['page'] - 1;
					$page = $page * 8;

					if($comments_select == 'new'){
						$articles = mysqli_query($connection, "SELECT * FROM `comments` ORDER BY `id` DESC LIMIT 8 OFFSET $page");
					}

					else{
						$articles = mysqli_query($connection, "SELECT * FROM `comments` ORDER BY `id` LIMIT 8 OFFSET $page");
					}

					if(($num_comments_this_page = mysqli_num_rows($articles)) == 0){
						$page = end($pages);
						$page++;
						header('Location:../pages/admin_page.php?page='.$page);
						exit();

					}

				}
				break;

			// Если сессия сортировки по пользователям, то проверяем, каким именно
			case 'users':

				switch($_SESSION['select']){
					case 'new':
						$header_content = 'Последние зарегистрированные пользователи';
						$users_select = 'new';
						break;
					case 'old':
						$header_content = 'Первые зарегистрированные пользователи';
						$users_select = 'old';
						break;
				}


				// Подсчёт пользователей
				$number_today_users = mysqli_query($connection, "SELECT `id` FROM `users` WHERE `data` ='".date('d.m.Y')."'");
				$num_today_users = mysqli_num_rows($number_today_users);

				$number_users = mysqli_query($connection, "SELECT `id` FROM `users`");
				$num_users = mysqli_num_rows($number_users);

				// Организация Пагинации
				if(!empty($num_users)){

					if($num_users/8 > 1){

						$pagination = true;
						$pages = array();
						$i = 0;
						$num_us = $num_users;
						while($num_us > 0){
							$num_us = $num_us - 8;			
							$pages[$i] = $i;
							$i++;
						}

						if(empty($_GET['page'])){
							if($users_select == 'new'){
								$articles = mysqli_query($connection, "SELECT * FROM `users` ORDER BY `id` DESC LIMIT 8");
							}

							else{
								$articles = mysqli_query($connection, "SELECT * FROM `users` ORDER BY `id` LIMIT 8");
							}

						}

					}

					else{
						if($users_select == 'new'){
							$articles = mysqli_query($connection, "SELECT * FROM `users` ORDER BY `id` DESC LIMIT 8");
						}

						else{
							$articles = mysqli_query($connection, "SELECT * FROM `users` ORDER BY `id` LIMIT 8");
						}

					}

				}

				if(isset($_GET['page']) && $_GET['page'] != false){

					$page = $_GET['page'] - 1;
					$page = $page * 8;
					if($users_select == 'new'){
						$articles = mysqli_query($connection, "SELECT * FROM `users` ORDER BY `id` DESC LIMIT 8 OFFSET $page");
					}

					else{
						$articles = mysqli_query($connection, "SELECT * FROM `users` ORDER BY `id` LIMIT 8 OFFSET $page");
					}

					if(($num_users_this_page = mysqli_num_rows($articles)) == 0){
						$page = end($pages);
						$page++;
						header('Location:../pages/admin_page.php?page='.$page);
						exit();
					}

				}

				break;
		}
	}

	// Если сортировка по статьям, то подсчитываем, сколько статей с определённым статусом
	if($sorting == 'articles'){

		$status_articles = mysqli_query($connection, "SELECT `status` FROM `articles`");

		// Подсчёт статей с определённом статусом
		while($stat_fetch_assoc = mysqli_fetch_assoc($status_articles)){

			switch($stat_fetch_assoc['status']){
				case 'public':
					$num_pub_art++;
					break;
				case 'waiting_moderation':
					$num_wait_moderation_art++;
					break;
				case 'edited':
					$num_edited_art++;
					break;
				case 'no_public':
					$num_no_pub_art++;
					break;
			}

		}

		$num_art = $num_pub_art + $num_wait_moderation_art + $num_edited_art + $num_no_pub_art;

		$today_articles = mysqli_query($connection, "SELECT `id` FROM `articles` WHERE `data_creation` ='".date('d.m.Y')."'");
		$num_today_art = mysqli_num_rows($today_articles);

		// Организация Пагинации
		if(!empty($num_art)){

			if($num_art/8 > 1){

				$pagination = true;
				$pages = array();
				$i = 0;
				$num_articles = $num_art;
				while($num_articles > 0){
					$num_articles = $num_articles - 8;			
					$pages[$i] = $i;
					$i++;
				}

			}

			if(($num_art/8 < 1) || (empty($_GET['page']))){

				switch($articles_select){
					case 'new':
						$articles = mysqli_query($connection, "SELECT * FROM `articles` ORDER BY `id` DESC LIMIT 8");
						break;
					case 'old':
						$articles = mysqli_query($connection, "SELECT * FROM `articles` ORDER BY `id` LIMIT 8");
						break;
					case 'popular':
						$articles = mysqli_query($connection, "SELECT * FROM `articles` ORDER BY `views` DESC LIMIT 8");
						break;
					case 'edited':
						$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'edited' ORDER BY `id` DESC LIMIT 8");
						if($num_edited_art < 9){
							$pagination = false;
						}
						break; 
					case 'public':
						$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'public' ORDER BY `id` DESC LIMIT 8");
						if($num_pub_art < 9){
							$pagination = false;
						}
						break;
					case 'waiting_moderation':
						$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'waiting_moderation' ORDER BY `id` LIMIT 8");
						if($num_wait_moderation_art < 9){
							$pagination = false;
						}
						break;
					case 'no_public':
						$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'no_public' ORDER BY `id` DESC LIMIT 8");
						if($num_no_pub_art < 9){
							$pagination = false;
						}
						break;
				}

			}

		}

		if(isset($_GET['page']) && $_GET['page'] != false){

			$page = $_GET['page'] - 1;
			$page = $page * 8;

			switch($articles_select){
				case 'new':
					$articles = mysqli_query($connection, "SELECT * FROM `articles` ORDER BY `id` DESC LIMIT 8 OFFSET $page");
					break;
				case 'old':
					$articles = mysqli_query($connection, "SELECT * FROM `articles` ORDER BY `id` LIMIT 8 OFFSET $page");
					break;
				case 'popular':
					$articles = mysqli_query($connection, "SELECT * FROM `articles` ORDER BY `views` DESC LIMIT 8 OFFSET $page");
					break;
				case 'edited':
					$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'edited' ORDER BY `id` DESC LIMIT 8 OFFSET $page");
					break; 
				case 'public':
					$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'public' ORDER BY `id` DESC LIMIT 8 OFFSET $page");
					break;
				case 'waiting_moderation':
					$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'waiting_moderation' ORDER BY `id` LIMIT 8 OFFSET $page");
					break;
				case 'no_public':
					$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'no_public' ORDER BY `id` DESC LIMIT 8 OFFSET $page");
					break;
			}

			if(($num_articles_this_page = mysqli_num_rows($articles)) == 0){
				$page = end($pages);
				$page++;
				header('Location:../pages/admin_page.php?page='.$page);
				exit();
			}
			
		}
		
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Управление сайтом</title>
	<link rel="stylesheet" href="/media/style/style.css">
	<link rel="stylesheet" href="../media/style/style_admin_page.css">
</head>
<body>
	
	<!-- Скрипт Согласия) -->
	<script>
		function deleteFile(){
			if(confirm('Вы действительно хотите удалить отмеченные комментарии?')){
				return true;
			}
			else{
				return false;
			}
		}
	</script>

	<div id="wrapper">
		
		<!-- Подключаем Header -->

		<?php require '../includes/header.php' ?>
		
		<!-- Секция Content - Основная часть сайта -->

		<section class="content">
			
			<div class="general_info">

				<?php

					// Если сортировка по статьям, то статьи были подсчитаны. Выводим их на экран
					if(isset($num_art)){
						if(empty($num_pub_art)){ $num_pub_art = 0; }
						if(empty($num_no_pub_art)){ $num_no_pub_art = 0; }
						if(empty($num_edited_art)){ $num_edited_art = 0; }
						if(empty($num_wait_moderation_art)){ $num_wait_moderation_art = 0; }
						if(empty($num_today_art)){ $num_today_art = 0; }
						if(empty($num_art)){ $num_art = 0; }
						echo '<table>
									<tr>
										<td class="input">Опубликовано</td>
										<td class="value">'.$num_pub_art.'</td>
									</tr>
									<tr>
										<td class="input">Неопубликовано</td>
										<td class="value">'.$num_no_pub_art.'</td>
									</tr>
									<tr>
										<td class="input">Редактируется</td>
										<td class="value">'.$num_edited_art.'</td>
									</tr>
									<tr>
										<td class="input">Ожидают модерации</td>
										<td class="value">'.$num_wait_moderation_art.'</td>
									</tr>
									<tr>
										<td class="input">Статей за сегодня</td>
										<td class="value">'.$num_today_art.'</td>
									</tr>
									<tr>
										<td class="input">Всего статей</td>
										<td class="value">'.$num_art.'</td>
									</tr>
							    </table>';
					}

					// Если сортировка по комментария, то комментарии были подсчитаны. Выводим их на экран
					if(isset($num_com)){
						echo '<table>
									<tr>
										<td class="input">Комментариев за сегодня</td>
										<td class="value">'.$num_today_com.'</td>
									</tr>
									<tr>
										<td class="input">Всего комментариев</td>
										<td class="value">'.$num_com.'</td>
									</tr>
							    </table>';
					}
					// Если сортировка по пользователям, то пользователи были подсчитаны. Выводим их на экран
					if(isset($num_users)){
						echo '<table>
									<tr>
										<td class="input">Новых пользователей за сегодня</td>
										<td class="value">'.$num_today_users.'</td>
									</tr>
									<tr>
										<td class="input">Всего пользователей</td>
										<td class="value">'.$num_users.'</td>
									</tr>
						    	</table>';
					}

				?>

			</div>
			
			<!-- Заголовок страницы -->

			<div class="header_content">

				<h2><?php echo $header_content; ?></h2>

				<!-- Форма, позволяющая нам сортировать и выбирать то, что нам нужно -->

				<form action="" method="POST">

					<details class="sorting">

						<!-- Блоки сортировки -->

						<summary>Сортировка</summary>

						<div>

						    <input type="radio" name="sorting" id="articles" <?php if($sorting == 'articles'){ echo 'checked="checked"';} ?> value="articles">

						    <details <?php if($sorting == 'articles'){ echo 'open="open"'; } ?>>

								<summary><label for="articles">Статьи</label></summary>

								<select name="articles_select">
									
									<option <?php if(($sorting == 'articles') && ($articles_select == 'new')){ echo 'selected="selected"'; } ?> value="new">Сначала новые</option>

									<option <?php if(($sorting == 'articles') && ($articles_select == 'old')){ echo 'selected="selected"'; } ?> value="old">Сначала старые</option>

									<option <?php if(($sorting == 'articles') && ($articles_select == 'popular')){ echo 'selected="selected"'; } ?> value="popular">Сначала популярные</option>

									<option <?php if(($sorting == 'articles') && ($articles_select == 'edited')){ echo 'selected="selected"'; } ?> value="edited">Редактируются</option>

									<option <?php if(($sorting == 'articles') && ($articles_select == 'public')){ echo 'selected="selected"'; } ?> value="public">Только опубликованные</option>

									<option <?php if(($sorting == 'articles') && ($articles_select == 'waiting_moderation')){ echo 'selected="selected"'; } ?> value="waiting_moderation">Ожидают модерации</option>

									<option <?php if(($sorting == 'articles') && ($articles_select == 'no_public')){ echo 'selected="selected"'; } ?> value="no_public">Только неопубликованные</option>

								</select>

							</details>

						</div>
						
						<div>

							<input type="radio" name="sorting" id="comments" <?php if($sorting == 'comments'){ echo 'checked="checked"';} ?> value="comments">

							<details <?php if($sorting == 'comments'){ echo 'open="open"'; } ?>>

								<summary><label for="comments">Комментарии</label></summary>

								<select name="comments_select">

									<option <?php if(($sorting == 'comments') && ($comments_select == 'new')){ echo 'selected="selected"'; } ?> value="new">Сначала новые</option>

									<option <?php if(($sorting == 'comments') && ($comments_select == 'old')){ echo 'selected="selected"'; } ?> value="old">Сначала старые</option>

								</select>

							</details>

						</div>
					
						<div>

							<input type="radio" name="sorting" id="users" value="users" <?php if($sorting == 'users'){echo 'checked="checked"';} ?>>

							<details <?php if($sorting == 'users'){ echo 'open="open"'; } ?>>

								<summary><label for="users">Пользователи</label></summary>

								<select name="users_select">

									<option <?php if($sorting == 'users' && $users_select == 'new'){ echo 'selected="selected"'; } ?> value="new">Сначала новые</option>

									<option <?php if($sorting == 'users' && $users_select == 'old'){ echo 'selected="selected"'; } ?> value="old">Сначала старые</option>

								</select>

							</details>

						</div>
						
						<!-- Кнопка "Применить", которая отсортирует и выведет нам, то что мы запросили -->
						<button type="submit" name="apply">Применить</button>

					</details>

				</form>

			</div>
			
			<!-- Основная Секция для вывода запросов -->
			<section class="articles">
					
				<?php

					
					switch($sorting){

						// Если выбрали сортировку по статьям, то сработает следущее
						case 'articles':

							// Подсчёт того, что запросили. Если, руководствуясь нашим запросом, ничего не нашёл, то просто выведет - Нет 'того, что просили' 
							if(($articles_select == 'new' || $articles_select == 'old' || $articles_select == 'popular') && $num_art == 0){
									$no_art = 'Нет статей';
							}
							if($articles_select == 'edited' && $num_edited_art == 0){
								$no_art = 'В данный момент ни одну статью не редактируют';
							}
							if($articles_select == 'public' && $num_pub_art == 0){
								$no_art = 'Нет опубликованных статей';
							}
							if($articles_select == 'waiting_moderation' && $num_wait_moderation_art == 0){
								$no_art = 'Нет статей, ожидающих модерации';
							}
							if($articles_select == 'no_public' && $num_no_pub_art == 0){
								$no_art = 'Нет неопубликованных статей ';
							}

							// Если же что-то нашлось, то делает следущее:
							if(!isset($no_art)){

				?>
					
				<!-- Форма, которая позволит нам изменять статус у статей -->
				<form action="" method="POST" id="form_changes">
					
					<!-- Кнопка сохранения изменений -->
					<div class="save_changes"><button type="submit" name="save_changes" class="save_changes">Сохранить изменения</button></div>

					<?php

						// Выводит всё, что нашел по 16 записей на каждой странице
					 	while($art = mysqli_fetch_assoc($articles)){					 		
						 
					?>

						<!-- Статья -->

					<article>

						<div
							<?php 
								// Определение статуса статьи
								switch($art['status']){
									case 'edited':
										echo 'class="status_edited"';
										break;
									case 'public':
										echo 'class="status_public"';
										break;
									case 'waiting_moderation':
										echo 'class="status_waiting_moderation"';
										break;
									case 'no_public':
										echo 'class="status_no_public"';
										break;
								}
							?>>

							<span>Статус: </span>

							<span>
								
								<!-- Список выбора 1 из 3-х статусов -->
								<select name="<?php echo $art['id']; ?>">

									<?php if($art['status'] != 'edited'){ ?>
										<option <?php if($art['status'] == 'public'){ echo 'selected="selected"'; } ?> value="public">Опубликовано</option>

										<option <?php if($art['status'] == 'waiting_moderation'){ echo 'selected="selected"'; } ?> value="waiting_moderation">Ожидает модерации</option>

										<option <?php if($art['status'] == 'no_public'){ echo 'selected="selected"'; } ?> value="no_public">Неопубликовано</option>

									<?php 

										}

										// Но если статья редактируется, то админ не вправе менять статус этой статье, до того пока её не перестанут редактировать
										else{

									?>
										<!-- Когда статья редактируется, то список состоит лишь из одного варианта статуса - 'Редактируется' -->
										<option selected="selected" value="edited">Редактируется</option>

									<?php

										}

									?>

								</select>

							</span>

						</div>

						<!-- Картинка -->

						<div>
							<a href="../pages/article.php?id=<?php echo $art['id'];?>&category=<?php echo $art['category'];?>">
								<img src="<?php echo '../media/img/'.$art['img'];?>" alt="<?php echo $art['title'];?>">
							</a>
						</div>

						<!-- Заголовок -->

						<h3>
							<a class="header" href="../pages/article.php?id=<?php echo $art['id'];?>
								&category=<?php echo $art['category'];?>"><?php echo $art['title']; ?>
							</a>
						</h3>

						<!-- Категория -->

						<a href="../pages/category.php?category=<?php echo $art['category'];?>" class="category">
							<?php echo $art['category'];?>
						</a>

						<!-- Дата публикации -->

						<span>
							• <?php echo $art['data_creation'];?>
						</span>

						<!-- Количество просмотров -->

						<span>
							•	
							<?php
							// Выводим количество просмотров(Пока фейковых)
							 echo $art['views'].' ';
							 // Пишем условия, которые меняют слово "Просмотров" в зависимости от последней цифры числа
							 $views = $art['views'];
							 $last = strlen($views)-1;
							 if($views{$last} == 1){
							 	echo 'Просмотр';
							 }
							 elseif(($views{$last} == 2)||($views{$last} == 3)||($views{$last} == 4)){
							 	echo 'Просмотра';
							 }
							 else{
							 	echo 'Просмотров';
							 }
							?>
						</span><br>

						<span><?php echo $art['author']; ?></span>

					</article>

					<!-- Конец статьи -->

					<?php

						}

					?>
					
					<!-- Кнопка сохранения статусов -->
					<div class="save_changes"><button type="submit" name="save_changes" class="save_changes">Сохранить изменения</button>

				</form>
				
				<?php

							}

							// Если не нашлось статей, которых мы запрашивали, то выводит сообщение о том, что статей не найдено
							else{
								echo '<h3 class="no_art">'.$no_art.'</h3>';
							}
							break;

						// Если выбрали сортировку по статьям, то сработает следущее
						case 'comments':

							// Подсчёт того, что запросили. Если, руководствуясь нашим запросом, ничего не нашёл, то просто выведет - Нет 'того, что просили' 
							if($num_com == 0){
								$no_com = 'Нет комментариев';
							}

							// Если хоть что-то нашлось, то делает следущее
							if(!isset($no_com)){

							?>

				<!-- Форма, которая позволит нам выбрать и удалить комментарии, которые мы отметим -->
				<form action="../pages/delete_file.php" method="POST">
					
					<!-- Кнопка удаления отмеченных комментариев -->
					<button onclick="return deleteFile();" type="submit" name="delete_com_admin_page" class="delete">Удалить отмеченные</button>

					<?php

						// Выводит всё, что нашел по 16 записей на каждой странице
						while($art = mysqli_fetch_assoc($articles)){

					?>
					
					<!-- Весь коммент -->
					<div class="comment">

						<span>Дата создания комментария: <?php echo $art['data']; ?></span>

						<p class="textarea">
								
							<?php echo $art['text_comment']; ?>

						</p>

						<div class="info_comment">
							
							<!-- Дополнительная, раскрывающаяся при нажатии информация о комментарии -->
							<details>

								<summary>Узнать подробнее о комментарии</summary>

								<table>
									<tr>
										<td class="input">Дата создания:</td>
										<td class="value"><?php echo $art['data']; ?></td>
									</tr>
									<tr>
										<td class="input">Статья:</td>
										<td class="value"><a href="../pages/article.php?id=<?php echo $art['id_article']; ?>&category=<?php echo $art['category_article'];?>"><?php echo $art['title_article']; ?></a></td>
									</tr>
									<tr>
										<td class="input">Автор:</td>
										<td class="value"><?php echo $art['author']; ?></td>
									</tr>
								</table>

							</details>
							
							<!-- Span c checkbox, по которым нужно кликнуть, чтобы выбрать коммент -->
							<span class="delete">
								<label>
									<input type="checkbox" name="<?php echo $art['id']; ?>" value="<?php echo $art['id']; ?>">Удалить
								</label>
							</span>

						</div>

					</div>

					<?php

						}

					?>

					<!-- Кнопка удаления отмеченных комментов -->
				<button onclick="return deleteFile();" type="submit" name="delete_com_admin_page" class="delete">Удалить отмеченные</button>

				</form>			

				<?php

							}

							// Если не нашлось комментариев, то выводит сообщение о том, что комментариев не найдено
							else{
								echo '<h3 class="no_art">'.$no_com.'</h3>';
							}
							break;

						// Если выбрали сортировку по пользователям, то сработает следущее
						case 'users':

							// Выводит всех пользователей по 16 на каждой странице
							while($art = mysqli_fetch_assoc($articles))
								{

				?>
				
				<!-- Блок с информацией о пользователе -->
				<div class="info_profile">

					<h3><?php echo $art['login']; ?></h3>

					<table>
						<tr>
							<td class="input">Зарегистрирован</td>
							<td class="value"><?php echo $art['data'] ?></td>
						</tr>
						<tr>
							<td class="input">Логин</td>
							<td class="value"><?php echo $art['login'] ?></td>
						</tr>
						<tr>
							<td class="input">Адрес электронной почты</td>
							<td class="value"><?php echo $art['email'] ?></td>
						</tr>
						<tr>
							<td class="input">Статус</td>
							<td class="value"><?php echo $art['status'] ?></td>
						</tr>
						<tr>
							<td class="input">Количество публикаций</td>
							<td class="value">
								<?php $number_user_articles = mysqli_query($connection, "SELECT `id` FROM `articles` WHERE `author`='".$art['login']."'");
									$num_user_art = mysqli_num_rows($number_user_articles);
									echo $num_user_art;
								?></td>
						</tr>
						<tr>
							<td class="input">Количество комментариев</td>
							<td class="value">
								<?php $number_user_comments = mysqli_query($connection, "SELECT `id` FROM `comments` WHERE `author`='".$art['login']."'");
									$num_user_com = mysqli_num_rows($number_user_comments);
									echo $num_user_com;
								?></td>
						</tr>
					</table>

				</div>

				<?php

								}
							break;
						}

				?>

			</section>
			
			<!-- Организация пагинации -->
			<div class="pagination">

			<?php
				if($pagination == true){

					if($_GET['page'] > 1)
						{

			?>

				<a class="pagination" href="../pages/admin_page.php?page=<?php echo $_GET['page'] - 1; ?>">&laquo;</a>

			<?php

					}

					foreach($pages as $v){

						if($_GET['page'] == $v+1){
								
			?>

				<a class="pagination selected" href="../pages/admin_page.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php       
								
						}
						elseif(empty($_GET['page']) && $v == 0){

			?>

					<a class="pagination selected" href="../pages/admin_page.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php

						}
						else{

			?>

						<a class="pagination" href="../pages/admin_page.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php     
						}
					}

				if($_GET['page'] != end($pages)+1 && isset($_GET['page'])){

			?>

					<a class="pagination" href="../pages/admin_page.php?page=<?php echo $_GET['page'] + 1; ?>">&raquo;</a>

			<?php

				}
				if(empty($_GET['page'])){

			?>

				<a class="pagination" href="../pages/admin_page.php?page=<?php echo $_GET['page'] + 2; ?>">&raquo;</a>

			<?php

				}
			}

			?>	

			</div>

		</section>
			
			<!-- Подключаем Sidebar -->
		<?php require '../includes/sidebar.php' ?>
			
			<!-- Подключаем Footer -->
		<?php require '../includes/footer.php' ?>

	</div>

</body>
</html>