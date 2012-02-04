<?php
	require_once('config.php');

	$data = json_decode($HTTP_RAW_POST_DATA);

	$bind = array(
		'addon_name' => $data->addon->name,
		'addon_version' => $data->addon->version,
		'title' => $data->exception->value,
		'json' => base64_encode($HTTP_RAW_POST_DATA),
		'ip' => $_SERVER['REMOTE_ADDR']
	);

	$conn->execute('INSERT INTO addon_exception(addon_name, addon_version, title, json, ip) VALUES(:addon_name, :addon_version, :title, :json, :ip)', $bind);
