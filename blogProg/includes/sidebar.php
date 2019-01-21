
<!-- Sidebar . Подключается к каждой странице -->

<aside>

		<h2>Популярные статьи</h2>

		<!-- Секция популярных статей -->

		<section class="popular_articles">
			
			<!-- Подключаемся к базе данных, берём из таблицы `articles` 6 статей с самым большим количеством просмотров и выводим их в сайдбаре -->

			<?php
				$popular_articles = mysqli_query($connection, "SELECT * FROM `articles` WHERE `status` = 'public' ORDER BY `views` DESC LIMIT 6");

				while($pop_art = mysqli_fetch_assoc($popular_articles))
					{
			?>
			<!-- Статья -->
			<article class="popular_articles">
				<!-- Картинка -->
				<div>
					<a href="../pages/article.php?id=<?php echo $pop_art['id']; ?>&category=<?php echo $pop_art['category']?>">
						<img src="<?php echo '../media/img/'.$pop_art['img'];?>" alt="<?php echo $pop_art['title']?>">
					</a>
				</div>
				<!-- Заголовок -->
				<h3>
					<a href="../pages/article.php?id=<?php echo $pop_art['id']; ?>&category=<?php echo $pop_art['category']?>">
						<?php echo $pop_art['title']?>						
					</a>
				</h3>
			</article>

			<?php
				}
			?>

		</section>

		<h2>Реклама</h2>
			
		<!-- Секция Рекламы -->

		<section class="reclame">
			<!-- Реклама -->
			<article class="reclame">
				<!-- Картинка Рекламы -->
				<div>
					<a href="http://yandex.com">
						<img src="/media/img/reclame.png" alt="Реклама">
					</a>
				</div>
				<!-- Заголовок Рекламы -->
				<h3>
					<a href="http://yandex.com">
						Яндекс доставка. Недорого!
					</a>
				</h3>
			</article>
		</section>

</aside>