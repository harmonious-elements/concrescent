<?php

session_name('PHPSESSID_CMADMIN');
session_start();

require_once dirname(__FILE__).'/../lib/database/database.php';
require_once dirname(__FILE__).'/../lib/database/admin.php';
require_once dirname(__FILE__).'/../lib/util/util.php';
require_once dirname(__FILE__).'/../lib/util/res.php';

$db = new cm_db();
$adb = new cm_admin_db($db);
$admin_user = $adb->logged_in_user();
if (!$admin_user) {
	$url = get_site_url(false) . '/admin/login.php?page=';
	$url .= urlencode($_SERVER['REQUEST_URI']);
	header('Location: ' . $url);
	exit(0);
}

function cm_admin_head($title) {
	echo '<!DOCTYPE HTML>';
	echo '<html>';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
	echo '<title>CONcrescent - ' . htmlspecialchars($title) . '</title>';
	echo '<link rel="shortcut icon" href="' . htmlspecialchars(theme_file_url('favicon.ico', false)) . '">';
	echo '<link rel="stylesheet" href="' . htmlspecialchars(theme_file_url('theme.css', false)) . '">';
}

function cm_admin_body($title) {
	echo '</head>';
	echo '<body class="cm-admin">';
	echo '<header>';
		echo '<div class="appname">CONcrescent</div>';
		echo '<div class="pagename">' . htmlspecialchars($title) . '</div>';
		echo '<div class="header-items">';
			echo '<div class="header-item">';
				echo htmlspecialchars($GLOBALS['admin_user']['name']);
			echo '</div>';
			echo '<div class="header-item">';
				$url = get_site_url(false) . '/admin/logout.php';
				echo '<a href="' . htmlspecialchars($url) . '">Log Out</a>';
			echo '</div>';
		echo '</div>';
	echo '</header>';
}

function cm_admin_tail() {
	echo '</body>';
	echo '</html>';
}