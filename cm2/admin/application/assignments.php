<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../../lib/database/application.php';
require_once dirname(__FILE__).'/../../lib/database/misc.php';
require_once dirname(__FILE__).'/../admin.php';

$context = (isset($_GET['c']) ? trim($_GET['c']) : null);
if (!$context) {
	header('Location: ../');
	exit(0);
}
$ctx_lc = strtolower($context);
$ctx_uc = strtoupper($context);
$ctx_info = (
	isset($cm_config['application_types'][$ctx_uc]) ?
	$cm_config['application_types'][$ctx_uc] : null
);
if (!$ctx_info) {
	header('Location: ../');
	exit(0);
}
$ctx_name = $ctx_info['nav_prefix'];
$ctx_name_lc = strtolower($ctx_name);

cm_admin_check_permission('application-assignments-'.$ctx_lc, 'application-assignments-'.$ctx_lc);

$apdb = new cm_application_db($db, $context);
$midb = new cm_misc_db($db);

if (isset($_POST['action'])) {
	header('Content-type: text/plain');
	switch ($_POST['action']) {
		case 'list-tags':
			$tags = $apdb->list_rooms_and_tables(true);
			$response = array('ok' => true, 'tags' => $tags);
			echo json_encode($response);
			break;
		case 'list-assignments':
			$assignments = $apdb->list_room_and_table_assignments(null, $ctx_uc);
			$response = array('ok' => true, 'assignments' => $assignments);
			echo json_encode($response);
			break;
	}
	exit(0);
}

$image_size = $midb->get_file_image_size('rooms-and-tables');
if (!$image_size) $image_size = array(640, 480);
$image_ratio = $image_size[1] * 100 / $image_size[0];

$event_dates = array();
$start_time = strtotime($cm_config['event']['start_date']);
$end_time = strtotime($cm_config['event']['end_date']);
while ($start_time <= $end_time) {
	$event_dates[] = date('D M j', $start_time);
	$start_time = strtotime('+1 day', $start_time);
}

cm_admin_head($ctx_name.' '.$ctx_info['assignment_term'][0].' Assignments');

echo '<link rel="stylesheet" href="assignments.css">';
echo '<style>';
	echo '.tag-map { padding-bottom: ' . $image_ratio . '%; }';
	echo '.calendar { height: ' . (count($event_dates) * 1440 + 37) . 'px; }';
	echo '.calendar-column { height: ' . (count($event_dates) * 1440 + 21) . 'px; }';
echo '</style>';

echo '<script type="text/javascript">';
	echo 'cm_app_event_info = ('.json_encode($cm_config['event']).');';
	$js_ctx_info = (
		array('ctx_lc' => $ctx_lc, 'ctx_uc' => $ctx_uc) + $ctx_info +
		array('ctx_name' => $ctx_name, 'ctx_name_lc' => $ctx_name_lc)
	);
	echo 'cm_app_context_info = ('.json_encode($js_ctx_info).');';
echo '</script>';
echo '<script type="text/javascript" src="assignments.js"></script>';

cm_admin_body($ctx_name.' '.$ctx_info['assignment_term'][0].' Assignments');
cm_admin_nav('application-assignments-'.$ctx_lc);
echo '<article>';

echo '<div class="card">';
	echo '<div class="card-title">Rooms &amp; Tables</div>';
	echo '<div class="card-content">';
		echo '<div class="spacing">';
			echo '<div class="tag-map">';
				echo '<div class="tags"></div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
echo '</div>';

echo '<div class="card">';
	echo '<div class="card-title">Assignments by Room or Table</div>';
	echo '<div class="card-content">';
		echo '<table border="0" cellpadding="0" cellspacing="0">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>R/T</th>';
					echo '<th>Time</th>';
					echo '<th>'.htmlspecialchars($ctx_info['application_name_term']).'</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody class="cm-assignments-by-room-or-table"></tbody>';
		echo '</table>';
	echo '</div>';
echo '</div>';

echo '<div class="card">';
	echo '<div class="card-title">Assignments by '.htmlspecialchars($ctx_info['application_name_term']).'</div>';
	echo '<div class="card-content">';
		echo '<table border="0" cellpadding="0" cellspacing="0">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>'.htmlspecialchars($ctx_info['application_name_term']).'</th>';
					echo '<th>R/T</th>';
					echo '<th>Time</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody class="cm-assignments-by-application-name"></tbody>';
		echo '</table>';
	echo '</div>';
echo '</div>';

echo '<div class="card">';
	echo '<div class="card-title">Assignments by Time</div>';
	echo '<div class="card-content">';
		echo '<div class="spacing">';
			echo '<div class="calendar">';
				echo '<div class="calendar-header">';
					echo '<div class="calendar-column">';
						echo '<div class="calendar-column-body">';
							foreach ($event_dates as $i => $event_date) {
								echo '<div class="calendar-day-label" style="top: '.($i * 1440).'px;">';
								echo htmlspecialchars($event_date);
								echo '</div>';
							}
						echo '</div>';
					echo '</div>';
					echo '<div class="calendar-column">';
						echo '<div class="calendar-column-body">';
							foreach ($event_dates as $i => $event_date) {
								for ($h = 0; $h < 24; $h++) {
									echo '<div class="calendar-hour-label" style="top: '.($i * 1440 + $h * 60).'px;">';
									echo substr('00'.$h, -2);
									echo '</div>';
								}
							}
						echo '</div>';
					echo '</div>';
					echo '<div class="calendar-column">';
						echo '<div class="calendar-column-body">';
							foreach ($event_dates as $i => $event_date) {
								for ($h = 0; $h < 24; $h++) {
									echo '<div class="calendar-hour-label" style="top: '.($i * 1440 + $h * 60).'px;">';
									echo (($h % 12) ? ($h % 12) : 12) . (($h < 12) ? 'am' : 'pm');
									echo '</div>';
								}
							}
						echo '</div>';
					echo '</div>';
				echo '</div>';
				echo '<div class="calendar-body"></div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
echo '</div>';

echo '</article>';
cm_admin_dialogs();
cm_admin_tail();