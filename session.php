<?php

function session_set_issues($issues) {
	$_SESSION['issues'] = $issues;
}

function session_get_index($id) {
	foreach($_SESSION['issues'] as $idx => $issue) {
		if($issue['id'] == $id) {
			return $idx;	
		}
	}
}

function session_get_previous_item($id) {
	$idx = session_get_index($id);
	if($idx < count($_SESSION['issues'])) {
		return $_SESSION['issues'][$idx + 1];
	}

	return null;
}

function session_get_next_item($id) {
	$idx = session_get_index($id);
	if($idx > 0) {
		return $_SESSION['issues'][$idx - 1];
	}

	return null;
}

