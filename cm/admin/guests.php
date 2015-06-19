<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/cmbase/util.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

$conn = get_db_connection();
db_require_table('guest_badges', $conn);
db_require_table('guests', $conn);
$badge_names = get_guest_badge_names($conn);
$extension_questions = get_extension_questions('guest', $conn);

if (isset($_POST['start_id'])) {
	header('Content-type: text/plain');
	$guests = array();
	$start_id = (int)$_POST['start_id'];
	$end_id = $start_id;
	$batch_size = 100;
	
	$results = mysql_query('SELECT * FROM '.db_table_name('guests').' WHERE `id` >= '.$start_id.' ORDER BY `id` LIMIT '.$batch_size, $conn);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_guest($result, $badge_names);
		$extension_answers = get_extension_answers('guest', $result['id'], $conn);
		$html_content = render_list_row(
			array_merge(array(
				('GA'.$result['id']),
				$result['guest_name'],
				$result['badge_name'],
				$result['num_supporters'],
			), extension_answer_values_in_list($extension_questions, $extension_answers), array(
				array('html' => application_status_html($result['application_status'])),
				array('html' => contract_status_html($result['contract_status'])),
			)),
			null,
			/*  selectable = */ false,
			/*  switchable = */ false,
			/*      active = */ false,
			/*  deleteable = */ false,
			/* reorderable = */ false,
			/*        edit = */ ('guest.php?id='.$result['id']),
			/*      review = */ false
		);
		$guests[] = array(
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
		'entities' => $guests,
	);
	echo json_encode($response);
	exit(0);
}

render_admin_head('Guest Applications');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'guests.php',
	entityType: 'guest applications',
	progressive: true,
	searchable: true,
	maxResults: 20,
});</script><?php

render_admin_body('Guest Applications');

echo '<div class="card">';
render_list_search('name, badge type, or contact info', 'card-content-only');
echo '</div>';

echo '<div class="card entity-list-card">';
render_list_table(array_merge(
	array('ID', 'Guest Name', 'Badge Type', '# Guests/Supporters'),
	extension_question_names_in_list($extension_questions),
	array('Application Status', 'Contract Status')
), null, 'guest.php', $conn);
echo '</div>';

render_admin_dialogs();
render_admin_tail();