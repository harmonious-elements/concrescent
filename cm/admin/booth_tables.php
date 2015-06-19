<?php

require_once dirname(__FILE__).'/admin.php';

$conn = get_db_connection();
db_require_table('booth_tables', $conn);
db_require_table('booth_badges', $conn);
db_require_table('booths', $conn);

function render_tags($connection) {
	global $booth_tables;
	if (!(isset($booth_tables) && $booth_tables)) {
		$badge_names = get_booth_badge_names($connection);
		$booth_tables = get_booth_tables($connection, $badge_names);
	}
	foreach ($booth_tables as $result) {
		echo '<div';
			echo ' class="';
				echo 'tag';
				if (isset($result['booth'])) {
					echo ' tag-assigned';
				}
			echo '"';
			echo ' data-content="'.htmlspecialchars($result['id']).'"';
			if (isset($result['booth'])) {
				echo ' title="'.htmlspecialchars($result['booth']['booth_name']).'"';
			}
			echo ' style="';
				echo 'left: '.htmlspecialchars($result['x']).'%;';
				echo ' top: '.htmlspecialchars($result['y']).'%;';
			echo '"';
		echo '>';
			echo htmlspecialchars($result['id']);
		echo '</div>';
	}
}

function render_assignments($connection) {
	global $booth_tables;
	if (!(isset($booth_tables) && $booth_tables)) {
		$badge_names = get_booth_badge_names($connection);
		$booth_tables = get_booth_tables($connection, $badge_names);
	}
	$tables = array_values($booth_tables);
	$cells = count($tables);
	if ($cells) {
		$cols = 2;
		$rows = floor(($cells + $cols - 1) / $cols);
		echo '<table border="0" cellpadding="0" cellspacing="0" class="assignments">';
			echo '<tr>';
				for ($x = 0, $i = 0; $x < $cols; $x++, $i += $rows) {
					echo '<td>';
						echo '<table border="0" cellpadding="0" cellspacing="0">';
							for ($y = 0, $j = $i; $y < $rows && $j < $cells; $y++, $j++) {
								echo '<tr>';
									echo '<th>' . htmlspecialchars($tables[$j]['id']) . '</th>';
									echo '<td>';
										if (isset($tables[$j]['booth'])) {
											echo '<a href="booth.php?id='.$tables[$j]['booth']['id'].'" target="_blank">';
												echo htmlspecialchars($tables[$j]['booth']['booth_name']);
											echo '</a>';
										}
									echo '</td>';
								echo '</tr>';
							}
						echo '</table>';
					echo '</td>';
				}
			echo '</tr>';
		echo '</table>';
	} else {
		echo '<p>No tables have been tagged.</p>';
	}
}

function cmp_booth_name($a, $b) {
	return strnatcasecmp($a['booth_name'], $b['booth_name']);
}

function render_assignments_by_name($connection) {
	global $booth_tables;
	if (!(isset($booth_tables) && $booth_tables)) {
		$badge_names = get_booth_badge_names($connection);
		$booth_tables = get_booth_tables($connection, $badge_names);
	}
	$booths = array();
	foreach ($booth_tables as $table) {
		if (isset($table['booth']) && $table['booth']) {
			$booths[$table['booth']['id']] = $table['booth'];
		}
	}
	$booths = array_values($booths);
	usort($booths, 'cmp_booth_name');
	
	$cells = count($booths);
	if ($cells) {
		$cols = 2;
		$rows = floor(($cells + $cols - 1) / $cols);
		echo '<table border="0" cellpadding="0" cellspacing="0" class="assignments">';
			echo '<tr>';
				for ($x = 0, $i = 0; $x < $cols; $x++, $i += $rows) {
					echo '<td>';
						echo '<table border="0" cellpadding="0" cellspacing="0">';
							for ($y = 0, $j = $i; $y < $rows && $j < $cells; $y++, $j++) {
								echo '<tr>';
									echo '<td>';
										echo '<a href="booth.php?id='.$booths[$j]['id'].'" target="_blank">';
											echo htmlspecialchars($booths[$j]['booth_name']);
										echo '</a>';
									echo '</td>';
									echo '<th>' . htmlspecialchars(implode(', ', $booths[$j]['table_id'])) . '</th>';
								echo '</tr>';
							}
						echo '</table>';
					echo '</td>';
				}
			echo '</tr>';
		echo '</table>';
	} else {
		echo '<p>No tables have been assigned.</p>';
	}
}

$message = null;
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'upload':
			$message = upload_booth_map($_FILES['file']);
			break;
		case 'tag':
			$set = encode_booth_table($_POST);
			$q = 'INSERT INTO '.db_table_name('booth_tables').' SET '.$set.' ON DUPLICATE KEY UPDATE '.$set;
			mysql_query($q, $conn);
			render_tags($conn);
			exit(0);
			break;
		case 'untag':
			$id = $_POST['id'];
			$q = 'DELETE FROM '.db_table_name('booth_tables').' WHERE `id` = '.q_string($id);
			mysql_query($q, $conn);
			render_tags($conn);
			exit(0);
			break;
		case 'assignments':
			render_assignments($conn);
			exit(0);
			break;
	}
}

render_admin_head('Table Floor Plan');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmbtedit.js')) . '"></script>';
echo '<link rel="stylesheet" href="' . htmlspecialchars(resource_file_url('cmbtedit.css')) . '">';
echo '<style>.tag-map { padding-bottom: '.booth_map_aspect_ratio().'%; }</style>';

render_admin_body('Table Floor Plan');

echo '<div class="card">';
	echo '<div class="card-content spaced">';
		echo '<h1>Table Floor Plan</h1>';
		echo '<p>Upload an image of your event floor plan. Then click to tag a table with its identifier.</p>';
		echo '<div class="tag-map">';
			echo '<div class="tag-form hidden">';
				echo '<input type="hidden" class="tag-form-x">';
				echo '<input type="hidden" class="tag-form-y">';
				echo '<input type="text" class="tag-form-input">';
			echo '</div>';
			echo '<div class="untag-form hidden">';
				echo '<input type="hidden" class="untag-form-id">';
				echo '<button class="untag-form-confirm">Delete</button>';
			echo '</div>';
			echo '<div class="tags">';
				render_tags($conn);
			echo '</div>';
		echo '</div>';
		echo '<p>';
			echo '<form action="booth_tables.php" method="post" enctype="multipart/form-data">';
				echo '<input type="hidden" name="action" value="upload">';
				echo '<label for="file">Upload image:</label>';
				echo '&nbsp;&nbsp;<input type="file" name="file" id="file">';
				echo '&nbsp;&nbsp;<input type="submit" value="Upload">';
			echo '</form>';
		echo '</p>';
		echo '<hr>';
		echo '<h1>Table Assignments</h1>';
		echo '<div class="assignments-div">';
			render_assignments($conn);
		echo '</div>';
		echo '<hr>';
		echo '<h1>Table Assignments by Name</h1>';
		echo '<div>';
			render_assignments_by_name($conn);
		echo '</div>';
	echo '</div>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();