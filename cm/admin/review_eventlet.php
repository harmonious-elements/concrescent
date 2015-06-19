<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';
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
			if ($id) {
				$application_status = $_POST['application_status'];
				$set = encode_eventlet(array(
					'replaced_by' => (($application_status == 'Cancelled') ? (int)$_POST['replaced_by'] : 0),
					'badge_id' => (int)$_POST['badge_id'],
					'application_status' => $application_status,
				));
				$q = 'UPDATE '.db_table_name('eventlets').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
				
				$email_template = null;
				switch ($application_status) {
					case 'Accepted': $email_template = get_mail_template('eventlet_accepted', $conn); break;
					case 'Maybe': $email_template = get_mail_template('eventlet_maybe', $conn); break;
					case 'Rejected': $email_template = get_mail_template('eventlet_rejected', $conn); break;
				}
				if ($email_template && trim($email_template['body'])) {
					$results = mysql_query('SELECT * FROM '.db_table_name('eventlets').' WHERE `id` = '.$id, $conn);
					$result = mysql_fetch_assoc($results);
					$result = decode_eventlet($result, $badge_names);
					mail_send($result['contact_email_address'], $email_template, $result);
				}
				
				$changed = true;
			}
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
	header('Location: review_eventlets.php');
	exit(0);
}

render_admin_head($name ? ('Review Panel/Activity Application - '.$name) : 'Review Panel/Activity Application');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';

render_admin_body('Review Panel/Activity Application');

echo '<div class="card">';
	echo '<form action="review_eventlet.php?id='.$id.'" method="post">';
		echo '<div class="card-content spaced">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<h1>' . htmlspecialchars($name) . '</h1>';
			echo '<p>';
				echo '<b>Panel/Activity Type:</b> ';
				echo htmlspecialchars($result['badge_name']);
			echo '</p>';
			echo '<hr>';
			echo '<h2>Primary Contact Information</h2>';
			echo '<p>';
				echo '<b>Name:</b> ';
				echo htmlspecialchars($result['contact_real_name']);
			echo '</p>';
			echo '<p>';
				echo '<b>Email Address:</b> ';
				echo email_link($result['contact_email_address']);
			echo '</p>';
			echo '<p>';
				echo '<b>Phone Number:</b> ';
				echo htmlspecialchars($result['contact_phone_number']);
			echo '</p>';
			echo '<hr>';
			echo '<h2>Panel/Activity Information</h2>';
			echo '<p>';
				echo '<b>Panel/Activity Name:</b> ';
				echo htmlspecialchars($result['eventlet_name']);
			echo '</p>';
			echo '<p>';
				echo '<b>Panel/Activity Description:</b> ';
				echo htmlspecialchars($result['eventlet_description']);
			echo '</p>';
			echo '<p>';
				echo '<b>Number of Panelists/Hosts:</b> ';
				echo htmlspecialchars($result['num_staffers']);
			echo '</p>';
			echo render_extension_answers_h3_p($extension_questions, $extension_answers);
			echo '<hr>';
			echo '<h2>Panelist/Host Information</h2>';
				function render_staffers() {
					global $id, $conn, $eventlet_info;
					$staffer_results = mysql_query('SELECT * FROM '.db_table_name('eventlet_staffers').' WHERE `eventlet_id` = '.$id.' ORDER BY `id`', $conn);
					while ($staffer_result = mysql_fetch_assoc($staffer_results)) {
						$staffer_result = decode_eventlet_staffer($staffer_result, $eventlet_info);
						echo '<tr>';
							echo '<td>'.htmlspecialchars($staffer_result['real_name']).'</td>';
							echo '<td>'.htmlspecialchars($staffer_result['fandom_name']).'</td>';
							echo '<td>'.email_link($staffer_result['email_address']).'</td>';
							echo '<td>'.htmlspecialchars($staffer_result['phone_number']).'</td>';
							echo '<td>'.($staffer_result['attendee_id'] ? 'Yes' : 'No').'</td>';
						echo '</tr>';
					}
				}
				render_list_table(array(
					'Real Name', 'Fandom Name', 'Email Address',
					'Phone Number', 'Already Registered'
				), 'render_staffers', false, $conn, false);
			echo '<hr>';
			echo '<h2>Change Panel/Activity Type</h2>';
				echo '<p><select name="badge_id">';
					foreach ($badge_names as $badge_id => $badge_name) {
						echo '<option value="'.$badge_id.'"';
						if ($result && (int)$result['badge_id'] == $badge_id) echo ' selected="selected"';
						echo '>'.htmlspecialchars($badge_name).'</option>';
					}
				echo '</select></p>';
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
							foreach ($eventlet_info as $eventlet_id => $eventlet) {
								if ($eventlet_id != $id) {
									echo '<option value="'.$eventlet_id.'"';
									if ($result && $result['replaced_by'] == $eventlet_id) echo ' selected="selected"';
									echo '>EA'.$eventlet_id.' - '.htmlspecialchars($eventlet['eventlet_name']).'</option>';
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