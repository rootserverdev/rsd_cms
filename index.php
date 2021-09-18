<?php
	include 'db_conf.php';
	include 'default_page_start.php';
	include 'default_page_navigation.php';
	include 'default_page_header.php';
	//if (isset($_GET['a']) & $_GET['a'] <> '') {include $_GET['a'] . '.php';} else {include 'start.php';}
	if (isset($_GET['a']) & $_GET['a'] <> '') { echo build_site($_GET['a']);} else {include 'start.php';}
	 // Hier muss der Content rein...
	include 'default_page_end.php';
?>
	