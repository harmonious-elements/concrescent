<?php

require_once dirname(__FILE__).'/../lib/database/attendee.php';
require_once dirname(__FILE__).'/admin.php';

cm_admin_check_permission('statistics', 'statistics');

$atdb = new cm_attendee_db($db);
$name_map = $atdb->get_badge_type_name_map();
$atstat = $atdb->get_attendee_statistics(300, $name_map);
$name_map['*'] = 'Attendees';

$header = array('Date');
$last = array(0);
foreach ($name_map as $btid => $btname) {
	$header[] = $btname . ' - In System';
	$header[] = $btname . ' - Paid';
	$header[] = $btname . ' - Printed';
	$header[] = $btname . ' - Checked In';
	$last[] = 0;
	$last[] = 0;
	$last[] = 0;
	$last[] = 0;
}

$data = array($header);
$timestamps = $atstat['timestamps'];
ksort($timestamps);
foreach ($timestamps as $timestamp) {
	$row = array($timestamp);
	foreach ($name_map as $btid => $btname) {
		$row[] = isset($atstat['timelines'][$btid][0][$timestamp]) ? ($last[count($row)] = $atstat['timelines'][$btid][0][$timestamp]) : $last[count($row)];
		$row[] = isset($atstat['timelines'][$btid][1][$timestamp]) ? ($last[count($row)] = $atstat['timelines'][$btid][1][$timestamp]) : $last[count($row)];
		$row[] = isset($atstat['timelines'][$btid][2][$timestamp]) ? ($last[count($row)] = $atstat['timelines'][$btid][2][$timestamp]) : $last[count($row)];
		$row[] = isset($atstat['timelines'][$btid][3][$timestamp]) ? ($last[count($row)] = $atstat['timelines'][$btid][3][$timestamp]) : $last[count($row)];
	}
	$data[] = $row;
}

cm_admin_head('Statistics');
if (count($header) > 1 && count($data) > 1) {
	$json_data = json_encode($data);
	$json_data = preg_replace('/\\[([0-9]+)/', '[new Date($1)', $json_data);
	echo '<script type="text/javascript">cm_stat_data = (' . $json_data . '); cm_stat_count = ' . count($header) . ';</script>';
	echo '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
	echo '<script type="text/javascript" src="statistics.js"></script>';
}

cm_admin_body('Statistics');
cm_admin_nav('statistics');

echo '<article>';
	echo '<div class="card">';
		echo '<div class="card-content">';
			if (count($header) > 1 && count($data) > 1) {
				echo '<div class="spacing"><div id="cm-stat-chart">Loading Chart...</div></div>';
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="cm-statistics">';
				echo '<thead>';
					echo '<tr>';
						echo '<th class="cm-stat-corner">Badge Type</th>';
						echo '<th class="td-numeric cm-stat-col" id="cm-stat-col-1">In System</th>';
						echo '<th class="td-numeric cm-stat-col" id="cm-stat-col-2">Paid</th>';
						echo '<th class="td-numeric cm-stat-col" id="cm-stat-col-3">Printed</th>';
						echo '<th class="td-numeric cm-stat-col" id="cm-stat-col-4">Checked In</th>';
					echo '</tr>';
				echo '</thead>';
				$index = 0;
				foreach ($atstat['counters'] as $btid => $count) {
					$tbody = ($btid == '*') ? 'tfoot' : 'tbody';
					echo '<'.$tbody.'>';
						echo '<tr>';
							$td = ($btid == '*') ? 'th' : 'td';
							echo '<'.$td.' class="cm-stat-row" id="cm-stat-row-' . ($index + 1) . '">' . htmlspecialchars($name_map[$btid]) . '</'.$td.'>';
							echo '<'.$td.' class="td-numeric cm-stat" id="cm-stat-' . (++$index) . '">' . number_format($count[0]) . '</'.$td.'>';
							echo '<'.$td.' class="td-numeric cm-stat" id="cm-stat-' . (++$index) . '">' . number_format($count[1]) . '</'.$td.'>';
							echo '<'.$td.' class="td-numeric cm-stat" id="cm-stat-' . (++$index) . '">' . number_format($count[2]) . '</'.$td.'>';
							echo '<'.$td.' class="td-numeric cm-stat" id="cm-stat-' . (++$index) . '">' . number_format($count[3]) . '</'.$td.'>';
						echo '</tr>';
					echo '</'.$tbody.'>';
				}
			echo '</table>';
		echo '</div>';
	echo '</div>';
echo '</article>';

cm_admin_dialogs();
cm_admin_tail();