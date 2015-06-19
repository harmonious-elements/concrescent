<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';

$conn = get_db_connection();
db_require_table('staffer_badges', $conn);
db_require_table('staffer_extension_questions', $conn);
db_require_table('staffers', $conn);
db_require_table('staffer_extension_answers', $conn);
$badge_names = get_staffer_badge_names($conn);
$extension_questions = get_extension_questions('staffer', $conn);
$staffer_info = get_staffer_info($conn, $badge_names);

$id = 0;
$changed = false;
if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'save':
			$dates_available = array();
			$start_date = $GLOBALS['event_date_start_staff'];
			$end_date = $GLOBALS['event_date_end_staff'];
			while (strtotime($start_date) <= strtotime($end_date)) {
				if (isset($_POST['date_available_' . $start_date]) && $_POST['date_available_' . $start_date]) {
					$dates_available[] = $start_date;
				}
				$start_date = date('Y-m-d', strtotime('+1 day', strtotime($start_date)));
			}
			$set = encode_staffer(array_merge($_POST, array('dates_available' => $dates_available)));
			if ($id) {
				$q = 'UPDATE '.db_table_name('staffers').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
			} else {
				$q = 'INSERT INTO '.db_table_name('staffers').' SET '.$set.', `date_created` = NOW()';
				mysql_query($q, $conn);
				$id = (int)mysql_insert_id($conn);
			}
			$extension_answers = get_posted_extension_answers($extension_questions);
			set_extension_answers('staffer', $id, $extension_answers, $conn);
			$changed = true;
			break;
	}
} else if (isset($_GET['id'])) {
	$id = (int)$_GET['id'];
}

if ($id) {
	$results = mysql_query('SELECT * FROM '.db_table_name('staffers').' WHERE `id` = '.$id, $conn);
	$result = mysql_fetch_assoc($results);
	$result = decode_staffer($result, $badge_names);
	$name = $result['real_name'];
	$extension_answers = get_extension_answers('staffer', $id, $conn);
} else {
	$result = null;
	$name = null;
	$extension_answers = array();
}

render_admin_head($name ? ('Edit Staff Application - '.$name) : 'Add Staff Application');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';

render_admin_body($name ? 'Edit Staff Application' : 'Add Staff Application');

echo '<div class="card">';
	echo '<form action="staffer.php?id='.$id.'" method="post">';
		echo '<div class="card-content">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="form entity-record staffer-record">';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-per">Personal Information</div></th></tr></thead>';
				echo '<tbody class="sh-per">';
					echo '<tr>';
						echo '<th><label for="first_name">First Name:</label></th>';
						echo '<td><input type="text" name="first_name" value="';
						if ($result) echo htmlspecialchars($result['first_name']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="last_name">Last Name:</label></th>';
						echo '<td><input type="text" name="last_name" value="';
						if ($result) echo htmlspecialchars($result['last_name']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="fandom_name">Fandom Name:</label></th>';
						echo '<td><input type="text" name="fandom_name" value="';
						if ($result) echo htmlspecialchars($result['fandom_name']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="date_of_birth">Date of Birth:</label></th>';
						echo '<td><input type="date" name="date_of_birth" value="';
						if ($result) echo htmlspecialchars($result['date_of_birth']);
						echo '">';
						if (!ua('Chrome')) echo ' (YYYY-MM-DD)';
						echo '</td>';
					echo '</tr>';
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
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-con">Contact Information</div></th></tr></thead>';
				echo '<tbody class="sh-con'.($id ? ' hidden' : '').'">';
					echo '<tr>';
						echo '<th><label for="email_address">Email Address:</label></th>';
						echo '<td><input type="email" name="email_address" value="';
						if ($result) echo htmlspecialchars($result['email_address']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="phone_number">Phone Number:</label></th>';
						echo '<td><input type="text" name="phone_number" value="';
						if ($result) echo htmlspecialchars($result['phone_number']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="address_1">Street Address:</label></th>';
						echo '<td><input type="text" name="address_1" value="';
						if ($result) echo htmlspecialchars($result['address_1']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="address_2">Address Line 2:</label></th>';
						echo '<td><input type="text" name="address_2" value="';
						if ($result) echo htmlspecialchars($result['address_2']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="city">City:</label></th>';
						echo '<td><input type="text" name="city" value="';
						if ($result) echo htmlspecialchars($result['city']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="state">State or Province:</label></th>';
						echo '<td><input type="text" name="state" value="';
						if ($result) echo htmlspecialchars($result['state']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="zip_code">ZIP or Postal Code:</label></th>';
						echo '<td><input type="text" name="zip_code" value="';
						if ($result) echo htmlspecialchars($result['zip_code']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="country">Country:</label></th>';
						echo '<td><input type="text" name="country" value="';
						if ($result) echo htmlspecialchars($result['country']);
						echo '"></td>';
					echo '</tr>';
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-ext">Staff Information</div></th></tr></thead>';
				echo '<tbody class="sh-ext">';
					echo '<tr>';
						echo '<th><label>Dates Available:</label></th>';
						echo '<td>';
						$dates_available = $result ? $result['dates_available'] : array();
						$start_date = $GLOBALS['event_date_start_staff'];
						$end_date = $GLOBALS['event_date_end_staff'];
						$first = true;
						while (strtotime($start_date) <= strtotime($end_date)) {
							if ($first) $first = false;
							else echo '<br>';
							echo '<label><input type="checkbox" name="date_available_' . $start_date . '" value="' . $start_date . '"';
							if (in_array($start_date, $dates_available)) {
								echo ' checked="checked"';
							}
							echo '>' . date('l, F j, Y', strtotime($start_date)) . '</label>';
							$start_date = date('Y-m-d', strtotime('+1 day', strtotime($start_date)));
						}
						echo '</td>';
					echo '</tr>';
					echo render_extension_answers_editor($extension_questions, $extension_answers);
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-ice">Emergency Contact Information</div></th></tr></thead>';
				echo '<tbody class="sh-ice'.($id ? ' hidden' : '').'">';
					echo '<tr>';
						echo '<th><label for="ice_name">Emergency Contact Name:</label></th>';
						echo '<td><input type="text" name="ice_name" value="';
						if ($result) echo htmlspecialchars($result['ice_name']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="ice_relationship">Emergency Contact Relationship:</label></th>';
						echo '<td><input type="text" name="ice_relationship" value="';
						if ($result) echo htmlspecialchars($result['ice_relationship']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="ice_email_address">Emergency Contact Email Address:</label></th>';
						echo '<td><input type="text" name="ice_email_address" value="';
						if ($result) echo htmlspecialchars($result['ice_email_address']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="ice_phone_number">Emergency Contact Phone Number:</label></th>';
						echo '<td><input type="text" name="ice_phone_number" value="';
						if ($result) echo htmlspecialchars($result['ice_phone_number']);
						echo '"></td>';
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
							foreach ($staffer_info as $staffer_id => $staffer) {
								if ($staffer_id != $id) {
									echo '<option value="'.$staffer_id.'"';
									if ($result && $result['replaced_by'] == $staffer_id) echo ' selected="selected"';
									echo '>S'.$staffer_id.' - '.htmlspecialchars($staffer['display_name']).'</option>';
								}
							}
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="assigned_position">Assigned Position:</label></th>';
						echo '<td><input type="text" name="assigned_position" value="';
						if ($result) echo htmlspecialchars($result['assigned_position']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="notes">Notes:</label></th>';
						echo '<td><textarea name="notes">';
						if ($result) echo htmlspecialchars($result['notes']);
						echo '</textarea></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th></th>';
						echo '<td>';
							echo '<b>Notice:</b>';
							echo ' Changing application status or assigned position using this page';
							echo ' does not notify the staff member of the status of their application.';
							echo ' Please use <b><a href="review_staffers.php">Review Staff Applications</a></b>';
							echo ' to process incoming staff applications or resend notification emails.';
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
						echo '<th><label for="payment_price">Transaction Amount:</label></th>';
						echo '<td><input type="number" name="payment_price" min="0" step="0.01" value="';
						if ($result) echo htmlspecialchars($result['payment_price']);
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
							echo '<td>' . url_link($result['confirm_payment_url']);
						echo '</tr>';
					}
					if ($result && $result['payment_txn_id'] && $result['payment_lookup_key']) {
						echo '<tr>';
							echo '<th><label>Review Order Link:</label></th>';
							echo '<td>' . url_link($result['review_order_url']);
						echo '</tr>';
					}
					echo '<tr>';
						echo '<th></th>';
						echo '<td>';
							echo '<b>Notice:</b>';
							echo ' Changing application status or assigned position using this page';
							echo ' does not notify the staff member of the status of their application.';
							echo ' Please use <b><a href="review_staffers.php">Review Staff Applications</a></b>';
							echo ' to process incoming staff applications or resend notification emails.';
						echo '</td>';
					echo '</tr>';
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-rcd">Record Information</div></th></tr></thead>';
				echo '<tbody class="sh-rcd">';
					if ($result) {
						echo '<tr>';
							echo '<th><label>ID Number:</label></th>';
							echo '<td>S' . $result['id'] . '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label>Last Printed:</label></th>';
							echo '<td>';
								echo $result['print_time'] ? htmlspecialchars($result['print_time']) : 'Never';
								$count = (int)$result['print_count'];
								echo ' (' . $count . (($count == 1) ? ' time' : ' times') . ' total)';
								echo '<br><label><input type="radio" name="print" value="" checked="checked">Keep As-Is</label>';
								echo '&nbsp;&nbsp;<label><input type="radio" name="print" value="1">Mark Printed</label>';
								echo '&nbsp;&nbsp;<label><input type="radio" name="print" value="RESET">Reset</label>';
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label>Checked In:</label></th>';
							echo '<td>';
								echo $result['checkin_time'] ? htmlspecialchars($result['checkin_time']) : 'Never';
								$count = (int)$result['checkin_count'];
								echo ' (' . $count . (($count == 1) ? ' time' : ' times') . ' total)';
								echo '<br><label><input type="radio" name="checkin" value="" checked="checked">Keep As-Is</label>';
								echo '&nbsp;&nbsp;<label><input type="radio" name="checkin" value="1">Check In Now</label>';
								echo '&nbsp;&nbsp;<label><input type="radio" name="checkin" value="RESET">Reset</label>';
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label>Date Created:</label></th>';
							echo '<td>' . $result['date_created'] . '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label>Date Modified:</label></th>';
							echo '<td>' . $result['date_modified'] . '</td>';
						echo '</tr>';
					} else {
						echo '<tr>';
							echo '<th><label>Checked In:</label></th>';
							echo '<td><label><input type="checkbox" name="checkin" value="1">Check In Now</label></td>';
						echo '</tr>';
					}
				echo '</tbody>';
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