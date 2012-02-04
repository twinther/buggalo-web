<?php
	require_once('config.php');

	$query = $_REQUEST['query'] + '%';
	$rows = $conn->fetch('SELECT id AS value, concat("ID ", id, " - ", title) AS label FROM addon_exception WHERE id LIKE :query OR title LIKE :query', array('query' => $query));
	header('Content-type: application/json');
	echo json_encode($rows);
