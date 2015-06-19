<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';

$conn = get_db_connection();
db_require_table('attendee_badges', $conn);
db_require_table('attendee_extension_questions', $conn);
db_require_table('attendees', $conn);
db_require_table('attendee_extension_answers', $conn);
$badge_names = get_attendee_badge_names($conn);
$extension_questions = get_extension_questions('attendee', $conn);

$id = 0;
$changed = false;
if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'save':
			$set = encode_attendee($_POST);
			if ($id) {
				$q = 'UPDATE '.db_table_name('attendees').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
			} else {
				$q = 'INSERT INTO '.db_table_name('attendees').' SET '.$set.', `date_created` = NOW()';
				mysql_query($q, $conn);
				$id = (int)mysql_insert_id($conn);
			}
			$extension_answers = get_posted_extension_answers($extension_questions);
			set_extension_answers('attendee', $id, $extension_answers, $conn);
			$changed = true;
			break;
	}
} else if (isset($_GET['id'])) {
	$id = (int)$_GET['id'];
}

if ($id) {
	$results = mysql_query('SELECT * FROM '.db_table_name('attendees').' WHERE `id` = '.$id, $conn);
	$result = mysql_fetch_assoc($results);
	$result = decode_attendee($result, $badge_names);
	$name = $result['real_name'];
	$extension_answers = get_extension_answers('attendee', $id, $conn);
} else {
	$result = null;
	$name = null;
	$extension_answers = array();
}

render_admin_head($name ? ('Edit Attendee - '.$name) : 'Add Attendee');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';

render_admin_body($name ? 'Edit Attendee' : 'Add Attendee');

echo '<div class="card">';
	echo '<form action="attendee.php?id='.$id.'" method="post">';
		echo '<div class="card-content">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="form entity-record attendee-record">';
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
						echo '<th><label for="name_on_badge">Name on Badge:</label></th>';
						echo '<td><select name="name_on_badge">';
							echo '<option value="FandomReal"';
								if ($result && $result['name_on_badge'] == 'FandomReal') echo ' selected="selected"';
								echo '>Fandom Name Large, Real Name Small</option>';
							echo '<option value="RealFandom"';
								if ($result && $result['name_on_badge'] == 'RealFandom') echo ' selected="selected"';
								echo '>Real Name Large, Fandom Name Small</option>';
							echo '<option value="FandomOnly"';
								if ($result && $result['name_on_badge'] == 'FandomOnly') echo ' selected="selected"';
								echo '>Fandom Name Only</option>';
							echo '<option value="RealOnly"';
								if ($result && $result['name_on_badge'] == 'RealOnly') echo ' selected="selected"';
								echo '>Real Name Only</option>';
						echo '</select></td>';
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
						echo '<th></th>';
						echo '<td>';
							echo '<label><input type="radio" name="do_not_spam" value="0"';
							if (!($result && $result['do_not_spam'])) echo ' checked="checked"';
							echo '>OK to Contact</label> ';
							echo '<label><input type="radio" name="do_not_spam" value="1"';
							if ($result && $result['do_not_spam']) echo ' checked="checked"';
							echo '>Do Not Contact</label>';
						echo '</td>';
					echo '</tr>';
					if ($result && $result['unsubscribe_link']) {
						echo '<tr>';
							echo '<th><label>Unsubscribe Link:</label></th>';
							echo '<td>' . url_link($result['unsubscribe_link']) . '</td>';
						echo '</tr>';
					}
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
				if (count($extension_questions)) {
					echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-ext">Additional Information</div></th></tr></thead>';
					echo '<tbody class="sh-ext'.($id ? ' hidden' : '').'">';
					echo render_extension_answers_editor($extension_questions, $extension_answers);
					echo '</tbody>';
				}
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
								echo '>Cancelled by Attendee</option>';
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
						echo '<th><label for="payment_original_price">Original Badge Price:</label></th>';
						echo '<td><input type="number" name="payment_original_price" min="0" step="0.01" value="';
						if ($result) echo htmlspecialchars($result['payment_original_price']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="payment_promo_code">Promo Code:</label></th>';
						echo '<td><input type="text" name="payment_promo_code" value="';
						if ($result) echo htmlspecialchars($result['payment_promo_code']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="payment_final_price">Discounted Badge Price:</label></th>';
						echo '<td><input type="number" name="payment_final_price" min="0" step="0.01" value="';
						if ($result) echo htmlspecialchars($result['payment_final_price']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="payment_total_price">Transaction Amount:</label></th>';
						echo '<td><input type="number" name="payment_total_price" min="0" step="0.01" value="';
						if ($result) echo htmlspecialchars($result['payment_total_price']);
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
					if ($result && $result['order_url']) {
						echo '<tr>';
							echo '<th><label>Review Order Link:</label></th>';
							echo '<td>' . url_link($result['order_url']) . '</td>';
						echo '</tr>';
					}
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-rcd">Record Information</div></th></tr></thead>';
				echo '<tbody class="sh-rcd">';
					if ($result) {
						echo '<tr>';
							echo '<th><label>ID Number:</label></th>';
							echo '<td>A' . $result['id'] . '</td>';
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