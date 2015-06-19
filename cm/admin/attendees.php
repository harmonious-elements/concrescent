<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/cmbase/util.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

$conn = get_db_connection();
db_require_table('attendee_badges', $conn);
db_require_table('attendees', $conn);
$badge_names = get_attendee_badge_names($conn);
$extension_questions = get_extension_questions('attendee', $conn);

if (isset($_POST['start_id'])) {
	header('Content-type: text/plain');
	$attendees = array();
	$start_id = (int)$_POST['start_id'];
	$end_id = $start_id;
	$batch_size = 100;
	
	$results = mysql_query('SELECT * FROM '.db_table_name('attendees').' WHERE `id` >= '.$start_id.' ORDER BY `id` LIMIT '.$batch_size, $conn);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_attendee($result, $badge_names);
		$extension_answers = get_extension_answers('attendee', $result['id'], $conn);
		$html_content = render_list_row(
			array_merge(array(
				('A'.$result['id']),
				$result['real_name'],
				$result['fandom_name'],
				$result['badge_name'],
				array('html' => (
					'<span style="white-space: nowrap;">'.
					'<span class="nospam nospam-'.
					($result['on_mailing_list'] ? 'false' : 'true').
					'" title="'.
					($result['on_mailing_list'] ? 'OK to' : 'Do Not').
					' Contact">&#x271'.
					($result['on_mailing_list'] ? '3' : '7').
					';</span>&nbsp;'.
					email_link($result['email_address']).
					'</span>'
				)),
			), extension_answer_values_in_list($extension_questions, $extension_answers), array(
				array('html' => payment_status_html($result['payment_status'])),
				$result['payment_promo_code'],
				($result['payment_date'] ? $result['payment_date'] : 'never'),
			)),
			null,
			/*  selectable = */ false,
			/*  switchable = */ false,
			/*      active = */ false,
			/*  deleteable = */ false,
			/* reorderable = */ false,
			/*        edit = */ ('attendee.php?id='.$result['id']),
			/*      review = */ false
		);
		$attendees[] = array(
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
		'entities' => $attendees,
	);
	echo json_encode($response);
	exit(0);
}

render_admin_head('Attendees');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'attendees.php',
	entityType: 'attendees',
	progressive: true,
	searchable: true,
	maxResults: 20,
});</script><?php

render_admin_body('Attendees');

echo '<div class="card">';
render_list_search('name, badge type, contact info, or transaction ID', 'card-content-only');
echo '</div>';

echo '<div class="card entity-list-card">';
render_list_table(array_merge(
	array('ID', 'Real Name', 'Fandom Name', 'Badge Type', 'Email Address'),
	extension_question_names_in_list($extension_questions),
	array('Payment Status', 'Promo Code', 'Payment Date')
), null, 'attendee.php', $conn);
echo '</div>';

render_admin_dialogs();
render_admin_tail();