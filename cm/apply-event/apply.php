<?php

require_once dirname(__FILE__).'/application.php';
require_once dirname(__FILE__).'/../lib/base/util.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';

function render_staffer_form($i, $result, $errors) {
	echo '<thead class="sh-stf sh-stf-'.$i.'"><tr><th colspan="2">Panelist/Host #'.($i + 1).'</th></tr></thead>';
	echo '<tbody class="sh-stf sh-stf-'.$i.'">';
		echo '<tr>';
			echo '<th><label for="first_name_'.$i.'">First Name:</label></th>';
			echo '<td>';
				echo '<input type="text" name="first_name_'.$i.'" value="';
				if ($result) echo htmlspecialchars($result['first_name']);
				echo '">';
				if (isset($errors['first_name_'.$i])) {
					echo '<span class="error">'.htmlspecialchars($errors['first_name_'.$i]).'</span>';
				}
			echo '</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th><label for="last_name_'.$i.'">Last Name:</label></th>';
			echo '<td>';
				echo '<input type="text" name="last_name_'.$i.'" value="';
				if ($result) echo htmlspecialchars($result['last_name']);
				echo '">';
				if (isset($errors['last_name_'.$i])) {
					echo '<span class="error">'.htmlspecialchars($errors['last_name_'.$i]).'</span>';
				}
			echo '</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th><label for="fandom_name_'.$i.'">Fandom Name:</label></th>';
			echo '<td>';
				echo '<input type="text" name="fandom_name_'.$i.'" value="';
				if ($result) echo htmlspecialchars($result['fandom_name']);
				echo '" class="input-fandom-name">';
				if (isset($errors['fandom_name_'.$i])) {
					echo '<span class="error">'.htmlspecialchars($errors['fandom_name_'.$i]).'</span>';
				}
			echo '</td>';
		echo '</tr>';
		echo '<tr class="tr-name-on-badge">';
			echo '<th><label for="name_on_badge_'.$i.'">Name on Badge:</label></th>';
			echo '<td>';
				echo '<select name="name_on_badge_'.$i.'" class="select-name-on-badge">';
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
				echo '</select>';
				if (isset($errors['name_on_badge_'.$i])) {
					echo '<span class="error">'.htmlspecialchars($errors['name_on_badge_'.$i]).'</span>';
				}
			echo '</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th><label for="date_of_birth_'.$i.'">Date of Birth:</label></th>';
			echo '<td>';
				echo '<input type="date" name="date_of_birth_'.$i.'" value="';
				if ($result) echo htmlspecialchars($result['date_of_birth']);
				echo '" class="input-date-of-birth">';
				if (isset($errors['date_of_birth_'.$i])) {
					echo '<span class="error">'.htmlspecialchars($errors['date_of_birth_'.$i]).'</span>';
				} else if (!ua('Chrome')) {
					echo ' (YYYY-MM-DD)';
				}
			echo '</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th><label for="email_address_'.$i.'">Email Address:</label></th>';
			echo '<td>';
				echo '<input type="email" name="email_address_'.$i.'" value="';
				if ($result) echo htmlspecialchars($result['email_address']);
				echo '">';
				if (isset($errors['email_address_'.$i])) {
					echo '<span class="error">'.htmlspecialchars($errors['email_address_'.$i]).'</span>';
				}
			echo '</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th><label for="phone_number_'.$i.'">Phone Number:</label></th>';
			echo '<td>';
				echo '<input type="text" name="phone_number_'.$i.'" value="';
				if ($result) echo htmlspecialchars($result['phone_number']);
				echo '">';
				if (isset($errors['phone_number_'.$i])) {
					echo '<span class="error">'.htmlspecialchars($errors['phone_number_'.$i]).'</span>';
				}
			echo '</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th><label>Already Registered:</label></th>';
			echo '<td>';
				echo '<label><input type="checkbox" name="already_registered_'.$i.'"';
				if (($result && $result['attendee_id']) || isset($errors['already_registered_'.$i])) echo ' checked="checked"';
				echo '>This panelist/host has already registered as an attendee.</label>';
				if (isset($errors['already_registered_'.$i])) {
					echo '<span class="error">'.htmlspecialchars($errors['already_registered_'.$i]).'</span>';
				}
			echo '</td>';
		echo '</tr>';
	echo '</tbody>';
}

if (isset($_POST['render_new_staffer_form'])) {
	$i = (int)$_POST['render_new_staffer_form'];
	render_staffer_form($i, null, array());
	exit(0);
}

$conn = get_db_connection();
db_require_table('attendees', $conn);
db_require_table('eventlet_badges', $conn);
db_require_table('eventlet_extension_questions', $conn);
db_require_table('eventlets', $conn);
db_require_table('eventlet_extension_answers', $conn);
db_require_table('eventlet_staffers', $conn);
$badge_names = get_eventlet_badge_names($conn);
$badge_info = get_valid_eventlet_badges($conn);
$extension_questions = get_active_extension_questions('eventlet', $conn);
$descriptions = get_active_question_descriptions('eventlet', $conn);

$result = null;
$errors = array();
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'save':
			$contact_first_name = trim($_POST['contact_first_name']);
			if (!$contact_first_name) {
				$errors['contact_first_name'] = 'First name is required.';
			}
			$contact_last_name = trim($_POST['contact_last_name']);
			if (!$contact_last_name) {
				$errors['contact_last_name'] = 'Last name is required.';
			}
			$contact_email_address = trim($_POST['contact_email_address']);
			if (!$contact_email_address) {
				$errors['contact_email_address'] = 'Email address is required.';
			}
			$contact_phone_number = trim($_POST['contact_phone_number']);
			if (!$contact_phone_number) {
				$errors['contact_phone_number'] = 'Phone number is required.';
			} else if (strlen($contact_phone_number) < 7) {
				$errors['contact_phone_number'] = 'Phone number is too short.';
			}
			$badge_id = (int)$_POST['badge_id'];
			if (isset($badge_info[$badge_id])) {
				$badge = $badge_info[$badge_id];
			} else {
				$errors['badge_id'] = 'The panel or activity type you selected is not available.';
			}
			$eventlet_name = trim($_POST['eventlet_name']);
			if (!$eventlet_name) {
				$errors['eventlet_name'] = 'Panel or activity name is required.';
			}
			$eventlet_description = trim($_POST['eventlet_description']);
			if (!$eventlet_description) {
				$errors['eventlet_description'] = 'Panel or activity description is required.';
			}
			$num_staffers = (int)trim($_POST['num_staffers']);
			if ($num_staffers < 1) {
				$errors['num_staffers'] = 'Number of panelists/hosts must be at least 1.';
			} else if ($badge['max_staffers'] && $num_staffers > $badge['max_staffers']) {
				$errors['num_staffers'] = 'Number of panelists/hosts is limited to at most '.$badge['max_staffers'].'.';
			}
			$result = array(
				'contact_first_name' => $contact_first_name,
				'contact_last_name' => $contact_last_name,
				'contact_email_address' => $contact_email_address,
				'contact_phone_number' => $contact_phone_number,
				'badge_id' => $badge_id,
				'eventlet_name' => $eventlet_name,
				'eventlet_description' => $eventlet_description,
				'num_staffers' => $num_staffers,
				'application_status' => 'Submitted',
				'payment_status' => 'Incomplete',
				'payment_type' => null,
				'payment_txn_id' => null,
				'payment_original_price' => null,
				'payment_final_price' => null,
				'payment_date' => null,
				'payment_details' => null,
				'payment_lookup_key' => 'UUID()',
			);
			$extension_answers = get_posted_extension_answers($extension_questions, $errors);
			$eventlet_staffers = array();
			for ($i = 0; $i < $num_staffers; $i++) {
				$first_name = trim($_POST['first_name_'.$i]);
				if (!$first_name) {
					$errors['first_name_'.$i] = 'First name is required.';
				}
				$last_name = trim($_POST['last_name_'.$i]);
				if (!$last_name) {
					$errors['last_name_'.$i] = 'Last name is required.';
				}
				$fandom_name = trim($_POST['fandom_name_'.$i]);
				$name_on_badge = $fandom_name ? trim($_POST['name_on_badge_'.$i]) : 'RealOnly';
				if (!(
					$name_on_badge == 'FandomReal' ||
					$name_on_badge == 'RealFandom' ||
					$name_on_badge == 'FandomOnly' ||
					$name_on_badge == 'RealOnly'
				)) {
					$errors['name_on_badge_'.$i] = 'Name on badge is required.';
				}
				$date_of_birth = parse_date(trim($_POST['date_of_birth_'.$i]));
				if (!$date_of_birth) {
					$errors['date_of_birth_'.$i] = 'Date of birth is required.';
				}
				$email_address = trim($_POST['email_address_'.$i]);
				if (!$email_address) {
					$errors['email_address_'.$i] = 'Email address is required.';
				}
				$phone_number = trim($_POST['phone_number_'.$i]);
				if (!$phone_number) {
					$errors['phone_number_'.$i] = 'Phone number is required.';
				} else if (strlen($phone_number) < 7) {
					$errors['phone_number_'.$i] = 'Phone number is too short.';
				}
				$attendee_id = null;
				if ($_POST['already_registered_'.$i]) {
					$q = ('SELECT * FROM '.db_table_name('attendees').
					     ' WHERE `first_name` LIKE "'.purify_string($first_name).'"'.
					     ' AND `last_name` LIKE "'.purify_string($last_name).'"'.
					     ' AND (`date_of_birth` LIKE "'.purify_string($date_of_birth).'"'.
					     ' OR `email_address` LIKE "'.purify_string($email_address).'"'.
					     ' OR `phone_number` LIKE "'.purify_string($phone_number).'")'.
					     ' AND `payment_status` = "Completed"'.
					     ' ORDER BY `payment_final_price` DESC');
					$r = mysql_query($q, $conn);
					if ($s = mysql_fetch_assoc($r)) {
						$attendee_id = (int)$s['id'];
					} else {
						$errors['already_registered_'.$i] = 'Could not find existing registration.';
					}
				}
				$eventlet_staffers[] = array(
					'first_name' => $first_name,
					'last_name' => $last_name,
					'fandom_name' => $fandom_name,
					'name_on_badge' => $name_on_badge,
					'date_of_birth' => $date_of_birth,
					'email_address' => $email_address,
					'phone_number' => $phone_number,
					'attendee_id' => $attendee_id,
				);
			}
			if (!count($errors)) {
				$set = encode_eventlet($result);
				$q = 'INSERT INTO '.db_table_name('eventlets').' SET '.$set.', `date_created` = NOW()';
				mysql_query($q, $conn);
				$eventlet_id = (int)mysql_insert_id($conn);
				set_extension_answers('eventlet', $eventlet_id, $extension_answers, $conn);
				foreach ($eventlet_staffers as $eventlet_staffer) {
					$set = encode_eventlet_staffer(array_merge(array('eventlet_id' => $eventlet_id), $eventlet_staffer));
					$q = 'INSERT INTO '.db_table_name('eventlet_staffers').' SET '.$set.', `date_created` = NOW()';
					mysql_query($q, $conn);
				}
				
				$email_template = get_mail_template('eventlet_submitted', $conn);
				if ($email_template && trim($email_template['body'])) {
					$results = mysql_query('SELECT * FROM '.db_table_name('eventlets').' WHERE `id` = '.$eventlet_id, $conn);
					$result = mysql_fetch_assoc($results);
					$result = decode_eventlet($result, $badge_names);
					mail_send($result['contact_email_address'], $email_template, $result);
				}
				
				render_application_head('Panel/Activity Application');
				render_application_body('Panel/Activity Application');
				echo '<div class="card">';
					echo '<div class="card-title">Application Submitted</div>';
					echo '<div class="card-content">';
						echo '<p>Your panel or activity application has been submitted.</p>';
					echo '</div>';
				echo '</div>';
				render_application_tail();
				exit(0);
			}
			break;
	}
} else {
	$extension_answers = array();
	$eventlet_staffers = array();
}

render_application_head('Panel/Activity Application');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('jquery.js')) . '"></script>';
echo '<script>';
	echo 'var badge_ids = '.json_encode(array_keys($badge_info)).';';
	echo 'var badge_info = '.json_encode($badge_info).';';
echo '</script>';
echo '<script type="text/javascript" src="apply.js"></script>';

render_application_body('Panel/Activity Application');

echo '<div class="card">';
	if (!count($badge_info)) {
		echo '<div class="card-content">';
			echo '<p class="application-closed">';
				echo 'Panel and activity applications for <b>'.htmlspecialchars($event_name).'</b> are currently closed.';
				if ($contact = get_mail_contact('eventlet_submitted', $conn)) {
					echo ' Please <b><a href="mailto:'.htmlspecialchars($contact).'">contact us</a></b> if you have any questions.';
				}
			echo '</p>';
		echo '</div>';
	} else {
		echo '<form action="apply.php" method="post">';
			echo '<div class="card-title">';
				echo 'Panel/Activity Application for ' . htmlspecialchars($event_name);
			echo '</div>';
			echo '<div class="card-content">';
				if (count($errors)) {
					echo '<div class="error-notification">';
					echo '<h2>You\'re not done yet!</h2>';
					echo '<p>';
					echo 'Some information was missing from your application.';
					echo ' Please address the issues in red and try submitting again.';
					echo ' Your application has not been submitted until you see the message &ldquo;Application Submitted.&rdquo;';
					echo '</p>';
					echo '</div>';
				}
				if (isset($descriptions['Panel/Activity Application'])) {
					echo '<p class="intro">' . safe_html_string($descriptions['Panel/Activity Application']) . '</p>';
				}
				echo '<table border="0" cellpadding="0" cellspacing="0" class="form entity-record eventlet-record">';
					echo '<thead>';
						echo '<tr><th colspan="2">Primary Contact Information</th></tr>';
						if (isset($descriptions['Primary Contact Information'])) {
							echo '<tr><td colspan="2">' . safe_html_string($descriptions['Primary Contact Information']) . '</td></tr>';
						}
					echo '</thead>';
					echo '<tbody class="sh-con">';
						echo '<tr>';
							echo '<th><label for="contact_first_name">First Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="contact_first_name" value="';
								if ($result) echo htmlspecialchars($result['contact_first_name']);
								echo '">';
								if (isset($errors['contact_first_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['contact_first_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="contact_last_name">Last Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="contact_last_name" value="';
								if ($result) echo htmlspecialchars($result['contact_last_name']);
								echo '">';
								if (isset($errors['contact_last_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['contact_last_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="contact_email_address">Email Address:</label></th>';
							echo '<td>';
								echo '<input type="email" name="contact_email_address" value="';
								if ($result) echo htmlspecialchars($result['contact_email_address']);
								echo '">';
								if (isset($errors['contact_email_address'])) {
									echo '<span class="error">'.htmlspecialchars($errors['contact_email_address']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="contact_phone_number">Phone Number:</label></th>';
							echo '<td>';
								echo '<input type="text" name="contact_phone_number" value="';
								if ($result) echo htmlspecialchars($result['contact_phone_number']);
								echo '">';
								if (isset($errors['contact_phone_number'])) {
									echo '<span class="error">'.htmlspecialchars($errors['contact_phone_number']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
					echo '</tbody>';
					echo '<thead>';
						echo '<tr><th colspan="2">Panel/Activity Information</th></tr>';
						if (isset($descriptions['Panel/Activity Information'])) {
							echo '<tr><td colspan="2">' . safe_html_string($descriptions['Panel/Activity Information']) . '</td></tr>';
						}
					echo '</thead>';
					echo '<tbody class="sh-ext">';
						echo '<tr>';
							echo '<th><label for="badge_id">Panel/Activity Type:</label></th>';
							echo '<td>';
								echo '<select name="badge_id" class="select-badge-id">';
									foreach ($badge_info as $badge_id => $badge) {
										echo '<option value="'.$badge_id.'"';
										if ($result && (int)$result['badge_id'] == $badge_id) echo ' selected="selected"';
										echo '>';
										echo htmlspecialchars($badge['name']);
										echo '</option>';
									}
								echo '</select>';
								if (isset($errors['badge_id'])) {
									echo '<span class="error">'.htmlspecialchars($errors['badge_id']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr class="tr-badge-description hidden">';
							echo '<th></th>';
							echo '<td><div class="td-badge-description"></div></td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="eventlet_name">Panel/Activity Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="eventlet_name" value="';
								if ($result) echo htmlspecialchars($result['eventlet_name']);
								echo '">';
								if (isset($errors['eventlet_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['eventlet_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="eventlet_description">Panel/Activity Description:</label></th>';
							echo '<td>';
								echo '<textarea name="eventlet_description">';
								if ($result) echo htmlspecialchars($result['eventlet_description']);
								echo '</textarea>';
								if (isset($errors['eventlet_description'])) {
									echo '<span class="error error-line">'.htmlspecialchars($errors['eventlet_description']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo render_extension_answers_form($extension_questions, $extension_answers, $errors);
					echo '</tbody>';
					echo '<thead>';
						echo '<tr><th colspan="2">Panelist/Host Information</th></tr>';
						if (isset($descriptions['Panelist/Host Information'])) {
							echo '<tr><td colspan="2">' . safe_html_string($descriptions['Panelist/Host Information']) . '</td></tr>';
						}
					echo '</thead>';
					echo '<tbody class="sh-stc">';
						echo '<tr>';
							echo '<th><label for="num_staffers">Number of Panelists/Hosts:</label></th>';
							echo '<td>';
								echo '<input type="number" min="1" name="num_staffers" value="';
								if ($result) echo htmlspecialchars($result['num_staffers']);
								else echo '1';
								echo '" class="epa-num-staffers">';
								if (isset($errors['num_staffers'])) {
									echo '<span class="error">'.htmlspecialchars($errors['num_staffers']).'</span>';
								} else {
									echo '<span class="rate-num-staffers"></span>';
								}
							echo '</td>';
						echo '</tr>';
					echo '</tbody>';
					$num_staffers = $result ? max($result['num_staffers'], 1) : 1;
					for ($i = 0; $i < $num_staffers; $i++) {
						render_staffer_form($i, ($eventlet_staffers ? $eventlet_staffers[$i] : null), $errors);
					}
				echo '</table>';
			echo '</div>';
			echo '<div class="card-buttons">';
				echo '<input type="hidden" name="action" value="save">';
				echo '<input type="submit" name="submit" value="Apply" class="apply-button">';
			echo '</div>';
		echo '</form>';
	}
echo '</div>';

render_application_tail();