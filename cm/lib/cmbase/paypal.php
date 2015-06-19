<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../base/paypal.php';

function get_paypal_api_url() {
	global $paypal_api_url;
	return $paypal_api_url;
}

function get_paypal_token() {
	global $paypal_api_url, $paypal_client_id, $paypal_secret;
	$token = paypal_token($paypal_api_url, $paypal_client_id, $paypal_secret);
	return $token;
}

function paypal_total($total) {
	global $paypal_currency;
	$amount = array(
		'total' => number_format($total, 2, '.', ''),
		'currency' => $paypal_currency,
	);
	return $amount;
}

function paypal_item($name, $price) {
	global $paypal_currency;
	$item = array(
		'quantity' => '1',
		'name' => $name,
		'price' => number_format($price, 2, '.', ''),
		'currency' => $paypal_currency,
	);
	return $item;
}