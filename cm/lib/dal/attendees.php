<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/dal.php';
require_once dirname(__FILE__).'/questions.php';
require_once dirname(__FILE__).'/../schema/attendees.php';
require_once dirname(__FILE__).'/../base/sql.php';
require_once dirname(__FILE__).'/../base/util.php';
require_once dirname(__FILE__).'/../cmbase/util.php';

function decode_attendee_badge($result) {
	global $event_date_start, $event_date_end;
	$id = (int)$result['id'];
	$id_string = 'AB'.$id;
	$name = unpurify_string($result['name']);
	$description = unpurify_string($result['description']);
	$description_html = safe_html_string($description);
	$start_date = $result['start_date'];
	$end_date = $result['end_date'];
	$min_age = (int)$result['min_age'];
	$max_age = (int)$result['max_age'];
	$min_birthdate = $max_age ? (((int)$event_date_start - $max_age - 1) . substr($event_date_start, 4)) : null;
	$max_birthdate = $min_age ? (((int)$event_date_end   - $min_age    ) . substr($event_date_end  , 4)) : null;
	$count = (int)$result['count'];
	$active = !!$result['active'];
	$price = (float)$result['price'];
	$price_string = price_string($price);
	$order = (int)$result['order'];
	return array(
		'id' => $id,
		'id_string' => $id_string,
		'name' => $name,
		'description' => $description,
		'description_html' => $description_html,
		'start_date' => $start_date,
		'end_date' => $end_date,
		'min_age' => $min_age,
		'max_age' => $max_age,
		'min_birthdate' => $min_birthdate,
		'max_birthdate' => $max_birthdate,
		'count' => $count,
		'active' => $active,
		'price' => $price,
		'price_string' => $price_string,
		'order' => $order,
	);
}

function encode_attendee_badge($result) {
	$set = array();
	if (isset($result['name'       ])) $set[] = '`name` = '        . q_string        ($result['name'       ]);
	if (isset($result['description'])) $set[] = '`description` = ' . q_string_or_null($result['description']);
	if (isset($result['start_date' ])) $set[] = '`start_date` = '  . q_date_or_null  ($result['start_date' ]);
	if (isset($result['end_date'   ])) $set[] = '`end_date` = '    . q_date_or_null  ($result['end_date'   ]);
	if (isset($result['min_age'    ])) $set[] = '`min_age` = '     . q_int_or_null   ($result['min_age'    ]);
	if (isset($result['max_age'    ])) $set[] = '`max_age` = '     . q_int_or_null   ($result['max_age'    ]);
	if (isset($result['count'      ])) $set[] = '`count` = '       . q_int_or_null   ($result['count'      ]);
	if (isset($result['active'     ])) $set[] = '`active` = '      . q_boolean       ($result['active'     ]);
	if (isset($result['price'      ])) $set[] = '`price` = '       . q_float         ($result['price'      ]);
	return implode(', ', $set);
}

function decode_promo_code($result, $badge_names) {
	$id = (int)$result['id'];
	$id_string = 'AC'.$id;
	$code = unpurify_string($result['code']);
	$description = unpurify_string($result['description']);
	$description_html = safe_html_string($description);
	$badge_id = (int)$result['badge_id'];
	$badge_id_string = ($badge_id ? ('AB'.$badge_id) : '');
	$badge_name = (isset($badge_names[$badge_id]) ? $badge_names[$badge_id] : 'Unknown');
	$limit = (int)$result['limit'];
	$start_date = $result['start_date'];
	$end_date = $result['end_date'];
	$active = !!$result['active'];
	$price = (float)$result['price'];
	$price_string = price_string($price);
	$percentage = !!$result['percentage'];
	return array(
		'id' => $id,
		'id_string' => $id_string,
		'code' => $code,
		'description' => $description,
		'description_html' => $description_html,
		'badge_id' => $badge_id,
		'badge_id_string' => $badge_id_string,
		'badge_name' => $badge_name,
		'limit' => $limit,
		'start_date' => $start_date,
		'end_date' => $end_date,
		'active' => $active,
		'price' => $price,
		'price_string' => $price_string,
		'percentage' => $percentage,
	);
}

function encode_promo_code($result) {
	$set = array();
	if (isset($result['code'])) {
		$code = strtoupper(preg_replace('/[^A-Za-z0-9!@#$%&*?]/', '', $result['code']));
		$set[] = '`code` = ' . q_string($code);
	}
	if (isset($result['description'])) $set[] = '`description` = ' . q_string_or_null($result['description']);
	if (isset($result['badge_id'   ])) $set[] = '`badge_id` = '    . q_int_or_null   ($result['badge_id'   ]);
	if (isset($result['limit'      ])) $set[] = '`limit` = '       . q_int_or_null   ($result['limit'      ]);
	if (isset($result['start_date' ])) $set[] = '`start_date` = '  . q_date_or_null  ($result['start_date' ]);
	if (isset($result['end_date'   ])) $set[] = '`end_date` = '    . q_date_or_null  ($result['end_date'   ]);
	if (isset($result['active'     ])) $set[] = '`active` = '      . q_boolean       ($result['active'     ]);
	if (isset($result['price'      ])) $set[] = '`price` = '       . q_float         ($result['price'      ]);
	if (isset($result['percentage' ])) $set[] = '`percentage` = '  . q_boolean       ($result['percentage' ]);
	return implode(', ', $set);
}

function decode_attendee_blacklist($result) {
	$id = (int)$result['id'];
	$id_string = 'AD'.$id;
	$first_name = unpurify_string($result['first_name']);
	$last_name = unpurify_string($result['last_name']);
	$real_name = trim(trim($first_name) . ' ' . trim($last_name));
	$reversed_name = trim(trim($last_name) . ' ' . trim($first_name));
	$fandom_name = unpurify_string($result['fandom_name']);
	$email_address = unpurify_string($result['email_address']);
	$phone_number = unpurify_string($result['phone_number']);
	$normalized_real_name = unpurify_string($result['normalized_real_name']);
	$normalized_reversed_name = unpurify_string($result['normalized_reversed_name']);
	$normalized_fandom_name = unpurify_string($result['normalized_fandom_name']);
	$normalized_email_address = unpurify_string($result['normalized_email_address']);
	$normalized_phone_number = unpurify_string($result['normalized_phone_number']);
	return array(
		'id' => $id,
		'id_string' => $id_string,
		'first_name' => $first_name,
		'last_name' => $last_name,
		'real_name' => $real_name,
		'reversed_name' => $reversed_name,
		'fandom_name' => $fandom_name,
		'email_address' => $email_address,
		'phone_number' => $phone_number,
		'normalized_real_name' => $normalized_real_name,
		'normalized_reversed_name' => $normalized_reversed_name,
		'normalized_fandom_name' => $normalized_fandom_name,
		'normalized_email_address' => $normalized_email_address,
		'normalized_phone_number' => $normalized_phone_number,
	);
}

function encode_attendee_blacklist($result) {
	$set = array();
	if (isset($result['first_name']) || isset($result['last_name'])) {
		$first_name = (isset($result['first_name']) ? $result['first_name'] : '');
		$last_name = (isset($result['last_name']) ? $result['last_name'] : '');
		$normalized_real_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $first_name . $last_name));
		$normalized_reversed_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $last_name . $first_name));
		$set[] = '`first_name` = ' . q_string_or_null($first_name);
		$set[] = '`last_name` = ' . q_string_or_null($last_name);
		$set[] = '`normalized_real_name` = ' . q_string_or_null($normalized_real_name);
		$set[] = '`normalized_reversed_name` = ' . q_string_or_null($normalized_reversed_name);
	}
	if (isset($result['fandom_name'])) {
		$fandom_name = $result['fandom_name'];
		$normalized_fandom_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $fandom_name));
		$set[] = '`fandom_name` = ' . q_string_or_null($fandom_name);
		$set[] = '`normalized_fandom_name` = ' . q_string_or_null($normalized_fandom_name);
	}
	if (isset($result['email_address'])) {
		$email = $result['email_address'];
		$normalized_email = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $email));
		$set[] = '`email_address` = ' . q_string_or_null($email);
		$set[] = '`normalized_email_address` = ' . q_string_or_null($normalized_email);
	}
	if (isset($result['phone_number'])) {
		$phone_number = $result['phone_number'];
		$normalized_phone_number = preg_replace('/[^0-9]+/', '', $phone_number);
		$set[] = '`phone_number` = ' . q_string_or_null($phone_number);
		$set[] = '`normalized_phone_number` = ' . q_string_or_null($normalized_phone_number);
	}
	return implode(', ', $set);
}

function decode_attendee($result, $badge_names) {
	global $event_date_start;
	$reg_url = get_base_url() . 'registration';
	$id = (int)$result['id'];
	$id_string = 'A'.$id;
	$first_name = unpurify_string($result['first_name']);
	$last_name = unpurify_string($result['last_name']);
	$real_name = trim(trim($first_name) . ' ' . trim($last_name));
	$fandom_name = unpurify_string($result['fandom_name']);
	$name_on_badge = $result['name_on_badge'];
	switch ($name_on_badge) {
		case 'FandomReal':
			$name_on_badge_string = 'Fandom Name Large, Real Name Small';
			$only_name = '';
			$large_name = $fandom_name;
			$small_name = $real_name;
			$display_name = trim($fandom_name) . ' (' . trim($real_name) . ')';
			break;
		case 'RealFandom':
			$name_on_badge_string = 'Real Name Large, Fandom Name Small';
			$only_name = '';
			$large_name = $real_name;
			$small_name = $fandom_name;
			$display_name = trim($real_name) . ' (' . trim($fandom_name) . ')';
			break;
		case 'FandomOnly':
			$name_on_badge_string = 'Fandom Name Only';
			$only_name = $fandom_name;
			$large_name = '';
			$small_name = '';
			$display_name = $fandom_name;
			break;
		case 'RealOnly':
			$name_on_badge_string = 'Real Name Only';
			$only_name = $real_name;
			$large_name = '';
			$small_name = '';
			$display_name = $real_name;
			break;
	}
	$date_of_birth = $result['date_of_birth'];
	$date1 = new DateTime($event_date_start);
	$date2 = new DateTime($date_of_birth);
	$interval = $date1->diff($date2);
	$age = $interval->y;
	$badge_id = (int)$result['badge_id'];
	$badge_id_string = 'AB'.$badge_id;
	$badge_name = (isset($badge_names[$badge_id]) ? $badge_names[$badge_id] : 'Unknown');
	$do_not_spam = !!$result['do_not_spam'];
	$on_mailing_list = !$do_not_spam;
	$email_address = unpurify_string($result['email_address']);
	$unsubscribe_link = $on_mailing_list ? ($reg_url.'/unsubscribe.php?email='.$email_address) : null;
	$phone_number = unpurify_string($result['phone_number']);
	$address_1 = unpurify_string($result['address_1']);
	$address_2 = unpurify_string($result['address_2']);
	$address = trim(trim($address_1) . "\n" . trim($address_2));
	$city = unpurify_string($result['city']);
	$state = unpurify_string($result['state']);
	$zip_code = unpurify_string($result['zip_code']);
	$csz = trim(trim(trim($city) . ' ' . trim($state)) . ' ' . trim($zip_code));
	$country = unpurify_string($result['country']);
	$address_full = trim(trim(trim($address) . "\n" . trim($csz)) . "\n" . trim($country));
	$ice_name = unpurify_string($result['ice_name']);
	$ice_relationship = unpurify_string($result['ice_relationship']);
	$ice_email_address = unpurify_string($result['ice_email_address']);
	$ice_phone_number = unpurify_string($result['ice_phone_number']);
	$payment_status = $result['payment_status'];
	$payment_status_string = payment_status_string($payment_status);
	$payment_status_html = payment_status_html($payment_status);
	$payment_type = unpurify_string($result['payment_type']);
	$payment_txn_id = unpurify_string($result['payment_txn_id']);
	$payment_original_price = (float)$result['payment_original_price'];
	$payment_original_price_string = price_string($payment_original_price);
	$payment_promo_code = unpurify_string($result['payment_promo_code']);
	$payment_final_price = (float)$result['payment_final_price'];
	$payment_final_price_string = price_string($payment_final_price);
	$payment_total_price = (float)$result['payment_total_price'];
	$payment_total_price_string = price_string($payment_total_price);
	$payment_date = $result['payment_date'];
	$payment_details = unpurify_string($result['payment_details']);
	$payment_lookup_key = unpurify_string($result['payment_lookup_key']);
	$order_url = ($payment_txn_id && $payment_lookup_key) ? ($reg_url.'/order.php?txn='.$payment_txn_id.'&key='.$payment_lookup_key) : null;
	$search_content = strtolower(implode('||', array(
		$id,
		$real_name,
		$fandom_name,
		$date_of_birth,
		$badge_name,
		$email_address,
		$phone_number,
		$address_full,
		$payment_status_string,
		$payment_txn_id,
		$payment_promo_code,
		$payment_lookup_key,
	)));
	$print_count = (int)$result['print_count'];
	$print_time = $result['print_time'];
	$checkin_count = (int)$result['checkin_count'];
	$checkin_time = $result['checkin_time'];
	$date_created = $result['date_created'];
	$date_modified = $result['date_modified'];
	return array(
		'id' => $id,
		'id_string' => $id_string,
		'first_name' => $first_name,
		'last_name' => $last_name,
		'real_name' => $real_name,
		'fandom_name' => $fandom_name,
		'name_on_badge' => $name_on_badge,
		'name_on_badge_string' => $name_on_badge_string,
		'only_name' => $only_name,
		'large_name' => $large_name,
		'small_name' => $small_name,
		'display_name' => $display_name,
		'date_of_birth' => $date_of_birth,
		'age' => $age,
		'badge_id' => $badge_id,
		'badge_id_string' => $badge_id_string,
		'badge_name' => $badge_name,
		'do_not_spam' => $do_not_spam,
		'on_mailing_list' => $on_mailing_list,
		'email_address' => $email_address,
		'unsubscribe_link' => $unsubscribe_link,
		'phone_number' => $phone_number,
		'address_1' => $address_1,
		'address_2' => $address_2,
		'address' => $address,
		'city' => $city,
		'state' => $state,
		'zip_code' => $zip_code,
		'csz' => $csz,
		'country' => $country,
		'address_full' => $address_full,
		'ice_name' => $ice_name,
		'ice_relationship' => $ice_relationship,
		'ice_email_address' => $ice_email_address,
		'ice_phone_number' => $ice_phone_number,
		'payment_status' => $payment_status,
		'payment_status_string' => $payment_status_string,
		'payment_status_html' => $payment_status_html,
		'payment_type' => $payment_type,
		'payment_txn_id' => $payment_txn_id,
		'payment_original_price' => $payment_original_price,
		'payment_original_price_string' => $payment_original_price_string,
		'payment_promo_code' => $payment_promo_code,
		'payment_final_price' => $payment_final_price,
		'payment_final_price_string' => $payment_final_price_string,
		'payment_total_price' => $payment_total_price,
		'payment_total_price_string' => $payment_total_price_string,
		'payment_date' => $payment_date,
		'payment_details' => $payment_details,
		'payment_lookup_key' => $payment_lookup_key,
		'order_url' => $order_url,
		'search_content' => $search_content,
		'print_count' => $print_count,
		'print_time' => $print_time,
		'checkin_count' => $checkin_count,
		'checkin_time' => $checkin_time,
		'date_created' => $date_created,
		'date_modified' => $date_modified,
	);
}

function encode_attendee($result) {
	$set = array();
	if (isset($result['first_name'            ])) $set[] = '`first_name` = '             . q_string        ($result['first_name'            ]);
	if (isset($result['last_name'             ])) $set[] = '`last_name` = '              . q_string        ($result['last_name'             ]);
	if (isset($result['fandom_name'           ])) $set[] = '`fandom_name` = '            . q_string_or_null($result['fandom_name'           ]);
	if (isset($result['name_on_badge'         ])) $set[] = '`name_on_badge` = '          . q_string        ($result['name_on_badge'         ]);
	if (isset($result['date_of_birth'         ])) $set[] = '`date_of_birth` = '          . q_date          ($result['date_of_birth'         ]);
	if (isset($result['badge_id'              ])) $set[] = '`badge_id` = '               . q_int           ($result['badge_id'              ]);
	if (isset($result['do_not_spam'           ])) $set[] = '`do_not_spam` = '            . q_boolean       ($result['do_not_spam'           ]);
	else if (isset($result['on_mailing_list'  ])) $set[] = '`do_not_spam` = '            . q_boolean       (!$result['on_mailing_list'      ]);
	if (isset($result['email_address'         ])) $set[] = '`email_address` = '          . q_string        ($result['email_address'         ]);
	if (isset($result['phone_number'          ])) $set[] = '`phone_number` = '           . q_string_or_null($result['phone_number'          ]);
	if (isset($result['address_1'             ])) $set[] = '`address_1` = '              . q_string_or_null($result['address_1'             ]);
	if (isset($result['address_2'             ])) $set[] = '`address_2` = '              . q_string_or_null($result['address_2'             ]);
	if (isset($result['city'                  ])) $set[] = '`city` = '                   . q_string_or_null($result['city'                  ]);
	if (isset($result['state'                 ])) $set[] = '`state` = '                  . q_string_or_null($result['state'                 ]);
	if (isset($result['zip_code'              ])) $set[] = '`zip_code` = '               . q_string_or_null($result['zip_code'              ]);
	if (isset($result['country'               ])) $set[] = '`country` = '                . q_string_or_null($result['country'               ]);
	if (isset($result['ice_name'              ])) $set[] = '`ice_name` = '               . q_string_or_null($result['ice_name'              ]);
	if (isset($result['ice_relationship'      ])) $set[] = '`ice_relationship` = '       . q_string_or_null($result['ice_relationship'      ]);
	if (isset($result['ice_email_address'     ])) $set[] = '`ice_email_address` = '      . q_string_or_null($result['ice_email_address'     ]);
	if (isset($result['ice_phone_number'      ])) $set[] = '`ice_phone_number` = '       . q_string_or_null($result['ice_phone_number'      ]);
	if (isset($result['payment_status'        ])) $set[] = '`payment_status` = '         . q_string        ($result['payment_status'        ]);
	if (isset($result['payment_type'          ])) $set[] = '`payment_type` = '           . q_string_or_null($result['payment_type'          ]);
	if (isset($result['payment_txn_id'        ])) $set[] = '`payment_txn_id` = '         . q_string_or_null($result['payment_txn_id'        ]);
	if (isset($result['payment_original_price'])) $set[] = '`payment_original_price` = ' . q_float_or_null ($result['payment_original_price']);
	if (isset($result['payment_promo_code'    ])) $set[] = '`payment_promo_code` = '     . q_string_or_null($result['payment_promo_code'    ]);
	if (isset($result['payment_final_price'   ])) $set[] = '`payment_final_price` = '    . q_float_or_null ($result['payment_final_price'   ]);
	if (isset($result['payment_total_price'   ])) $set[] = '`payment_total_price` = '    . q_float_or_null ($result['payment_total_price'   ]);
	if (isset($result['payment_details'       ])) $set[] = '`payment_details` = '        . q_string_or_null($result['payment_details'       ]);
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
	if (isset($result['print']) && $result['print']) {
		if ($result['print'] == 'RESET') {
			$set[] = '`print_count` = 0';
			$set[] = '`print_time` = NULL';
		} else {
			$set[] = '`print_count` = IFNULL(`print_count`, 0) + 1';
			$set[] = '`print_time` = NOW()';
		}
	}
	if (isset($result['checkin']) && $result['checkin']) {
		if ($result['checkin'] == 'RESET') {
			$set[] = '`checkin_count` = 0';
			$set[] = '`checkin_time` = NULL';
		} else {
			$set[] = '`checkin_count` = IFNULL(`checkin_count`, 0) + 1';
			$set[] = '`checkin_time` = NOW()';
		}
	}
	$set[] = '`date_modified` = NOW()';
	return implode(', ', $set);
}

function get_attendee_badge_names($connection) {
	db_require_table('attendee_badges', $connection);
	$badge_names = array();
	$results = mysql_query('SELECT * FROM '.db_table_name('attendee_badges').' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$name = unpurify_string($result['name']);
		$badge_names[$id] = $name;
	}
	return $badge_names;
}

function get_valid_attendee_badges($connection) {
	global $event_date_start, $event_date_end;
	db_require_table('attendee_badges', $connection);
	db_require_table('attendees', $connection);
	$bb = db_table_name('attendee_badges');
	$b = db_table_name('attendees');
	$badge_info = array();
	$badge_query = (
		'SELECT * FROM '.$bb.
		' WHERE '.$bb.'.`active`'.
		' AND ('.$bb.'.`start_date` IS NULL OR '.$bb.'.`start_date` <= CURDATE())'.
		' AND ('.$bb.'.`end_date` IS NULL OR '.$bb.'.`end_date` >= CURDATE())'.
		' AND ('.$bb.'.`count` IS NULL OR '.$bb.'.`count` > ('.
		'  SELECT COUNT(*) FROM '.$b.
		'  WHERE '.$b.'.`badge_id` = '.$bb.'.`id`'.
		'  AND '.$b.'.`payment_status` = \'Completed\''.
		' ))'.
		' ORDER BY `order`'
	);
	$results = mysql_query($badge_query, $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$badge_info[$id] = decode_attendee_badge($result);
	}
	return $badge_info;
}

function get_valid_attendee_badges_plus_other_crap($connection) {
	global $event_date_start, $event_date_end;
	db_require_table('attendee_badges', $connection);
	$bb = db_table_name('attendee_badges');
	$badge_info = array();
	$badge_query = (
		'SELECT * FROM '.$bb.
		' WHERE '.$bb.'.`active`'.
		' AND ('.$bb.'.`start_date` IS NULL OR '.$bb.'.`start_date` <= CURDATE())'.
		' AND ('.$bb.'.`end_date` IS NULL OR '.$bb.'.`end_date` >= CURDATE())'.
		' ORDER BY `order`'
	);
	$results = mysql_query($badge_query, $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$badge_info[$id] = decode_attendee_badge($result);
	}
	return $badge_info;
}

function get_purchased_attendee_badge_count($badge_id, $connection) {
	db_require_table('attendees', $connection);
	$results = mysql_query(
		('SELECT COUNT(*)'.
		' FROM '.db_table_name('attendees').
		' WHERE `badge_id` = '.(int)$badge_id.
		' AND `payment_status` = \'Completed\''),
		$connection
	);
	$result = mysql_fetch_assoc($results);
	return (int)$result['COUNT(*)'];
}

function get_promo_code_use_count($code, $connection) {
	db_require_table('attendees', $connection);
	$results = mysql_query(
		('SELECT COUNT(*)'.
		' FROM '.db_table_name('attendees').
		' WHERE `payment_promo_code` = '.q_string($code).
		' AND `payment_status` = \'Completed\''),
		$connection
	);
	$result = mysql_fetch_assoc($results);
	return (int)$result['COUNT(*)'];
}

function attendee_is_blacklisted($result, $connection) {
	db_require_table('attendee_blacklist', $connection);
	$first_name = (isset($result['first_name']) ? $result['first_name'] : '');
	$last_name = (isset($result['last_name']) ? $result['last_name'] : '');
	$fandom_name = (isset($result['fandom_name']) ? $result['fandom_name'] : '');
	$email = (isset($result['email_address']) ? $result['email_address'] : '');
	$phone_number = (isset($result['phone_number']) ? $result['phone_number'] : '');
	$normalized_real_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $first_name . $last_name));
	$normalized_reversed_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $last_name . $first_name));
	$normalized_fandom_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $fandom_name));
	$normalized_email = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $email));
	$normalized_phone_number = preg_replace('/[^0-9]+/', '', $phone_number);
	$q = array();
	if ($normalized_real_name) {
		$q[] = '`normalized_real_name` = '.q_string($normalized_real_name);
		$q[] = '`normalized_reversed_name` = '.q_string($normalized_real_name);
		$q[] = '`normalized_fandom_name` = '.q_string($normalized_real_name);
	}
	if ($normalized_reversed_name) {
		$q[] = '`normalized_real_name` = '.q_string($normalized_reversed_name);
		$q[] = '`normalized_reversed_name` = '.q_string($normalized_reversed_name);
		$q[] = '`normalized_fandom_name` = '.q_string($normalized_reversed_name);
	}
	if ($normalized_fandom_name) {
		$q[] = '`normalized_real_name` = '.q_string($normalized_fandom_name);
		$q[] = '`normalized_reversed_name` = '.q_string($normalized_fandom_name);
		$q[] = '`normalized_fandom_name` = '.q_string($normalized_fandom_name);
	}
	if ($normalized_email) {
		$q[] = '`normalized_email_address` = '.q_string($normalized_email);
	}
	if ($normalized_phone_number) {
		$q[] = '`normalized_phone_number` = '.q_string($normalized_phone_number);
	}
	$q = implode(' OR ', $q);
	if ($q) {
		$q = 'SELECT COUNT(*) FROM '.db_table_name('attendee_blacklist').' WHERE '.$q;
		$r = mysql_query($q, $connection);
		$r = mysql_fetch_assoc($r);
		$r = !!$r['COUNT(*)'];
		return $r;
	} else {
		return false;
	}
}

function attendee_display_name($result) {
	$first_name = $result['first_name'];
	$last_name = $result['last_name'];
	$real_name = trim(trim($first_name) . ' ' . trim($last_name));
	$fandom_name = $result['fandom_name'];
	$name_on_badge = $result['name_on_badge'];
	switch ($name_on_badge) {
		case 'FandomReal':
			$display_name = trim($fandom_name) . ' (' . trim($real_name) . ')';
			break;
		case 'RealFandom':
			$display_name = trim($real_name) . ' (' . trim($fandom_name) . ')';
			break;
		case 'FandomOnly':
			$display_name = $fandom_name;
			break;
		case 'RealOnly':
			$display_name = $real_name;
			break;
	}
	return $display_name;
}