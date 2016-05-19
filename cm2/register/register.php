<?php

session_name('PHPSESSID_CMREG');
session_start();

require_once dirname(__FILE__).'/../lib/database/database.php';
require_once dirname(__FILE__).'/../lib/database/attendee.php';
require_once dirname(__FILE__).'/../lib/database/forms.php';
require_once dirname(__FILE__).'/../lib/util/res.php';
require_once dirname(__FILE__).'/../lib/util/util.php';

$db = new cm_db();

$atdb = new cm_attendee_db($db);
$name_list = $atdb->list_badge_type_names();

$fdb = new cm_forms_db($db, 'attendee');
$questions = $fdb->list_questions();

function cm_reg_cart_count() {
	if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
	return count($_SESSION['cart']);
}

function cm_reg_cart_add($item) {
	if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
	$_SESSION['cart'][] = $item;
}

function cm_reg_cart_get($index) {
	if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
	if (!isset($_SESSION['cart'][$index])) return null;
	return $_SESSION['cart'][$index];
}

function cm_reg_cart_set($index, $item) {
	if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
	$_SESSION['cart'][$index] = $item;
}

function cm_reg_cart_remove($index) {
	if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
	if (!isset($_SESSION['cart'][$index])) return;
	array_splice($_SESSION['cart'], $index, 1);
}

function cm_reg_cart_reset_promo_code() {
	if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
	foreach ($_SESSION['cart'] as $index => $item) {
		$_SESSION['cart'][$index]['payment-promo-code'] = null;
		$_SESSION['cart'][$index]['payment-promo-price'] = $item['payment-badge-price'];
	}
}

function cm_reg_cart_destroy() {
	unset($_SESSION['cart']);
	unset($_SESSION['cart_hash']);
	unset($_SESSION['cart_state']);
	session_destroy();
}

function cm_reg_head($title) {
	echo '<!DOCTYPE HTML>';
	echo '<html>';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
	echo '<title>Register - ' . htmlspecialchars($title) . '</title>';
	echo '<link rel="shortcut icon" href="' . htmlspecialchars(theme_file_url('favicon.ico', false)) . '">';
	echo '<link rel="stylesheet" href="' . htmlspecialchars(resource_file_url('cm.css', false)) . '">';
	echo '<link rel="stylesheet" href="' . htmlspecialchars(theme_file_url('theme.css', false)) . '">';
	echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('jquery.js', false)) . '"></script>';
	echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmui.js', false)) . '"></script>';
}

function cm_reg_body($title) {
	echo '</head>';
	echo '<body class="cm-reg">';
	echo '<header>';
		echo '<div class="pagename">' . htmlspecialchars($title) . '</div>';
		echo '<div class="header-items">';
			echo '<div class="header-item">';
				$url = get_site_url(false) . '/register/cart.php';
				$count = cm_reg_cart_count();
				$count .= ($count == 1) ? ' item' : ' items';
				echo '<a href="' . htmlspecialchars($url) . '">Shopping Cart: ' . $count . '</a>';
			echo '</div>';
		echo '</div>';
	echo '</header>';
}

function cm_reg_tail() {
	echo '</body>';
	echo '</html>';
}