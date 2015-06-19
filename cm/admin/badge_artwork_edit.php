<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/badges.php';

function render_badge_type_table($badge_types, $checked_badge_types) {
	$cols = 2;
	$cells = count($badge_types);
	$rows = ceil($cells / $cols);
	echo '<table border="0" cellspacing="0" cellpadding="0" class="badge-types">';
	for ($row = 0; $row < $rows; $row++) {
		echo '<tr>';
		for ($col = 0; $col < $cols; $col++) {
			$cell = $col * $rows + $row;
			if ($cell < $cells) {
				$bt = $badge_types[$cell];
				$btid = $bt['id_string'];
				$btname = $bt['name'];
				echo '<td class="badge-type-check">';
				echo '<input type="checkbox"';
				echo ' name="badge-type-'.htmlspecialchars($btid).'"';
				echo ' id="badge-type-'.htmlspecialchars($btid).'"';
				if (in_array($btid, $checked_badge_types)) {
					echo ' checked="checked"';
				}
				echo '>';
				echo '</td>';
				echo '<td class="badge-type-id">';
				echo '<label for="badge-type-'.htmlspecialchars($btid).'">';
				echo htmlspecialchars($btid);
				echo '</label>';
				echo '</td>';
				echo '<td class="badge-type-name">';
				echo '<label for="badge-type-'.htmlspecialchars($btid).'">';
				echo htmlspecialchars($btname);
				echo '</label>';
				echo '</td>';
			} else {
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';
}

$conn = get_db_connection();

if (isset($_POST['id'])) {
	$id = $_POST['id'];
} else if (isset($_GET['id'])) {
	$id = $_GET['id'];
} else {
	header('Location: badge_artwork.php');
}

if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = null;
}

if ($action == 'load') {
	$checked_badge_types = get_badge_artwork_map($id, $conn);
	$badge_artwork_fields = get_badge_artwork_fields($id, $conn);
	echo json_encode(array(
		'checked_badge_types' => $checked_badge_types,
		'badge_artwork_fields' => $badge_artwork_fields,
	));
	exit(0);
}

if ($action == 'save') {
	if (isset($_POST['checked_badge_types'])) {
		$checked_badge_types = json_decode($_POST['checked_badge_types'], true);
	} else if (isset($_GET['checked_badge_types'])) {
		$checked_badge_types = json_decode($_GET['checked_badge_types'], true);
	} else {
		echo 'Bad request.';
		exit(0);
	}
	if (isset($_POST['badge_artwork_fields'])) {
		$badge_artwork_fields = json_decode($_POST['badge_artwork_fields'], true);
	} else if (isset($_GET['badge_artwork_fields'])) {
		$badge_artwork_fields = json_decode($_GET['badge_artwork_fields'], true);
	} else {
		echo 'Bad request.';
		exit(0);
	}
	set_badge_artwork_map($id, $checked_badge_types, $conn);
	set_badge_artwork_fields($id, $badge_artwork_fields, $conn);
	echo 'Changes saved.';
	exit(0);
}

$ba = get_badge_artwork($id, $conn);
if (!$ba) {
	header('Location: badge_artwork.php');
}

if ($action == 'img') {
	if (!echo_badge_artwork($ba['filename'])) {
		header('Content-Type: image/png');
		$image = imagecreate(300, 200);
		$bg = imagecolorallocate($image, 255, 255, 255);
		imagefilledrectangle($image, 0, 0, 300, 200, $bg);
		$fg = imagecolorallocate($image, 0, 0, 255);
		imagestring($image, 5, (300-9*21)/2, 200/2-8-12, 'Could not load image.', $fg);
		imagestring($image, 5, (300-9*24)/2, 200/2-8+12, 'Please upload a new one.', $fg);
		imagepng($image);
		imagedestroy($image);
	}
	exit(0);
}

$badge_artwork_names = get_badge_artwork_names($conn);
$badge_types = get_all_badge_types($conn);
$checked_badge_types = get_badge_artwork_map($id, $conn);
$badge_artwork_fields = get_badge_artwork_fields($id, $conn);

render_admin_head('Badge Artwork');

echo '<script>';
	echo 'var badge_artwork_id = '.(int)$id.';';
	echo 'var checked_badge_types = '.json_encode($checked_badge_types).';';
	echo 'var badge_artwork_fields = '.json_encode($badge_artwork_fields).';';
echo '</script>';
echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmbaedit.js')) . '"></script>';
echo '<link rel="stylesheet" href="' . htmlspecialchars(resource_file_url('cmbaedit.css')) . '">';
if ($badge_printing_external_stylesheet) {
	echo '<link rel="stylesheet" href="' . htmlspecialchars($badge_printing_external_stylesheet) . '">';
}
echo ('<style>.badge-artwork {'.
	'padding-bottom: '.badge_artwork_aspect_ratio($ba['filename']).'%;'.
	'background: url(\'badge_artwork_edit.php?action=img&id=' . (int)$id . '\') no-repeat center;'.
	'background-size: 100% 100%;'.
'}</style>');

render_admin_body('Badge Artwork');

echo '<div class="card">';
	echo '<div class="card-content spaced">';

		echo '<h1>Edit Badge Artwork</h1>';
		echo '<p>Start dragging to add a text field. Click a text field to edit, or use arrow keys to cycle through. Drag the blue handles to resize.</p>';
		echo '<div class="badge-artwork-container'; if ($ba['vertical']) echo ' vertical'; echo '">';
			echo '<div class="badge-artwork-container-inner'; if ($ba['vertical']) echo ' vertical'; echo '">';
				echo '<div class="badge-artwork'; if ($ba['vertical']) echo ' vertical'; echo '">';
					echo '<div class="badge-artwork-fields'; if ($ba['vertical']) echo ' vertical'; echo '">';
					echo '</div>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
		echo '<p style="text-align: right;">';
			echo 'Or, import layout from existing badge artwork: ';
			echo '<select name="import-layout-select" id="import-layout-select">';
			foreach ($badge_artwork_names as $id => $filename) {
				echo '<option value="'.(int)$id.'">';
				echo htmlspecialchars($filename);
				echo '</option>';
			}
			echo '</select>';
			echo ' ';
			echo '<button id="import-layout-button">Import</button>';
		echo '</p>';

		echo '<hr class="badge-artwork-field-form">';
		echo '<h2 class="badge-artwork-field-form">Edit Text Field</h2>';
		echo '<table border="0" cellspacing="0" cellpadding="0" class="form badge-artwork-field-form">';
			echo '<tr><th>Field Type:</th><td>';
				echo '<select name="field-type" id="field-type">';
					echo '<option value="only_name">Only Name</option>';
					echo '<option value="large_name">Large Name</option>';
					echo '<option value="small_name">Small Name</option>';
					echo '<option value="group_name">Group Name</option>';
					echo '<option value="badge_name">Badge Type Name</option>';
					echo '<option value="assigned_position">Assigned Position</option>';
					echo '<option value="id_string">Person ID</option>';
					echo '<option value="group_id_string">Group ID</option>';
					echo '<option value="badge_id_string">Badge Type ID</option>';
					echo '<option value="first_name">First Name</option>';
					echo '<option value="last_name">Last Name</option>';
					echo '<option value="real_name">Real Name</option>';
					echo '<option value="fandom_name">Fandom Name</option>';
					echo '<option value="display_name">Display Name</option>';
				echo '</select>';
			echo '</td></tr>';
			echo '<tr><th>Font Name:</th><td>';
				echo '<input type="text" name="font-family" id="font-family">';
			echo '</td></tr>';
			echo '<tr><th>Font Style:</th><td>';
				echo '<label><input type="checkbox" name="font-weight-bold" id="font-weight-bold">Bold</label><br>';
				echo '<label><input type="checkbox" name="font-style-italic" id="font-style-italic">Italic</label>';
			echo '</td></tr>';
			echo '<tr><th>Text Color (Adults):</th><td>';
				echo '<input type="text" name="color" id="color">';
			echo '</td></tr>';
			echo '<tr><th>Background (Adults):</th><td>';
				echo '<input type="text" name="background" id="background">';
			echo '</td></tr>';
			echo '<tr><th>Text Color (Minors):</th><td>';
				echo '<input type="text" name="color-minors" id="color-minors">';
			echo '</td></tr>';
			echo '<tr><th>Background (Minors):</th><td>';
				echo '<input type="text" name="background-minors" id="background-minors">';
			echo '</td></tr>';
		echo '</table>';

		echo '<hr>';
		echo '<h2>Applicable Badge Types</h2>';
		render_badge_type_table($badge_types, $checked_badge_types);

	echo '</div>';
	echo '<div class="card-buttons right">';
		echo '<button id="save-changes">Save Changes</button>';
	echo '</div>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();