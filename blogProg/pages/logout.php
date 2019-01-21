<?php
	
	// Выход из профиля

	$exit = 'yes';
	header('Location:'. urldecode($_COOKIE['page']));
	setcookie('auth', $exit, (time()+(30*24*60*60)), '/');
	exit();

?>