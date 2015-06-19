<?php

require_once dirname(__FILE__).'/../../lib/common.php';

function render_head($title) {
	echo '<!DOCTYPE HTML>';
	echo '<html>';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
	echo '<title>' . htmlspecialchars($title) . '</title>';
	echo '<link rel="shortcut icon" href="' . htmlspecialchars(theme_file_url('public-favicon.ico')) . '">';
	echo '<link rel="stylesheet" href="' . htmlspecialchars(theme_file_url('base.css')) . '">';
	echo '<link rel="stylesheet" href="' . htmlspecialchars(theme_file_url('public.css')) . '">';
}

function render_body($title, $header_items) {
	echo '</head>';
	echo '<body>';
	echo '<header>';
	echo '<div class="header-title">';
	echo htmlspecialchars($title);
	echo '</div>';
	if ($header_items && count($header_items)) {
		foreach ($header_items as $header_item) {
			echo '<div class="header-item">';
			echo $header_item;
			echo '</div>';
		}
	}
	echo '</header>';
	echo '<article>';
}

function render_tail() {
	echo '</article>';
	echo '</body>';
	echo '</html>';
}