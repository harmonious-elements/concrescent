<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

$conn = get_db_connection();
db_require_table('booth_tables', $conn);
db_require_table('booth_badges', $conn);
db_require_table('booth_extension_questions', $conn);
db_require_table('booths', $conn);
db_require_table('booth_extension_answers', $conn);
db_require_table('booth_staffers', $conn);
$badge_names = get_booth_badge_names($conn);
$booth_tables = get_booth_tables($conn, $badge_names);
$extension_questions = get_extension_questions('booth', $conn);
$booth_info = get_booth_info($conn, $badge_names);

$id = 0;
$changed = false;
if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'save':
			$table_id = array();
			foreach ($booth_tables as $table) {
				if (isset($_POST['table_id_' . $table['id']]) && $_POST['table_id_' . $table['id']]) {
					$table_id[] = $table['id'];
				}
			}
			$set = encode_booth(array_merge($_POST, array('table_id' => $table_id)));
			if ($id) {
				$q = 'UPDATE '.db_table_name('booths').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
			} else {
				$q = 'INSERT INTO '.db_table_name('booths').' SET '.$set.', `date_created` = NOW()';
				mysql_query($q, $conn);
				$id = (int)mysql_insert_id($conn);
			}
			$extension_answers = get_posted_extension_answers($extension_questions);
			set_extension_answers('booth', $id, $extension_answers, $conn);
			$changed = true;
			break;
		case 'list_staffers':
			header('Content-type: text/html');
			$results = mysql_query('SELECT * FROM '.db_table_name('booth_staffers').' WHERE `booth_id` = '.$id.' ORDER BY `id`', $conn);
			while ($result = mysql_fetch_assoc($results)) {
				$result = decode_booth_staffer($result, $booth_info);
				echo '<tr>';
				echo '<td>B'.htmlspecialchars($result['id']).'</td>';
				echo '<td>'.htmlspecialchars($result['real_name']).'</td>';
				echo '<td>'.htmlspecialchars($result['fandom_name']).'</td>';
				echo '<td>'.email_link($result['email_address']).'</td>';
				echo '<td>'.($result['attendee_id'] ? 'Yes' : 'No').'</td>';
				echo '<td>'.htmlspecialchars($result['checkin_time'] ? $result['checkin_time'] : 'never').'</td>';
				echo '<td class="td-actions td-actions-edit">';
				echo '<a href="booth_staffer.php?id='.$result['id'].'" target="_blank" role="button" class="a-button edit-button">Edit</a>';
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
	$results = mysql_query('SELECT * FROM '.db_table_name('booths').' WHERE `id` = '.$id, $conn);
	$result = mysql_fetch_assoc($results);
	$result = decode_booth($result, $badge_names);
	$name = $result['booth_name'];
	$extension_answers = get_extension_answers('booth', $id, $conn);
} else {
	$result = null;
	$name = null;
	$extension_answers = array();
}

render_admin_head($name ? ('Edit Table Application - '.$name) : 'Add Table Application');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';
if ($id) {
	echo '<script type="text/javascript">';
		echo '$(document).ready(function() {';
			echo 'var loadBoothStaffers = function() {';
				echo 'cmui.showButterbar(\'Loading table staffers...\');';
				echo 'jQuery.post(\'booth.php\', { \'id\': '.$id.', \'action\': \'list_staffers\' }, function(data) {';
					echo '$(\'table.entity-list tbody\').html(data);';
					echo 'cmui.hideButterbar();';
					echo 'setTimeout(loadBoothStaffers, 2000);';
				echo '});';
			echo '};';
			echo 'loadBoothStaffers();';
		echo '});';
	echo '</script>';
}
echo '<link rel="stylesheet" href="' . htmlspecialchars(resource_file_url('cmbtselect.css')) . '">';
echo '<style>.tag-map { padding-bottom: '.booth_map_aspect_ratio().'%; }</style>';

render_admin_body($name ? 'Edit Table Application' : 'Add Table Application');

echo '<div class="card">';
	echo '<form action="booth.php?id='.$id.'" method="post">';
		echo '<div class="card-content">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="form entity-record booth-record">';
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
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-ext">Table Information</div></th></tr></thead>';
				echo '<tbody class="sh-ext">';
					echo '<tr>';
						echo '<th><label for="badge_id">Table Type:</label></th>';
						echo '<td><select name="badge_id">';
							foreach ($badge_names as $badge_id => $badge_name) {
								echo '<option value="'.$badge_id.'"';
								if ($result && (int)$result['badge_id'] == $badge_id) echo ' selected="selected"';
								echo '>'.htmlspecialchars($badge_name).'</option>';
							}
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="business_name">Business Name:</label></th>';
						echo '<td><input type="text" name="business_name" value="';
						if ($result) echo htmlspecialchars($result['business_name']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="booth_name">Table Name:</label></th>';
						echo '<td><input type="text" name="booth_name" value="';
						if ($result) echo htmlspecialchars($result['booth_name']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="num_tables">Number of Tables:</label></th>';
						echo '<td><input type="number" min="1" name="num_tables" value="';
						if ($result) echo htmlspecialchars($result['num_tables']);
						echo '"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="num_staffers">Number of Staffers:</label></th>';
						echo '<td><input type="number" min="1" name="num_staffers" value="';
						if ($result) echo htmlspecialchars($result['num_staffers']);
						echo '"></td>';
					echo '</tr>';
					echo render_extension_answers_editor($extension_questions, $extension_answers);
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-stf">Staffer Information</div></th></tr></thead>';
				echo '<tbody class="sh-stf">';
					echo '<tr>';
						echo '<td colspan="2">';
							if ($id) {
								render_list_table(array(
									'ID', 'Real Name', 'Fandom Name', 'Email Address',
									'Already Registered', 'Checked In'
								), null, 'booth_staffer.php?booth_id='.$id, $conn);
							} else {
								echo 'Staffer information must be added only after this record is saved.';
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
							foreach ($booth_info as $booth_id => $booth) {
								if ($booth_id != $id) {
									echo '<option value="'.$booth_id.'"';
									if ($result && $result['replaced_by'] == $booth_id) echo ' selected="selected"';
									echo '>BA'.$booth_id.' - '.htmlspecialchars($booth['booth_name']).'</option>';
								}
							}
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label>Assigned Table:</label></th>';
						echo '<td>';
							echo '<div class="tag-map">';
								echo '<div class="tags">';
									foreach ($booth_tables as $table) {
										echo '<label';
											echo ' class="';
												echo 'tag';
												if (isset($table['booth'])) {
													echo ' tag-assigned';
												}
											echo '"';
											echo ' data-content="'.htmlspecialchars($table['id']).'"';
											if (isset($table['booth'])) {
												echo ' title="'.htmlspecialchars($table['booth']['booth_name']).'"';
											}
											echo ' style="';
												echo 'left: '.htmlspecialchars($table['x']).'%;';
												echo ' top: '.htmlspecialchars($table['y']).'%;';
											echo '"';
										echo '>';
											echo '<input type="checkbox" name="table_id_'.htmlspecialchars($table['id']).'" value="1"';
											if ($result && in_array($table['id'], $result['table_id'])) {
												echo ' checked="checked"';
											}
											echo '>';
											echo '<span>'.htmlspecialchars($table['id']).'</span>';
										echo '</label>';
									}
								echo '</div>';
							echo '</div>';
						echo '</td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th></th>';
						echo '<td>';
							echo '<b>Notice:</b>';
							echo ' Changing application status or assigned table using this page';
							echo ' does not notify the applicant of the status of their application.';
							echo ' Please use <b><a href="review_booths.php">Review Table Applications</a></b>';
							echo ' to process incoming table applications or resend notification emails.';
						echo '</td>';
					echo '</tr>';
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-pmt">Payment Information</div></th></tr></thead>';
				echo '<tbody class="sh-pmt">';
					echo '<tr>';
						echo '<th><label for="permit_number">Permit Number:</label></th>';
						echo '<td><input type="text" name="permit_number" value="';
						if ($result) echo htmlspecialchars($result['permit_number']);
						echo '"></td>';
					echo '</tr>';
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
							echo ' Changing application status or assigned table using this page';
							echo ' does not notify the applicant of the status of their application.';
							echo ' Please use <b><a href="review_booths.php">Review Table Applications</a></b>';
							echo ' to process incoming table applications or resend notification emails.';
						echo '</td>';
					echo '</tr>';
				echo '</tbody>';
				if ($result) {
					echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-rcd">Record Information</div></th></tr></thead>';
					echo '<tbody class="sh-rcd">';
						echo '<tr>';
							echo '<th><label>ID Number:</label></th>';
							echo '<td>BA' . $result['id'] . '</td>';
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