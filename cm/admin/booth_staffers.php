<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

if (isset($_POST['start_id'])) {
	header('Content-type: text/plain');
	$booth_staffers = array();
	$start_id = (int)$_POST['start_id'];
	$end_id = $start_id;
	$batch_size = 100;
	
	$conn = get_db_connection();
	db_require_table('booth_badges', $conn);
	db_require_table('booths', $conn);
	db_require_table('booth_staffers', $conn);
	$badge_names = get_booth_badge_names($conn);
	$booth_info = get_booth_info($conn, $badge_names);
	
	$results = mysql_query('SELECT * FROM '.db_table_name('booth_staffers').' WHERE `id` >= '.$start_id.' ORDER BY `id` LIMIT '.$batch_size, $conn);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_booth_staffer($result, $booth_info);
		$html_content = render_list_row(
			array(
				('B'.$result['id']),
				$result['real_name'],
				$result['fandom_name'],
				$result['business_name'],
				$result['booth_name'],
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
			/*        edit = */ ('booth_staffer.php?id='.$result['id']),
			/*      review = */ false
		);
		$booth_staffers[] = array(
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
		'entities' => $booth_staffers,
	);
	echo json_encode($response);
	exit(0);
}

render_admin_head('Table Staffers');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'booth_staffers.php',
	entityType: 'table staffers',
	progressive: true,
	searchable: true,
	maxResults: 20,
});</script><?php

render_admin_body('Table Staffers');

echo '<div class="card">';
render_list_search('name, table name, or contact info', 'card-content-only');
echo '</div>';

echo '<div class="card entity-list-card">';
render_list_table(array(
	'ID', 'Real Name', 'Fandom Name', 'Business Name', 'Table Name',
	'Table Type', 'Email Address', 'Already Registered'
), null, 'booth_staffer.php', $conn);
echo '</div>';

render_admin_dialogs();
render_admin_tail();