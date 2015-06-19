<?php

require_once dirname(__FILE__).'/../../lib/common.php';

function render_head($title) {
	echo '<!DOCTYPE HTML>';
	echo '<html>';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
	echo '<title>CONcrescent - ' . htmlspecialchars($title) . '</title>';
	echo '<link rel="shortcut icon" href="' . htmlspecialchars(theme_file_url('admin-favicon.ico')) . '">';
	echo '<link rel="stylesheet" href="' . htmlspecialchars(theme_file_url('base.css')) . '">';
	echo '<link rel="stylesheet" href="' . htmlspecialchars(theme_file_url('admin.css')) . '">';
}

function render_body($title, $nav_links, $admin_user) {
	echo '</head>';
	echo '<body>';
	echo '<header>';
	echo '<span class="appname">CONcrescent</span>';
	echo '<span class="pagename">' . htmlspecialchars($title) . '</span>';
	if ($admin_user) {
		echo '<span class="username">' . htmlspecialchars($admin_user['name']) . ' &middot; <a href="logout.php">Log Out</a></span>';
	} else {
		echo '<span class="username"><a href="login.php">Log In</a></span>';
	}
	echo '</header>';
	if ($nav_links) {
		echo '<nav>';
		echo '<ul>';
		$page_url = get_page_filename();
		$current = ($page_url == 'index.php');
		echo $current ? '<li class="current">' : '<li>';
		echo '<a href="index.php">Home</a>';
		echo '</li>';
		echo '</ul>';
		echo '<hr>';
		echo '<ul>';
		foreach ($nav_links as $nav_link) {
			if (isset($nav_link['---'])) {
				echo '</ul>';
				echo '<hr>';
				echo '<ul>';
			} else {
				$current = ($page_url == $nav_link['href'] || in_array($page_url, $nav_link['related']));
				echo $current ? '<li class="current">' : '<li>';
				echo '<a href="' . htmlspecialchars($nav_link['href']) . '"';
				if (isset($nav_link['description']) && $nav_link['description']) {
					echo ' title="' . htmlspecialchars($nav_link['description']) . '"';
				}
				echo '>';
				echo htmlspecialchars($nav_link['text']);
				echo '</a>';
				echo '</li>';
			}
		}
		echo '</ul>';
		echo '</nav>';
		echo '<article>';
	} else {
		echo '<article class="nonav">';
	}
}

function render_dialogs() {
	echo '</article>';
	echo '<div class="dialog-cover hidden"></div>';
}

function render_tail() {
	echo '<div class="butterbar hidden"></div>';
	echo '</body>';
	echo '</html>';
}