<?php

session_name('PHPSESSID_CMADMIN');
session_start();
require_once dirname(__FILE__).'/../lib/common.php';
require_once dirname(__FILE__).'/../lib/admin.php';
require_once theme_file_path('admin.php');

$page = isset($_GET['page']) ? $_GET['page'] : null;
$attempted = false;

if (isset($_POST['username']) && isset($_POST['password'])) {
	$attempted = true;
	if (admin_log_in(get_db_connection(), $_POST['username'], $_POST['password'])) {
		if ($page) {
			header('Location: ' . $page);
		} else {
			header('Location: index.php');
		}
		exit(0);
	}
}

admin_log_out();

render_head('Log In');
render_body('Log In', null, null);

echo '<div class="card login">';
	echo '<form action="';
		if ($page) {
			echo 'login.php?page=' . urlencode($page);
		} else {
			echo 'login.php';
		}
	echo '" method="post">';
		echo '<div class="card-title">Log In</div>';
		echo '<div class="card-content">';
			echo '<table border="0" cellpadding="0" cellspacing="0">';
				if ($attempted) {
					echo '<tr><td colspan="2">Login failed. Please try again.</td></tr>';
				}
				echo '<tr>';
					echo '<th><label for="username">User Name:</label></th>';
					echo '<td><input type="text" name="username" id="username"></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<th><label for="password">Password:</label></th>';
					echo '<td><input type="password" name="password" id="password"></td>';
				echo '</tr>';
			echo '</table>';
		echo '</div>';
		echo '<div class="card-buttons">';
			echo '<input type="submit" value="Log In">';
		echo '</div>';
	echo '</form>';
echo '</div>';

render_dialogs();
render_tail();