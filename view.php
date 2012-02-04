<?php
	require_once('config.php');

	if(isset($_REQUEST['duplicate_of_id'])) {
		$conn->execute('UPDATE addon_exception SET status="DUPLICATE", duplicate_of_id=:duplicate_of_id WHERE id=:id',
			array('id' => $_REQUEST['id'], 'duplicate_of_id' => $_REQUEST['duplicate_of_id']));
	}

	if(isset($_REQUEST['state'])) {
		$conn->execute('UPDATE addon_exception SET status=:status WHERE id=:id', array('id' => $_REQUEST['id'], 'status' => $_REQUEST['state']));	
	}

	$item = $conn->fetch_row("SELECT a.* FROM addon_exception a WHERE a.id=:id", array('id' => $_REQUEST['id']));

	$item['json_string'] = $item['json'];
	if(base64_decode($item['json'])) {
		$item['json_string'] = base64_decode($item['json']);
	}
	$item['json'] = json_decode($item['json_string'], true);
	$item['exception_type'] = htmlentities($item['json']['exception']['type']);
	$item['stacktrace'] = str_replace('    ', '&nbsp;&nbsp;&nbsp;&nbsp;',nl2br(htmlentities(implode($item['json']['exception']['stacktrace']))));
	$item['sys.argv'] = htmlentities('["'.implode('", "', $item['json']['execution']['sys.argv']).'"]');
	$item['country'] = substr(exec('/usr/bin/geoiplookup '.$item['ip']), 23);


	$status = $item['status'];
	if($status == 'DUPLICATE') {
		$status .= ' of <a href="view.php?id='.$item['duplicate_of_id'].'">'.$item['duplicate_of_id'].'</a>';
	}

	$prev = session_get_previous_item($_REQUEST['id']);
	$next = session_get_next_item($_REQUEST['id']);
	if($prev) {
		$prev_item = '<a accesskey="p" class="button" href="view.php?id='.$prev['id'].'"><span style="display: inline-block;" class="ui-icon ui-icon-triangle-1-s"></span>'.$prev['id'].'</a>';
	}
	if($next) {
		$next_item = '<a accesskey="n" class="button" href="view.php?id='.$next['id'].'"><span style="display: inline-block;" class="ui-icon ui-icon-triangle-1-n"></span>'.$next['id'].'</a>';
	}

	$content = <<<HTML
	<h2>ID {$item['id']} - {$item['title']} ({$status})</h2>
	<div style="display: inline-block;">
		<a class="button" href="index.php">&laquo; Back to overview</a>
		Change state to:
		<a class="button" href="view.php?id={$item['id']}&state=OPEN">Open</a>
		<a class="button" href="view.php?id={$item['id']}&state=FIXED">Fixed</a>
		Duplicate of:
		<form method="get" action="view.php">
			<input type="hidden" name="id" value="{$item['id']}" />
			<input id="duplicate_autocomplete" name="duplicate_of_id" value="{$item['duplicate_of_id']}"/>
			<input type="submit" class="button" value="Duplicate" />
		</form>
	</div>
	<div style="float: right;">
		{$next_item}<br />
		{$prev_item}
	</div>
	<table width="100%">
		<tbody>
			<tr><td colspan="2" class="header">Information</td></tr>
			<tr><th>ID</th><td>{$item['id']}</td></tr>
			<tr><th>Date</th><td>{$item['timestamp']}</td></tr>
			<tr><th>End-user Date</th><td>{$item['json']['timestamp']}</td></tr>
			<tr><th>Submitter</th><td>{$item['ip']} ({$item['country']})</td></tr>

			<tr><td colspan="2" class="header">Addon</td></tr>
			<tr><th>ID</th><td>{$item['json']['addon']['id']}</td></tr>
			<tr><th>Name</th><td>{$item['json']['addon']['name']}</td></tr>
			<tr><th>Version</th><td>{$item['json']['addon']['version']}</td></tr>
			<tr><th>Path</th><td>{$item['json']['addon']['path']}</td></tr>
			<tr><th>Profile</th><td>{$item['json']['addon']['profile']}</td></tr>

			<tr><td colspan="2" class="header">Exception</td></tr>
			<tr><th>Type</th><td>{$item['exception_type']}</td></tr>
			<tr><th>Message</th><td>{$item['title']}</td></tr>
			<tr style="background-color: #f0f0f0;"><th>Stacktrace</th><td>{$item['stacktrace']}</td></tr>

			<tr><td colspan="2" class="header">Execution</td></tr>
			<tr><th>Python</th><td>{$item['json']['execution']['python']}</td></tr>
			<tr><th>sys.argv</th><td>{$item['sys.argv']}</td></tr>

			<tr><td colspan="2" class="header">System</td></tr>
			<tr><th>Nodename</th><td>{$item['json']['system']['nodename']}</td></tr>
			<tr><th>Sysname</th><td>{$item['json']['system']['sysname']}</td></tr>
			<tr><th>Release</th><td>{$item['json']['system']['release']}</td></tr>
			<tr><th>Version</th><td>{$item['json']['system']['version']}</td></tr>
			<tr><th>Machine</th><td>{$item['json']['system']['machine']}</td></tr>
			
			<tr><td colspan="2" class="header">XBMC</td></tr>
			<tr><th>Build version</th><td>{$item['json']['xbmc']['buildVersion']}</td></tr>
			<tr><th>Build date</th><td>{$item['json']['xbmc']['buildDate']}</td></tr>
			<tr><th>Language</th><td>{$item['json']['xbmc']['language']}</td></tr>
			<tr><th>Skin</th><td>{$item['json']['xbmc']['skin']}</td></tr>


<!--			<tr>
				<th>Data</th>
				<td>{$item['json_string']}</td>
			</tr>-->
		</tbody>
	</table>

	<script type="text/javascript">
$(document).ready(function() {
	$('.button').button();
	$('#duplicate_autocomplete').autocomplete({
		'source' : function(request, response_callback) {
			jQuery.ajax('autocomplete.json.php', {
					'dataType' : 'json',
					'data' : {
						'query' : request.term
					},
					'success' : function(data, textStatus, jqXHR) {
						console.log(data);
						response_callback(data);
					}
				});

		}
	});
})

	</script>
HTML;

	include('_template.php');
