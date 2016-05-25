<?php

ini_set('max_execution_time', 300);
ini_set('memory_limit', '1024M');

require_once dirname(__FILE__).'/../../lib/database/attendee.php';
require_once dirname(__FILE__).'/../admin.php';

if (isset($_POST['submit'])) {
	$time = microtime(true);
	$atdb = new cm_attendee_db($db);
	$atdb->rebuild_index();
	$time = microtime(true) - $time;
}

cm_admin_head('Rebuild Attendee Search Index');
cm_admin_body('Rebuild Attendee Search Index');
cm_admin_nav('attendee-reindex');

echo '<article>';
	echo '<form action="reindex.php" method="post" class="card">';
		echo '<div class="card-content">';
			if (isset($_POST['submit'])) {
				echo '<p class="cm-success-box">';
					echo 'Attendee search index rebuilt in <b>';
					echo (round($time * 100) / 100);
					echo '</b> seconds.';
				echo '</p>';
			} else {
				echo '<p class="cm-warning-box">';
					echo 'Rebuilding the attendee search index takes on the order of minutes.';
				echo '</p>';
			}
			echo '<p>';
				echo '<input type="submit" name="submit" value="Rebuild Attendee Search Index">';
			echo '</p>';
		echo '</div>';
	echo '</form>';
echo '</article>';

cm_admin_dialogs();
cm_admin_tail();