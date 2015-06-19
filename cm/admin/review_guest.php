<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';
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
			if ($id) {
				$application_status = $_POST['application_status'];
				$set = encode_guest(array(
					'replaced_by' => (($application_status == 'Cancelled') ? (int)$_POST['replaced_by'] : 0),
					'badge_id' => (int)$_POST['badge_id'],
					'application_status' => $application_status,
					'contract_status' => $_POST['contract_status'],
				));
				$q = 'UPDATE '.db_table_name('guests').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
				
				$email_template = null;
				switch ($application_status) {
					case 'Accepted': $email_template = get_mail_template('guest_accepted', $conn); break;
					case 'Maybe': $email_template = get_mail_template('guest_maybe', $conn); break;
					case 'Rejected': $email_template = get_mail_template('guest_rejected', $conn); break;
				}
				if ($email_template && trim($email_template['body'])) {
					$results = mysql_query('SELECT * FROM '.db_table_name('guests').' WHERE `id` = '.$id, $conn);
					$result = mysql_fetch_assoc($results);
					$result = decode_guest($result, $badge_names);
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
	$results = mysql_query('SELECT * FROM '.db_table_name('guests').' WHERE `id` = '.$id, $conn);
	$result = mysql_fetch_assoc($results);
	$result = decode_guest($result, $badge_names);
	$name = $result['guest_name'];
	$extension_answers = get_extension_answers('guest', $id, $conn);
} else {
	header('Location: review_guests.php');
	exit(0);
}

render_admin_head($name ? ('Review Guest Application - '.$name) : 'Review Guest Application');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';

render_admin_body('Review Guest Application');

echo '<div class="card">';
	echo '<form action="review_guest.php?id='.$id.'" method="post">';
		echo '<div class="card-content spaced">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<h1>' . htmlspecialchars($name) . '</h1>';
			echo '<p>';
				echo '<b>Badge Type:</b> ';
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
			echo '<h2>Guest Information</h2>';
			echo '<p>';
				echo '<b>Guest Name:</b> ';
				echo htmlspecialchars($result['guest_name']);
			echo '</p>';
			echo '<p>';
				echo '<b>Guest Description:</b> ';
				echo htmlspecialchars($result['guest_description']);
			echo '</p>';
			echo '<p>';
				echo '<b>Number of Guests/Supporters:</b> ';
				echo htmlspecialchars($result['num_supporters']);
			echo '</p>';
			echo render_extension_answers_h3_p($extension_questions, $extension_answers);
			echo '<hr>';
			echo '<h2>Guest &amp; Supporter Information</h2>';
				function render_staffers() {
					global $id, $conn, $guest_info;
					$supporter_results = mysql_query('SELECT * FROM '.db_table_name('guest_supporters').' WHERE `guest_id` = '.$id.' ORDER BY `id`', $conn);
					while ($supporter_result = mysql_fetch_assoc($supporter_results)) {
						$supporter_result = decode_guest_supporter($supporter_result, $guest_info);
						echo '<tr>';
							echo '<td>'.htmlspecialchars($supporter_result['real_name']).'</td>';
							echo '<td>'.htmlspecialchars($supporter_result['fandom_name']).'</td>';
							echo '<td>'.email_link($supporter_result['email_address']).'</td>';
							echo '<td>'.htmlspecialchars($supporter_result['phone_number']).'</td>';
						echo '</tr>';
					}
				}
				render_list_table(array(
					'Real Name', 'Fandom Name',
					'Email Address', 'Phone Number'
				), 'render_staffers', false, $conn, false);
			echo '<hr>';
			echo '<h2>Change Badge Type</h2>';
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
							foreach ($guest_info as $guest_id => $guest) {
								if ($guest_id != $id) {
									echo '<option value="'.$guest_id.'"';
									if ($result && $result['replaced_by'] == $guest_id) echo ' selected="selected"';
									echo '>BA'.$guest_id.' - '.htmlspecialchars($guest['guest_name']).'</option>';
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
			echo '<h2>Contract Status</h2>';
				echo '<p>';
					echo '<label>';
						echo '<input type="radio" name="contract_status" value="Incomplete"';
						if ($result['contract_status'] == 'Incomplete') echo ' checked="checked"';
						echo '>Incomplete';
					echo '</label>';
					echo '<br>';
					echo '<label>';
						echo '<input type="radio" name="contract_status" value="Cancelled"';
						if ($result['contract_status'] == 'Cancelled') echo ' checked="checked"';
						echo '>Cancelled by Applicant';
					echo '</label>';
					echo '<br>';
					echo '<label>';
						echo '<input type="radio" name="contract_status" value="Completed"';
						if ($result['contract_status'] == 'Completed') echo ' checked="checked"';
						echo '>Completed';
					echo '</label>';
					echo '<br>';
					echo '<label>';
						echo '<input type="radio" name="contract_status" value="Refunded"';
						if ($result['contract_status'] == 'Refunded') echo ' checked="checked"';
						echo '>Refunded';
					echo '</label>';
					echo '<br>';
					echo '<label>';
						echo '<input type="radio" name="contract_status" value="Pulled"';
						if ($result['contract_status'] == 'Pulled') echo ' checked="checked"';
						echo '>Badge Pulled';
					echo '</label>';
				echo '</p>';
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