<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../../lib/database/badge-artwork.php';
require_once dirname(__FILE__).'/../admin.php';

cm_admin_check_permission('badge-printing-setup', 'badge-printing-setup');

$bp_config = $cm_config['badge_printing'];
$badb = new cm_badge_artwork_db($db);
$artwork = $badb->list_badge_artwork();

if (isset($_POST['action'])) {
	$custom_size = (isset($_POST['custom-size']) && (int)$_POST['custom-size']);
	if ($custom_size) {
		$width = trim($_POST['width']); if (!$width) $width = $bp_config['width'];
		$height = trim($_POST['height']); if (!$height) $height = $bp_config['height'];
		$vertical = (isset($_POST['vertical']) && (int)$_POST['vertical']);
		setcookie('badge_printing_width', $width, time()+60*60*24*30, '/');
		setcookie('badge_printing_height', $height, time()+60*60*24*30, '/');
		setcookie('badge_printing_vertical', ($vertical ? 1 : 0), time()+60*60*24*30, '/');
	} else {
		$width = $bp_config['width'];
		$height = $bp_config['height'];
		$vertical = $bp_config['vertical'];
		setcookie('badge_printing_width', '', time()-3600, '/');
		setcookie('badge_printing_height', '', time()-3600, '/');
		setcookie('badge_printing_vertical', '', time()-3600, '/');
	}
	$blank = (isset($_POST['blank']) && (int)$_POST['blank']);
	if ($blank) {
		setcookie('badge_printing_blank', 1, time()+60*60*24*30, '/');
	} else {
		setcookie('badge_printing_blank', '', time()-3600, '/');
	}
	$only_print = (isset($_POST['only-print']) && (int)$_POST['only-print']);
	if ($only_print) {
		$only_print = array();
		foreach ($artwork as $ba) {
			$name = $ba['file-name'];
			$post_name = preg_replace('/[^A-Za-z0-9]+/', '-', $name);
			if (isset($_POST['only-print-'.$post_name]) && (int)$_POST['only-print-'.$post_name]) {
				$only_print[] = $name;
			}
		}
		setcookie('badge_printing_only_print', implode(',', $only_print), time()+60*60*24*30, '/');
	} else {
		setcookie('badge_printing_only_print', '', time()-3600, '/');
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
		$bp_config['width']
	);
	$height = (
		isset($_COOKIE['badge_printing_height']) ?
		$_COOKIE['badge_printing_height'] :
		$bp_config['height']
	);
	$vertical = (
		isset($_COOKIE['badge_printing_vertical']) ?
		(!!(int)$_COOKIE['badge_printing_vertical']) :
		$bp_config['vertical']
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

cm_admin_head('Badge Printing Setup');
cm_admin_body('Badge Printing Setup');
cm_admin_nav('badge-printing-setup');

echo '<article>';
	echo '<form action="printing-setup.php" method="post" class="card">';
		echo '<div class="card-content">';

			if ($message) {
				echo '<p class="cm-success-box">';
					echo htmlspecialchars($message);
				echo '</p>';
			}
			echo '<p class="cm-note-box">';
				echo 'The settings on this page affect <b>THIS COMPUTER ONLY</b>. ';
				echo 'To change the global default badge size, edit the CONcrescent ';
				echo 'configuration file directly.';
			echo '</p>';
			echo '<hr>';

			echo '<h3>Badge Size</h3>';
			echo '<div class="spacing">';
				echo '<div>';
					echo '<label>';
						echo '<input type="radio" name="custom-size" value="0"';
						if (!$custom_size) echo ' checked'; echo '>Default (';
						echo htmlspecialchars($bp_config['width']); echo ' by ';
						echo htmlspecialchars($bp_config['height']); echo ')';
					echo '</label>';
				echo '</div>';
				echo '<div>';
					echo '<label>';
						echo '<input type="radio" name="custom-size" value="1"';
						if ($custom_size) echo ' checked'; echo '>Custom:';
					echo '</label>';
				echo '</div>';
			echo '</div>';
			echo '<table border="0" cellspacing="0" cellpadding="0" class="cm-form-table">';
				echo '<tr>';
					echo '<th>Width:</th>';
					echo '<td><input type="text" name="width" value="' . htmlspecialchars($width) . '"></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<th>Height:</th>';
					echo '<td><input type="text" name="height" value="' . htmlspecialchars($height) . '"></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<th>Orientation:</th>';
					echo '<td>';
						echo '<label>';
							echo '<input type="radio" name="vertical" value="0"';
							if (!$vertical) echo ' checked'; echo '>Horizontal';
						echo '</label>';
						echo '&nbsp;&nbsp;&nbsp;&nbsp;';
						echo '<label>';
							echo '<input type="radio" name="vertical" value="1"';
							if ($vertical) echo ' checked'; echo '>Vertical';
						echo '</label>';
					echo '</td>';
				echo '</tr>';
			echo '</table>';
			echo '<hr>';

			echo '<h3>Badge Artwork</h3>';
			echo '<div class="spacing">';
				echo '<div>';
					echo '<label>';
						echo '<input type="radio" name="blank" value="0"';
						if (!$blank) echo ' checked'; echo '>';
						echo 'Print badge artwork and badge text (use blank badge stock).';
					echo '</label>';
				echo '</div>';
				echo '<div>';
					echo '<label>';
						echo '<input type="radio" name="blank" value="1"';
						if ($blank) echo ' checked'; echo '>';
						echo 'Print badge text only (use pre-printed badge or label stock).';
					echo '</label>';
				echo '</div>';
			echo '</div>';
			echo '<hr>';

			echo '<h3>Restrictions</h3>';
			echo '<div class="spacing">';
				echo '<div>';
					echo '<label>';
						echo '<input type="radio" name="only-print" value="0"';
						if (!$only_print) echo ' checked'; echo '>';
						echo 'Allow any badge artwork to be printed from this computer.';
					echo '</label>';
				echo '</div>';
				echo '<div>';
					echo '<label>';
						echo '<input type="radio" name="only-print" value="1"';
						if ($only_print) echo ' checked'; echo '>';
						echo 'Allow only the following badge artwork to be printed:';
					echo '</label>';
				echo '</div>';
			echo '</div>';
			echo '<div class="spacing" style="padding-left: 84px;">';
				foreach ($artwork as $ba) {
					$name = $ba['file-name'];
					$post_name = preg_replace('/[^A-Za-z0-9]+/', '-', $name);
					echo '<div>';
						echo '<label>';
							echo '<input type="checkbox" name="only-print-';
							echo htmlspecialchars($post_name); echo '" value="1"';
							if ($only_print && in_array($name, $only_print)) echo ' checked';
							echo '>'; echo htmlspecialchars($name);
						echo '</label>';
					echo '</div>';
				}
			echo '</div>';

		echo '</div>';
		echo '<div class="card-buttons">';
			echo '<input type="submit" name="action" value="Save Changes">';
		echo '</div>';
	echo '</form>';
echo '</article>';

cm_admin_dialogs();
cm_admin_tail();