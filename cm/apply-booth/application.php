<?php

session_name('PHPSESSID_CMAPPLYBOOTH');
session_start();
require_once dirname(__FILE__).'/../lib/common.php';
require_once dirname(__FILE__).'/../lib/booths.php';
require_once dirname(__FILE__).'/../lib/cart.php';
require_once theme_file_path('public.php');

function render_application_head($title) {
	render_head($title);
}

function render_application_body($title) {
	render_body($title, null);
}

function render_application_tail() {
	render_tail();
}