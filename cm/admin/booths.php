<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/cmbase/util.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

$conn = get_db_connection();
db_require_table('booth_badges', $conn);
db_require_table('booths', $conn);
$badge_names = get_booth_badge_names($conn);
$extension_questions = get_extension_questions('booth', $conn);

if (isset($_POST['start_id'])) {
	header('Content-type: text/plain');
	$booths = array();
	$start_id = (int)$_POST['start_id'];
	$end_id = $start_id;
	$batch_size = 100;
	
	$results = mysql_query('SELECT * FROM '.db_table_name('booths').' WHERE `id` >= '.$start_id.' ORDER BY `id` LIMIT '.$batch_size, $conn);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_booth($result, $badge_names);
		$extension_answers = get_extension_answers('booth', $result['id'], $conn);
		$html_content = render_list_row(
			array_merge(array(
				('BA'.$result['id']),
				$result['business_name'],
				$result['booth_name'],
				$result['badge_name'],
				$result['num_tables'],
				$result['num_staffers'],
			), extension_answer_values_in_list($extension_questions, $extension_answers), array(
				array('html' => application_status_html($result['application_status'])),
				implode(', ', $result['table_id']),
				$result['permit_number'],
				array('html' => payment_status_html($result['payment_status'])),
				($result['payment_date'] ? $result['payment_date'] : 'never'),
			)),
			null,
			/*  selectable = */ false,
			/*  switchable = */ false,
			/*      active = */ false,
			/*  deleteable = */ false,
			/* reorderable = */ false,
			/*        edit = */ ('booth.php?id='.$result['id']),
			/*      review = */ false
		);
		$booths[] = array(
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
		'entities' => $booths,
	);
	echo json_encode($response);
	exit(0);
}

render_admin_head('Table Applications');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'booths.php',
	entityType: 'table applications',
	progressive: true,
	searchable: true,
	maxResults: 20,
});</script><?php

render_admin_body('Table Applications');

echo '<div class="card">';
render_list_search('name, badge type, contact info, or transaction ID', 'card-content-only');
echo '</div>';

echo '<div class="card entity-list-card">';
render_list_table(array_merge(
	array('ID', 'Business Name', 'Table Name', 'Table Type', '# Tables', '# Staffers'),
	extension_question_names_in_list($extension_questions),
	array('Application Status', 'Assigned Table', 'Permit Number', 'Payment Status', 'Payment Date')
), null, 'booth.php', $conn);
echo '</div>';

render_admin_dialogs();
render_admin_tail();