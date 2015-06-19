<?php

require_once dirname(__FILE__).'/application.php';
require_once dirname(__FILE__).'/../lib/base/util.php';
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
$badge_info = get_valid_staffer_badges($conn);
$extension_questions = get_active_extension_questions('staffer', $conn);
$descriptions = get_active_question_descriptions('staffer', $conn);

$result = null;
$errors = array();
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'save':
			$first_name = trim($_POST['first_name']);
			if (!$first_name) {
				$errors['first_name'] = 'First name is required.';
			}
			$last_name = trim($_POST['last_name']);
			if (!$last_name) {
				$errors['last_name'] = 'Last name is required.';
			}
			$fandom_name = trim($_POST['fandom_name']);
			$date_of_birth = parse_date(trim($_POST['date_of_birth']));
			if (!$date_of_birth) {
				$errors['date_of_birth'] = 'Date of birth is required.';
			}
			$badge_id = (int)$_POST['badge_id'];
			if (isset($badge_info[$badge_id])) {
				$badge = $badge_info[$badge_id];
				if ($date_of_birth && (
					($badge['min_birthdate'] && $date_of_birth < $badge['min_birthdate']) ||
					($badge['max_birthdate'] && $date_of_birth > $badge['max_birthdate'])
				)) {
					$errors['badge_id'] = 'The badge you selected is not applicable.';
				}
			} else {
				$errors['badge_id'] = 'The badge you selected is not available.';
			}
			$email_address = trim($_POST['email_address']);
			if (!$email_address) {
				$errors['email_address'] = 'Email address is required.';
			}
			$phone_number = trim($_POST['phone_number']);
			if (!$phone_number) {
				$errors['phone_number'] = 'Phone number is required.';
			} else if (strlen($phone_number) < 7) {
				$errors['phone_number'] = 'Phone number is too short.';
			}
			$address_1 = trim($_POST['address_1']);
			if (!$address_1) {
				$errors['address_1'] = 'Address is required.';
			}
			$address_2 = trim($_POST['address_2']);
			$city = trim($_POST['city']);
			if (!$city) {
				$errors['city'] = 'City is required.';
			}
			$state = trim($_POST['state']);
			$zip_code = trim($_POST['zip_code']);
			$country = trim($_POST['country']);
			$dates_available = array();
			$start_date = $GLOBALS['event_date_start_staff'];
			$end_date = $GLOBALS['event_date_end_staff'];
			while (strtotime($start_date) <= strtotime($end_date)) {
				if (isset($_POST['date_available_' . $start_date]) && $_POST['date_available_' . $start_date]) {
					$dates_available[] = $start_date;
				}
				$start_date = date('Y-m-d', strtotime('+1 day', strtotime($start_date)));
			}
			if (!count($dates_available)) {
				$errors['dates_available'] = 'At least one day must be selected.';
			}
			$ice_name = trim($_POST['ice_name']);
			$ice_relationship = trim($_POST['ice_relationship']);
			$ice_email_address = trim($_POST['ice_email_address']);
			$ice_phone_number = trim($_POST['ice_phone_number']);
			$payment_price = $badge ? $badge['price'] : 0;
			$result = array(
				'first_name' => $first_name,
				'last_name' => $last_name,
				'fandom_name' => $fandom_name,
				'date_of_birth' => $date_of_birth,
				'badge_id' => $badge_id,
				'email_address' => $email_address,
				'phone_number' => $phone_number,
				'address_1' => $address_1,
				'address_2' => $address_2,
				'city' => $city,
				'state' => $state,
				'zip_code' => $zip_code,
				'country' => $country,
				'dates_available' => $dates_available,
				'application_status' => 'Submitted',
				'assigned_position' => null,
				'notes' => null,
				'ice_name' => $ice_name,
				'ice_relationship' => $ice_relationship,
				'ice_email_address' => $ice_email_address,
				'ice_phone_number' => $ice_phone_number,
				'payment_status' => 'Incomplete',
				'payment_type' => null,
				'payment_txn_id' => null,
				'payment_price' => $payment_price,
				'payment_date' => null,
				'payment_details' => null,
				'payment_lookup_key' => 'UUID()',
			);
			$extension_answers = get_posted_extension_answers($extension_questions, $errors);
			if (!count($errors)) {
				$check_query = 'SELECT COUNT(*) FROM '.db_table_name('staffers').' WHERE '.encode_staffer_where(array(
					'first_name' => $first_name,
					'last_name' => $last_name,
					'date_of_birth' => $date_of_birth,
					'email_address' => $email_address,
				));
				$check = mysql_fetch_assoc(mysql_query($check_query));
				if ((int)$check['COUNT(*)']) {
					render_application_head('Staff Application');
					render_application_body('Staff Application');
					echo '<div class="card">';
						echo '<div class="card-title">Application Already Submitted</div>';
						echo '<div class="card-content">';
							echo '<p>A staff application for this person has already been submitted.</p>';
							echo '<p><a href="apply.php">Start a new application.</a></p>';
						echo '</div>';
					echo '</div>';
					render_application_tail();
					exit(0);
				}
				
				$set = encode_staffer($result);
				$q = 'INSERT INTO '.db_table_name('staffers').' SET '.$set.', `date_created` = NOW()';
				mysql_query($q, $conn);
				$staffer_id = (int)mysql_insert_id($conn);
				set_extension_answers('staffer', $staffer_id, $extension_answers, $conn);
				
				$email_template = get_mail_template('staff_submitted', $conn);
				if ($email_template && trim($email_template['body'])) {
					$results = mysql_query('SELECT * FROM '.db_table_name('staffers').' WHERE `id` = '.$staffer_id, $conn);
					$result = mysql_fetch_assoc($results);
					$result = decode_staffer($result, $badge_names);
					mail_send($result['email_address'], $email_template, $result);
				}
				
				render_application_head('Staff Application');
				render_application_body('Staff Application');
				echo '<div class="card">';
					echo '<div class="card-title">Application Submitted</div>';
					echo '<div class="card-content">';
						echo '<p>Your staff application has been submitted.</p>';
						echo '<p><a href="apply.php">Start a new application.</a></p>';
					echo '</div>';
				echo '</div>';
				render_application_tail();
				exit(0);
			}
			break;
	}
} else {
	$extension_answers = array();
}

render_application_head('Staff Application');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('jquery.js')) . '"></script>';
echo '<script>';
	echo 'var badge_ids = '.json_encode(array_keys($badge_info)).';';
	echo 'var badge_info = '.json_encode($badge_info).';';
echo '</script>';
echo '<script type="text/javascript" src="apply.js"></script>';

render_application_body('Staff Application');

echo '<div class="card">';
	if (!count($badge_info)) {
		echo '<div class="card-content">';
			echo '<p class="application-closed">';
				echo 'Staff applications for <b>'.htmlspecialchars($event_name).'</b> are currently closed.';
				if ($contact = get_mail_contact('staff_submitted', $conn)) {
					echo ' Please <b><a href="mailto:'.htmlspecialchars($contact).'">contact us</a></b> if you have any questions.';
				}
			echo '</p>';
		echo '</div>';
	} else {
		echo '<form action="apply.php" method="post">';
			echo '<div class="card-title">';
				echo 'Staff Application for ' . htmlspecialchars($event_name);
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
				if (isset($descriptions['Staff Application'])) {
					echo '<p class="intro">' . safe_html_string($descriptions['Staff Application']) . '</p>';
				}
				echo '<table border="0" cellpadding="0" cellspacing="0" class="form entity-record staffer-record">';
					echo '<thead>';
						echo '<tr><th colspan="2">Personal Information</th></tr>';
						if (isset($descriptions['Personal Information'])) {
							echo '<tr><td colspan="2">' . safe_html_string($descriptions['Personal Information']) . '</td></tr>';
						}
					echo '</thead>';
					echo '<tbody class="sh-per">';
						echo '<tr>';
							echo '<th><label for="first_name">First Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="first_name" value="';
								if ($result) echo htmlspecialchars($result['first_name']);
								echo '">';
								if (isset($errors['first_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['first_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="last_name">Last Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="last_name" value="';
								if ($result) echo htmlspecialchars($result['last_name']);
								echo '">';
								if (isset($errors['last_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['last_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="fandom_name">Fandom Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="fandom_name" value="';
								if ($result) echo htmlspecialchars($result['fandom_name']);
								echo '" class="input-fandom-name">';
								if (isset($errors['fandom_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['fandom_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="date_of_birth">Date of Birth:</label></th>';
							echo '<td>';
								echo '<input type="date" name="date_of_birth" value="';
								if ($result) echo htmlspecialchars($result['date_of_birth']);
								echo '" class="input-date-of-birth">';
								if (isset($errors['date_of_birth'])) {
									echo '<span class="error">'.htmlspecialchars($errors['date_of_birth']).'</span>';
								} else if (!ua('Chrome')) {
									echo ' (YYYY-MM-DD)';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="badge_id">Badge Type:</label></th>';
							echo '<td>';
								echo '<select name="badge_id" class="select-badge-id">';
									foreach ($badge_info as $badge_id => $badge) {
										echo '<option value="'.$badge_id.'"';
										if ($result && (int)$result['badge_id'] == $badge_id) echo ' selected="selected"';
										echo '>';
										echo htmlspecialchars($badge['name']);
										echo ' - ';
										echo htmlspecialchars($badge['price_string']);
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
					echo '</tbody>';
					echo '<thead>';
						echo '<tr><th colspan="2">Contact Information</th></tr>';
						if (isset($descriptions['Contact Information'])) {
							echo '<tr><td colspan="2">' . safe_html_string($descriptions['Contact Information']) . '</td></tr>';
						}
					echo '</thead>';
					echo '<tbody class="sh-con">';
						echo '<tr>';
							echo '<th><label for="email_address">Email Address:</label></th>';
							echo '<td>';
								echo '<input type="email" name="email_address" value="';
								if ($result) echo htmlspecialchars($result['email_address']);
								echo '">';
								if (isset($errors['email_address'])) {
									echo '<span class="error">'.htmlspecialchars($errors['email_address']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="phone_number">Phone Number:</label></th>';
							echo '<td>';
								echo '<input type="text" name="phone_number" value="';
								if ($result) echo htmlspecialchars($result['phone_number']);
								echo '">';
								if (isset($errors['phone_number'])) {
									echo '<span class="error">'.htmlspecialchars($errors['phone_number']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="address_1">Street Address:</label></th>';
							echo '<td>';
								echo '<input type="text" name="address_1" value="';
								if ($result) echo htmlspecialchars($result['address_1']);
								echo '">';
								if (isset($errors['address_1'])) {
									echo '<span class="error">'.htmlspecialchars($errors['address_1']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="address_2">Address Line 2:</label></th>';
							echo '<td>';
								echo '<input type="text" name="address_2" value="';
								if ($result) echo htmlspecialchars($result['address_2']);
								echo '">';
								if (isset($errors['address_2'])) {
									echo '<span class="error">'.htmlspecialchars($errors['address_2']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="city">City:</label></th>';
							echo '<td>';
								echo '<input type="text" name="city" value="';
								if ($result) echo htmlspecialchars($result['city']);
								echo '">';
								if (isset($errors['city'])) {
									echo '<span class="error">'.htmlspecialchars($errors['city']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="state">State or Province:</label></th>';
							echo '<td>';
								echo '<input type="text" name="state" value="';
								if ($result) echo htmlspecialchars($result['state']);
								echo '">';
								if (isset($errors['state'])) {
									echo '<span class="error">'.htmlspecialchars($errors['state']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="zip_code">ZIP or Postal Code:</label></th>';
							echo '<td>';
								echo '<input type="text" name="zip_code" value="';
								if ($result) echo htmlspecialchars($result['zip_code']);
								echo '">';
								if (isset($errors['zip_code'])) {
									echo '<span class="error">'.htmlspecialchars($errors['zip_code']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="country">Country:</label></th>';
							echo '<td>';
								echo '<input type="text" name="country" value="';
								if ($result) echo htmlspecialchars($result['country']);
								echo '">';
								if (isset($errors['country'])) {
									echo '<span class="error">'.htmlspecialchars($errors['country']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
					echo '</tbody>';
					echo '<thead>';
						echo '<tr><th colspan="2">Staffing Information</th></tr>';
						if (isset($descriptions['Staffing Information'])) {
							echo '<tr><td colspan="2">' . safe_html_string($descriptions['Staffing Information']) . '</td></tr>';
						}
					echo '</thead>';
					echo '<tbody class="sh-ext">';
						if (isset($descriptions['Dates Available'])) {
							echo '<tr><th></th><td><b><label>Dates Available:</label></b></td></tr>';
							echo '<tr><th></th><td>'.safe_html_string($descriptions['Dates Available']).'</td></tr>';
							echo '<tr><th></th><td>';
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
							echo '</td></tr>';
							if (isset($errors['dates_available'])) {
								echo '<tr><th></th><td><span class="error error-line">'.htmlspecialchars($errors['dates_available']).'</span></td></tr>';
							}
						} else {
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
									if (isset($errors['dates_available'])) {
										echo '<span class="error error-line">'.htmlspecialchars($errors['dates_available']).'</span>';
									}
								echo '</td>';
							echo '</tr>';
						}
						echo render_extension_answers_form($extension_questions, $extension_answers, $errors);
					echo '</tbody>';
					echo '<thead>';
						echo '<tr><th colspan="2">Emergency Contact Information</th></tr>';
						if (isset($descriptions['Emergency Contact Information'])) {
							echo '<tr><td colspan="2">' . safe_html_string($descriptions['Emergency Contact Information']) . '</td></tr>';
						}
					echo '</thead>';
					echo '<tbody class="sh-ice">';
						echo '<tr>';
							echo '<th><label for="ice_name">Emergency Contact Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="ice_name" value="';
								if ($result) echo htmlspecialchars($result['ice_name']);
								echo '">';
								if (isset($errors['ice_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['ice_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="ice_relationship">Emergency Contact Relationship:</label></th>';
							echo '<td>';
								echo '<input type="text" name="ice_relationship" value="';
								if ($result) echo htmlspecialchars($result['ice_relationship']);
								echo '">';
								if (isset($errors['ice_relationship'])) {
									echo '<span class="error">'.htmlspecialchars($errors['ice_relationship']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="ice_email_address">Emergency Contact Email Address:</label></th>';
							echo '<td>';
								echo '<input type="text" name="ice_email_address" value="';
								if ($result) echo htmlspecialchars($result['ice_email_address']);
								echo '">';
								if (isset($errors['ice_email_address'])) {
									echo '<span class="error">'.htmlspecialchars($errors['ice_email_address']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="ice_phone_number">Emergency Contact Phone Number:</label></th>';
							echo '<td>';
								echo '<input type="text" name="ice_phone_number" value="';
								if ($result) echo htmlspecialchars($result['ice_phone_number']);
								echo '">';
								if (isset($errors['ice_phone_number'])) {
									echo '<span class="error">'.htmlspecialchars($errors['ice_phone_number']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
					echo '</tbody>';
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