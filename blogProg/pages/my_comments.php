<?php
	
	// Если не авторизован, то перенаправляет на главную
	if(empty($_COOKIE['auth'])||$_COOKIE['auth'] == 'yes'){
		header('Location:../index.php');
		exit();
	}

	// Подключаем файл с подключением к базе данных
	require '../db/db.php';

	// Берём id комментариев пользователя
	$my_comments = mysqli_query($connection, "SELECT `id` FROM `comments` WHERE `author` = '".$_COOKIE['user']['login']."'");
	$num_comments = mysqli_num_rows($my_comments);
 	
 	// Организация Пагинации
	if(!empty($num_comments)){
		if($num_comments/8 > 1){
			$pagination = true;
			$pages = array();
			$i = 0;
			$num_com = $num_comments;
			while($num_com > 0){
				$num_com = $num_com - 8;			
				$pages[$i] = $i;
				$i++; 
			}
			if(empty($_GET['page'])){
				$my_comments = mysqli_query($connection, "SELECT * FROM `comments` WHERE `author`='".$_COOKIE['user']['login']."' ORDER BY `id` DESC LIMIT 8");
			}
		}
		else{
			$my_comments = mysqli_query($connection, "SELECT * FROM `comments` WHERE `author`='".$_COOKIE['user']['login']."' ORDER BY `id` DESC LIMIT 8");
		}
	}
	if(isset($_GET['page'])){
		$page = $_GET['page'] - 1;
		$page = $page * 8;
		$my_comments = mysqli_query($connection, "SELECT * FROM `comments` WHERE `author`='".$_COOKIE['user']['login']."' ORDER BY `id` DESC LIMIT 8 OFFSET $page");
		if(($num_comments_this_page = mysqli_num_rows($my_comments)) == 0){
			$page = end($pages);
			$page++;
			header('Location:../pages/my_comments.php?page='.$page);
			exit();
		}
	}

	// Подсчёт комментариев за сегодня
	$today_my_comments = mysqli_query($connection, "SELECT `id` FROM `comments` WHERE `author` = '".$_COOKIE['user']['login']."' AND `data` = '".date('d.m.Y')."'");
	$num_today_my_com = mysqli_num_rows($today_my_comments);
	if(empty($num_today_my_com)){
		$num_today_my_com = 0;
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Мои комментарии</title>
	<!-- Подключаем стили для страницы index.php и общие стили(стили шапки, подвала, сайдбара. Они везде одинаковые)-->
	<link rel="stylesheet" href="../media/style/style.css">
	<link rel="stylesheet" href="../media/style/style_my_comments.css">
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

	<!-- Блок wrapper - обёртка для всех частей страницы -->

	<div id="wrapper">
		
		<!-- Подключаем Header -->

		<?php require '../includes/header.php' ?>
		
		<!-- Секция Content - Основная часть сайта -->

		<section class="content">

			<!-- Блок информации о количестве комментариев -->

			<div class="general_info">
				
				<table>
					<tr>
						<td class="input">Комментариев за сегодня</td>
						<td class="value"><?php echo $num_today_my_com; ?></td>
					</tr>
					<tr>
						<td class="input">Всего комментариев</td>
						<td class="value"><?php echo $num_comments; ?></td>
					</tr>
		    	</table>

			</div>

			<!-- Комментарии пользователя -->
		
			<h2>Мои комментарии</h2>

			<section class="articles">
				
				<!-- Форма, которая позволяет удалять комментарии, которые отмечает пользователь -->
				<form action="../pages/delete_file.php" method="POST">
				
				<?php

					// Если есть комментарии, то
					if($num_comments != 0){
					
				?>
					
					<!-- Кнопка удаления отмеченных комментариев -->
					<button onclick="return deleteFile();" type="submit" name="delete_my_com" class="delete">Удалить отмеченные</button>

				<?php

					}

					// Если нет комментариев, то выводит
					if($num_comments == 0){
						echo '<br><h3>У вас ещё нет комментариев!</h3>';
					}
					// Иначе, выводит комментарии пользователя по 8 на каждой странице
					else{

						while($my_com = mysqli_fetch_assoc($my_comments))
							{

				?>

				<div class="comment">
					<span>Дата создания комментария: <?php echo $my_com['data']; ?></span>
					<p class="textarea">
							
						<?php echo $my_com['text_comment']; ?>

					</p>
					<div class="info_comment">
						<!-- Для более подробной информации -->
						<details>

							<summary>Узнать подробнее о комментарии</summary>

							<table>
								<tr>
									<td class="input">Дата создания:</td>
									<td class="value"><?php echo $my_com['data']; ?></td>
								</tr>
								<tr>
									<td class="input">Статья:</td>
									<td class="value"><a href="../pages/article.php?id=<?php echo $my_com['id_article']; ?>&category=<?php echo $my_com['category_article'];?>"><?php echo $my_com['title_article']; ?></a></td>
								</tr>
							</table>

						</details>
						
						<!-- Span c checkbox для выбора комментария -->
						<span class="delete">
							<label>
								<input type="checkbox" name="<?php echo $my_com['id']; ?>" value="<?php echo $my_com['id']; ?>">
								Удалить
							</label>
						</span>
					</div>		
				</div>

				<?php

						}
					}	
						// Если есть комментарии, то выводится кнопка "Удалить отмеченные" 
						if($num_comments != 0){

				?>

				<button onclick="return deleteFile();" type="submit" name="delete_my_com" class="delete">Удалить отмеченные</button>

				<?php } ?>

			</form>
				
			</section>

			<!-- Организация Пагинации -->

			<div class="pagination">

			<?php

				if(isset($pagination)){

					if($_GET['page'] > 1)
						{

			?>

				<a class="pagination" href="../pages/my_comments.php?page=<?php echo $_GET['page'] - 1; ?>">&laquo;</a>

			<?php

					}

					foreach($pages as $v){

						if($_GET['page'] == $v+1){
								
			?>

				<a class="pagination selected" href="../pages/my_comments.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php       
								
						}
						elseif(empty($_GET['page']) && $v == 0){

			?>

					<a class="pagination selected" href="../pages/my_comments.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>
			<?php

					}
					else{

			?>

						<a class="pagination" href="../pages/my_comments.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php
			
						}
					}

				if($_GET['page'] != end($pages)+1 && isset($_GET['page'])){

			?>

					<a class="pagination" href="../pages/my_comments.php?page=<?php echo $_GET['page'] + 1; ?>">&raquo;</a>

			<?php

					}
					if(empty($_GET['page'])){

			?>

					<a class="pagination" href="../pages/my_comments.php?page=<?php echo $_GET['page'] + 2; ?>">&raquo;</a>


			<?php

				}
			}

			?>	

			</div>

		</section>
		
		<!-- Подключаем сайдбар -->

		<?php require '../includes/sidebar.php' ?>
			
		<!-- Подключаем Footer -->

		<?php require '../includes/footer.php' ?>

	</div>

</body>

</html>