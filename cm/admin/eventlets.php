<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/cmbase/util.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

$conn = get_db_connection();
db_require_table('eventlet_badges', $conn);
db_require_table('eventlets', $conn);
$badge_names = get_eventlet_badge_names($conn);
$extension_questions = get_extension_questions('eventlet', $conn);

if (isset($_POST['start_id'])) {
	header('Content-type: text/plain');
	$eventlets = array();
	$start_id = (int)$_POST['start_id'];
	$end_id = $start_id;
	$batch_size = 100;
	
	$results = mysql_query('SELECT * FROM '.db_table_name('eventlets').' WHERE `id` >= '.$start_id.' ORDER BY `id` LIMIT '.$batch_size, $conn);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_eventlet($result, $badge_names);
		$extension_answers = get_extension_answers('eventlet', $result['id'], $conn);
		$html_content = render_list_row(
			array_merge(array(
				('EA'.$result['id']),
				$result['eventlet_name'],
				$result['badge_name'],
				$result['num_staffers'],
			), extension_answer_values_in_list($extension_questions, $extension_answers), array(
				array('html' => application_status_html($result['application_status'])),
				array('html' => payment_status_html($result['payment_status'])),
				($result['payment_date'] ? $result['payment_date'] : 'never'),
			)),
			null,
			/*  selectable = */ false,
			/*  switchable = */ false,
			/*      active = */ false,
			/*  deleteable = */ false,
			/* reorderable = */ false,
			/*        edit = */ ('eventlet.php?id='.$result['id']),
			/*      review = */ false
		);
		$eventlets[] = array(
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
		'entities' => $eventlets,
	);
	echo json_encode($response);
	exit(0);
}

render_admin_head('Panel/Activity Applications');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'eventlets.php',
	entityType: 'panel/activity applications',
	progressive: true,
	searchable: true,
	maxResults: 20,
});</script><?php

render_admin_body('Panel/Activity Applications');

echo '<div class="card">';
render_list_search('name, badge type, contact info, or transaction ID', 'card-content-only');
echo '</div>';

echo '<div class="card entity-list-card">';
render_list_table(array_merge(
	array('ID', 'Panel/Activity Name', 'Panel/Activity Type', '# Panelists/Hosts'),
	extension_question_names_in_list($extension_questions),
	array('Application Status', 'Payment Status', 'Payment Date')
), null, 'eventlet.php', $conn);
echo '</div>';

render_admin_dialogs();
render_admin_tail();