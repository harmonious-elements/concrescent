<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';
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
			if ($id) {
				$application_status = $_POST['application_status'];
				$table_id = array();
				foreach ($booth_tables as $table) {
					if (isset($_POST['table_id_' . $table['id']]) && $_POST['table_id_' . $table['id']]) {
						$table_id[] = $table['id'];
					}
				}
				$set = encode_booth(array(
					'replaced_by' => (($application_status == 'Cancelled') ? (int)$_POST['replaced_by'] : 0),
					'badge_id' => (int)$_POST['badge_id'],
					'application_status' => $application_status,
					'table_id' => $table_id,
				));
				$q = 'UPDATE '.db_table_name('booths').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
				
				$email_template = null;
				switch ($application_status) {
					case 'Accepted': $email_template = get_mail_template('booth_accepted', $conn); break;
					case 'Maybe': $email_template = get_mail_template('booth_maybe', $conn); break;
					case 'Rejected': $email_template = get_mail_template('booth_rejected', $conn); break;
				}
				if ($email_template && trim($email_template['body'])) {
					$results = mysql_query('SELECT * FROM '.db_table_name('booths').' WHERE `id` = '.$id, $conn);
					$result = mysql_fetch_assoc($results);
					$result = decode_booth($result, $badge_names);
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
	$results = mysql_query('SELECT * FROM '.db_table_name('booths').' WHERE `id` = '.$id, $conn);
	$result = mysql_fetch_assoc($results);
	$result = decode_booth($result, $badge_names);
	$name = $result['booth_name'];
	$extension_answers = get_extension_answers('booth', $id, $conn);
} else {
	header('Location: review_booths.php');
	exit(0);
}

render_admin_head($name ? ('Review Table Application - '.$name) : 'Review Table Application');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';
echo '<link rel="stylesheet" href="' . htmlspecialchars(resource_file_url('cmbtselect.css')) . '">';
echo '<style>.tag-map { padding-bottom: '.booth_map_aspect_ratio().'%; }</style>';

render_admin_body('Review Table Application');

echo '<div class="card">';
	echo '<form action="review_booth.php?id='.$id.'" method="post">';
		echo '<div class="card-content spaced">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<h1>' . htmlspecialchars($name) . '</h1>';
			echo '<p>';
				echo '<b>Table Type:</b> ';
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
			echo '<h2>Table Information</h2>';
			echo '<p>';
				echo '<b>Business Name:</b> ';
				echo htmlspecialchars($result['business_name']);
			echo '</p>';
			echo '<p>';
				echo '<b>Table Name:</b> ';
				echo htmlspecialchars($result['booth_name']);
			echo '</p>';
			echo '<p>';
				echo '<b>Number of Tables:</b> ';
				echo htmlspecialchars($result['num_tables']);
			echo '</p>';
			echo '<p>';
				echo '<b>Number of Staffers:</b> ';
				echo htmlspecialchars($result['num_staffers']);
			echo '</p>';
			echo render_extension_answers_h3_p($extension_questions, $extension_answers);
			echo '<hr>';
			echo '<h2>Staffer Information</h2>';
				function render_staffers() {
					global $id, $conn, $booth_info;
					$staffer_results = mysql_query('SELECT * FROM '.db_table_name('booth_staffers').' WHERE `booth_id` = '.$id.' ORDER BY `id`', $conn);
					while ($staffer_result = mysql_fetch_assoc($staffer_results)) {
						$staffer_result = decode_booth_staffer($staffer_result, $booth_info);
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
			echo '<h2>Change Table Type</h2>';
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
							foreach ($booth_info as $booth_id => $booth) {
								if ($booth_id != $id) {
									echo '<option value="'.$booth_id.'"';
									if ($result && $result['replaced_by'] == $booth_id) echo ' selected="selected"';
									echo '>BA'.$booth_id.' - '.htmlspecialchars($booth['booth_name']).'</option>';
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
			echo '<h2>Assigned Table</h2>';
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