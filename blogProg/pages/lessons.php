<?php

	// Подключаем файл с подключением к базе данных
	require '../db/db.php';

	$articles = mysqli_query($connection, "SELECT `id` FROM `articles` WHERE `status` = 'public'");
	$num_art = mysqli_num_rows($articles);

	$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'public' ORDER BY `id` DESC LIMIT 8");

	if(!empty($num_art)){
		if(($num_art/8) > 0){
			$pagination = true;
			$pages = array();
			$i = 0;
			while($num_art > 0){
				$num_art = $num_art - 8;			
				$pages[$i] = $i;
				$i++; 
			}
		}
	}
	if(isset($_GET['page'])){
		$page = $_GET['page'] - 1;
		$page = $page * 8;
		$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'public' ORDER BY `id` DESC LIMIT 8 OFFSET $page");
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Уроки</title>
	<!-- Подключаем стили для страницы index.php и общие стили(стили шапки, подвала, сайдбара. Они везде одинаковые)-->
	<link rel="stylesheet" href="../media/style/style_index.css">
	<link rel="stylesheet" href="../media/style/style.css">
</head>
<body>
	
	<!-- Блок wrapper - обёртка для всех частей страницы -->

	<div id="wrapper">
		
		<!-- Подключаем Header -->

		<?php require '../includes/header.php' ?>
		
		<!-- Секция Content - Основная часть сайта -->

		<section class="content">
		
			<h2>Уроки</h2>

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
						<a href="../pages/article.php?id=<?php echo $art['id'];?>&category=<?php echo $art['category'];?>">
							<img src="<?php echo '../media/img/'.$art['img'];?>" alt="<?php echo $art['title'];?>">
						</a>
					</div>
					<!-- Заголовок -->
					<h3>
						<a class="header" href="../pages/article.php?id=<?php echo $art['id'];?>&category=<?php echo $art['category'];?>"><?php echo $art['title']; ?>
						</a>
					</h3>
					<!-- Категория -->
					<a href="../pages/category.php?category=<?php echo $art['category'];?>" class="category">
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
				
			<?php

				if(isset($pagination)){

					foreach($pages as $v){

			?>		
						<a href="../pages/lessons.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>
			<?php

					}
				}

			?>
				
		</section>
		
		<!-- Подключаем Сайдбар -->

		<?php require '../includes/sidebar.php' ?>
			

			<!-- Подключаем Footer -->
		<?php require '../includes/footer.php' ?>

	</div>

</body>

</html>