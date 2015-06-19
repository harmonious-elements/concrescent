<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

if (isset($_POST['start_id'])) {
	header('Content-type: text/plain');
	$guest_supporters = array();
	$start_id = (int)$_POST['start_id'];
	$end_id = $start_id;
	$batch_size = 100;
	
	$conn = get_db_connection();
	db_require_table('guest_badges', $conn);
	db_require_table('guests', $conn);
	db_require_table('guest_supporters', $conn);
	$badge_names = get_guest_badge_names($conn);
	$guest_info = get_guest_info($conn, $badge_names);
	
	$results = mysql_query('SELECT * FROM '.db_table_name('guest_supporters').' WHERE `id` >= '.$start_id.' ORDER BY `id` LIMIT '.$batch_size, $conn);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_guest_supporter($result, $guest_info);
		$html_content = render_list_row(
			array(
				('G'.$result['id']),
				$result['real_name'],
				$result['fandom_name'],
				$result['guest_name'],
				$result['badge_name'],
				array('html' => email_link($result['email_address'])),
			),
			null,
			/*  selectable = */ false,
			/*  switchable = */ false,
			/*      active = */ false,
			/*  deleteable = */ false,
			/* reorderable = */ false,
			/*        edit = */ ('guest_supporter.php?id='.$result['id']),
			/*      review = */ false
		);
		$guest_supporters[] = array(
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
		'entities' => $guest_supporters,
	);
	echo json_encode($response);
	exit(0);
}

render_admin_head('Guests & Supporters');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'guest_supporters.php',
	entityType: 'guests and supporters',
	progressive: true,
	searchable: true,
	maxResults: 20,
});</script><?php

render_admin_body('Guests & Supporters');

echo '<div class="card">';
render_list_search('contact name, guest name, or contact info', 'card-content-only');
echo '</div>';

echo '<div class="card entity-list-card">';
render_list_table(array(
	'ID', 'Real Name', 'Fandom Name', 'Guest Name',
	'Badge Type', 'Email Address'
), null, 'guest_supporter.php', $conn);
echo '</div>';

render_admin_dialogs();
render_admin_tail();