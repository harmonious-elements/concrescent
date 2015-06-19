<?php

session_name('PHPSESSID_CMPAY');
session_start();
require_once dirname(__FILE__).'/../lib/cmbase/paypal.php';
require_once dirname(__FILE__).'/../lib/cmbase/res.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/dal/payments.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';
require_once theme_file_path('public.php');

function render_payment_head($title) {
	render_head($title);
}

function render_payment_body($title) {
	render_body($title, null);
}

function render_payment_tail() {
	render_tail();
}