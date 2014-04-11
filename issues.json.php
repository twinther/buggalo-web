<?php
	require_once('config.php');

	$columns = array('id','timestamp','addon_name', 'addon_version', 'title', 'ip', 'status');

	$echo = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : -1;
	$start = isset($_REQUEST['iDisplayStart']) ? intval($_REQUEST['iDisplayStart']) : false;
	$length = isset($_REQUEST['iDisplayLength']) ? intval($_REQUEST['iDisplayLength']) : false;
	$search_query = isset($_REQUEST['sSearch']) ? $_REQUEST['sSearch'] : false;
	$order_by = isset($_REQUEST['iSortCol_0']) ? $_REQUEST['iSortCol_0'] : 0;
	$order_direction = isset($_REQUEST['sSortDir_0']) ? $_REQUEST['sSortDir_0'] : 'desc';

	$columns_search_query = array();
	foreach($columns as $idx => $column) {
		if(isset($_REQUEST['sSearch_'.$idx]) && $_REQUEST['sSearch_'.$idx] != '') {
			$columns_search_query[$column] = $_REQUEST['sSearch_'.$idx];
		}
	}



//	$where = " WHERE status IN ('NEW', 'OPEN') ";
//	if(isset($_REQUEST['show']) && $_REQUEST['show'] == 'all') {
//		$where = '';
//	}

	$row = $conn->fetch_row('SELECT COUNT(*) AS total_records FROM addon_exception');
	$total_records = $row['total_records'];

	$bind = array();
	$sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM addon_exception WHERE 1=1 ' ;
	if($search_query) {
		$bind['search_query'] = '%'.$search_query.'%';
		$sql.= ' AND (';
		foreach($columns as $idx => $column) {
			$sql.= $column.' LIKE :search_query ';
			if($idx < count($columns)-1) {
				$sql.= 'OR ';
			}
		}
		$sql.= ') ';
	}
	foreach($columns_search_query as $column => $query) {
		$bind[$column.'_search_query'] = $query;
		$sql.= 'AND '.$column.'=:'.$column.'_search_query ';
	}
	$sql.= 'ORDER BY '.$columns[$order_by].' '.$order_direction.' ';

	$rows = $conn->fetch($sql, $bind);
	session_set_issues($rows);

	$issues = array();
	if (is_array($rows))
	{
		foreach($rows as $row) {
			$issues[] = array(
				$row['id'],
				$row['timestamp'],
				$row['addon_name'],
				$row['addon_version'],
				$row['title'],
				$row['ip'],
				$row['status']
			);
		}
	}

	$issues_page = array_slice($issues, $start, $length);

	$json = array(
		'sEcho' => $echo,
		'iTotalRecords' => $total_records,
		'iTotalDisplayRecords' => count($issues),
		'aaData' => $issues_page
	);
	header('content-type: application/json');
	echo json_encode((object) $json);

