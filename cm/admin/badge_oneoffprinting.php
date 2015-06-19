<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/badges.php';

function field_type_string($field_type) {
	switch ($field_type) {
		case 'only_name'        : return 'Only Name'        ; break;
		case 'large_name'       : return 'Large Name'       ; break;
		case 'small_name'       : return 'Small Name'       ; break;
		case 'group_name'       : return 'Group Name'       ; break;
		case 'badge_name'       : return 'Badge Type Name'  ; break;
		case 'assigned_position': return 'Assigned Position'; break;
		case 'id_string'        : return 'Person ID'        ; break;
		case 'group_id_string'  : return 'Group ID'         ; break;
		case 'badge_id_string'  : return 'Badge Type ID'    ; break;
		case 'first_name'       : return 'First Name'       ; break;
		case 'last_name'        : return 'Last Name'        ; break;
		case 'real_name'        : return 'Real Name'        ; break;
		case 'fandom_name'      : return 'Fandom Name'      ; break;
		case 'display_name'     : return 'Display Name'     ; break;
	}
	return $field_type;
}

function field_type_order($field_type) {
	switch ($field_type) {
		case 'only_name'        : return -14; break;
		case 'large_name'       : return -13; break;
		case 'small_name'       : return -12; break;
		case 'group_name'       : return -11; break;
		case 'badge_name'       : return -10; break;
		case 'assigned_position': return  -9; break;
		case 'id_string'        : return  -8; break;
		case 'group_id_string'  : return  -7; break;
		case 'badge_id_string'  : return  -6; break;
		case 'first_name'       : return  -5; break;
		case 'last_name'        : return  -4; break;
		case 'real_name'        : return  -3; break;
		case 'fandom_name'      : return  -2; break;
		case 'display_name'     : return  -1; break;
	}
	return 0;
}

function field_type_cmp($a, $b) {
	$cmp = field_type_order($a) - field_type_order($b);
	return $cmp ? $cmp : strnatcasecmp($a, $b);
}

function field_cmp($a, $b) {
	return field_type_cmp($a['field_type'], $b['field_type']);
}

$conn = get_db_connection();

if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = null;
}

if ($action == 'img') {
	if (isset($_POST['ba'])) {
		$badge_artwork_id = $_POST['ba'];
	} else if (isset($_GET['ba'])) {
		$badge_artwork_id = $_GET['ba'];
	} else {
		header('Location: badge_oneoffprinting.php');
		exit(0);
	}
	$badge_artwork = get_badge_artwork($badge_artwork_id, $conn);
	if (!$badge_artwork) {
		header('Location: badge_oneoffprinting.php');
		exit(0);
	}
	if (!echo_badge_artwork($badge_artwork['filename'])) {
		header('Content-Type: image/png');
		$image = imagecreate(300, 200);
		$bg = imagecolorallocate($image, 255, 255, 255);
		imagefilledrectangle($image, 0, 0, 300, 200, $bg);
		imagepng($image);
		imagedestroy($image);
	}
	exit(0);
}

if ($action == 'list_fields') {
	if (isset($_POST['ba'])) {
		$badge_artwork_id = $_POST['ba'];
	} else if (isset($_GET['ba'])) {
		$badge_artwork_id = $_GET['ba'];
	} else {
		header('Location: badge_oneoffprinting.php');
		exit(0);
	}
	$badge_artwork_fields = get_badge_artwork_fields($badge_artwork_id, $conn);
	usort($badge_artwork_fields, 'field_cmp');
	foreach ($badge_artwork_fields as $f) {
		$ts = htmlspecialchars(field_type_string($f['field_type']));
		$ti = htmlspecialchars($f['field_type']);
		echo '<tr>';
		echo '<th>'.$ts.':</th>';
		echo '<td><input type="text" name="'.$ti.'" id="'.$ti.'"></td>';
		echo '</tr>';
	}
	exit(0);
}

render_admin_head('One-Off Badge Printing');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmbaoneoff.js')) . '"></script>';
echo '<link rel="stylesheet" href="' . htmlspecialchars(resource_file_url('cmbaoneoff.css')) . '">';

render_admin_body('One-Off Badge Printing');

echo '<div class="card">';
	echo '<div class="card-content spaced">';
		echo '<h1>One-Off Badge Printing</h1>';
		echo '<h3>Select Badge Artwork</h3>';
		echo '<div class="artwork-select">';
			$artwork = get_all_badge_artwork($conn);
			if ($artwork) {
				foreach ($artwork as $a) {
					echo '<div class="artwork ba'.(int)$a['id'].'" onclick="list_fields('.(int)$a['id'].');">';
					echo '<img src="badge_oneoffprinting.php?action=img&ba='.(int)$a['id'].'"';
					if ($a['vertical']) {
						echo ' class="vertical"';
					} else {
						echo ' class="horizontal"';
					}
					echo ' title="'.htmlspecialchars($a['filename']).'">';
					echo '</div>';
				}
			} else {
				echo '<div class="no-artwork">No artwork.</div>';
			}
		echo '</div>';
		echo '<h3 class="badge-info hidden">Enter Badge Info</h3>';
		echo '<table border="0" cellpadding="0" cellspacing="0" class="badge-info hidden form">';
			echo '<tbody class="badge-info-tbody">';
			echo '</tbody>';
			echo '<tbody>';
				echo '<tr>';
				echo '<th>Age:</th>';
				echo '<td>';
				echo '<label><input type="radio" name="age" value="100" checked="checked"> Adult</label>';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<label><input type="radio" name="age" value="0"> Minor</label>';
				echo '</td>';
				echo '</tr>';
			echo '</tbody>';
		echo '</table>';
		echo '<h3 class="badge-info hidden">Print Badge</h3>';
		echo '<p class="badge-info hidden"><a target="_blank" class="print-button a-button">Print</a></p>';
	echo '</div>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();