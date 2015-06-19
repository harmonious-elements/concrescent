<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/badges.php';

$conn = get_db_connection();
$badge_artwork_names = get_badge_artwork_names($conn);

if (isset($_POST['action']) && ($_POST['action'] == 'save')) {
	$custom_size = (isset($_POST['custom_size']) && (int)$_POST['custom_size']);
	if ($custom_size) {
		$width = trim($_POST['width']); if (!$width) $width = $badge_printing_width;
		$height = trim($_POST['height']); if (!$height) $height = $badge_printing_height;
		$vertical = (isset($_POST['vertical']) && (int)$_POST['vertical']);
		setcookie('badge_printing_width', $width, time()+60*60*24*30, '/');
		setcookie('badge_printing_height', $height, time()+60*60*24*30, '/');
		setcookie('badge_printing_vertical', ($vertical ? 1 : 0), time()+60*60*24*30, '/');
	} else {
		$width = $badge_printing_width;
		$height = $badge_printing_height;
		$vertical = $badge_printing_vertical;
		setcookie('badge_printing_width', false, 0, '/');
		setcookie('badge_printing_height', false, 0, '/');
		setcookie('badge_printing_vertical', false, 0, '/');
	}
	$blank = (isset($_POST['blank']) && (int)$_POST['blank']);
	if ($blank) {
		setcookie('badge_printing_blank', 1, time()+60*60*24*30, '/');
	} else {
		setcookie('badge_printing_blank', false, 0, '/');
	}
	$only_print = (isset($_POST['only_print']) && (int)$_POST['only_print']);
	if ($only_print) {
		$only_print = array();
		foreach ($badge_artwork_names as $id => $filename) {
			if (isset($_POST['only_print_'.$id]) && (int)$_POST['only_print_'.$id]) {
				$only_print[] = $id;
			}
		}
	}
	if ($only_print) {
		setcookie('badge_printing_only_print', implode(',', $only_print), time()+60*60*24*30, '/');
	} else {
		setcookie('badge_printing_only_print', false, 0, '/');
	}
	$message = 'Changes saved.';
} else {
	$custom_size = (
		isset($_COOKIE['badge_printing_width']) ||
		isset($_COOKIE['badge_printing_height']) ||
		isset($_COOKIE['badge_printing_vertical'])
	);
	$width = (
		isset($_COOKIE['badge_printing_width']) ?
		$_COOKIE['badge_printing_width'] :
		$badge_printing_width
	);
	$height = (
		isset($_COOKIE['badge_printing_height']) ?
		$_COOKIE['badge_printing_height'] :
		$badge_printing_height
	);
	$vertical = (
		isset($_COOKIE['badge_printing_vertical']) ?
		(!!(int)$_COOKIE['badge_printing_vertical']) :
		$badge_printing_vertical
	);
	$blank = (
		isset($_COOKIE['badge_printing_blank']) &&
		(!!(int)$_COOKIE['badge_printing_blank'])
	);
	$only_print = (
		isset($_COOKIE['badge_printing_only_print']) ?
		explode(',', $_COOKIE['badge_printing_only_print']) :
		false
	);
	$message = null;
}

render_admin_head('Badge Printing Setup');
render_admin_body('Badge Printing Setup');

echo '<div class="card">';
	echo '<form action="badge_printing_setup.php" method="post">';
		echo '<div class="card-content spaced">';
			if ($message) {
				echo '<div class="notification">' . htmlspecialchars($message) . '</div>';
			}
			echo '<h1>Badge Printing Setup</h1>';
			echo '<p>';
				echo 'The settings on this page affect <b>THIS COMPUTER ONLY</b>. ';
				echo 'To change the global default badge size, edit the ';
				echo 'CONcrescent configuration file directly.';
			echo '</p>';
			echo '<hr>';
			echo '<p><b>Badge Size:</b></p>';
			echo '<p>';
				echo '<label><input type="radio" name="custom_size" value="0"';
				if (!$custom_size) echo ' checked="checked"';
				echo '> Default (';
				echo htmlspecialchars($badge_printing_width);
				echo ' x ';
				echo htmlspecialchars($badge_printing_height);
				echo ')</label>';
				echo '<br>';
				echo '<label><input type="radio" name="custom_size" value="1"';
				if ($custom_size) echo ' checked="checked"';
				echo '> Custom:</label>';
			echo '</p>';
			echo '<table border="0" cellspacing="0" cellpadding="0" class="form">';
			echo '<tr><th>Width:</th><td><input type="text" name="width" value="'.htmlspecialchars($width).'"></td></tr>';
			echo '<tr><th>Height:</th><td><input type="text" name="height" value="'.htmlspecialchars($height).'"></td></tr>';
			echo '<tr><th>Orientation:</th><td>';
				echo '<label><input type="radio" name="vertical" value="0"';
				if (!$vertical) echo ' checked="checked"';
				echo '> Horizontal</label>';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<label><input type="radio" name="vertical" value="1"';
				if ($vertical) echo ' checked="checked"';
				echo '> Vertical</label>';
			echo '</td></tr>';
			echo '</table>';
			echo '<hr>';
			echo '<p><b>Badge Artwork:</b></p>';
			echo '<p>';
				echo '<label><input type="radio" name="blank" value="0"';
				if (!$blank) echo ' checked="checked"';
				echo '> Print badge artwork and badge information</label>';
				echo '<br>';
				echo '<label><input type="radio" name="blank" value="1"';
				if ($blank) echo ' checked="checked"';
				echo '> Print badge information only (use pre-printed badge artwork)</label>';
			echo '</p>';
			echo '<hr>';
			echo '<p><b>Restrictions:</b></p>';
			echo '<p>';
				echo '<label><input type="radio" name="only_print" value="0"';
				if (!$only_print) echo ' checked="checked"';
				echo '> Allow any badge artwork to be printed from this computer</label>';
				echo '<br>';
				echo '<label><input type="radio" name="only_print" value="1"';
				if ($only_print) echo ' checked="checked"';
				echo '> Only allow the following badge artwork:</label>';
			echo '</p>';
			echo '<p style="margin-left: 1in;">';
				$first = true;
				foreach ($badge_artwork_names as $id => $filename) {
					if ($first) $first = false; else echo '<br>';
					echo '<label><input type="checkbox" name="only_print_'.htmlspecialchars($id).'" value="1"';
					if ($only_print && in_array($id, $only_print)) echo ' checked="checked"';
					echo '> '.htmlspecialchars($filename).'</label>';
				}
			echo '</p>';
		echo '</div>';
		echo '<div class="card-buttons right">';
			echo '<input type="hidden" name="action" value="save">';
			echo '<button id="save-changes">Save Changes</button>';
		echo '</div>';
	echo '</form>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();