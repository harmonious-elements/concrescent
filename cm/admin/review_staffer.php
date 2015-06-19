<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';

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
			if ($id) {
				$application_status = $_POST['application_status'];
				$assigned_position = $_POST['assigned_position'];
				$notes = $_POST['notes'];
				$set = encode_staffer(array(
					'replaced_by' => (($application_status == 'Cancelled') ? (int)$_POST['replaced_by'] : 0),
					'application_status' => $application_status,
					'assigned_position' => $assigned_position,
					'notes' => $notes,
				));
				$q = 'UPDATE '.db_table_name('staffers').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
				
				$email_template = null;
				switch ($application_status) {
					case 'Accepted': $email_template = get_mail_template('staff_accepted', $conn); break;
					case 'Maybe': $email_template = get_mail_template('staff_maybe', $conn); break;
					case 'Rejected': $email_template = get_mail_template('staff_rejected', $conn); break;
				}
				if ($email_template && trim($email_template['body'])) {
					$results = mysql_query('SELECT * FROM '.db_table_name('staffers').' WHERE `id` = '.$id, $conn);
					$result = mysql_fetch_assoc($results);
					$result = decode_staffer($result, $badge_names);
					mail_send($result['email_address'], $email_template, $result);
				}
				
				$changed = true;
			}
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
	$display_name = $result['display_name'];
	$extension_answers = get_extension_answers('staffer', $id, $conn);
} else {
	header('Location: review_staffers.php');
	exit(0);
}

render_admin_head($name ? ('Review Staff Application - '.$name) : 'Review Staff Application');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';

render_admin_body('Review Staff Application');

echo '<div class="card">';
	echo '<form action="review_staffer.php?id='.$id.'" method="post">';
		echo '<div class="card-content spaced">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<h1>' . htmlspecialchars($display_name) . '</h1>';
			echo '<p>';
				echo '<b>Date of Birth:</b> ';
				echo htmlspecialchars(date('F j, Y', strtotime($result['date_of_birth'])));
				echo ' (age ' . $result['age'] . ' at start of event)';
			echo '</p>';
			echo '<p>';
				echo '<b>Badge Type:</b> ';
				echo htmlspecialchars($result['badge_name']);
			echo '</p>';
			echo '<hr>';
			echo '<h2>Contact Information</h2>';
			echo '<p>';
				echo '<b>Email Address:</b> ';
				echo email_link($result['email_address']);
			echo '</p>';
			echo '<p>';
				echo '<b>Phone Number:</b> ';
				echo htmlspecialchars($result['phone_number']);
			echo '</p>';
			echo '<p>';
				echo '<b>Street Address:</b><br>';
				echo paragraph_string($result['address_full']);
			echo '</p>';
			echo '<hr>';
			echo '<h2>Staff Information</h2>';
			echo '<p>';
				echo '<h3>Dates Available</h3>';
				$dates_available = $result['dates_available'];
				$start_date = $GLOBALS['event_date_start_staff'];
				$end_date = $GLOBALS['event_date_end_staff'];
				$first = true;
				while (strtotime($start_date) <= strtotime($end_date)) {
					if (in_array($start_date, $dates_available)) {
						if ($first) $first = false;
						else echo '<br>';
						echo date('l, F j, Y', strtotime($start_date));
					}
					$start_date = date('Y-m-d', strtotime('+1 day', strtotime($start_date)));
				}
			echo '</p>';
			echo render_extension_answers_h3_p($extension_questions, $extension_answers);
			echo '<hr>';
			echo '<h2>Emergency Contact Information</h2>';
			echo '<p>';
				echo '<b>Name:</b> ';
				echo htmlspecialchars($result['ice_name']);
			echo '</p>';
			echo '<p>';
				echo '<b>Relationship:</b> ';
				echo htmlspecialchars($result['ice_relationship']);
			echo '</p>';
			echo '<p>';
				echo '<b>Email Address:</b> ';
				echo email_link($result['ice_email_address']);
			echo '</p>';
			echo '<p>';
				echo '<b>Phone Number:</b> ';
				echo htmlspecialchars($result['ice_phone_number']);
			echo '</p>';
			echo '<hr>';
			echo '<h2>Application Status</h2>';
				echo '<p>';
					echo '<label>';
						echo '<input type="radio" name="application_status" value="Submitted"';
						if ($result['application_status'] == 'Submitted') echo ' checked="checked"';
						echo '>Submitted';
					echo '</label>';
					echo '<br>';
					echo '<label>';
						echo '<input type="radio" name="application_status" value="Accepted"';
						if ($result['application_status'] == 'Accepted') echo ' checked="checked"';
						echo '>Accepted';
					echo '</label>';
					echo '<br>';
					echo '<label>';
						echo '<input type="radio" name="application_status" value="Maybe"';
						if ($result['application_status'] == 'Maybe') echo ' checked="checked"';
						echo '>Waitlisted';
					echo '</label>';
					echo '<br>';
					echo '<label>';
						echo '<input type="radio" name="application_status" value="Rejected"';
						if ($result['application_status'] == 'Rejected') echo ' checked="checked"';
						echo '>Rejected';
					echo '</label>';
					echo '<br>';
					echo '<label>';
						echo '<input type="radio" name="application_status" value="Cancelled"';
						if ($result['application_status'] == 'Cancelled') echo ' checked="checked"';
						echo '>Cancelled by Applicant / Replaced By <select name="replaced_by">';
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
						echo '</select>';
					echo '</label>';
					echo '<br>';
					echo '<label>';
						echo '<input type="radio" name="application_status" value="Pulled"';
						if ($result['application_status'] == 'Pulled') echo ' checked="checked"';
						echo '>Badge Pulled';
					echo '</label>';
				echo '</p>';
				echo '<p>';
					echo '<b>Assigned Position:</b> ';
					echo '<input type="text" name="assigned_position" value="';
					echo htmlspecialchars($result['assigned_position']);
					echo '">';
				echo '</p>';
				echo '<p><b>Notes:</b></p>';
				echo '<p>';
					echo '<textarea name="notes">';
					echo htmlspecialchars($result['notes']);
					echo '</textarea>';
				echo '</p>';
				if (!$result['payment_txn_id'] && $result['payment_lookup_key']) {
					echo '<p>';
						echo '<b>Confirmation &amp; Payment Link:</b> ';
						echo url_link($result['confirm_payment_url']);
					echo '</p>';
				}
				if ($result['payment_txn_id'] && $result['payment_lookup_key']) {
					echo '<p>';
						echo '<b>Review Order Link:</b> ';
						echo url_link($result['review_order_url']);
					echo '</p>';
				}
		echo '</div>';
		echo '<div class="card-buttons">';
			echo '<input type="hidden" name="action" value="save">';
			echo '<input type="hidden" name="id" value="'.$id.'">';
			echo '<input type="submit" name="submit" value="Update Status">';
		echo '</div>';
	echo '</form>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();