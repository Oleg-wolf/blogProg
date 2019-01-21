<?php

	// Подключаем файл с подключением к базе данных
	require '../db/db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Блог Программиста</title>
	<!-- Подключаем стили для страницы index.php и общие стили(стили шапки, подвала, сайдбара. Они везде одинаковые)-->
	<link rel="stylesheet" href="../media/style/style_index.css">
	<link rel="stylesheet" href="../media/style/style.css">
</head>
<body>
	
	<!-- Блок wrapper - обёртка для всех частей страницы -->

	<div id="wrapper">
		
		<!-- Подключаем Header -->

		<?php require '../includes/header.php' ?>

		<!-- Секция Content - Основная часть сайта, где будут выводится найденные статьи -->

		<section class="content">

			<h2>Поиск по запросу: <?php echo $_GET['search'] ?></h2><!-- Выводим запрос пользователя -->

			<section class="articles">

				<?php

					// Проверяем, ввёл ли пользователь запрос. Если ввёл и нажал кнопку "Найти", то выводит все статьи, в заголовках которых встречается фраза из запроса пользователя. Если статей, удовлетворяющих запросу, нет, то выводит сообщение "По запросу:'запрос' ничего не найдено!". Если пользователь пытается найти пустой запрос, то выводит "Вы ничего не ввели!"

					$search_articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'public' ORDER BY `id` DESC");

					if($_GET['search'] != ''){

						$i = 0;

						while($search_art = mysqli_fetch_assoc($search_articles)){

							if(mb_stristr($search_art['title'], $_GET['search']) == true){

								$i++;

				?>
				<!-- Статья -->
				<article>
					<!-- Картинка статьи -->
					<div>
						<a href="article.php?id=<?php echo $search_art['id'];?>&category=<?php echo $search_art['category'];?>">
							<img src="<?php echo '../media/img/'.$search_art['img'];?>" alt="<?php echo $search_art['title'];?>">
						</a>
					</div>
					<!-- Заголовок статьи -->
					<h3>
						<a class="header" href="article.php?id=<?php echo $search_art['id'];?>&category=<?php echo $search_art['category'];?>"><?php echo $search_art['title']; ?>
						</a>
					</h3>
					<!-- Категория статьи -->
					<a href="category.php?category=<?php echo $search_art['category']; ?>" class="category">
						<?php echo $search_art['category'];?>
					</a>
					<!-- Дата публикации -->
					<span>
						• <?php echo $search_art['data_public'];?>
					</span>
					<!-- Количество просмотровы -->
					<span>
						•	
						<?php
						// Выводим количество просмотров(Пока фейковых)
						 echo $search_art['views'].' ';
						 // Пишем условия, которые меняют слово "Просмотров" в зависимости от последней цифры числа
						 $views = $search_art['views'];
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
				}
				
				// Если ничего не нашёл, то выводит это сообщение!
				if($i == 0){
					echo "<p>По запросу ".'"'.$_GET['search'].'"'." ничего не найдено!</p>";
				}

			}
			// Иначе значит, что запрос пустой, поэтому выводит этот запрос
			else{
				echo "<p>Вы ничего не ввели!</p>";
			}
				?>

			</section>

		</section>
		
		<!-- Подключаем Сайдбар -->

		<?php require '../includes/sidebar.php' ?>
			
		<!-- Подключаем Footer -->

		<?php require '../includes/footer.php' ?>

	</div>

</body>

</html>