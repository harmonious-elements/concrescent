<?php

require_once dirname(__FILE__).'/admin.php';

$conn = get_db_connection();
db_require_table('guest_badges', $conn);
db_require_table('guests', $conn);
db_require_table('guest_supporters', $conn);
$badge_names = get_guest_badge_names($conn);
$guest_info = get_guest_info($conn, $badge_names);

$id = 0;
$changed = false;
if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'save':
			$set = encode_guest_supporter($_POST);
			if ($id) {
				$q = 'UPDATE '.db_table_name('guest_supporters').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
			} else {
				$q = 'INSERT INTO '.db_table_name('guest_supporters').' SET '.$set.', `date_created` = NOW()';
				mysql_query($q, $conn);
				$id = (int)mysql_insert_id($conn);
			}
			$changed = true;
			break;
	}
} else if (isset($_GET['id'])) {
	$id = (int)$_GET['id'];
}

if ($id) {
	$results = mysql_query('SELECT * FROM '.db_table_name('guest_supporters').' WHERE `id` = '.$id, $conn);
	$result = mysql_fetch_assoc($results);
	$result = decode_guest_supporter($result, $guest_info);
	$name = $result['real_name'];
	$eid = $result['guest_id'];
} else {
	$result = null;
	$name = null;
	$eid = (isset($_GET['guest_id']) && $_GET['guest_id']) ? (int)$_GET['guest_id'] : null;
}

render_admin_head($name ? ('Edit Guests & Supporters - '.$name) : 'Add Guests & Supporters');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';

render_admin_body($name ? 'Edit Guests & Supporters' : 'Add Guests & Supporters');

echo '<div class="card">';
	echo '<form action="guest_supporter.php?id='.$id.'" method="post">';
		echo '<div class="card-content">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="form entity-record guest-supporter-record">';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-per">Personal Information</div></th></tr></thead>';
				echo '<tbody class="sh-per">';
					echo '<tr>';
						echo '<th><label for="guest_id">Guest Application:</label></th>';
						echo '<td><select name="guest_id">';
							foreach ($guest_info as $guest_id => $guest) {
								echo '<option value="'.$guest_id.'"';
								if ($eid && $eid == $guest_id) echo ' selected="selected"';
								echo '>GA'.$guest_id.' - '.htmlspecialchars($guest['guest_name']).'</option>';
							}
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="first_name">First Name:</label></th>';
						echo '<td><input type="text" name="first_name" value="';
						if ($result) echo htmlspecialchars($result['first_name']);
						echo '" class="epa-first-name"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="last_name">Last Name:</label></th>';
						echo '<td><input type="text" name="last_name" value="';
						if ($result) echo htmlspecialchars($result['last_name']);
						echo '" class="epa-last-name"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="fandom_name">Fandom Name:</label></th>';
						echo '<td><input type="text" name="fandom_name" value="';
						if ($result) echo htmlspecialchars($result['fandom_name']);
						echo '" class="epa-fandom-name"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="name_on_badge">Name on Badge:</label></th>';
						echo '<td><select name="name_on_badge" class="epa-name-on-badge">';
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
						echo '" class="epa-date-of-birth">';
						if (!ua('Chrome')) echo ' (YYYY-MM-DD)';
						echo '</td>';
					echo '</tr>';
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-con">Contact Information</div></th></tr></thead>';
				echo '<tbody class="sh-con'.($id ? ' hidden' : '').'">';
					echo '<tr>';
						echo '<th><label for="email_address">Email Address:</label></th>';
						echo '<td><input type="email" name="email_address" value="';
						if ($result) echo htmlspecialchars($result['email_address']);
						echo '" class="epa-email-address"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="phone_number">Phone Number:</label></th>';
						echo '<td><input type="text" name="phone_number" value="';
						if ($result) echo htmlspecialchars($result['phone_number']);
						echo '" class="epa-phone-number"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="address_1">Street Address:</label></th>';
						echo '<td><input type="text" name="address_1" value="';
						if ($result) echo htmlspecialchars($result['address_1']);
						echo '" class="epa-address-1"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="address_2">Address Line 2:</label></th>';
						echo '<td><input type="text" name="address_2" value="';
						if ($result) echo htmlspecialchars($result['address_2']);
						echo '" class="epa-address-2"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="city">City:</label></th>';
						echo '<td><input type="text" name="city" value="';
						if ($result) echo htmlspecialchars($result['city']);
						echo '" class="epa-city"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="state">State or Province:</label></th>';
						echo '<td><input type="text" name="state" value="';
						if ($result) echo htmlspecialchars($result['state']);
						echo '" class="epa-state"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="zip_code">ZIP or Postal Code:</label></th>';
						echo '<td><input type="text" name="zip_code" value="';
						if ($result) echo htmlspecialchars($result['zip_code']);
						echo '" class="epa-zip-code"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="country">Country:</label></th>';
						echo '<td><input type="text" name="country" value="';
						if ($result) echo htmlspecialchars($result['country']);
						echo '" class="epa-country"></td>';
					echo '</tr>';
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-ice">Emergency Contact Information</div></th></tr></thead>';
				echo '<tbody class="sh-ice'.($id ? ' hidden' : '').'">';
					echo '<tr>';
						echo '<th><label for="ice_name">Emergency Contact Name:</label></th>';
						echo '<td><input type="text" name="ice_name" value="';
						if ($result) echo htmlspecialchars($result['ice_name']);
						echo '" class="epa-ice-name"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="ice_relationship">Emergency Contact Relationship:</label></th>';
						echo '<td><input type="text" name="ice_relationship" value="';
						if ($result) echo htmlspecialchars($result['ice_relationship']);
						echo '" class="epa-ice-relationship"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="ice_email_address">Emergency Contact Email Address:</label></th>';
						echo '<td><input type="text" name="ice_email_address" value="';
						if ($result) echo htmlspecialchars($result['ice_email_address']);
						echo '" class="epa-ice-email-address"></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="ice_phone_number">Emergency Contact Phone Number:</label></th>';
						echo '<td><input type="text" name="ice_phone_number" value="';
						if ($result) echo htmlspecialchars($result['ice_phone_number']);
						echo '" class="epa-ice-phone-number"></td>';
					echo '</tr>';
				echo '</tbody>';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-rcd">Record Information</div></th></tr></thead>';
				echo '<tbody class="sh-rcd">';
					if ($result) {
						echo '<tr>';
							echo '<th><label>ID Number:</label></th>';
							echo '<td>G' . $result['id'] . '</td>';
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