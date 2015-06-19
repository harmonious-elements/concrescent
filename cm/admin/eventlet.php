<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

$conn = get_db_connection();
db_require_table('eventlet_badges', $conn);
db_require_table('eventlet_extension_questions', $conn);
db_require_table('eventlets', $conn);
db_require_table('eventlet_extension_answers', $conn);
db_require_table('eventlet_staffers', $conn);
$badge_names = get_eventlet_badge_names($conn);
$extension_questions = get_extension_questions('eventlet', $conn);
$eventlet_info = get_eventlet_info($conn, $badge_names);

$id = 0;
$changed = false;
if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'save':
			$set = encode_eventlet($_POST);
			if ($id) {
				$q = 'UPDATE '.db_table_name('eventlets').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
			} else {
				$q = 'INSERT INTO '.db_table_name('eventlets').' SET '.$set.', `date_created` = NOW()';
				mysql_query($q, $conn);
				$id = (int)mysql_insert_id($conn);
			}
			$extension_answers = get_posted_extension_answers($extension_questions);
			set_extension_answers('eventlet', $id, $extension_answers, $conn);
			$changed = true;
			break;
		case 'list_staffers':
			header('Content-type: text/html');
			$results = mysql_query('SELECT * FROM '.db_table_name('eventlet_staffers').' WHERE `eventlet_id` = '.$id.' ORDER BY `id`', $conn);
			while ($result = mysql_fetch_assoc($results)) {
				$result = decode_eventlet_staffer($result, $eventlet_info);
				echo '<tr>';
				echo '<td>E'.htmlspecialchars($result['id']).'</td>';
				echo '<td>'.htmlspecialchars($result['real_name']).'</td>';
				echo '<td>'.htmlspecialchars($result['fandom_name']).'</td>';
				echo '<td>'.email_link($result['email_address']).'</td>';
				echo '<td>'.($result['attendee_id'] ? 'Yes' : 'No').'</td>';
				echo '<td>'.htmlspecialchars($result['checkin_time'] ? $result['checkin_time'] : 'never').'</td>';
				echo '<td class="td-actions td-actions-edit">';
				echo '<a href="eventlet_staffer.php?id='.$result['id'].'" target="_blank" role="button" class="a-button edit-button">Edit</a>';
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
	$results = mysql_query('SELECT * FROM '.db_table_name('eventlets').' WHERE `id` = '.$id, $conn);
	$result = mysql_fetch_assoc($results);
	$result = decode_eventlet($result, $badge_names);
	$name = $result['eventlet_name'];
	$extension_answers = get_extension_answers('eventlet', $id, $conn);
} else {
	$result = null;
	$name = null;
	$extension_answers = array();
}

render_admin_head($name ? ('Edit Panel/Activity Application - '.$name) : 'Add Panel/Activity Application');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';
if ($id) {
	echo '<script type="text/javascript">';
		echo '$(document).ready(function() {';
			echo 'var loadEventletStaffers = function() {';
				echo 'cmui.showButterbar(\'Loading panelists/hosts...\');';
				echo 'jQuery.post(\'eventlet.php\', { \'id\': '.$id.', \'action\': \'list_staffers\' }, function(data) {';
					echo '$(\'table.entity-list tbody\').html(data);';
					echo 'cmui.hideButterbar();';
					echo 'setTimeout(loadEventletStaffers, 2000);';
				echo '});';
			echo '};';
			echo 'loadEventletStaffers();';
		echo '});';
	echo '</script>';
}

render_admin_body($name ? 'Edit Panel/Activity Application' : 'Add Panel/Activity Application');

echo '<div class="card">';
	echo '<form action="eventlet.php?id='.$id.'" method="post">';
		echo '<div class="card-content">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="form entity-record eventlet-record">';
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
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-ext">Panel/Activity Information</div></th></tr></thead>';
				echo '<tbody class="sh-ext">';
					echo '<tr>';
						echo '<th><label for="badge_id">Panel/Activity Type:</label></th>';
						echo '<td><select name="badge_id">';
							foreach ($badge_names as $badge_id => $badge_name) {
								echo '<option value="'.$badge_id.'"';
								if ($result && (int)$result['badge_id'] == $badge_id) echo ' selected="selected"';
								echo '>'.htmlspecialchars($badge_name).'</option>';
							}
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="eventlet_name">Panel/Activity Name:</label></th>';
						echo '<td><input type="text" name="eventlet_name" value="';
						if ($result) echo htmlspecialchars($result['eventlet_name']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="eventlet_description">Panel/Activity Description:</label></th>';
						echo '<td><textarea name="eventlet_description">';
						if ($result) echo htmlspecialchars($result['eventlet_description']);
						echo '</textarea></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="num_staffers">Number of Panelists/Hosts:</label></th>';
						echo '<td><input type="number" min="1" name="num_staffers" value="';
						if ($result) echo htmlspecialchars($result['num_staffers']);
						echo '"></td>';
					echo '</tr>';
					echo render_extension_answers_editor($extension_questions, $extension_answers);
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-stf">Panelist/Host Information</div></th></tr></thead>';
				echo '<tbody class="sh-stf">';
					echo '<tr>';
						echo '<td colspan="2">';
							if ($id) {
								render_list_table(array(
									'ID', 'Real Name', 'Fandom Name', 'Email Address',
									'Already Registered', 'Checked In'
								), null, 'eventlet_staffer.php?eventlet_id='.$id, $conn);
							} else {
								echo 'Panelist or host information must be added only after this record is saved.';
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
							foreach ($eventlet_info as $eventlet_id => $eventlet) {
								if ($eventlet_id != $id) {
									echo '<option value="'.$eventlet_id.'"';
									if ($result && $result['replaced_by'] == $eventlet_id) echo ' selected="selected"';
									echo '>EA'.$eventlet_id.' - '.htmlspecialchars($eventlet['eventlet_name']).'</option>';
								}
							}
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th></th>';
						echo '<td>';
							echo '<b>Notice:</b>';
							echo ' Changing application status using this page does not notify the applicant of the status of their application.';
							echo ' Please use <b><a href="review_eventlets.php">Review Panel/Activity Applications</a></b>';
							echo ' to process incoming panel/activity applications or resend notification emails.';
						echo '</td>';
					echo '</tr>';
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-pmt">Payment Information</div></th></tr></thead>';
				echo '<tbody class="sh-pmt">';
					echo '<tr>';
						echo '<th><label for="payment_status">Payment Status:</label></th>';
						echo '<td><select name="payment_status">';
							echo '<option value="Incomplete"';
								if ($result && $result['payment_status'] == 'Incomplete') echo ' selected="selected"';
								echo '>Incomplete</option>';
							echo '<option value="Cancelled"';
								if ($result && $result['payment_status'] == 'Cancelled') echo ' selected="selected"';
								echo '>Cancelled by Applicant</option>';
							echo '<option value="Completed"';
								if ($result && $result['payment_status'] == 'Completed') echo ' selected="selected"';
								echo '>Completed</option>';
							echo '<option value="Refunded"';
								if ($result && $result['payment_status'] == 'Refunded') echo ' selected="selected"';
								echo '>Refunded</option>';
							echo '<option value="Pulled"';
								if ($result && $result['payment_status'] == 'Pulled') echo ' selected="selected"';
								echo '>Badge Pulled</option>';
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="payment_type">Payment Type:</label></th>';
						echo '<td><input type="text" name="payment_type" value="';
						if ($result) echo htmlspecialchars($result['payment_type']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="payment_txn_id">Transaction ID:</label></th>';
						echo '<td><input type="text" name="payment_txn_id" value="';
						if ($result) echo htmlspecialchars($result['payment_txn_id']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="payment_original_price">Original Price:</label></th>';
						echo '<td><input type="number" name="payment_original_price" min="0" step="0.01" value="';
						if ($result) echo htmlspecialchars($result['payment_original_price']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="payment_final_price">Discounted Price:</label></th>';
						echo '<td><input type="number" name="payment_final_price" min="0" step="0.01" value="';
						if ($result) echo htmlspecialchars($result['payment_final_price']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="payment_date">Payment Date:</label></th>';
						echo '<td><input type="datetime-local" name="payment_date" value="';
						if ($result) {
							$d = $result['payment_date'];
							$d = preg_replace('/^([0-9]{4}-[0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2}):[0-9]{2}$/', '$1T$2', $d);
							echo htmlspecialchars($d);
						}
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="payment_details">Payment Details:</label></th>';
						echo '<td><textarea name="payment_details">';
						if ($result) echo htmlspecialchars($result['payment_details']);
						echo '</textarea></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label>Lookup Key:</label></th>';
						echo '<td>';
							echo ($result && $result['payment_lookup_key']) ? htmlspecialchars($result['payment_lookup_key']) : 'Not Set';
							echo '<br><label><input type="radio" name="payment_lookup_key" value="keep" checked="checked">Keep As-Is</label>';
							echo '&nbsp;&nbsp;<label><input type="radio" name="payment_lookup_key" value="clear">Clear</label>';
							echo '&nbsp;&nbsp;<label><input type="radio" name="payment_lookup_key" value="new">Generate New Key</label>';
						echo '</td>';
					echo '</tr>';
					if ($result && !$result['payment_txn_id'] && $result['payment_lookup_key']) {
						echo '<tr>';
							echo '<th><label>Confirmation &amp; Payment Link:</label></th>';
							echo '<td>' . url_link($result['confirm_payment_url']) . '</td>';
						echo '</tr>';
					}
					if ($result && $result['payment_txn_id'] && $result['payment_lookup_key']) {
						echo '<tr>';
							echo '<th><label>Review Order Link:</label></th>';
							echo '<td>' . url_link($result['review_order_url']) . '</td>';
						echo '</tr>';
					}
					echo '<tr>';
						echo '<th></th>';
						echo '<td>';
							echo '<b>Notice:</b>';
							echo ' Changing application status using this page does not notify the applicant of the status of their application.';
							echo ' Please use <b><a href="review_eventlets.php">Review Panel/Activity Applications</a></b>';
							echo ' to process incoming panel/activity applications or resend notification emails.';
						echo '</td>';
					echo '</tr>';
				echo '</tbody>';
				if ($result) {
					echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-rcd">Record Information</div></th></tr></thead>';
					echo '<tbody class="sh-rcd">';
						echo '<tr>';
							echo '<th><label>ID Number:</label></th>';
							echo '<td>EA' . $result['id'] . '</td>';
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