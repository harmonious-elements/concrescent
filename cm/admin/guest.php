<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

$conn = get_db_connection();
db_require_table('guest_badges', $conn);
db_require_table('guest_extension_questions', $conn);
db_require_table('guests', $conn);
db_require_table('guest_extension_answers', $conn);
db_require_table('guest_supporters', $conn);
$badge_names = get_guest_badge_names($conn);
$extension_questions = get_extension_questions('guest', $conn);
$guest_info = get_guest_info($conn, $badge_names);

$id = 0;
$changed = false;
if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'save':
			$set = encode_guest($_POST);
			if ($id) {
				$q = 'UPDATE '.db_table_name('guests').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
			} else {
				$q = 'INSERT INTO '.db_table_name('guests').' SET '.$set.', `date_created` = NOW()';
				mysql_query($q, $conn);
				$id = (int)mysql_insert_id($conn);
			}
			$extension_answers = get_posted_extension_answers($extension_questions);
			set_extension_answers('guest', $id, $extension_answers, $conn);
			$changed = true;
			break;
		case 'list_supporters':
			header('Content-type: text/html');
			$results = mysql_query('SELECT * FROM '.db_table_name('guest_supporters').' WHERE `guest_id` = '.$id.' ORDER BY `id`', $conn);
			while ($result = mysql_fetch_assoc($results)) {
				$result = decode_guest_supporter($result, $guest_info);
				echo '<tr>';
				echo '<td>G'.htmlspecialchars($result['id']).'</td>';
				echo '<td>'.htmlspecialchars($result['real_name']).'</td>';
				echo '<td>'.htmlspecialchars($result['fandom_name']).'</td>';
				echo '<td>'.email_link($result['email_address']).'</td>';
				echo '<td>'.htmlspecialchars($result['checkin_time'] ? $result['checkin_time'] : 'never').'</td>';
				echo '<td class="td-actions td-actions-edit">';
				echo '<a href="guest_supporter.php?id='.$result['id'].'" target="_blank" role="button" class="a-button edit-button">Edit</a>';
				echo '</td>';
				echo '</tr>';
			}
			exit(0);
			break;
	}
} else if (isset($_GET['id'])) {
	$id = (int)$_GET['id'];
}

if ($id) {
	$results = mysql_query('SELECT * FROM '.db_table_name('guests').' WHERE `id` = '.$id, $conn);
	$result = mysql_fetch_assoc($results);
	$result = decode_guest($result, $badge_names);
	$name = $result['guest_name'];
	$extension_answers = get_extension_answers('guest', $id, $conn);
} else {
	$result = null;
	$name = null;
	$extension_answers = array();
}

render_admin_head($name ? ('Edit Guest Application - '.$name) : 'Add Guest Application');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';
if ($id) {
	echo '<script type="text/javascript">';
		echo '$(document).ready(function() {';
			echo 'var loadGuestSupporters = function() {';
				echo 'cmui.showButterbar(\'Loading guests and supporters...\');';
				echo 'jQuery.post(\'guest.php\', { \'id\': '.$id.', \'action\': \'list_supporters\' }, function(data) {';
					echo '$(\'table.entity-list tbody\').html(data);';
					echo 'cmui.hideButterbar();';
					echo 'setTimeout(loadGuestSupporters, 2000);';
				echo '});';
			echo '};';
			echo 'loadGuestSupporters();';
		echo '});';
	echo '</script>';
}

render_admin_body($name ? 'Edit Guest Application' : 'Add Guest Application');

echo '<div class="card">';
	echo '<form action="guest.php?id='.$id.'" method="post">';
		echo '<div class="card-content">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="form entity-record guest-record">';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-con">Contact Information</div></th></tr></thead>';
				echo '<tbody class="sh-con">';
					echo '<tr>';
						echo '<th><label for="contact_first_name">Contact First Name:</label></th>';
						echo '<td><input type="text" name="contact_first_name" value="';
						if ($result) echo htmlspecialchars($result['contact_first_name']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="contact_last_name">Contact Last Name:</label></th>';
						echo '<td><input type="text" name="contact_last_name" value="';
						if ($result) echo htmlspecialchars($result['contact_last_name']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="contact_email_address">Contact Email Address:</label></th>';
						echo '<td><input type="email" name="contact_email_address" value="';
						if ($result) echo htmlspecialchars($result['contact_email_address']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="contact_phone_number">Contact Phone Number:</label></th>';
						echo '<td><input type="text" name="contact_phone_number" value="';
						if ($result) echo htmlspecialchars($result['contact_phone_number']);
						echo '"></td>';
					echo '</tr>';
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-ext">Guest Information</div></th></tr></thead>';
				echo '<tbody class="sh-ext">';
					echo '<tr>';
						echo '<th><label for="badge_id">Badge Type:</label></th>';
						echo '<td><select name="badge_id">';
							foreach ($badge_names as $badge_id => $badge_name) {
								echo '<option value="'.$badge_id.'"';
								if ($result && (int)$result['badge_id'] == $badge_id) echo ' selected="selected"';
								echo '>'.htmlspecialchars($badge_name).'</option>';
							}
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="guest_name">Guest Name:</label></th>';
						echo '<td><input type="text" name="guest_name" value="';
						if ($result) echo htmlspecialchars($result['guest_name']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="guest_description">Guest Description:</label></th>';
						echo '<td><textarea name="guest_description">';
						if ($result) echo htmlspecialchars($result['guest_description']);
						echo '</textarea></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="num_supporters">Number of Guests and Supporters:</label></th>';
						echo '<td><input type="number" min="1" name="num_supporters" value="';
						if ($result) echo htmlspecialchars($result['num_supporters']);
						echo '"></td>';
					echo '</tr>';
					echo render_extension_answers_editor($extension_questions, $extension_answers);
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-stf">Guest &amp; Supporter Information</div></th></tr></thead>';
				echo '<tbody class="sh-stf">';
					echo '<tr>';
						echo '<td colspan="2">';
							if ($id) {
								render_list_table(array(
									'ID', 'Real Name', 'Fandom Name',
									'Email Address', 'Checked In'
								), null, 'guest_supporter.php?guest_id='.$id, $conn);
							} else {
								echo 'Guest and supporter information must be added only after this record is saved.';
							}
						echo '</td>';
					echo '</tr>';
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-app">Application Status</div></th></tr></thead>';
				echo '<tbody class="sh-app">';
					echo '<tr>';
						echo '<th><label for="application_status">Application Status:</label></th>';
						echo '<td><select name="application_status">';
							echo '<option value="Submitted"';
								if ($result && $result['application_status'] == 'Submitted') echo ' selected="selected"';
								echo '>Submitted</option>';
							echo '<option value="Accepted"';
								if ($result && $result['application_status'] == 'Accepted') echo ' selected="selected"';
								echo '>Accepted</option>';
							echo '<option value="Maybe"';
								if ($result && $result['application_status'] == 'Maybe') echo ' selected="selected"';
								echo '>Waitlisted</option>';
							echo '<option value="Rejected"';
								if ($result && $result['application_status'] == 'Rejected') echo ' selected="selected"';
								echo '>Rejected</option>';
							echo '<option value="Cancelled"';
								if ($result && $result['application_status'] == 'Cancelled') echo ' selected="selected"';
								echo '>Cancelled by Applicant</option>';
							echo '<option value="Pulled"';
								if ($result && $result['application_status'] == 'Pulled') echo ' selected="selected"';
								echo '>Badge Pulled</option>';
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="replaced_by">Replaced By:</label></th>';
						echo '<td><select name="replaced_by">';
							echo '<option value=""';
							if (!($result && $result['replaced_by'])) echo ' selected="selected"';
							echo '>(Not Set)</option>';
							foreach ($guest_info as $guest_id => $guest) {
								if ($guest_id != $id) {
									echo '<option value="'.$guest_id.'"';
									if ($result && $result['replaced_by'] == $guest_id) echo ' selected="selected"';
									echo '>BA'.$guest_id.' - '.htmlspecialchars($guest['guest_name']).'</option>';
								}
							}
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="contract_status">Contract Status:</label></th>';
						echo '<td><select name="contract_status">';
							echo '<option value="Incomplete"';
								if ($result && $result['contract_status'] == 'Incomplete') echo ' selected="selected"';
								echo '>Incomplete</option>';
							echo '<option value="Cancelled"';
								if ($result && $result['contract_status'] == 'Cancelled') echo ' selected="selected"';
								echo '>Cancelled by Applicant</option>';
							echo '<option value="Completed"';
								if ($result && $result['contract_status'] == 'Completed') echo ' selected="selected"';
								echo '>Completed</option>';
							echo '<option value="Refunded"';
								if ($result && $result['contract_status'] == 'Refunded') echo ' selected="selected"';
								echo '>Refunded</option>';
							echo '<option value="Pulled"';
								if ($result && $result['contract_status'] == 'Pulled') echo ' selected="selected"';
								echo '>Badge Pulled</option>';
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th></th>';
						echo '<td>';
							echo '<b>Notice:</b>';
							echo ' Changing application status using this page does not notify the applicant of the status of their application.';
							echo ' Please use <b><a href="review_guests.php">Review Guest Applications</a></b>';
							echo ' to process incoming guest applications or resend notification emails.';
						echo '</td>';
					echo '</tr>';
				echo '</tbody>';
				if ($result) {
					echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-rcd">Record Information</div></th></tr></thead>';
					echo '<tbody class="sh-rcd">';
						echo '<tr>';
							echo '<th><label>ID Number:</label></th>';
							echo '<td>GA' . $result['id'] . '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label>Date Created:</label></th>';
							echo '<td>' . $result['date_created'] . '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label>Date Modified:</label></th>';
							echo '<td>' . $result['date_modified'] . '</td>';
						echo '</tr>';
					echo '</tbody>';
				}
			echo '</table>';
		echo '</div>';
		echo '<div class="card-buttons right">';
			echo '<input type="hidden" name="action" value="save">';
			echo '<input type="hidden" name="id" value="'.$id.'">';
			echo '<input type="submit" name="submit" value="Save Changes">';
		echo '</div>';
	echo '</form>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();