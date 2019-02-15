<?php

session_name('PHPSESSID_CMREG');
session_start();

require_once dirname(__FILE__).'/../config/config.php';
require_once dirname(__FILE__).'/../lib/database/database.php';
require_once dirname(__FILE__).'/../lib/database/attendee.php';
require_once dirname(__FILE__).'/../lib/database/forms.php';
require_once dirname(__FILE__).'/../lib/database/mail.php';
require_once dirname(__FILE__).'/../lib/util/res.php';
require_once dirname(__FILE__).'/../lib/util/util.php';

$event_name = $cm_config['event']['name'];

$db = new cm_db();

$atdb = new cm_attendee_db($db);
$name_map = $atdb->get_badge_type_name_map();

$fdb = new cm_forms_db($db, 'attendee');
$questions = $fdb->list_questions();

$mdb = new cm_mail_db($db);
$contact_address = $mdb->get_contact_address('attendee-paid');

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

function cm_reg_cart_total() {
	if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
	$total = 0;
	foreach ($_SESSION['cart'] as $item) {
		$total += (float)$item['payment-promo-price'];
		if (isset($item['addons']) && $item['addons']) {
			foreach ($item['addons'] as $addon) {
				$total += (float)$addon['price'];
			}
		}
	}
	return $total;
}

function cm_reg_cart_set_state($state) {
	if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
	$_SESSION['cart_hash'] = md5(serialize($_SESSION['cart']));
	$_SESSION['cart_state'] = $state;
}

function cm_reg_cart_check_state($expected_state) {
	if (!isset($_SESSION['cart'])) return false;
	if (!isset($_SESSION['cart_hash'])) return false;
	if (!isset($_SESSION['cart_state'])) return false;
	$expected_hash = md5(serialize($_SESSION['cart']));
	if ($_SESSION['cart_hash'] != $expected_hash) return false;
	if ($_SESSION['cart_state'] != $expected_state) return false;
	return true;
}

function cm_reg_cart_destroy() {
	unset($_SESSION['cart']);
	unset($_SESSION['cart_hash']);
	unset($_SESSION['cart_state']);
	session_destroy();
}

function cm_reg_post_edit_get() {
	if (isset($_SESSION['post_edit'])) {
		return $_SESSION['post_edit'];
	} else {
		return null;
	}
}

function cm_reg_post_edit_set($item) {
	$_SESSION['post_edit'] = $item;
}

function cm_reg_post_edit_total() {
	$total = 0;
	if (isset($_SESSION['post_edit'])) {
		$item = $_SESSION['post_edit'];
		if (isset($item['new-badge-type'])) {
			$bt = $item['new-badge-type'];
			if (isset($bt['price-diff'])) {
				$total += (float)$bt['price-diff'];
			}
		}
		if (isset($item['new-addons'])) {
			foreach ($item['new-addons'] as $addon) {
				$total += (float)$addon['price'];
			}
		}
	}
	return $total;
}

function cm_reg_post_edit_set_state($state) {
	if (!isset($_SESSION['post_edit'])) $_SESSION['post_edit'] = array();
	$_SESSION['post_edit_hash'] = md5(serialize($_SESSION['post_edit']));
	$_SESSION['post_edit_state'] = $state;
}

function cm_reg_post_edit_check_state($expected_state) {
	if (!isset($_SESSION['post_edit'])) return false;
	if (!isset($_SESSION['post_edit_hash'])) return false;
	if (!isset($_SESSION['post_edit_state'])) return false;
	$expected_hash = md5(serialize($_SESSION['post_edit']));
	if ($_SESSION['post_edit_hash'] != $expected_hash) return false;
	if ($_SESSION['post_edit_state'] != $expected_state) return false;
	return true;
}

function cm_reg_post_edit_destroy() {
	unset($_SESSION['post_edit']);
	unset($_SESSION['post_edit_hash']);
	unset($_SESSION['post_edit_state']);
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

function cm_reg_body($title, $show_cart = true) {
	echo '</head>';
	echo '<body class="cm-reg">';
	echo '<header>';
		echo '<div class="pagename">' . htmlspecialchars($title) . '</div>';
		if ($show_cart) {
			echo '<div class="header-items">';
				echo '<div class="header-item">';
					$url = get_site_url(false) . '/register/cart.php';
					$count = cm_reg_cart_count();
					$count .= ($count == 1) ? ' item' : ' items';
					echo '<a href="' . htmlspecialchars($url) . '">Shopping Cart: ' . $count . '</a>';
				echo '</div>';
			echo '</div>';
		}
	echo '</header>';
}

function cm_reg_tail() {
	echo '</body>';
	echo '</html>';
}

function cm_reg_closed() {
	global $event_name, $contact_address;
	cm_reg_head('Registration Closed');
	cm_reg_body('Registration Closed', false);
	echo '<article>';
	echo '<div class="card">';
	echo '<div class="card-content">';
	echo '<p>';
	echo 'Registration for <b>';
	echo htmlspecialchars($event_name);
	echo '</b> is currently closed.';
	if ($contact_address) {
		echo ' Please <b><a href="mailto:';
		echo htmlspecialchars($contact_address);
		echo '">contact us</a></b> if you have any questions.';
	}
	echo '</p>';
	echo '</div>';
	echo '</div>';
	echo '</article>';
	cm_reg_tail();
	exit(0);
}

function cm_reg_message($title, $custom_text_name, $default_text, $fields = null) {
	global $event_name, $fdb, $contact_address;
	cm_reg_head($title);
	cm_reg_body($title, false);
	echo '<article>';
	echo '<div class="card">';
	echo '<div class="card-title">';
	echo htmlspecialchars($title);
	echo '</div>';
	echo '<div class="card-content">';
	$text = $fdb->get_custom_text($custom_text_name);
	if (!$text) $text = $default_text;
	$text = safe_html_string($text, true);
	$merge_fields = array(
		'event-name' => $event_name,
		'event_name' => $event_name,
		'contact-address' => $contact_address,
		'contact_address' => $contact_address
	);
	if ($fields) {
		foreach ($fields as $k => $v) {
			$merge_fields[strtolower(str_replace('_', '-', $k))] = $v;
			$merge_fields[strtolower(str_replace('-', '_', $k))] = $v;
		}
	}
	echo mail_merge_html($text, $merge_fields);
	echo '</div>';
	echo '<div class="card-buttons">';
	echo '<a href="index.php" role="button" class="button register-button">';
	echo 'Start a New Registration';
	echo '</a>';
	echo '</div>';
	echo '</div>';
	echo '</article>';
	cm_reg_tail();
	exit(0);
}