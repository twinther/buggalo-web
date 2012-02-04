<?php
	session_start();

	define('BASEDIR', dirname(__FILE__));

	require_once(BASEDIR.'/libs/lib_mysql.php');
	require_once(BASEDIR.'/session.php');

	new mysql_connection('dbname', 'localhost', 'username', 'password');
	$conn = &mysql_connection::get_instance();
	$conn->open();

