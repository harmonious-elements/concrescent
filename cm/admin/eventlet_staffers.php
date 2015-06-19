<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

if (isset($_POST['start_id'])) {
	header('Content-type: text/plain');
	$eventlet_staffers = array();
	$start_id = (int)$_POST['start_id'];
	$end_id = $start_id;
	$batch_size = 100;
	
	$conn = get_db_connection();
	db_require_table('eventlet_badges', $conn);
	db_require_table('eventlets', $conn);
	db_require_table('eventlet_staffers', $conn);
	$badge_names = get_eventlet_badge_names($conn);
	$eventlet_info = get_eventlet_info($conn, $badge_names);
	
	$results = mysql_query('SELECT * FROM '.db_table_name('eventlet_staffers').' WHERE `id` >= '.$start_id.' ORDER BY `id` LIMIT '.$batch_size, $conn);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_eventlet_staffer($result, $eventlet_info);
		$html_content = render_list_row(
			array(
				('E'.$result['id']),
				$result['real_name'],
				$result['fandom_name'],
				$result['eventlet_name'],
				$result['badge_name'],
				array('html' => email_link($result['email_address'])),
				($result['attendee_id'] ? 'Yes' : 'No'),
			),
			null,
			/*  selectable = */ false,
			/*  switchable = */ false,
			/*      active = */ false,
			/*  deleteable = */ false,
			/* reorderable = */ false,
			/*        edit = */ ('eventlet_staffer.php?id='.$result['id']),
			/*      review = */ false
		);
		$eventlet_staffers[] = array(
			'id' => $result['id'],
			'search_content' => $result['search_content'],
			'html_content' => $html_content,
		);
		$end_id = $result['id'];
	}
	
	$response = array(
		'start_id' => $start_id,
		'end_id' => $end_id,
		'next_start_id' => $end_id + 1,
		'batch_size' => $batch_size,
		'entities' => $eventlet_staffers,
	);
	echo json_encode($response);
	exit(0);
}

render_admin_head('Panelists/Hosts');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'eventlet_staffers.php',
	entityType: 'panelists and activity hosts',
	progressive: true,
	searchable: true,
	maxResults: 20,
});</script><?php

render_admin_body('Panelists/Hosts');

echo '<div class="card">';
render_list_search('contact name, panel/activity name, or contact info', 'card-content-only');
echo '</div>';

echo '<div class="card entity-list-card">';
render_list_table(array(
	'ID', 'Real Name', 'Fandom Name',
	'Panel/Activity Name', 'Panel/Activity Type',
	'Email Address', 'Already Registered'
), null, 'eventlet_staffer.php', $conn);
echo '</div>';

render_admin_dialogs();
render_admin_tail();