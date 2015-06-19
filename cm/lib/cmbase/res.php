<?php

require_once dirname(__FILE__).'/../../config/config.php';

function theme_file_path($file) {
	global $theme_base;
	return realpath(dirname(__FILE__) . '/../../' . $theme_base . '/' . $file);
}

function theme_file_url($file) {
	$path = theme_file_path($file);
	$root = realpath($_SERVER['DOCUMENT_ROOT']);
	$rl = strlen($root);
	if (substr($path, 0, $rl) == $root) {
		$path = substr($path, $rl);
	}
	if (substr($path, 0, 1) != '/') {
		$path = '/' . $path;
	}
	return $path;
}

function resource_file_path($file) {
	return realpath(dirname(__FILE__) . '/../res/' . $file);
}

function resource_file_url($file) {
	$path = resource_file_path($file);
	$root = realpath($_SERVER['DOCUMENT_ROOT']);
	$rl = strlen($root);
	if (substr($path, 0, $rl) == $root) {
		$path = substr($path, $rl);
	}
	if (substr($path, 0, 1) != '/') {
		$path = '/' . $path;
	}
	return $path;
}

function config_file_path($file) {
	return realpath(dirname(__FILE__) . '/../../config') . '/' . $file;
}