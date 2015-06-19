<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';

if (isset($_POST['download'])) {
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename='.$_POST['download'].'.csv');
	header('Pragma: no-cache');
	header('Expires: 0');
	$out = fopen('php://output', 'w');
	
	switch ($_POST['download']) {
		case 'guests':
			$conn = get_db_connection();
			db_require_table('guest_badges', $conn);
			db_require_table('guest_extension_questions', $conn);
			db_require_table('guests', $conn);
			db_require_table('guest_extension_answers', $conn);
			$badge_names = get_guest_badge_names($conn);
			$extension_questions = get_extension_questions('guest', $conn);
			
			$row = array_merge(array(
				'ID',
				'ID String',
				'Contact First Name',
				'Contact Last Name',
				'Contact Real Name',
				'Contact Email Address',
				'Contact Phone Number',
				'Badge Type ID',
				'Badge Type ID String',
				'Badge Type Name',
				'Guest Name',
				'Guest Description',
				'Number of Guests/Supporters',
			), extension_question_names($extension_questions), array(
				'Application Status',
				'Contract Status',
				'Date Created',
				'Date Modified',
			));
			fputcsv($out, $row);
			
			$results = mysql_query('SELECT * FROM '.db_table_name('guests').' ORDER BY `id`', $conn);
			while ($result = mysql_fetch_assoc($results)) {
				$result = decode_guest($result, $badge_names);
				$extension_answers = get_extension_answers('guest', $result['id'], $conn);
				
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
					$result['guest_name'],
					$result['guest_description'],
					$result['num_supporters'],
				), extension_answer_values($extension_questions, $extension_answers), array(
					$result['application_status_string'],
					$result['contract_status_string'],
					$result['date_created'],
					$result['date_modified'],
				));
				fputcsv($out, $row);
			}
			
			break;
		case 'guest_supporters':
			$conn = get_db_connection();
			db_require_table('guest_badges', $conn);
			db_require_table('guests', $conn);
			db_require_table('guest_supporters', $conn);
			$badge_names = get_guest_badge_names($conn);
			$guest_info = get_guest_info($conn, $badge_names);
			
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
				'Guest ID',
				'Guest ID String',
				'Guest Name',
				'Guest Description',
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
				'Emergency Contact Name',
				'Emergency Contact Relationship',
				'Emergency Contact Email Address',
				'Emergency Contact Phone Number',
				'Application Status',
				'Contract Status',
				'Print Count',
				'Last Printed',
				'Check-In Count',
				'Checked In',
				'Date Created',
				'Date Modified',
			);
			fputcsv($out, $row);
			
			$results = mysql_query('SELECT * FROM '.db_table_name('guest_supporters').' ORDER BY `id`', $conn);
			while ($result = mysql_fetch_assoc($results)) {
				$result = decode_guest_supporter($result, $guest_info);
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
					$result['guest_id'],
					$result['guest_id_string'],
					$result['guest_name'],
					$result['guest_description'],
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
					$result['contract_status_string'],
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

render_admin_head('Download Guest Records');
render_admin_body('Download Guest Records');

echo '<div class="card">';
	echo '<div class="card-content spaced">';
		echo '<p><b>Notice:</b> Downloaded CSV data should be used for reporting purposes only.</p>';
		echo '<p><b>Guest Applications:</b></p>';
		echo '<form action="guests_download.php" method="post">';
			echo '<p>';
				echo '<input type="hidden" name="download" value="guests">';
				echo '<input type="submit" value="Download CSV">';
			echo '</p>';
		echo '</form>';
		echo '<p><b>Guests &amp; Supporters:</b></p>';
		echo '<form action="guests_download.php" method="post">';
			echo '<p>';
				echo '<input type="hidden" name="download" value="guest_supporters">';
				echo '<input type="submit" value="Download CSV">';
			echo '</p>';
		echo '</form>';
	echo '</div>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();