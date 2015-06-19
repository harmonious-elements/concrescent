<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/cmbase/util.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

$conn = get_db_connection();
db_require_table('staffer_badges', $conn);
db_require_table('staffers', $conn);
$badge_names = get_staffer_badge_names($conn);
$extension_questions = get_extension_questions('staffer', $conn);

if (isset($_POST['start_id'])) {
	header('Content-type: text/plain');
	$staffers = array();
	$start_id = (int)$_POST['start_id'];
	$end_id = $start_id;
	$batch_size = 100;
	
	$results = mysql_query('SELECT * FROM '.db_table_name('staffers').' WHERE `id` >= '.$start_id.' ORDER BY `id` LIMIT '.$batch_size, $conn);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_staffer($result, $badge_names);
		$extension_answers = get_extension_answers('staffer', $result['id'], $conn);
		$html_content = render_list_row(
			array_merge(array(
				('S'.$result['id']),
				$result['real_name'],
				$result['fandom_name'],
				$result['badge_name'],
				array('html' => email_link($result['email_address'])),
			), extension_answer_values_in_list($extension_questions, $extension_answers), array(
				array('html' => application_status_html($result['application_status'])),
				$result['assigned_position'],
				array('html' => payment_status_html($result['payment_status'])),
				($result['payment_date'] ? $result['payment_date'] : 'never'),
			)),
			null,
			/*  selectable = */ false,
			/*  switchable = */ false,
			/*      active = */ false,
			/*  deleteable = */ false,
			/* reorderable = */ false,
			/*        edit = */ ('staffer.php?id='.$result['id']),
			/*      review = */ false
		);
		$staffers[] = array(
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
		'entities' => $staffers,
	);
	echo json_encode($response);
	exit(0);
}

render_admin_head('Staff Applications');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'staffers.php',
	entityType: 'staff applications',
	progressive: true,
	searchable: true,
	maxResults: 20,
});</script><?php

render_admin_body('Staff Applications');

echo '<div class="card">';
render_list_search('name, badge type, contact info, or transaction ID', 'card-content-only');
echo '</div>';

echo '<div class="card entity-list-card">';
render_list_table(array_merge(
	array('ID', 'Real Name', 'Fandom Name', 'Badge Type', 'Email Address'),
	extension_question_names_in_list($extension_questions),
	array('Application Status', 'Assigned Position', 'Payment Status', 'Payment Date')
), null, 'staffer.php', $conn);
echo '</div>';

render_admin_dialogs();
render_admin_tail();