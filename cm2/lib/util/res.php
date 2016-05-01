<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/util.php';

function config_file_path($file) {
	return realpath(dirname(__FILE__) . '/../../config') . '/' . $file;
}

function config_file_url($file, $full) {
	return get_site_url($full) . '/config/' . $file;
}

function resource_file_path($file) {
	return realpath(dirname(__FILE__) . '/../res') . '/' . $file;
}

function resource_file_url($file, $full) {
	return get_site_url($full) . '/lib/res/' . $file;
}

function theme_file_path($file) {
	$theme = $GLOBALS['cm_config']['theme']['location'];
	return realpath(dirname(__FILE__) . '/../../' . $theme) . '/' . $file;
}

function theme_file_url($file, $full) {
	$theme = $GLOBALS['cm_config']['theme']['location'];
	return get_site_url($full) . '/' . $theme . '/' . $file;
}