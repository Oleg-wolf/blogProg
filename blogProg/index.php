<?php

	$index_page = true;

	// Подключаем файл с подключением к базе данных

	require 'db/db.php';

	$articles = mysqli_query($connection, "SELECT `id` FROM `articles` WHERE `status` = 'public'");
	$num_art = mysqli_num_rows($articles);

	// Реализация пагинации по сайту

	if(!empty($num_art)){

		// Если есть статьи, то если статей больше 4, то выполняем пагинцию
		if($num_art/4 > 1){

			$pagination = true;
			$pages = array();
			$i = 0;
			$number_art = $num_art;
			while($number_art > 0){
				$number_art = $number_art - 4;			
				$pages[$i] = $i;
				$i++; 
			}

		}

		if(($num_art/4 < 1) || (empty($_GET['page']))||(!isset($_GET['page']))){
			$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'public' ORDER BY `id` DESC LIMIT 4");
		}

	}

	// Если есть GET['page'] с номером страницы, то реализуем пагинцаю со сдвигом

	if(isset($_GET['page'])){

		$page = $_GET['page'] - 1;
		$page = $page * 4;
		$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'public' ORDER BY `id` DESC LIMIT 4 OFFSET $page");

		if(($num_articles_this_page = mysqli_num_rows($articles)) == 0){
			$page = end($pages);
			$page++;
			header('Location:../index.php?page='.$page);
			exit();
		}
		
	}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Блог Программиста</title>
	<!-- Подключаем стили для страницы index.php и общие стили(стили шапки, подвала, сайдбара. Они везде одинаковые)-->
	<link rel="stylesheet" href="/media/style/style_index.css">
	<link rel="stylesheet" href="/media/style/style.css">
</head>
<body>
	
	<!-- Блок wrapper - обёртка для всех частей страницы -->

	<div id="wrapper">
		
		<!-- Подключаем Header -->

		<?php require 'includes/header.php' ?>
		
		<!-- Секция Content - Основная часть сайта -->

		<section class="content">
		
			<h2>Последние записи</h2>

			<section class="articles">
				<?php

					// Подключаемся к базе данных, берём из неё последние 8 статей и выводим их

				 	while($art = mysqli_fetch_assoc($articles))
					 	{					 		
					 
				?>

				<!-- Статья -->

				<article>
					<!-- Картинка -->
					<div>
						<a href="pages/article.php?id=<?php echo $art['id'];?>&category=<?php echo $art['category'];?>">
							<img src="<?php echo '/media/img/'.$art['img'];?>" alt="<?php echo $art['title'];?>">
						</a>
					</div>
					<!-- Заголовок -->
					<h3>
						<a class="header" href="pages/article.php?id=<?php echo $art['id'];?>&category=		<?php echo $art['category'];?>"><?php echo $art['title']; ?>
						</a>
					</h3>
					<!-- Категория -->
					<a href="pages/category.php?category=<?php echo $art['category'];?>" class="category">
						<?php echo $art['category'];?>
					</a>
					<!-- Дата публикации -->
					<span>
						• <?php echo $art['data_public'];?>
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
					</span>
				</article>
				<!-- Конец статьи -->
				<?php

					}


				?>

			</section>
			
		<!-- Блок пагинации по сайту -->

			<div class="pagination">

			<?php

				if(isset($pagination)){

					if($_GET['page'] > 1){

			?>

				<a class="pagination" href="index.php?page=<?php echo $_GET['page'] - 1; ?>">&laquo;</a>

			<?php

					}

					foreach($pages as $v){

						if($_GET['page'] == $v+1){
								
			?>

				<a class="pagination selected" href="index.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php       
								
						}
						elseif(empty($_GET['page']) && $v == 0){

			?>

						<a class="pagination selected" href="index.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php

						}
						else{

			?>
						<a class="pagination" href="index.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php

						}

					}
					
					if($_GET['page'] != end($pages)+1 && isset($_GET['page'])){

			?>

					<a class="pagination" href="index.php?page=<?php echo $_GET['page'] + 1; ?>">&raquo;</a>

			<?php

					}
					if(empty($_GET['page'])){

			?>

					<a class="pagination" href="index.php?page=<?php echo $_GET['page'] + 2; ?>">&raquo;</a>

			<?php

					}

				}

			?>	

			</div>
		</section>
		
		<!-- Подключаем Сайдбар -->

		<?php require 'includes/sidebar.php' ?>
			

			<!-- Подключаем Footer -->
		<?php require 'includes/footer.php' ?>

	</div>

</body>

</html>