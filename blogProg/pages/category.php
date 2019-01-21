<?php
	
	// Подключаем файл с подключением к базе данных
	require '../db/db.php';

	// Берём id статей из данной категории
	$articles = mysqli_query($connection, "SELECT `id` FROM `articles` WHERE `category` = '".$_GET['category']."' AND `status` = 'public'");
	$num_articles = mysqli_num_rows($articles);

	// Организация Пагинации
	if(!empty($num_articles)){

		if($num_articles/8 > 1){
			$pagination = true;
			$pages = array();
			$i = 0;
			$num_art = $num_articles;
			while($num_art > 0){
				$num_art = $num_art - 8;			
				$pages[$i] = $i;
				$i++; 
			}
		}
		if(($num_art/8 < 1) || (empty($_GET['page']))){
		$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `category` = '".$_GET['category']."' AND `status` = 'public' ORDER BY `id` DESC LIMIT 8");
		}
	}
	if(isset($_GET['page'])){
		$page = $_GET['page'] - 1;
		$page = $page * 8;
		$articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `category` = '".$_GET['category']."' AND `status` = 'public' ORDER BY `id` DESC LIMIT 8 OFFSET $page");
		if(($num_articles_this_page = mysqli_num_rows($articles)) == 0){
			$page = end($pages);
			$page++;
			header('Location:../pages/category.php?page='.$page);
			exit();
		}
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $_GET['category'];?></title>
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
			
			<!-- Выводим название категории в заголовке -->
			<h2>Категория: <?php echo $_GET['category'];?></h2>

			<section class="articles">
				<?php

					// Подключаемся к базе данных, берём из неё 8 статей из данной категории и выводим их на странице

					while ($art = mysqli_fetch_assoc($articles))
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
					<a href="../pages/category.php?category=<?php echo $_GET['category'];?>" class="category">
						<?php echo $art['category'];?>
					</a>
					<!-- Дата публикации -->
					<span>
						• <?php echo $art['data'];?>
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
			
			<!-- Организация Пагинации -->

			<div class="pagination">
	
			<?php

				if(isset($pagination)){

					if($_GET['page'] > 1)
						{

			?>

				<a class="pagination" href="../pages/category.php?page=<?php echo $_GET['page'] - 1; ?>">&laquo;</a>

			<?php

					}

					foreach($pages as $v){

						if($_GET['page'] == $v+1){
								
			?>

				<a class="pagination selected" href="../pages/category.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php       
								
						}
						else{

			?>

						<a class="pagination" href="../pages/category.php?page=<?php echo $v+1; ?>"><?php echo $v+1; ?></a>

			<?php     
						}
					}

				if($_GET['page'] != end($pages)+1){

			?>

					<a class="pagination" href="../pages/category.php?page=<?php echo $_GET['page'] + 1; ?>">&raquo;</a>

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