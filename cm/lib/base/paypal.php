<?php

function paypal_token($api_url, $client_id, $secret) {
	$curl = curl_init('https://' . $api_url . '/v1/oauth2/token');
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'Accept: application/json',
		'Accept-Language: en_US',
	));
	curl_setopt($curl, CURLOPT_USERPWD, (
		$client_id . ':' . $secret
	));
	curl_setopt($curl, CURLOPT_POSTFIELDS, (
		'grant_type=client_credentials'
	));
	$result = curl_exec($curl);
	curl_close($curl);
	return json_decode($result, true);
}

function paypal_api($api_url, $token, $api, $data) {
	$curl = curl_init('https://' . $api_url . '/v1/' . $api);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
	));
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	$result = curl_exec($curl);
	curl_close($curl);
	return json_decode($result, true);
}

function paypal_payment($api_url, $token, $return_url, $cancel_url, $transaction) {
	return paypal_api($api_url, $token, 'payments/payment', array(
		'intent' => 'sale',
		'redirect_urls' => array(
			'return_url' => $return_url,
			'cancel_url' => $cancel_url,
		),
		'payer' => array(
			'payment_method' => 'paypal',
		),
		'transactions' => array(
			$transaction
		),
	));
}

function paypal_payment_cc($api_url, $token, $return_url, $cancel_url, $transaction, $cc) {
	return paypal_api($api_url, $token, 'payments/payment', array(
		'intent' => 'sale',
		'redirect_urls' => array(
			'return_url' => $return_url,
			'cancel_url' => $cancel_url,
		),
		'payer' => array(
			'payment_method' => 'credit_card',
			'funding_instruments' => array(
				array('credit_card' => $cc),
			),
		),
		'transactions' => array(
			$transaction
		),
	));
}

function paypal_link($payment, $rel) {
	foreach ($payment['links'] as $link) {
		if ($link['rel'] == $rel) {
			return $link['href'];
		}
	}
}

function paypal_link_approval_url($payment) {
	return paypal_link($payment, 'approval_url');
}

function paypal_redirect($url) {
	header('Location: ' . $url);
	exit(0);
}

function paypal_execute($api_url, $token, $payment_id, $payer_id) {
	return paypal_api($api_url, $token, (
		'payments/payment/' . $payment_id . '/execute'
	), array(
		'payer_id' => $payer_id,
	));
}

function paypal_transaction_id($sale) {
	return $sale['transactions'][0]['related_resources'][0]['sale']['id'];
}