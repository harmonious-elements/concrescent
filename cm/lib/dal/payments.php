<?php

require_once dirname(__FILE__).'/dal.php';
require_once dirname(__FILE__).'/../schema/payments.php';
require_once dirname(__FILE__).'/../base/sql.php';
require_once dirname(__FILE__).'/../base/util.php';
require_once dirname(__FILE__).'/../cmbase/util.php';

function decode_payment($result) {
	$id = (int)$result['id'];
	$id_string = 'P'.$id;
	$name = unpurify_string($result['name']);
	$description = unpurify_string($result['description']);
	$description_html = safe_html_string($description);
	$first_name = unpurify_string($result['first_name']);
	$last_name = unpurify_string($result['last_name']);
	$real_name = trim(trim($first_name) . ' ' . trim($last_name));
	$email_address = unpurify_string($result['email_address']);
	$payment_status = $result['payment_status'];
	$payment_status_string = payment_status_string($payment_status);
	$payment_status_html = payment_status_html($payment_status);
	$payment_type = unpurify_string($result['payment_type']);
	$payment_txn_id = unpurify_string($result['payment_txn_id']);
	$payment_price = (float)$result['payment_price'];
	$payment_price_string = price_string($payment_price);
	$payment_date = $result['payment_date'];
	$payment_details = unpurify_string($result['payment_details']);
	$payment_lookup_key = unpurify_string($result['payment_lookup_key']);
	$order_url = get_base_url() . 'payment/order.php?';
	$confirm_payment_url = $payment_lookup_key ? ($order_url.'id='.$id.'&key='.$payment_lookup_key) : null;
	$review_order_url = ($payment_txn_id && $payment_lookup_key) ? ($order_url.'txn='.$payment_txn_id.'&key='.$payment_lookup_key) : null;
	$order_url = $review_order_url ? $review_order_url : $confirm_payment_url;
	$search_content = strtolower(implode('||', array(
		$id,
		$name,
		$description,
		$real_name,
		$email_address,
		$payment_status_string,
		$payment_txn_id,
		$payment_lookup_key,
	)));
	return array(
		'id' => $id,
		'id_string' => $id_string,
		'name' => $name,
		'description' => $description,
		'description_html' => $description_html,
		'first_name' => $first_name,
		'last_name' => $last_name,
		'real_name' => $real_name,
		'email_address' => $email_address,
		'payment_status' => $payment_status,
		'payment_status_string' => $payment_status_string,
		'payment_status_html' => $payment_status_html,
		'payment_type' => $payment_type,
		'payment_txn_id' => $payment_txn_id,
		'payment_price' => $payment_price,
		'payment_price_string' => $payment_price_string,
		'payment_date' => $payment_date,
		'payment_details' => $payment_details,
		'payment_lookup_key' => $payment_lookup_key,
		'confirm_payment_url' => $confirm_payment_url,
		'review_order_url' => $review_order_url,
		'order_url' => $order_url,
		'search_content' => $search_content,
	);
}

function encode_payment($result) {
	$set = array();
	if (isset($result['name'           ])) $set[] = '`name` = '            . q_string        ($result['name'           ]);
	if (isset($result['description'    ])) $set[] = '`description` = '     . q_string_or_null($result['description'    ]);
	if (isset($result['first_name'     ])) $set[] = '`first_name` = '      . q_string        ($result['first_name'     ]);
	if (isset($result['last_name'      ])) $set[] = '`last_name` = '       . q_string        ($result['last_name'      ]);
	if (isset($result['email_address'  ])) $set[] = '`email_address` = '   . q_string        ($result['email_address'  ]);
	if (isset($result['payment_status' ])) $set[] = '`payment_status` = '  . q_string        ($result['payment_status' ]);
	if (isset($result['payment_type'   ])) $set[] = '`payment_type` = '    . q_string_or_null($result['payment_type'   ]);
	if (isset($result['payment_txn_id' ])) $set[] = '`payment_txn_id` = '  . q_string_or_null($result['payment_txn_id' ]);
	if (isset($result['payment_price'  ])) $set[] = '`payment_price` = '   . q_float_or_null ($result['payment_price'  ]);
	if (isset($result['payment_details'])) $set[] = '`payment_details` = ' . q_string_or_null($result['payment_details']);
	if (isset($result['payment_date'])) {
		$payment_date = trim($result['payment_date']);
		if ($payment_date != 'NOW()') {
			$payment_date = q_string_or_null($payment_date);
		}
		$set[] = '`payment_date` = ' . $payment_date;
	}
	if (isset($result['payment_lookup_key'])) {
		switch ($result['payment_lookup_key']) {
			case 'CLEAR': case 'clear':
			case 'NULL': case 'null':
				$set[] = '`payment_lookup_key` = NULL';
				break;
			case 'UUID()': case 'uuid()':
			case 'RESET': case 'reset':
			case 'UUID': case 'uuid':
			case 'NEW': case 'new':
				$set[] = '`payment_lookup_key` = UUID()';
				break;
		}
	}
	return implode(', ', $set);
}

function get_payment($id, $conn) {
	db_require_table('payments', $conn);
	$results = mysql_query('SELECT * FROM '.db_table_name('payments').' WHERE `id` = '.q_int($id), $conn);
	if ($result = mysql_fetch_assoc($results)) {
		return decode_payment($result);
	} else {
		return null;
	}
}

function get_payment_for_payment($id, $key, $conn) {
	db_require_table('payments', $conn);
	$results = mysql_query(
		('SELECT * FROM '.db_table_name('payments').
		' WHERE `id` = '.q_int($id).
		' AND (`payment_status` = \'Incomplete\' OR `payment_status` = \'Cancelled\')'.
		' AND `payment_lookup_key` = '.q_string($key).
		' ORDER BY `id`'),
		$conn
	);
	if ($result = mysql_fetch_assoc($results)) {
		return decode_payment($result);
	} else {
		return null;
	}
}

function get_payment_for_review($txn, $key, $conn) {
	db_require_table('payments', $conn);
	$results = mysql_query(
		('SELECT * FROM '.db_table_name('payments').
		' WHERE `payment_txn_id` = '.q_string($txn).
		' AND `payment_lookup_key` = '.q_string($key).
		' ORDER BY `id`'),
		$conn
	);
	if ($result = mysql_fetch_assoc($results)) {
		return decode_payment($result);
	} else {
		return null;
	}
}

function set_payment_cancelled($id, $type, $total, $conn) {
	db_require_table('payments', $conn);
	$set = encode_payment(array(
		'payment_status' => 'Cancelled',
		'payment_type' => $type,
		'payment_txn_id' => null,
		'payment_price' => $total,
		'payment_date' => 'NOW()',
		'payment_details' => null,
	));
	$q = 'UPDATE '.db_table_name('payments').' SET '.$set.' WHERE `id` = '.$id;
	mysql_query($q, $conn);
}

function set_payment_failed($id, $type, $details, $conn) {
	db_require_table('payments', $conn);
	$set = encode_payment(array(
		'payment_status' => 'Incomplete',
		'payment_type' => $type,
		'payment_txn_id' => null,
		'payment_date' => 'NOW()',
		'payment_details' => json_encode($details),
	));
	$q = 'UPDATE '.db_table_name('payments').' SET '.$set.' WHERE `id` = '.$id;
	mysql_query($q, $conn);
}

function set_payment_completed($id, $type, $txn, $total, $details, $conn) {
	db_require_table('payments', $conn);
	$set = encode_payment(array(
		'payment_status' => 'Completed',
		'payment_type' => $type,
		'payment_txn_id' => $txn,
		'payment_price' => $total,
		'payment_date' => 'NOW()',
		'payment_details' => json_encode($details),
	));
	$q = 'UPDATE '.db_table_name('payments').' SET '.$set.' WHERE `id` = '.$id;
	mysql_query($q, $conn);
}