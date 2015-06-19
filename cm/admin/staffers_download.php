<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';

if (isset($_POST['download'])) {
	$conn = get_db_connection();
	db_require_table('staffer_badges', $conn);
	db_require_table('staffer_extension_questions', $conn);
	db_require_table('staffers', $conn);
	db_require_table('staffer_extension_answers', $conn);
	$badge_names = get_staffer_badge_names($conn);
	$extension_questions = get_extension_questions('staffer', $conn);
	
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename=staffers.csv');
	header('Pragma: no-cache');
	header('Expires: 0');
	$out = fopen('php://output', 'w');
	
	$row = array_merge(array(
		'ID',
		'ID String',
		'First Name',
		'Last Name',
		'Real Name',
		'Fandom Name',
		'Name on Badge',
		'Only Name',
		'Large Name',
		'Small Name',
		'Display Name',
		'Date of Birth',
		'Age (Start of Event)',
		'Badge Type ID',
		'Badge Type ID String',
		'Badge Type Name',
		'Email Address',
		'Phone Number',
		'Street Address 1',
		'Street Address 2',
		'City',
		'State or Province',
		'ZIP or Postal Code',
		'Country',
		'Full Address',
		'Dates Available',
	), extension_question_names($extension_questions), array(
		'Application Status',
		'Assigned Position',
		'Notes',
		'Emergency Contact Name',
		'Emergency Contact Relationship',
		'Emergency Contact Email Address',
		'Emergency Contact Phone Number',
		'Payment Status',
		'Payment Type',
		'Transaction ID',
		'Transaction Amount',
		'Payment Date',
		'Payment Details',
		'Lookup Key',
		'Confirm & Pay Link',
		'Review Order Link',
		'Print Count',
		'Last Printed',
		'Check-In Count',
		'Checked In',
		'Date Created',
		'Date Modified',
	));
	fputcsv($out, $row);
	
	$results = mysql_query('SELECT * FROM '.db_table_name('staffers').' ORDER BY `id`', $conn);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_staffer($result, $badge_names);
		$extension_answers = get_extension_answers('staffer', $result['id'], $conn);
		
		$row = array_merge(array(
			$result['id'],
			$result['id_string'],
			$result['first_name'],
			$result['last_name'],
			$result['real_name'],
			$result['fandom_name'],
			$result['name_on_badge_string'],
			$result['only_name'],
			$result['large_name'],
			$result['small_name'],
			$result['display_name'],
			$result['date_of_birth'],
			$result['age'],
			$result['badge_id'],
			$result['badge_id_string'],
			$result['badge_name'],
			$result['email_address'],
			$result['phone_number'],
			$result['address_1'],
			$result['address_2'],
			$result['city'],
			$result['state'],
			$result['zip_code'],
			$result['country'],
			$result['address_full'],
			implode("\n", $result['dates_available']),
		), extension_answer_values($extension_questions, $extension_answers), array(
			$result['application_status_string'],
			$result['assigned_position'],
			$result['notes'],
			$result['ice_name'],
			$result['ice_relationship'],
			$result['ice_email_address'],
			$result['ice_phone_number'],
			$result['payment_status_string'],
			$result['payment_type'],
			$result['payment_txn_id'],
			$result['payment_price'],
			$result['payment_date'],
			$result['payment_details'],
			$result['payment_lookup_key'],
			$result['confirm_payment_url'],
			$result['review_order_url'],
			$result['print_count'],
			$result['print_time'],
			$result['checkin_count'],
			$result['checkin_time'],
			$result['date_created'],
			$result['date_modified'],
		));
		fputcsv($out, $row);
	}
	
	fclose($out);
	exit(0);
}

render_admin_head('Download Staff Application Records');
render_admin_body('Download Staff Application Records');

echo '<div class="card">';
	echo '<div class="card-content spaced">';
		echo '<p><b>Notice:</b> Downloaded CSV data should be used for reporting purposes only.</p>';
		echo '<form action="staffers_download.php" method="post">';
			echo '<p><input type="submit" name="download" value="Download CSV"></p>';
		echo '</form>';
	echo '</div>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();