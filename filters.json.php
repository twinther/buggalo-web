<?php
	require_once('config.php');

	$types = array('addon_name', 'addon_version', 'ip', 'status');

	$json = array();
	foreach($types as $type) {
		$rows = $conn->fetch('SELECT DISTINCT '.$type.' FROM addon_exception ORDER BY '.$type);
		foreach($rows as $row) {
			$json[$type][] = $row[$type];
		}
	}

	header('content-type: application/json');
	echo json_encode($json);

