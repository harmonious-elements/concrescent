<?php

require_once dirname(__FILE__).'/admin.php';

$conn = get_db_connection();
db_require_table('attendee_badges', $conn);
db_require_table('attendees', $conn);
$badge_names = get_attendee_badge_names($conn);

function get_count_total($badge_id, $connection) {
	$q = 'SELECT COUNT(*) FROM '.db_table_name('attendees');
	if ($badge_id) $q .= ' WHERE `badge_id` = '.(int)$badge_id;
	$results = mysql_query($q, $connection);
	$result = mysql_fetch_assoc($results);
	return $result['COUNT(*)'];
}

function get_count_completed($badge_id, $connection) {
	$q = 'SELECT COUNT(*) FROM '.db_table_name('attendees');
	$q .= ' WHERE `payment_status` = \'Completed\'';
	if ($badge_id) $q .= ' AND `badge_id` = '.(int)$badge_id;
	$results = mysql_query($q, $connection);
	$result = mysql_fetch_assoc($results);
	return $result['COUNT(*)'];
}

function get_count_checkedin($badge_id, $connection) {
	$q = 'SELECT COUNT(*) FROM '.db_table_name('attendees');
	$q .= ' WHERE `checkin_count`';
	if ($badge_id) $q .= ' AND `badge_id` = '.(int)$badge_id;
	$results = mysql_query($q, $connection);
	$result = mysql_fetch_assoc($results);
	return $result['COUNT(*)'];
}

render_admin_head('Attendee Overview');
render_admin_body('Attendee Overview');

echo '<div class="card">';
	echo '<div class="card-content">';
		echo '<table border="0" cellpadding="0" cellspacing="0">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Badge Type</th>';
					echo '<th class="td-numeric">In System</th>';
					echo '<th class="td-numeric">Payment Completed</th>';
					echo '<th class="td-numeric">Checked In</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
				foreach ($badge_names as $id => $name) {
					echo '<tr>';
						echo '<td>'.htmlspecialchars($name).'</td>';
						echo '<td class="td-numeric">'.get_count_total($id, $conn).'</td>';
						echo '<td class="td-numeric">'.get_count_completed($id, $conn).'</td>';
						echo '<td class="td-numeric">'.get_count_checkedin($id, $conn).'</td>';
					echo '</tr>';
				}
			echo '</tbody>';
			echo '<tfoot>';
				echo '<tr>';
					echo '<th>Total</th>';
					echo '<th class="td-numeric">'.get_count_total(0, $conn).'</th>';
					echo '<th class="td-numeric">'.get_count_completed(0, $conn).'</th>';
					echo '<th class="td-numeric">'.get_count_checkedin(0, $conn).'</th>';
				echo '</tr>';
			echo '</tfoot>';
		echo '</table>';
	echo '</div>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();