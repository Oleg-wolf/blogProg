<?php
 	
 	// Подключаем файл с подключением к базе данных

 	require '../db/db.php';

 	// Берём всю информацию по статье, ID которой совпадает с ID, переданным в глобальном массиве GET

 	if(empty($_GET['id'])||empty($_GET['category'])){
 		header('Location: ../index.php');
 		exit();
 	}

 	$article = mysqli_query($connection, "SELECT * FROM `articles` WHERE `id`=".$_GET['id']);
 	$art = mysqli_fetch_assoc($article);

 	if(empty($art)){
 		header('Location: ../index.php');
 		exit();
 	}

 	if($_GET['category'] != $art['category']){
 		header('Location: ../index.php');
 		exit();
 	}

 	if((strcasecmp($_COOKIE['user']['login'], 'admin') != 0) || $_COOKIE['user']['status'] != 'admin'){
 		if($art['status'] != 'public' && $art['author'] != $_COOKIE['user']['login']){
 			header('Location: ../index.php');
 			exit();
 		}
	}
	if(isset($_GET['reply_comment']) && isset($_GET['comment'])){
		$all_info_comment = mysqli_query($connection, "SELECT * FROM `comments` WHERE `id` = '".$_GET['comment']."'");
		$all_info_com = mysqli_fetch_assoc($all_info_comment);
		if($_GET['id'] != $all_info_com['id_article']){
			header('Location:../pages/article.php?id='.$_GET['id'].'&category='.$_GET['category']);
			exit();
		}
		if($all_info_com['author'] != urldecode($_COOKIE['user']['login'])){
			header('Location:../pages/article.php?id='.$_GET['id'].'&category='.$_GET['category']);
			exit();
		}
	}

 	if(isset($_POST['send_comment'])){
		$text_comment = trim($_POST['text_comment']);
		if($text_comment == ''){
			$error = 'Введите комментарий!';
		}	
		else{
			$data = date('d.m.Y');
			mysqli_query($connection, "INSERT INTO `comments` (`text_comment`, `id_article`, `title_article`, `category_article`, `data`, `author`) VALUES('$text_comment','".$_GET['id']."', '".$art['title']."', '".$_GET['category']."', '".$data."', '".$_COOKIE['user']['login']."')");
			header('Location:article.php?id='. $_GET['id'] . '&category='. $_GET['category']);
			exit();
		}
	}
	if(isset($_POST['edit_comment'])){
		$text_edit_comment = trim($_POST['text_edit_comment']);
		if($text_edit_comment == ''){
			$error = 'Введите комментарий!';
		}	
		else{
			mysqli_query($connection, "UPDATE `comments` SET `text_comment`='$text_edit_comment' WHERE `id`='".$_GET['comment']."'");
			header('Location:../pages/article.php?id='. $_GET['id'] . '&category='. $_GET['category']);
			
		}
	}
	if(isset($_POST['no_edit_comment'])){
		header('Location:article.php?id='. $_GET['id'] . '&category='. $_GET['category']);
		
	}

 ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $art['title'];?></title><!-- Заголовок статьи -->
	<!-- Подключаем стили для страницы article.php и общие стили(стили шапки, подвала, сайдбара. Они везде одинаковые)-->
	<link rel="stylesheet" href="../media/style/style.css">
	<link rel="stylesheet" href="../media/style/style_article.css">
</head>
<body>
	
	<script>
		function deleteFile(){
			if(confirm('Вы действительно хотите удалить комментарий?')){
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

		<?php require '../includes/header.php'; ?>
		
		<!-- Блок Content - Основная часть сайта -->

		<div class="content">

			<!-- Статья -->

			<article class="article">
				<!-- Заголовок -->
				<h2 class="header">
					<?php echo $art['title'];?>
				</h2>
				<!-- Картинка -->
				<div class="img_art">
					<img src="../media/img/<?php echo $art['img']; ?>" alt="<?php echo $art['title']; ?>" class="img_art">
				</div>
				<!-- Текст -->
				<div class="text_art">
					<p>
						<?php echo $art['text']; ?>
					</p>
				</div>
			</article>

			<?php

				// Подключаемся к базе данных и берём 4 последних опубликованных статьи(кроме этой) с такой же категорией, как и у данной статьи. Если такие есть, то образуется section class="similar_publications" с этими статьями 

				$similar_publications = mysqli_query($connection, "SELECT * FROM `articles` WHERE `category` ='".$_GET['category']."' AND `id` != '".$_GET['id']."' AND `status` = 'public' ORDER BY `views` DESC LIMIT 4");
				if(($number_similar_public = mysqli_num_rows($similar_publications)) != 0){

			?>

			<section class="similar_publications">
				<h2>Похожие публикации</h2>

				<?php

					while($sim_public = mysqli_fetch_assoc($similar_publications))
						{

				?>
				<!-- Статья -->
				<article class="similar_publication">
					<!-- Картинка -->
					<div class="img_similar_art">
						<a href="article.php?id=<?php echo $sim_public['id'];?>&category=<?php echo $sim_public['category'];?>">
							<img src="<?php echo '../media/img/'. $sim_public['img']; ?>" alt="<?php echo $sim_public['title'] ?>">
						</a>
					</div>
					<!-- Заголовок -->
					<div class="info_similar_art">
						<h3>
							<a href="article.php?id=<?php echo $sim_public['id']?>&category=<?php echo $sim_public['category'];?>">
								<?php echo $sim_public['title'];?>
							</a>
						</h3>
						<!-- Категория -->
						<a href="category.php?category=<?php echo $sim_public['category'];?>" class="category">
							<?php echo $sim_public['category'];?>
						</a>
						<!-- Дата публикация -->
						<span>
							• <?php echo $sim_public['data_public'];?>
						</span>
						<!-- Количество просмотров -->
						<span>
							•
							<?php
							// Выводим число просмотров(Пока фейковых)
							 echo $sim_public['views'].' ';
							 // Пишем условия, которые меняют слово "Просмотров" в зависимости от последней цифры числа
							 $views = $sim_public['views'];
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

				</article>
				<!-- Конец похожей статьи -->
						<?php 
							
							}

						?>

			</section>
			<!-- Конец секции похожих статей -->
					<?php

						}

					?>
			
			<!-- Секция комментариев -->

			<section class="comments">

				<?php

					$all_comments = mysqli_query($connection, "SELECT `id` FROM `comments` WHERE `id_article` = '".$_GET['id']."'");
					$num_all_com = mysqli_num_rows($all_comments);

					if(empty($_GET['more'])){
						$comments = mysqli_query($connection, "SELECT * FROM `comments` WHERE `id_article` = '".$_GET['id']."' ORDER BY `id` DESC LIMIT 4");
						$num_com = mysqli_num_rows($comments);
					}
					else{
						$comments = mysqli_query($connection, "SELECT * FROM `comments` WHERE `id_article` = '".$_GET['id']."' ORDER BY `id` DESC LIMIT ".($_GET['more']+4)."");
						$num_com = mysqli_num_rows($comments);
					}

					// Если пользователь не авторизован, то он не имеет права оставлять комментарии
					if(!isset($_COOKIE['user']['login'])||$_COOKIE['auth'] == 'yes')
						{

				?>
				
				<!-- Выводится тому, кто зарегистрирован -->
				<h2>Комментарии <?php echo $num_all_com; ?></h2>
				<section class="add_comment">
					<div class="comments_prohibited">
						<p>Оставлять и редактировать комментарии доступно только авторизованным пользователям. <a href="../pages/authorization.php">Войти в аккаунт</a></p>
					</div>
				</section>

				<?php 
			
					}

					// Если зарегистрирован, то:
					else{

						// Если нажали кнопку "Редактировать комментарий, то вместо формы добавления комментариев появляется форма редактирования комментария:"
						if(isset($_GET['reply_comment']) && isset($_GET['comment'])){

							if($all_info_com['author'] == urldecode($_COOKIE['user']['login']) && $_GET['id'] == $all_info_com['id_article']){

								$edit_comment = mysqli_query($connection, "SELECT `text_comment` FROM `comments` WHERE `id` = '".$_GET['comment']."'");
								$edit_com = mysqli_fetch_assoc($edit_comment);

				?>

				<h2>Редактировать комментарий<span class="error"><?php echo $error; ?></span></h2>

				<!-- Секция формы добавления комментариев -->
				<section class="add_comment">

					<!-- Аватарка -->
					<div class="avatar_commentator">
						<img src="../media/img/avatar.png" alt="">
					</div>

					<!-- Форма редактирования комментариев -->
					<form action="" method="POST">

						<textarea rows="8" name="text_edit_comment"><?php echo $edit_com['text_comment']; ?></textarea>
						<button type="submit" name="edit_comment">Редактировать комментарий</button>
						<button type="submit" name="no_edit_comment">Не редактировать</button>

					</form>

				</section>

				<h2>Комментарии <?php echo $num_all_com; ?></h2>

				<?php

								}
							}
							else{

				?>

				<h2>Комментарии <?php echo $num_all_com; ?><span class="error"><?php echo $error; ?></span>

					<span class="success"><?php echo $message_edit_comment; ?></span></h2>

				<!-- Секция формы добавления комментариев -->
				<section class="add_comment">

					<!-- Аватарка -->
					<div class="avatar_commentator">
						<img src="../media/img/avatar.png" alt="">
					</div>

					<!-- Форма добавления комментариев -->
					<form action="" method="POST">

						<textarea placeholder="Присоединиться к обсуждению..." rows="8" name="text_comment"></textarea>
						<button type="submit" name="send_comment">Отправить комментарий</button>

					</form>

				</section>

				<?php

							}
						}

					// Подключаемся к базе данных и берём все комментарии, принадлежащие этой статье(для записи номера статьи, к которой принадлежит комментарий, в таблице 'comments' есть специальный столбец `id_article`, в который, при добавлении комментария в таблицу, добавляются номера соответствующих статей)

					while($com = mysqli_fetch_assoc($comments))
						{

				?>
				<!-- Комментарий -->
				<article class="comment">

					<!-- Секция "О комментрарии" -->
					<section class="about_comment">

						<div class="info_comment">

							<!-- Аватарка -->
							<div class="avatar_commentator">
								<img src="../media/img/avatar.png" alt="">
							</div>

							<!-- Имя отправителя -->
							<span class="name_commentator">
								<pre><?php echo $com['author'];?>   •</pre>
							</span>

							<!-- Дата отправления -->
							<span class="data_comment">
								<pre>   <?php echo $com['data']; ?></pre>
							</span>

						</div>
						
						<!-- Блок управления комментарием -->
						<div class="settings_comment">

							<?php
								
								// Если пользователь авторизован и является автором этого комментария, то он имеет право редактировать и удалять комментарий

								if($_COOKIE['auth'] == 'no' && $com['author'] == urldecode($_COOKIE['user']['login'])){

									// Во время редактирования
									if((isset($_GET['reply_comment'])) && ($_GET['comment'] == $com['id'])){
									
							?>

							<span>

								<a class="edit" href="article.php?id=<?php echo $_GET['id']; ?>     		&category=<?php echo $_GET['category']; ?>&reply_comment&comment=<?php echo $com['id']; ?>">	Редактировать
								</a>

				  			</span>

							<?php

									}

									// Когда уже не радактируется
									else{

							?>

							<span>
								<a href="article.php?id=<?php echo $_GET['id'];?>&category=<?php echo $_GET['category'];?>&reply_comment&comment=<?php echo $com['id']; ?>" class="edit">Редактировать</a>
							</span>

							<?php 

									}

							?>

							<span class="delete">
							
								<!-- Форма удаления комментария, с помощью которой, мы можем удалять свои комментарии -->
								<form action="../pages/delete_file.php" method="POST">

									<input type="hidden" name="id" value="<?php echo $com['id']; ?>">

									<button onclick="return deleteFile();" type="submit" name="delete_com_art" class="delete">Удалить</button>

								</form>

							</span>

							<?php

								}
								
							?>

						</div>

					</section>

					<!-- Текст комментария -->
					<article class="text_comment">

						<p>
							<?php echo $com['text_comment'];?>
						</p>

					</article>

				</article>

				<!-- Конец комментария -->
				
				<?php

					}

				?>

				<?php

				if($num_all_com > $_GET['more'] && $num_all_com > 4 && $num_all_com - $_GET['more'] > 4){

			?>

			<a class="more" href="../pages/article.php?id=<?php echo $_GET['id'];?>&category=<?php echo $_GET['category'];?>&more=<?php echo $num_com; ?>">Показать ещё</a>

			<?php

				}
				if($num_all_com > 4 && $num_all_com - $_GET['more'] < 4){


			?>

			<a class="more" href="../pages/article.php?id=<?php echo $_GET['id'];?>&category=<?php echo $_GET['category'];?>">Скрыть комментарии</a>

			<?php

				}

			?>

			</section>

		</div>
		
		<!-- Подключаем Сайдбар -->

		<?php require '../includes/sidebar.php';?>

		<!-- Подключаем Footer -->
		
		<?php require '../includes/footer.php';?>

	</div>

</body>
</html>