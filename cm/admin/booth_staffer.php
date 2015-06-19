<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

if (isset($_POST['action']) && $_POST['action'] == 'list') {
	header('Content-type: text/plain');
	$attendees = array();
	$start_id = (int)$_POST['start_id'];
	$end_id = $start_id;
	$batch_size = 100;
	
	$conn = get_db_connection();
	db_require_table('attendee_badges', $conn);
	db_require_table('attendees', $conn);
	$badge_names = get_attendee_badge_names($conn);
	
	$results = mysql_query('SELECT * FROM '.db_table_name('attendees').' WHERE `id` >= '.$start_id.' ORDER BY `id` LIMIT '.$batch_size, $conn);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_attendee($result, $badge_names);
		$html_content = '<tr>';
		$html_content .= '<td>A'.htmlspecialchars($result['id']).'</td>';
		$html_content .= '<td>'.htmlspecialchars($result['real_name']).'</td>';
		$html_content .= '<td>'.htmlspecialchars($result['fandom_name']).'</td>';
		$html_content .= '<td>'.htmlspecialchars($result['badge_name']).'</td>';
		$html_content .= '<td>'.email_link($result['email_address']).'</td>';
		$html_content .= '<td class="td-actions td-actions-edit">';
			$html_content .= '<input type="hidden" class="ea-attendee-id" value="'.htmlspecialchars($result['id']).'">';
			$html_content .= '<input type="hidden" class="ea-first-name" value="'.htmlspecialchars($result['first_name']).'">';
			$html_content .= '<input type="hidden" class="ea-last-name" value="'.htmlspecialchars($result['last_name']).'">';
			$html_content .= '<input type="hidden" class="ea-fandom-name" value="'.htmlspecialchars($result['fandom_name']).'">';
			$html_content .= '<input type="hidden" class="ea-name-on-badge" value="'.htmlspecialchars($result['name_on_badge']).'">';
			$html_content .= '<input type="hidden" class="ea-date-of-birth" value="'.htmlspecialchars($result['date_of_birth']).'">';
			$html_content .= '<input type="hidden" class="ea-email-address" value="'.htmlspecialchars($result['email_address']).'">';
			$html_content .= '<input type="hidden" class="ea-phone-number" value="'.htmlspecialchars($result['phone_number']).'">';
			$html_content .= '<input type="hidden" class="ea-address-1" value="'.htmlspecialchars($result['address_1']).'">';
			$html_content .= '<input type="hidden" class="ea-address-2" value="'.htmlspecialchars($result['address_2']).'">';
			$html_content .= '<input type="hidden" class="ea-city" value="'.htmlspecialchars($result['city']).'">';
			$html_content .= '<input type="hidden" class="ea-state" value="'.htmlspecialchars($result['state']).'">';
			$html_content .= '<input type="hidden" class="ea-zip-code" value="'.htmlspecialchars($result['zip_code']).'">';
			$html_content .= '<input type="hidden" class="ea-country" value="'.htmlspecialchars($result['country']).'">';
			$html_content .= '<input type="hidden" class="ea-ice-name" value="'.htmlspecialchars($result['ice_name']).'">';
			$html_content .= '<input type="hidden" class="ea-ice-relationship" value="'.htmlspecialchars($result['ice_relationship']).'">';
			$html_content .= '<input type="hidden" class="ea-ice-email-address" value="'.htmlspecialchars($result['ice_email_address']).'">';
			$html_content .= '<input type="hidden" class="ea-ice-phone-number" value="'.htmlspecialchars($result['ice_phone_number']).'">';
			$html_content .= '<button class="select-button">Select</button>';
		$html_content .= '</td>';
		$html_content .= '</tr>';
		$attendees[] = array(
			'id' => $result['id'],
			'search_content' => $result['search_content'],
			'html_content' => $html_content,
		);
		$end_id = $result['id'];
	}
	
	$response = array(
		'start_id' => $start_id,
		'end_id' => $end_id,
		'next_start_id' => $end_id + 1,
		'batch_size' => $batch_size,
		'entities' => $attendees,
	);
	echo json_encode($response);
	exit(0);
}

$conn = get_db_connection();
db_require_table('booth_badges', $conn);
db_require_table('booths', $conn);
db_require_table('booth_staffers', $conn);
$badge_names = get_booth_badge_names($conn);
$booth_info = get_booth_info($conn, $badge_names);

$id = 0;
$changed = false;
if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'save':
			$set = encode_booth_staffer($_POST);
			if ($id) {
				$q = 'UPDATE '.db_table_name('booth_staffers').' SET '.$set.' WHERE `id` = '.$id;
				mysql_query($q, $conn);
			} else {
				$q = 'INSERT INTO '.db_table_name('booth_staffers').' SET '.$set.', `date_created` = NOW()';
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
	$results = mysql_query('SELECT * FROM '.db_table_name('booth_staffers').' WHERE `id` = '.$id, $conn);
	$result = mysql_fetch_assoc($results);
	$result = decode_booth_staffer($result, $booth_info);
	$name = $result['real_name'];
	$bid = $result['booth_id'];
} else {
	$result = null;
	$name = null;
	$bid = (isset($_GET['booth_id']) && $_GET['booth_id']) ? (int)$_GET['booth_id'] : null;
}

render_admin_head($name ? ('Edit Table Staffer - '.$name) : 'Add Table Staffer');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmentities.js')) . '"></script>';
echo '<script type="text/javascript">entityPage();</script>';
echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'booth_staffer.php',
	entityType: 'attendees',
	progressive: true,
	searchable: true,
	maxResults: 5,
	selectable: true,
	selectAction: function(row) {
		var all = !!$('.set-attendee-id-extent-all').attr('checked');
		var blank = !!$('.set-attendee-id-extent-blank').attr('checked');
		$('.epa-attendee-id').val(row.find('.ea-attendee-id').val());
		$('.epa-attendee-id-display').text('A' + row.find('.ea-attendee-id').val());
		if (all || (blank && !$('.epa-first-name').val())) $('.epa-first-name').val(row.find('.ea-first-name').val());
		if (all || (blank && !$('.epa-last-name').val())) $('.epa-last-name').val(row.find('.ea-last-name').val());
		if (all || (blank && !$('.epa-fandom-name').val())) $('.epa-fandom-name').val(row.find('.ea-fandom-name').val());
		if (all || (blank && !$('.epa-name-on-badge').val())) $('.epa-name-on-badge').val(row.find('.ea-name-on-badge').val());
		if (all || (blank && !$('.epa-date-of-birth').val())) $('.epa-date-of-birth').val(row.find('.ea-date-of-birth').val());
		if (all || (blank && !$('.epa-email-address').val())) $('.epa-email-address').val(row.find('.ea-email-address').val());
		if (all || (blank && !$('.epa-phone-number').val())) $('.epa-phone-number').val(row.find('.ea-phone-number').val());
		if (all || (blank && !$('.epa-address-1').val())) $('.epa-address-1').val(row.find('.ea-address-1').val());
		if (all || (blank && !$('.epa-address-2').val())) $('.epa-address-2').val(row.find('.ea-address-2').val());
		if (all || (blank && !$('.epa-city').val())) $('.epa-city').val(row.find('.ea-city').val());
		if (all || (blank && !$('.epa-state').val())) $('.epa-state').val(row.find('.ea-state').val());
		if (all || (blank && !$('.epa-zip-code').val())) $('.epa-zip-code').val(row.find('.ea-zip-code').val());
		if (all || (blank && !$('.epa-country').val())) $('.epa-country').val(row.find('.ea-country').val());
		if (all || (blank && !$('.epa-ice-name').val())) $('.epa-ice-name').val(row.find('.ea-ice-name').val());
		if (all || (blank && !$('.epa-ice-relationship').val())) $('.epa-ice-relationship').val(row.find('.ea-ice-relationship').val());
		if (all || (blank && !$('.epa-ice-email-address').val())) $('.epa-ice-email-address').val(row.find('.ea-ice-email-address').val());
		if (all || (blank && !$('.epa-ice-phone-number').val())) $('.epa-ice-phone-number').val(row.find('.ea-ice-phone-number').val());
	},
	pageInit: function() {
		$('.set-attendee-id-clear-button').click(function() {
			$('.epa-attendee-id').val('');
			$('.epa-attendee-id-display').text('');
			event.stopPropagation();
			event.preventDefault();
		});
		$('.set-attendee-id-start-button').click(function() {
			cmui.showDialog('set-attendee-id');
			$('.search-filter').val('');
			$('.search-filter').change();
			$('.search-filter').focus();
			event.stopPropagation();
			event.preventDefault();
		});
		$('.set-attendee-id-done-button').click(function() {
			cmui.hideDialog();
		});
	},
});</script><?php

render_admin_body($name ? 'Edit Table Staffer' : 'Add Table Staffer');

echo '<div class="card">';
	echo '<form action="booth_staffer.php?id='.$id.'" method="post">';
		echo '<div class="card-content">';
			if ($changed) {
				echo '<div class="notification">Changes saved.</div>';
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="form entity-record booth-staffer-record">';
				echo '<thead><tr><th colspan="2"><div class="subhead" id="sh-per">Personal Information</div></th></tr></thead>';
				echo '<tbody class="sh-per">';
					echo '<tr>';
						echo '<th><label for="booth_id">Table:</label></th>';
						echo '<td><select name="booth_id">';
							foreach ($booth_info as $booth_id => $booth) {
								echo '<option value="'.$booth_id.'"';
								if ($bid && $bid == $booth_id) echo ' selected="selected"';
								echo '>BA'.$booth_id.' - '.htmlspecialchars($booth['booth_name']).'</option>';
							}
						echo '</select></td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th><label for="attendee_id">Attendee ID:</label></th>';
						echo '<td>';
							echo '<input type="hidden" name="attendee_id" value="';
							if ($result && $result['attendee_id']) echo htmlspecialchars($result['attendee_id']);
							echo '" class="epa-attendee-id">';
							echo '<span class="epa-attendee-id-display">';
							if ($result && $result['attendee_id']) echo 'A' . htmlspecialchars($result['attendee_id']);
							echo '</span>&nbsp;';
							echo '&nbsp;<button class="set-attendee-id-start-button">Select</button>';
							echo '&nbsp;<button class="set-attendee-id-clear-button">Clear</button>';
						echo '</td>';
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
							echo '<td>B' . $result['id'] . '</td>';
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

echo '<div class="dialog set-attendee-id-dialog hidden">';
	echo '<div class="dialog-title">Set Attendee ID</div>';
	echo '<div class="dialog-content">';
		render_list_search('name, badge type, contact info, or transaction ID', '', 'padding-bottom: 12px;');
		render_list_table(array('ID', 'Real Name', 'Fandom Name', 'Badge Type', 'Email Address'), null, false, $conn);
	echo '</div>';
	echo '<div class="dialog-buttons">';
		echo '<label><input type="radio" name="set_attendee_id_extent" value="id"';
			if ($id) echo ' checked="checked"';
			echo ' class="set-attendee-id-extent-id">Set ID Only</label>';
		echo '<label><input type="radio" name="set_attendee_id_extent" value="blank" class="set-attendee-id-extent-blank">Set Blank Fields</label>';
		echo '<label><input type="radio" name="set_attendee_id_extent" value="all"';
			if (!$id) echo ' checked="checked"';
			echo ' class="set-attendee-id-extent-all">Set All Fields</label>';
		echo '<button class="set-attendee-id-done-button">Done</button>';
	echo '</div>';
echo '</div>';

render_admin_tail();