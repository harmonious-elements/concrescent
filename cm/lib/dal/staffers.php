<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/dal.php';
require_once dirname(__FILE__).'/questions.php';
require_once dirname(__FILE__).'/../schema/staffers.php';
require_once dirname(__FILE__).'/../base/sql.php';
require_once dirname(__FILE__).'/../base/util.php';
require_once dirname(__FILE__).'/../cmbase/util.php';

function decode_staffer_badge($result) {
	global $event_date_start, $event_date_end;
	$id = (int)$result['id'];
	$id_string = 'SB'.$id;
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

function encode_staffer_badge($result) {
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

function decode_staffer($result, $badge_names) {
	global $event_date_start;
	$id = (int)$result['id'];
	$id_string = 'S'.$id;
	$replaced_by = (int)$result['replaced_by'];
	$first_name = unpurify_string($result['first_name']);
	$last_name = unpurify_string($result['last_name']);
	$real_name = trim(trim($first_name) . ' ' . trim($last_name));
	$fandom_name = unpurify_string($result['fandom_name']);
	$name_on_badge = trim($fandom_name) ? 'RealFandom' : 'RealOnly';
	switch ($name_on_badge) {
		case 'RealFandom':
			$name_on_badge_string = 'Real Name Large, Fandom Name Small';
			$only_name = '';
			$large_name = $real_name;
			$small_name = $fandom_name;
			$display_name = trim($real_name) . ' (' . trim($fandom_name) . ')';
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
	$badge_id_string = 'SB'.$badge_id;
	$badge_name = (isset($badge_names[$badge_id]) ? $badge_names[$badge_id] : 'Unknown');
	$email_address = unpurify_string($result['email_address']);
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
	$dates_available = explode(',', unpurify_string($result['dates_available']));
	$application_status = $result['application_status'];
	$application_status_string = application_status_string($application_status);
	$application_status_html = application_status_html($application_status);
	$assigned_position = unpurify_string($result['assigned_position']);
	$notes = unpurify_string($result['notes']);
	$ice_name = unpurify_string($result['ice_name']);
	$ice_relationship = unpurify_string($result['ice_relationship']);
	$ice_email_address = unpurify_string($result['ice_email_address']);
	$ice_phone_number = unpurify_string($result['ice_phone_number']);
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
	$order_url = get_base_url() . 'apply-staff/order.php?';
	$confirm_payment_url = $payment_lookup_key ? ($order_url.'id='.$id.'&key='.$payment_lookup_key) : null;
	$review_order_url = ($payment_txn_id && $payment_lookup_key) ? ($order_url.'txn='.$payment_txn_id.'&key='.$payment_lookup_key) : null;
	$order_url = $review_order_url ? $review_order_url : $confirm_payment_url;
	$search_content = strtolower(implode('||', array(
		$id,
		$real_name,
		$fandom_name,
		$date_of_birth,
		$badge_name,
		$email_address,
		$phone_number,
		$address_full,
		$application_status_string,
		$assigned_position,
		$notes,
		$payment_status_string,
		$payment_txn_id,
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
		'replaced_by' => $replaced_by,
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
		'email_address' => $email_address,
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
		'dates_available' => $dates_available,
		'application_status' => $application_status,
		'application_status_string' => $application_status_string,
		'application_status_html' => $application_status_html,
		'assigned_position' => $assigned_position,
		'notes' => $notes,
		'ice_name' => $ice_name,
		'ice_relationship' => $ice_relationship,
		'ice_email_address' => $ice_email_address,
		'ice_phone_number' => $ice_phone_number,
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
		'print_count' => $print_count,
		'print_time' => $print_time,
		'checkin_count' => $checkin_count,
		'checkin_time' => $checkin_time,
		'date_created' => $date_created,
		'date_modified' => $date_modified,
	);
}

function encode_staffer_array($result) {
	$set = array();
	if (isset($result['replaced_by'           ])) $set[] = '`replaced_by` = '            . q_int_or_null   ($result['replaced_by'           ]);
	if (isset($result['first_name'            ])) $set[] = '`first_name` = '             . q_string        ($result['first_name'            ]);
	if (isset($result['last_name'             ])) $set[] = '`last_name` = '              . q_string        ($result['last_name'             ]);
	if (isset($result['fandom_name'           ])) $set[] = '`fandom_name` = '            . q_string_or_null($result['fandom_name'           ]);
	if (isset($result['date_of_birth'         ])) $set[] = '`date_of_birth` = '          . q_date          ($result['date_of_birth'         ]);
	if (isset($result['badge_id'              ])) $set[] = '`badge_id` = '               . q_int           ($result['badge_id'              ]);
	if (isset($result['email_address'         ])) $set[] = '`email_address` = '          . q_string        ($result['email_address'         ]);
	if (isset($result['phone_number'          ])) $set[] = '`phone_number` = '           . q_string_or_null($result['phone_number'          ]);
	if (isset($result['address_1'             ])) $set[] = '`address_1` = '              . q_string_or_null($result['address_1'             ]);
	if (isset($result['address_2'             ])) $set[] = '`address_2` = '              . q_string_or_null($result['address_2'             ]);
	if (isset($result['city'                  ])) $set[] = '`city` = '                   . q_string_or_null($result['city'                  ]);
	if (isset($result['state'                 ])) $set[] = '`state` = '                  . q_string_or_null($result['state'                 ]);
	if (isset($result['zip_code'              ])) $set[] = '`zip_code` = '               . q_string_or_null($result['zip_code'              ]);
	if (isset($result['country'               ])) $set[] = '`country` = '                . q_string_or_null($result['country'               ]);
	if (isset($result['application_status'    ])) $set[] = '`application_status` = '     . q_string        ($result['application_status'    ]);
	if (isset($result['assigned_position'     ])) $set[] = '`assigned_position` = '      . q_string_or_null($result['assigned_position'     ]);
	if (isset($result['notes'                 ])) $set[] = '`notes` = '                  . q_string_or_null($result['notes'                 ]);
	if (isset($result['ice_name'              ])) $set[] = '`ice_name` = '               . q_string_or_null($result['ice_name'              ]);
	if (isset($result['ice_relationship'      ])) $set[] = '`ice_relationship` = '       . q_string_or_null($result['ice_relationship'      ]);
	if (isset($result['ice_email_address'     ])) $set[] = '`ice_email_address` = '      . q_string_or_null($result['ice_email_address'     ]);
	if (isset($result['ice_phone_number'      ])) $set[] = '`ice_phone_number` = '       . q_string_or_null($result['ice_phone_number'      ]);
	if (isset($result['payment_status'        ])) $set[] = '`payment_status` = '         . q_string        ($result['payment_status'        ]);
	if (isset($result['payment_type'          ])) $set[] = '`payment_type` = '           . q_string_or_null($result['payment_type'          ]);
	if (isset($result['payment_txn_id'        ])) $set[] = '`payment_txn_id` = '         . q_string_or_null($result['payment_txn_id'        ]);
	if (isset($result['payment_price'         ])) $set[] = '`payment_price` = '          . q_float_or_null ($result['payment_price'         ]);
	if (isset($result['payment_details'       ])) $set[] = '`payment_details` = '        . q_string_or_null($result['payment_details'       ]);
	if (isset($result['dates_available'])) {
		$dates_available = implode(',', $result['dates_available']);
		$set[] = '`dates_available` = ' . q_string($dates_available);
	}
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
	return $set;
}

function encode_staffer($result) {
	$set = encode_staffer_array($result);
	$set[] = '`date_modified` = NOW()';
	return implode(', ', $set);
}

function encode_staffer_where($result) {
	$set = encode_staffer_array($result);
	return implode(' AND ', $set);
}

function get_staffer_badge_names($connection) {
	db_require_table('staffer_badges', $connection);
	$badge_names = array();
	$results = mysql_query('SELECT * FROM '.db_table_name('staffer_badges').' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$name = unpurify_string($result['name']);
		$badge_names[$id] = $name;
	}
	return $badge_names;
}

function get_staffer_info($connection, $badge_names) {
	db_require_table('staffers', $connection);
	$staffer_info = array();
	$results = mysql_query('SELECT * FROM '.db_table_name('staffers').' ORDER BY `id`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$staffer_info[(int)$result['id']] = decode_staffer($result, $badge_names);
	}
	return $staffer_info;
}

function get_valid_staffer_badges($connection) {
	global $event_date_start, $event_date_end;
	db_require_table('staffer_badges', $connection);
	db_require_table('staffers', $connection);
	$bb = db_table_name('staffer_badges');
	$b = db_table_name('staffers');
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
		$badge_info[$id] = decode_staffer_badge($result);
	}
	return $badge_info;
}

function get_purchased_staffer_badge_count($badge_id, $connection) {
	db_require_table('staffers', $connection);
	$results = mysql_query(
		('SELECT COUNT(*)'.
		' FROM '.db_table_name('staffers').
		' WHERE `badge_id` = '.(int)$badge_id.
		' AND `payment_status` = \'Completed\''),
		$connection
	);
	$result = mysql_fetch_assoc($results);
	return (int)$result['COUNT(*)'];
}