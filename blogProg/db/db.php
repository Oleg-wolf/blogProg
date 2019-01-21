<?php
	
	// При каждом заходе почти на любую страницу на сайте создаются cookie со значением $_SERVER['REQUEST_URI'], которая позволяет нам переходить на страницы по coookie, и cookie соз значением времени
	setcookie('page', $_SERVER['REQUEST_URI'], (time()+(30*24*60*60)), '/');
	setcookie('last', date('d.m.Y H:i:s'), 0, 'blogProg');

	// Определяет авторизоан ли пользоатель или нет
	if(isset($_COOKIE['user']['login']) && $_COOKIE['auth'] != 'yes'){
		$exit = 'no';
		setcookie('auth', $exit, (time()+(30*24*60*60)), '/');
	}

	// Записываем в переменную $connection подключение к базе данных

	$connection = mysqli_connect('localhost','root','','BlogProg');

	// Если не удалось подключится к базе, то выводит mysqli_connect_error(), и прекращает работу скрипта

	if(!$connection){
		echo mysqli_connect_error();
		exit();
	}

?>