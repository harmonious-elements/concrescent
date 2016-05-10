<?php

session_name('PHPSESSID_CMADMIN');
session_start();

require_once dirname(__FILE__).'/../lib/database/database.php';
require_once dirname(__FILE__).'/../lib/database/admin.php';
require_once dirname(__FILE__).'/../lib/util/res.php';

$db = new cm_db();
$adb = new cm_admin_db($db);
$adb->log_out();

echo '<!DOCTYPE HTML>';
echo '<html>';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '<title>CONcrescent - Log Out</title>';
echo '<link rel="shortcut icon" href="' . htmlspecialchars(theme_file_url('favicon.ico', false)) . '">';
echo '<link rel="stylesheet" href="' . htmlspecialchars(resource_file_url('cm.css', false)) . '">';
echo '<link rel="stylesheet" href="' . htmlspecialchars(theme_file_url('theme.css', false)) . '">';
echo '</head>';
echo '<body class="cm-admin">';

echo '<header>';
	echo '<div class="appname">CONcrescent</div>';
	echo '<div class="header-items">';
		echo '<div class="header-item">';
			echo '<a href="login.php">Log In</a>';
		echo '</div>';
	echo '</div>';
echo '</header>';

echo '<article>';
	echo '<div class="card cm-logout">';
		echo '<div class="card-content">';
			echo '<p>You have been logged out.</p>';
		echo '</div>';
	echo '</div>';
echo '</article>';

echo '</body>';
echo '</html>';