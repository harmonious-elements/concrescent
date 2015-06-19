<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/badges.php';

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
		header('Location: badge_preprinting.php');
		exit(0);
	}
	$badge_artwork = get_badge_artwork($badge_artwork_id, $conn);
	if (!$badge_artwork) {
		header('Location: badge_preprinting.php');
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

if ($action == 'list_artwork') {
	if (isset($_POST['badge_id'])) {
		$badge_id = $_POST['badge_id'];
	} else if (isset($_GET['badge_id'])) {
		$badge_id = $_GET['badge_id'];
	} else {
		header('Location: badge_preprinting.php');
		exit(0);
	}
	if (isset($_POST['t'])) {
		$t = $_POST['t'];
	} else if (isset($_GET['t'])) {
		$t = $_GET['t'];
	} else {
		header('Location: badge_preprinting.php');
		exit(0);
	}
	if (isset($_POST['id'])) {
		$id = $_POST['id'];
	} else if (isset($_GET['id'])) {
		$id = $_GET['id'];
	} else {
		header('Location: badge_preprinting.php');
		exit(0);
	}
	$artwork = get_badge_artwork_for_badge_id($badge_id, $conn);
	if ($artwork) {
		foreach ($artwork as $a) {
			echo '<div class="artwork">';
			echo '<a href="badge_print.php?t='.htmlspecialchars($t).'&id='.(int)$id.'&ba='.(int)$a['id'].'"';
			echo ' target="_blank">';
			echo '<img src="badge_preprinting.php?action=img&ba='.(int)$a['id'].'"';
			if ($a['vertical']) {
				echo ' class="vertical"';
			} else {
				echo ' class="horizontal"';
			}
			echo ' title="'.htmlspecialchars($a['filename']).'">';
			echo '</a>';
			echo '</div>';
		}
	} else {
		echo '<div class="no-artwork">No artwork.</div>';
	}
	exit(0);
}

if ($action == 'list_holders') {
	if (isset($_POST['badge_id'])) {
		$badge_id = $_POST['badge_id'];
	} else if (isset($_GET['badge_id'])) {
		$badge_id = $_GET['badge_id'];
	} else {
		header('Location: badge_preprinting.php');
		exit(0);
	}
	if (isset($_POST['t'])) {
		$t = $_POST['t'];
	} else if (isset($_GET['t'])) {
		$t = $_GET['t'];
	} else {
		header('Location: badge_preprinting.php');
		exit(0);
	}
	if (isset($_POST['accepted_only'])) {
		$as = (int)$_POST['accepted_only'] ? 'Accepted' : null;
	} else if (isset($_GET['accepted_only'])) {
		$as = (int)$_GET['accepted_only'] ? 'Accepted' : null;
	} else {
		$as = null;
	}
	if (isset($_POST['paid_only'])) {
		$ps = (int)$_POST['paid_only'] ? 'Completed' : null;
	} else if (isset($_GET['paid_only'])) {
		$ps = (int)$_GET['paid_only'] ? 'Completed' : null;
	} else {
		$ps = null;
	}
	$holders = list_badge_holders($t, $badge_id, $as, $ps, null, null, $conn);
	if ($holders) {
		foreach ($holders as $h) {
			echo '<tr>';
			echo '<td><a href="#" onclick="list_artwork(\'';
			echo htmlspecialchars($t);
			echo '\',';
			echo (int)$h['id'];
			echo ',\'';
			echo htmlspecialchars($h['badge_id_string']);
			echo '\'); return true;">';
			echo htmlspecialchars($h['display_name']);
			echo '</a></td>';
			echo '<td>'.(isset($h['application_status_html']) ? $h['application_status_html'] : '').'</td>';
			echo '<td>'.(
				isset($h['payment_status_html']) ? $h['payment_status_html'] :
				(isset($h['contract_status_html']) ? $h['contract_status_html'] : '')
			).'</td>';
			echo '</tr>';
		}
	} else {
		echo '<tr class="no-holders"><td colspan="3" class="no-holders">No badge holders.</td></tr>';
	}
	exit(0);
}

render_admin_head('Badge Pre-Printing');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmbapreprint.js')) . '"></script>';
echo '<link rel="stylesheet" href="' . htmlspecialchars(resource_file_url('cmbapreprint.css')) . '">';

render_admin_body('Badge Pre-Printing');

echo '<div class="card badge-preprinting-card">';
	echo '<div class="card-content badge-preprinting-card-content spaced">';
		echo '<h1>Badge Pre-Printing</h1>';
		echo '<div class="badge-preprinting-columns-container">';
			echo '<div class="badge-preprinting-columns">';
				echo '<div class="badge-preprinting-column badge-preprinting-column-types">';
					echo '<h3>Badge Types</h3>';
					echo '<p><b>Step 1:</b> Click "paid", "accepted", or "all" to get the list of badge holders who have paid for, been accepted for, or registered for a badge, respectively.</p>';
					echo '<table border="0" cellpadding="0" cellspacing="0">';
						echo '<thead>';
							echo '<tr>';
								echo '<th>ID</th>';
								echo '<th>Badge&nbsp;Type</th>';
								echo '<th>paid</th>';
								echo '<th>accepted</th>';
								echo '<th>all</th>';
							echo '</tr>';
						echo '</thead>';
						echo '<tbody class="badge-preprinting-types">';
							$badge_types = get_all_badge_types($conn);
							foreach ($badge_types as $t) {
								echo '<tr>';
								echo '<td class="badge-type-id">'.htmlspecialchars($t['id_string']).'</td>';
								echo '<td class="badge-type-name">'.htmlspecialchars($t['name']).'</td>';
								echo '<td class="badge-type-load badge-type-load-paid">';
									echo '<a href="#" onclick="list_holders(\'';
									echo htmlspecialchars($t['t']);
									echo '\',\'';
									echo htmlspecialchars($t['id_string']);
									echo '\',true,true); return true;">paid</a>';
								echo '</td>';
								echo '<td class="badge-type-load badge-type-load-accepted">';
									echo '<a href="#" onclick="list_holders(\'';
									echo htmlspecialchars($t['t']);
									echo '\',\'';
									echo htmlspecialchars($t['id_string']);
									echo '\',true,false); return true;">accepted</a>';
								echo '</td>';
								echo '<td class="badge-type-load badge-type-load-all">';
									echo '<a href="#" onclick="list_holders(\'';
									echo htmlspecialchars($t['t']);
									echo '\',\'';
									echo htmlspecialchars($t['id_string']);
									echo '\',false,false); return true;">all</a>';
								echo '</td>';
								echo '</tr>';
							}
						echo '</tbody>';
					echo '</table>';
				echo '</div>';
				echo '<div class="badge-preprinting-column badge-preprinting-column-holders">';
					echo '<h3>Badge Holders</h3>';
					echo '<p><b>Step 2:</b> Click a badge holder\'s name to get the list of possible designs for their badge.</p>';
					echo '<table border="0" cellpadding="0" cellspacing="0">';
						echo '<thead>';
							echo '<tr>';
								echo '<th>Name</th>';
								echo '<th>App.&nbsp;Status</th>';
								echo '<th>Pmt.&nbsp;Status</th>';
							echo '</tr>';
						echo '</thead>';
						echo '<tbody class="badge-preprinting-holders">';
						echo '</tbody>';
					echo '</table>';
				echo '</div>';
				echo '<div class="badge-preprinting-column badge-preprinting-column-artwork">';
					echo '<h3>Print Badge</h3>';
					echo '<p><b>Step 3:</b> Click a badge design to print the badge!</p>';
					echo '<div class="badge-preprinting-artwork">';
					echo '</div>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();