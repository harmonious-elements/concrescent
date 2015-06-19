<?php

session_name('PHPSESSID_CMREG');
session_start();
require_once dirname(__FILE__).'/../lib/common.php';
require_once dirname(__FILE__).'/../lib/attendees.php';
require_once dirname(__FILE__).'/../lib/cart.php';
require_once theme_file_path('public.php');

function render_registration_head($title) {
	render_head('Registration - ' . $title);
}

function render_registration_body($title) {
	$cart = get_cart();
	$cart_link = '<a href="cart.php">Shopping Cart: ' . count($cart) . ((count($cart) == 1) ? ' item' : ' items') . '</a>';
	render_body($title, array($cart_link));
}

function render_registration_tail() {
	render_tail();
}