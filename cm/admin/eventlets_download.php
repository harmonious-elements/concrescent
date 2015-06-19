<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';

if (isset($_POST['download'])) {
	$conn = get_db_connection();
	db_require_table('attendee_badges', $conn);
	db_require_table('attendee_extension_questions', $conn);
	db_require_table('attendees', $conn);
	db_require_table('attendee_extension_answers', $conn);
	$badge_names = get_attendee_badge_names($conn);
	$extension_questions = get_extension_questions('attendee', $conn);
	
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename='.$_POST['download'].'.csv');
	header('Pragma: no-cache');
	header('Expires: 0');
	$out = fopen('php://output', 'w');
	
	switch ($_POST['download']) {
		case 'eventlets':
			$conn = get_db_connection();
			db_require_table('eventlet_badges', $conn);
			db_require_table('eventlet_extension_questions', $conn);
			db_require_table('eventlets', $conn);
			db_require_table('eventlet_extension_answers', $conn);
			$badge_names = get_eventlet_badge_names($conn);
			$extension_questions = get_extension_questions('eventlet', $conn);
			
			$row = array_merge(array(
				'ID',
				'ID String',
				'Contact First Name',
				'Contact Last Name',
				'Contact Real Name',
				'Contact Email Address',
				'Contact Phone Number',
				'Panel/Activity Type ID',
				'Panel/Activity Type ID String',
				'Panel/Activity Type Name',
				'Panel/Activity Name',
				'Panel/Activity Description',
				'Number of Panelists/Hosts',
			), extension_question_names($extension_questions), array(
				'Application Status',
				'Payment Status',
				'Payment Type',
				'Transaction ID',
				'Original Price',
				'Discounted Price',
				'Payment Date',
				'Payment Details',
				'Lookup Key',
				'Confirm & Pay Link',
				'Review Order Link',
				'Date Created',
				'Date Modified',
			));
			fputcsv($out, $row);
			
			$results = mysql_query('SELECT * FROM '.db_table_name('eventlets').' ORDER BY `id`', $conn);
			while ($result = mysql_fetch_assoc($results)) {
				$result = decode_eventlet($result, $badge_names);
				$extension_answers = get_extension_answers('eventlet', $result['id'], $conn);
				
				$row = array_merge(array(
					$result['id'],
					$result['id_string'],
					$result['contact_first_name'],
					$result['contact_last_name'],
					$result['contact_real_name'],
					$result['contact_email_address'],
					$result['contact_phone_number'],
					$result['badge_id'],
					$result['badge_id_string'],
					$result['badge_name'],
					$result['eventlet_name'],
					$result['eventlet_description'],
					$result['num_staffers'],
				), extension_answer_values($extension_questions, $extension_answers), array(
					$result['application_status_string'],
					$result['payment_status_string'],
					$result['payment_type'],
					$result['payment_txn_id'],
					$result['payment_original_price'],
					$result['payment_final_price'],
					$result['payment_date'],
					$result['payment_details'],
					$result['payment_lookup_key'],
					$result['confirm_payment_url'],
					$result['review_order_url'],
					$result['date_created'],
					$result['date_modified'],
				));
				fputcsv($out, $row);
			}
			
			break;
		case 'eventlet_staffers':
			$conn = get_db_connection();
			db_require_table('eventlet_badges', $conn);
			db_require_table('eventlets', $conn);
			db_require_table('eventlet_staffers', $conn);
			$badge_names = get_eventlet_badge_names($conn);
			$eventlet_info = get_eventlet_info($conn, $badge_names);
			
			$row = array(
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
				'Panel/Activity ID',
				'Panel/Activity ID String',
				'Panel/Activity Name',
				'Panel/Activity Description',
				'Panel/Activity Type ID',
				'Panel/Activity Type ID String',
				'Panel/Activity Type Name',
				'Email Address',
				'Phone Number',
				'Street Address 1',
				'Street Address 2',
				'City',
				'State or Province',
				'ZIP or Postal Code',
				'Country',
				'Full Address',
				'Emergency Contact Name',
				'Emergency Contact Relationship',
				'Emergency Contact Email Address',
				'Emergency Contact Phone Number',
				'Application Status',
				'Payment Status',
				'Print Count',
				'Last Printed',
				'Check-In Count',
				'Checked In',
				'Date Created',
				'Date Modified',
			);
			fputcsv($out, $row);
			
			$results = mysql_query('SELECT * FROM '.db_table_name('eventlet_staffers').' ORDER BY `id`', $conn);
			while ($result = mysql_fetch_assoc($results)) {
				$result = decode_eventlet_staffer($result, $eventlet_info);
				$row = array(
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
					$result['eventlet_id'],
					$result['eventlet_id_string'],
					$result['eventlet_name'],
					$result['eventlet_description'],
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
					$result['ice_name'],
					$result['ice_relationship'],
					$result['ice_email_address'],
					$result['ice_phone_number'],
					$result['application_status_string'],
					$result['payment_status_string'],
					$result['print_count'],
					$result['print_time'],
					$result['checkin_count'],
					$result['checkin_time'],
					$result['date_created'],
					$result['date_modified'],
				);
				fputcsv($out, $row);
			}
			
			break;
	}
	
	fclose($out);
	exit(0);
}

render_admin_head('Download Panel/Activity Records');
render_admin_body('Download Panel/Activity Records');

echo '<div class="card">';
	echo '<div class="card-content spaced">';
		echo '<p><b>Notice:</b> Downloaded CSV data should be used for reporting purposes only.</p>';
		echo '<p><b>Panel/Activity Applications:</b></p>';
		echo '<form action="eventlets_download.php" method="post">';
			echo '<p>';
				echo '<input type="hidden" name="download" value="eventlets">';
				echo '<input type="submit" value="Download CSV">';
			echo '</p>';
		echo '</form>';
		echo '<p><b>Panelists/Hosts:</b></p>';
		echo '<form action="eventlets_download.php" method="post">';
			echo '<p>';
				echo '<input type="hidden" name="download" value="eventlet_staffers">';
				echo '<input type="submit" value="Download CSV">';
			echo '</p>';
		echo '</form>';
	echo '</div>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();