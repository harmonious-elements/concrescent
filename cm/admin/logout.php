<?php

session_name('PHPSESSID_CMADMIN');
session_start();
require_once dirname(__FILE__).'/../lib/common.php';
require_once dirname(__FILE__).'/../lib/admin.php';
require_once theme_file_path('admin.php');

admin_log_out();

render_head('Log Out');
render_body('Log Out', null, null);

echo '<div class="card logout">';
	echo '<div class="card-content">';
		echo '<p>You have been logged out.</p>';
	echo '</div>';
echo '</div>';

render_dialogs();
render_tail();