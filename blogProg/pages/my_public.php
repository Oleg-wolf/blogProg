<?php

	// Если не авторизован, то перенаправляет на главную
	
	if(empty($_COOKIE['auth'])||$_COOKIE['auth'] == 'yes'){
		header('Location:../index.php');
		exit();
	}

	// Подключаем файл с подключением к базе данных
	require '../db/db.php';

	$get = false;

	// Если пользователь нажал кнопку редактировать статью, то выполняется следущее
	if(isset($_GET['reply_article']) && isset($_GET['article'])){
		// Берётся всё из этой статьи
		$reply_article = mysqli_query($connection, "SELECT * FROM `articles` WHERE `id` ='".$_GET['article']."' AND `author` = '".urldecode($_COOKIE['user']['login'])."'");
		$reply_art = mysqli_fetch_assoc($reply_article);

		//	Это происходит, если не передан id статьи 
		if(empty($reply_art)){
			header('Location: ../pages/my_public.php');
			exit();
		}
		else{
			// Иначе, статус статьи меняется на 'Редактируется'
			$get = true;
			$edit_message = 'Уважаемый пользователь, максимальное время, которое даётся на редактирование статьи - <b style="font-size:18px;">14 дней</b>. Постарайтесь успеть за это время отредактировать статью и отправить её на модерацию. Если вы не уложитесь за данное время, то статья автоматически удалится с сайта.';
			mysqli_query($connection, "UPDATE `articles` SET `status` = 'edited' WHERE `id` = '".$reply_art['id']."'");
		}
	}

	// Срабатывает, если пользователь отменил редактирование
	if(isset($_POST['no_edit_article'])){
		$data = date('d.m.Y');
		// Если админ, то статус статьи сразу 'public'
		if(strcasecmp($_COOKIE['user']['login'], 'admin') == 0){
			$status = 'public';
		}
		else{
			// Если просто пользователь статус - 'waiting_moderation'(Ждёт модерации)
			$status = 'waiting_moderation';
		}
		if($status == 'public'){
			mysqli_query($connection, "UPDATE `articles` SET `status` = '$status' `data` = '$data' WHERE `id` = '".$reply_art['id']."'");
			header('Location:../pages/my_public.php');
			exit();
		}
		else{
			mysqli_query($connection, "UPDATE `articles` SET `status` = '$status' WHERE `id` = '".$reply_art['id']."'");
			header('Location:../pages/my_public.php');
			exit();
		}
	}

	// Путь к папке, в которую помещаются изображения
	$dir = '../media/img/';

	// Работа с категориями
	$array_cat = array();

	$find_category = mysqli_query($connection, "SELECT `title` FROM `category`");

	while($add_cat = mysqli_fetch_assoc($find_category)['title']){
		ucfirst(mb_convert_case($add_cat, MB_CASE_TITLE, "UTF-8"));
		$array_cat[] = $add_cat;
	}

	// Переданные переменные 
	$checkbox_no_change_img = $_POST['no_change_img'];
	$public_my_public = $_POST['public_my_public'];
	$header_my_public = $_POST['header_my_public'];
	$category_my_public = $_POST['category_my_public'];
	$img_my_public = $_POST['img_my_public'];
	$text_my_public = $_POST['text_my_public'];

	// Если пользователь создал статью и нажал опубликовать, то срабатывает
	if(isset($public_my_public)){
		// Меняем у первых слов первую букву на заглавную
		$header_my_public = mb_strtoupper(mb_substr($header_my_public, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($header_my_public, 1, null, 'UTF-8');
		$category_my_public = mb_strtoupper(mb_substr($category_my_public, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($category_my_public, 1, null, 'UTF-8');
		$text_my_public = mb_strtoupper(mb_substr($text_my_public, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($text_my_public, 1, null, 'UTF-8');

		// Проверяем заголовок на уникальность
		$all_headers = mysqli_query($connection, "SELECT `title` FROM `articles`");

		// Срабатывает, если создаётся статья
		if($get == false){
			while(($all_headers_fetch_assoc = mysqli_fetch_assoc($all_headers))&&(!$error_header)){
				if($header_my_public === $all_headers_fetch_assoc['title']){
					$error_header = ' Статья с таким заголовком уже существует!';
				}
			}
		}
		// Срабатывает, если статья редактируется
		if($get == true){

			while(($all_headers_fetch_assoc = mysqli_fetch_assoc($all_headers))&&(!$error_header)){
				if(($header_my_public === $all_headers_fetch_assoc['title']) && ($header_my_public !== $reply_art['title'])){
					$error_header = ' Статья с таким заголовком уже существует!';
				}
			}
		}
		// Если без ошибок, то проверяем на корректность категории
		if(!$error_header){

			$error = true;
			foreach($array_cat as $cat){
				if($category_my_public === $cat){
					$error = false;
				}
			}

			// Если есть ошибка
			if($error == true){
				$error = ' Выберите категорию';
			}

			// Если без ошибок, то переходим к проверке картинки
			// Код срабатывает, если статья редактируется и пользователь изменил картинку
			// или если статья только создаётся
			elseif(($get == true && $img_my_public != '') || ($get == false)){

				// Проверка корректности изображений и нахождений ошибок
				$file_path = $_FILES['img_my_public']['tmp_name'];
				$error_code = $_FILES['img_my_public']['error'];

				if($error_code !== UPLOAD_ERR_OK || !is_uploaded_file($file_path)){
					$error_messages = [
						UPLOAD_ERR_INI_SIZE => 'Размер файла превысил значение 100M',
		        		UPLOAD_ERR_FORM_SIZE => 'Размер загружаемого файла превысил значение 10M.',
		       			UPLOAD_ERR_PARTIAL => 'Загружаемый файл был получен только частично.',
				        UPLOAD_ERR_NO_FILE => 'Файл не был загружен.',
				        UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка.',
				        UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск.',
				        UPLOAD_ERR_EXTENSION => 'PHP-расширение остановило загрузку файла.'
				    ];
					$unknown_message = 'При загрузке файла произошла неизвестная ошибка.';

					if(isset($error_messages[$error_code])){
						$output_message = $error_messages[$error_code];
					}
					else{
						$output_message = $unknown_message;
					}
					// Останавливает код и выводит ошибку
					die($output_message);
				}
				// Если без ошибок - продолжает работу
				else{
					// Проверяем тип файла и формат
					$fi = finfo_open(FILEINFO_MIME_TYPE);
					$mime = finfo_file($fi, $file_path);

					if(strpos($mime, 'image') === false){
						$error_image = ' Только изображения в формате .jpg ( .jpeg)';
					}
					else{
						if($mime !== 'image/jpeg'){
							$error_image = ' Только изображения в формате .jpg ( .jpeg)';
						}
						// Проверяем размеры картинки
						else{
							$img_size = getimagesize($file_path);
							if((($img_size[0] == 1920 && $img_size[1] == 1080) == false) && (($img_size[0] == 1366 && $img_size[1] == 768) == false) && (($img_size[0] == 1280 && $img_size[1] == 720) == false)){
								$error_img_size = '<br>Допустимые размеры изображений: 1280*720, 1366*768, 1920*1080';
							}
							// Создаём путь для загрузки и копируем файл из временного хранилища
							else{
								$data = date('d.m.Y');
								$uploaddir = $dir;
								$uploadfile = $uploaddir . basename($_FILES['img_my_public']['name']);
								if(move_uploaded_file($file_path, $uploadfile)){
									$file_path = $uploadfile;
								}
								// Если путь правильный и файл скопироан, то создаём публикацию
								if($file_path == $uploadfile){
									if(strcasecmp($_COOKIE['user']['login'], 'admin') == 0){
										$status = 'public';
									}
									else{
										$status = 'waiting_moderation';
									}
									// Заносим публикацию в базу данных, если статья создаётся
									if($get == false){		
										$img_my_public = basename($_FILES['img_my_public']['name']);
										// Если админ
										if($status == 'public'){
											mysqli_query($connection, "INSERT INTO `articles`(`title`, `img`, `category`, `data_creation`, `data_public`, `views`, `author`, `text`, `status`) VALUES('$header_my_public', '$img_my_public', '$category_my_public', '$data', '$data', '0', '".$_COOKIE['user']['login']."', '$text_my_public', '$status')");
											$success = 'Статья успешно опубликована';
										}
										// Если просто авторизованный пользователь
										else{
											mysqli_query($connection, "INSERT INTO `articles`(`title`, `img`, `category`, `data_creation`, `data_public`, `views`, `author`, `text`, `status`) VALUES('$header_my_public', '$img_my_public', '$category_my_public', '$data', '', '0', '".$_COOKIE['user']['login']."', '$text_my_public', '$status')");
											$success = 'Статья отправлена на модерацию! Если статья будет одобрена администрацией сайта, то она автоматически опубликуется. Узнать статус статьи можно ниже';
										}
									}
									// Обновляем статью, если статья редактировалась и пользователь менял изображение
									else{
										$img_my_public = basename($FILES['img_my_public']['name']);
										mysqli_query($connection, "UPDATE `articles` SET `title` = '$header_my_public', `img` = '$img_my_public', `category` = '$category_my_public', `data_public` = '$data', `text` = '$text_my_public', `status` = '$status' WHERE `id` = '".
											$reply_art['id']."'");
										header('Location:../pages/my_public.php');
										exit();
									}
								}
							}
						}
					}
				}
			}

			// Редактируем публикацию, у которой не поменялось изображение
			else{
				$data = date('d.m.Y');

				if(strcasecmp($_COOKIE['user']['login'], 'admin') == 0){
					$status = 'public';
				}
				else{
					$status = 'waiting_moderation';
				}

				mysqli_query($connection, "UPDATE `articles` SET `title` = '$header_my_public', `category` = '$category_my_public', `text` = '$text_my_public', `status` = '$status' WHERE `id` = '".$reply_art['id']."'");
				header('Location:../pages/my_public.php');
				exit();
			}
		}
	}

	// Берём id своих записей
	$my_articles = mysqli_query($connection, "SELECT `id` FROM `articles` WHERE `author` = '".$_COOKIE['user']['login']."'");
	$number_articles = mysqli_num_rows($my_articles);

	// Реализация Пагинации

	if(!empty($number_articles)){

		if($number_articles/6 > 1){
			$pagination = true;
			$pages = array();
			$i = 0;
			$num_articles = $number_articles;
			while($num_articles > 0){
				$num_articles = $num_articles - 6;			
				$pages[$i] = $i;
				$i++; 
			}
		}
		if(($num_art/6 < 1) || (empty($_GET['page']))){
			$my_articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `author` = '".$_COOKIE['user']['login']."' ORDER BY `id` DESC LIMIT 6");
		}
	}
	if(isset($_GET['page'])){
		$page = $_GET['page'] - 1;
		$page = $page * 6;
		$my_articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `author`='".$_COOKIE['user']['login']."' ORDER BY `id` DESC LIMIT 6 OFFSET $page");
		if(($num_articles_this_page = mysqli_num_rows($my_articles)) == 0){
			$page = end($pages);
			$page++;
			header('Location:../pages/my_public.php?page='.$page);
			exit();
		}
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Мои публикации</title>
	<!-- Подключаем стили для страницы index.php и общие стили(стили шапки, подвала, сайдбара. Они везде одинаковые)-->
	<link rel="stylesheet" href="../media/style/style_my_public.css">
	<link rel="stylesheet" href="../media/style/style.css">
</head>
<body>
	
	<!-- Скрипт Согласия) -->
	<script>
		function deleteFile(){
			if(confirm('Вы действительно хотите удалить статью?')){
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
			
			<!-- Выводим в случае успеного добавления статьи -->
			<?php
				if(isset($success))
					{
			?>
	
				<section class="add_comment">
					<div class="comments_prohibited">
						<p><?php echo $success; ?></p>
					</div>
				</section>

			<?php
				}
				// Выводим сообщение о том, что статья редактируется
				if($get == true)
					{
			?>

				<section class="add_comment">
					<div class="comments_prohibited">
						<p><?php echo $edit_message; ?></p>
					</div>
				</section>

			<?php

				}

			?>
			
			<!-- Форма для редактирования/создания статей -->
			<h2><?php if($get === true){ echo 'Редактировать публикацию'; }else{ echo 'Добавить публикацию'; } ?></h2>

			<article class="new_article">

				<form action="" method="POST" enctype="multipart/form-data">

					<input type="hidden" name="MAX_FILE_SIZE" value="10485760">

					<p>Заголовок статьи<span><?php echo $error_header; ?></span></p>

					<input type="text" name="header_my_public" required="required" title="Заголовок статьи" value="<?php if($get === true){ echo $reply_art['title']; }else{ echo $header_my_public; } ?>">

					<p>Выберите категорию статьи <span><?php echo $error; ?></span></p>

					<select name="category_my_public" required="required" title="Выбрать категорию">
						<option disabled="disabled" <?php if($get === false){ echo 'selected="selected"'; } ?>>Выбрать категорию</option>
						<?php
							foreach($array_cat as $cat){

								if($get === true){

									if($reply_art['category'] == $cat){
										echo '<option selected="selected" value='.$cat.'>'.$cat.'</option>';
									}
									else{
										echo '<option value='.$cat.'>'.$cat.'</option>';
									}
								}

								else{
									if($category_my_public == $cat){
										echo '<option selected="selected" value='.$cat.'>'.$cat.'</option>';
									}
									else{
										echo '<option value='.$cat.'>'.$cat.'</option>';
									}
								}
							}
						?>
					</select>

					<p>
						Картинка в формате .jpg(.jpeg)
						<span><?php echo $error_image, $error_img_size; ?></span>
					</p>

					<pre><div class="inputs"><?php if($get === true){ echo '<label><input type="checkbox" name="no_change_img" id="check"><span 						class="no_change_img">Оставить без изменений</span></label><span 			class="or">     или     </span>'; } ?>
							<input type="file" <?php if($get === false){ echo 'required="required"'; } ?> name="img_my_public" accept="image/jpeg" title="Выбрать изображение для статьи">
					</div></pre>

					<p>Текст</p>
					<textarea name="text_my_public" required="required" rows="8" title="Текст статьи" placeholder="Текст статьи"><?php if($get === true){ echo $reply_art['text']; }else{ echo $text_my_public; } ?></textarea>

					<?php 

						// Если редактируем
						if($get === true)
							{

					?>

						<button type="submit" name="public_my_public">Редактировать статью</button>
						<button type="submit" name="no_edit_article">Не редактировать</button>

					<?php

						}
						// Если создаём статью
						else{

					?>
						<button type="submit" name="public_my_public">Опубликовать</button>
					<?php
						}
					?>
				</form>
				
			</article>
			
			<!-- Публикации пользователя -->

			<h2>Ваши публикации <?php echo $number_articles; ?></h2>

			<section class="articles">

				<?php 

					// Если нет публикаций
					if(($num_my_art = mysqli_num_rows($my_articles) == 0)){
						echo '<p>У вас ещё нет статей!</p>';
					}
					// Иначе выводим по 6 на каждой странице
					else{
						while($my_art = mysqli_fetch_assoc($my_articles))
							{
								// Делаем статус у статьи - 'Редактируется', если пользователь редактирует статью
								if($my_art['id'] == $_GET['article']){
									$my_art['status'] = 'edited';
								}

				?>
				
				<!-- Статья пользователя -->

				<article>

					<?php

						// Определение Статуса статьи
						if($my_art['status'] == 'public'){
							echo '<div class="status_public">
									<span class="public">Статус: </span>
									<span class="public">Опубликовано</span>
								  </div>';
						}						
						elseif($my_art['status'] == 'waiting_moderation'){
							echo '<div class="status_waiting_moderation">
									<span class="waiting_moderation">Статус: </span>
									<span class="waiting_moderation">Ожидает модерации</span>
								  </div>';
						}
						elseif($my_art['status'] == 'edited'){
							echo '<div class="status_edited">
									<span class="edited">Статус: </span>
									<span class="edited">Редактируется</span>
								  </div>';
						}
						else{
							echo '<div class="status_no_public">
									<span class="no_public">Статус: </span>
									<span class="no_public">Не опубликовано</span>
								  </div>';
						}

					?>
				
					<!-- Картинка -->
					<div>
						<a href="../pages/article.php?id=<?php echo $my_art['id']; ?>&category=<?php echo $my_art['category']; ?>"><img src="<?php echo '../media/img/'.$my_art['img']; ?>">
						</a>
					</div>

					<h3>
						<a href="../pages/article.php?id=<?php echo $my_art['id']; ?>&category=<?php echo $my_art['category']; ?>"><?php echo $my_art['title']; ?>
						</a>
					</h3>

					<div class="info_art">

						<div>

							<a href="../pages/category.php?category=<?php echo $my_art['category']; ?>" class="category">

								<?php echo $my_art['category']; ?>

							</a>

							<!-- Дата публикации -->
							<span>•</span>

							<span>
								 <?php echo $my_art['data_creation'];?>
							</span>

							<!-- Количество просмотров -->
							<span>•</span>

							<span>
								
								<?php
								// Выводим количество просмотров(Пока фейковых)
								 echo $my_art['views'].' ';
								 // Пишем условия, которые меняют слово "Просмотров" в зависимости от последней цифры числа
								 $views = $my_art['views'];
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
							</span>

						</div>

						<div>

							<span class="edit">

								<a class="edit" href="my_public.php?reply_article&article=<?php echo $my_art['id']; ?>">Редактировать
								</a>

			  				</span>

							<span class="delete">

								<form action="../pages/delete_file.php" method="POST">
									<input type="hidden" name="id" value="<?php echo $my_art['id'];?>">
									<button class="delete" onclick="return deleteFile();" type="submit" name="delete_art">Удалить</button>
								</form>			

							</span>

						</div>

					</div>

				</article>

				<?php

					}
				}
				?>

			</section>
			
			<!-- Организация Пагинации -->

			<div class="pagination">

			<?php
				if(isset($pagination)){

					if($_GET['page'] > 1)
						{

			?>

				<a class="pagination" href="../pages/my_public.php?page=<?php echo $_GET['page'] - 1; ?>">&laquo;</a>

			<?php

					}

					foreach($pages as $v){

						if($_GET['page'] == $v+1){
								
			?>

				<a class="pagination selected" href="../pages/my_public.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php       
								
						}
						elseif(empty($_GET['page']) && $v == 0){

			?>

				<a class="pagination selected" href="../pages/my_public.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php

				}
				else{

			?>

						<a class="pagination" href="../pages/my_public.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php     
						}
					}

				if($_GET['page'] != end($pages)+1 && isset($_GET['page'])){

			?>

					<a class="pagination" href="../pages/my_public.php?page=<?php echo $_GET['page'] + 1; ?>">&raquo;</a>

			<?php

				}
				if(empty($_GET['page'])){

			?>

				<a class="pagination" href="../pages/my_public.php?page=<?php echo $_GET['page'] + 2; ?>">&raquo;</a>

			<?php

				}
			}

			?>	

			</div>
			
		</section>

		<!-- Подключаем Сайдбар -->

		<?php require '../includes/sidebar.php' ?>
			

		<!-- Подключаем Footer -->

		<?php require '../includes/footer.php' ?>

	</div>

</body>

</html>