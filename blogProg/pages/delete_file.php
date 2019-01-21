<?php
	
	// Подключение к базе данных
	$connection = mysqli_connect('localhost','root','','BlogProg');

		// Если нажали кнопку "Удалить статью" во вкладке 'Мои публикации', то срабатывает:
		if(isset($_POST['delete_art'])){

			mysqli_query($connection, "DELETE FROM `articles` WHERE `id` ='".$_POST['id']."'");
			header('Location:../pages/my_public.php');
			exit();

		}

		// Если нажали кнопку "Удалить комментарий к статье" у определённой статьи, то срабатывает:
		if(isset($_POST['delete_com_art'])){

			mysqli_query($connection, "DELETE FROM `comments` WHERE `id` ='".$_POST['id']."'");
			header('Location:..'.urldecode($_COOKIE['page']));
			exit();

		}

		// Если нажали кнопку "Удалить отмеченные комментарии" во вкладке 'Мои комментарии', то срабатывает:
		if(isset($_POST['delete_my_com'])){

			$all_id_comments = mysqli_query($connection, "SELECT `id` FROM `comments` WHERE `author` ='".$_COOKIE['user']['login']."'");

			while($all_id_com = mysqli_fetch_assoc($all_id_comments)['id']){

				if(isset($_POST[$all_id_com])){

					mysqli_query($connection, "DELETE FROM `comments` WHERE `id` ='".$_POST[$all_id_com]."'");

				}
				
			}

			header('Location:../pages/my_comments.php');
			exit();

		}

		// Если нажали кнопку "Удалить отмеченные комментарии" в Админке, то срабатывает:
		if(isset($_POST['delete_com_admin_page'])){

			$all_id_comments = mysqli_query($connection, "SELECT `id` FROM `comments`");

			while($all_id_com = mysqli_fetch_assoc($all_id_comments)['id']){

				if(isset($_POST[$all_id_com])){

					mysqli_query($connection, "DELETE FROM `comments` WHERE `id` ='".$_POST[$all_id_com]."'");

				}
				
			}

			header('Location:../pages/admin_page.php');
			exit();
			
		}

?>


