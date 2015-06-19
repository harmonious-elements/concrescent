<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/dal.php';
require_once dirname(__FILE__).'/questions.php';
require_once dirname(__FILE__).'/../schema/eventlets.php';
require_once dirname(__FILE__).'/../base/sql.php';
require_once dirname(__FILE__).'/../base/util.php';
require_once dirname(__FILE__).'/../cmbase/util.php';

function decode_eventlet_badge($result) {
	$id = (int)$result['id'];
	$id_string = 'EB'.$id;
	$name = unpurify_string($result['name']);
	$description = unpurify_string($result['description']);
	$description_html = safe_html_string($description);
	$start_date = $result['start_date'];
	$end_date = $result['end_date'];
	$count = (int)$result['count'];
	$active = !!$result['active'];
	$max_staffers = (int)$result['max_staffers'];
	$price_per_eventlet = (float)$result['price_per_eventlet'];
	$price_per_eventlet_string = price_string($price_per_eventlet);
	$price_per_staffer = (float)$result['price_per_staffer'];
	$price_per_staffer_string = price_string($price_per_staffer);
	$staffers_in_eventlet_price = (int)$result['staffers_in_eventlet_price'];
	$max_prereg_discount = $result['max_prereg_discount'];
	switch ($max_prereg_discount) {
		case 'None':
			$max_prereg_discount_string = 'No Discount';
			break;
		case 'StafferPrice':
			$max_prereg_discount_string = 'Price of Badge';
			break;
		case 'EventletPrice':
			$max_prereg_discount_string = 'Price of Panel/Activity';
			break;
		case 'TotalPrice':
			$max_prereg_discount_string = 'Total Payment Amount';
			break;
	}
	$order = (int)$result['order'];
	return array(
		'id' => $id,
		'id_string' => $id_string,
		'name' => $name,
		'description' => $description,
		'description_html' => $description_html,
		'start_date' => $start_date,
		'end_date' => $end_date,
		'count' => $count,
		'active' => $active,
		'max_staffers' => $max_staffers,
		'price_per_eventlet' => $price_per_eventlet,
		'price_per_eventlet_string' => $price_per_eventlet_string,
		'price_per_staffer' => $price_per_staffer,
		'price_per_staffer_string' => $price_per_staffer_string,
		'staffers_in_eventlet_price' => $staffers_in_eventlet_price,
		'max_prereg_discount' => $max_prereg_discount,
		'max_prereg_discount_string' => $max_prereg_discount_string,
		'order' => $order,
	);
}

function encode_eventlet_badge($result) {
	$set = array();
	if (isset($result['name'                      ])) $set[] = '`name` = '                       . q_string        ($result['name'                      ]);
	if (isset($result['description'               ])) $set[] = '`description` = '                . q_string_or_null($result['description'               ]);
	if (isset($result['start_date'                ])) $set[] = '`start_date` = '                 . q_date_or_null  ($result['start_date'                ]);
	if (isset($result['end_date'                  ])) $set[] = '`end_date` = '                   . q_date_or_null  ($result['end_date'                  ]);
	if (isset($result['count'                     ])) $set[] = '`count` = '                      . q_int_or_null   ($result['count'                     ]);
	if (isset($result['active'                    ])) $set[] = '`active` = '                     . q_boolean       ($result['active'                    ]);
	if (isset($result['max_staffers'              ])) $set[] = '`max_staffers` = '               . q_int_or_null   ($result['max_staffers'              ]);
	if (isset($result['price_per_eventlet'        ])) $set[] = '`price_per_eventlet` = '         . q_float         ($result['price_per_eventlet'        ]);
	if (isset($result['price_per_staffer'         ])) $set[] = '`price_per_staffer` = '          . q_float         ($result['price_per_staffer'         ]);
	if (isset($result['staffers_in_eventlet_price'])) $set[] = '`staffers_in_eventlet_price` = ' . q_int           ($result['staffers_in_eventlet_price']);
	if (isset($result['max_prereg_discount'       ])) $set[] = '`max_prereg_discount` = '        . q_string        ($result['max_prereg_discount'       ]);
	return implode(', ', $set);
}

function decode_eventlet($result, $badge_names) {
	$id = (int)$result['id'];
	$id_string = 'EA'.$id;
	$replaced_by = (int)$result['replaced_by'];
	$contact_first_name = unpurify_string($result['contact_first_name']);
	$contact_last_name = unpurify_string($result['contact_last_name']);
	$contact_real_name = trim(trim($contact_first_name) . ' ' . trim($contact_last_name));
	$contact_email_address = unpurify_string($result['contact_email_address']);
	$contact_phone_number = unpurify_string($result['contact_phone_number']);
	$badge_id = (int)$result['badge_id'];
	$badge_id_string = 'EB'.$badge_id;
	$badge_name = (isset($badge_names[$badge_id]) ? $badge_names[$badge_id] : 'Unknown');
	$eventlet_name = unpurify_string($result['eventlet_name']);
	$eventlet_description = unpurify_string($result['eventlet_description']);
	$num_staffers = (int)$result['num_staffers'];
	$application_status = $result['application_status'];
	$application_status_string = application_status_string($application_status);
	$application_status_html = application_status_html($application_status);
	$payment_status = $result['payment_status'];
	$payment_status_string = payment_status_string($payment_status);
	$payment_status_html = payment_status_html($payment_status);
	$payment_type = unpurify_string($result['payment_type']);
	$payment_txn_id = unpurify_string($result['payment_txn_id']);
	$payment_original_price = (float)$result['payment_original_price'];
	$payment_original_price_string = price_string($payment_original_price);
	$payment_final_price = (float)$result['payment_final_price'];
	$payment_final_price_string = price_string($payment_final_price);
	$payment_date = $result['payment_date'];
	$payment_details = unpurify_string($result['payment_details']);
	$payment_lookup_key = unpurify_string($result['payment_lookup_key']);
	$order_url = get_base_url() . 'apply-event/order.php?';
	$confirm_payment_url = $payment_lookup_key ? ($order_url.'id='.$id.'&key='.$payment_lookup_key) : null;
	$review_order_url = ($payment_txn_id && $payment_lookup_key) ? ($order_url.'txn='.$payment_txn_id.'&key='.$payment_lookup_key) : null;
	$order_url = $review_order_url ? $review_order_url : $confirm_payment_url;
	$search_content = strtolower(implode('||', array(
		$id,
		$contact_real_name,
		$contact_email_address,
		$contact_phone_number,
		$badge_name,
		$eventlet_name,
		$eventlet_description,
		$application_status_string,
		$payment_status_string,
		$payment_txn_id,
		$payment_lookup_key,
	)));
	$date_created = $result['date_created'];
	$date_modified = $result['date_modified'];
	return array(
		'id' => $id,
		'id_string' => $id_string,
		'replaced_by' => $replaced_by,
		'contact_first_name' => $contact_first_name,
		'contact_last_name' => $contact_last_name,
		'contact_real_name' => $contact_real_name,
		'contact_email_address' => $contact_email_address,
		'contact_phone_number' => $contact_phone_number,
		'badge_id' => $badge_id,
		'badge_id_string' => $badge_id_string,
		'badge_name' => $badge_name,
		'eventlet_name' => $eventlet_name,
		'eventlet_description' => $eventlet_description,
		'num_staffers' => $num_staffers,
		'application_status' => $application_status,
		'application_status_string' => $application_status_string,
		'application_status_html' => $application_status_html,
		'payment_status' => $payment_status,
		'payment_status_string' => $payment_status_string,
		'payment_status_html' => $payment_status_html,
		'payment_type' => $payment_type,
		'payment_txn_id' => $payment_txn_id,
		'payment_original_price' => $payment_original_price,
		'payment_original_price_string' => $payment_original_price_string,
		'payment_final_price' => $payment_final_price,
		'payment_final_price_string' => $payment_final_price_string,
		'payment_date' => $payment_date,
		'payment_details' => $payment_details,
		'payment_lookup_key' => $payment_lookup_key,
		'confirm_payment_url' => $confirm_payment_url,
		'review_order_url' => $review_order_url,
		'order_url' => $order_url,
		'search_content' => $search_content,
		'date_created' => $date_created,
		'date_modified' => $date_modified,
	);
}

function encode_eventlet($result) {
	$set = array();
	if (isset($result['replaced_by'           ])) $set[] = '`replaced_by` = '            . q_int_or_null   ($result['replaced_by'           ]);
	if (isset($result['contact_first_name'    ])) $set[] = '`contact_first_name` = '     . q_string        ($result['contact_first_name'    ]);
	if (isset($result['contact_last_name'     ])) $set[] = '`contact_last_name` = '      . q_string        ($result['contact_last_name'     ]);
	if (isset($result['contact_email_address' ])) $set[] = '`contact_email_address` = '  . q_string        ($result['contact_email_address' ]);
	if (isset($result['contact_phone_number'  ])) $set[] = '`contact_phone_number` = '   . q_string_or_null($result['contact_phone_number'  ]);
	if (isset($result['badge_id'              ])) $set[] = '`badge_id` = '               . q_int           ($result['badge_id'              ]);
	if (isset($result['eventlet_name'         ])) $set[] = '`eventlet_name` = '          . q_string        ($result['eventlet_name'         ]);
	if (isset($result['eventlet_description'  ])) $set[] = '`eventlet_description` = '   . q_string        ($result['eventlet_description'  ]);
	if (isset($result['num_staffers'          ])) $set[] = '`num_staffers` = '           . q_int           ($result['num_staffers'          ]);
	if (isset($result['application_status'    ])) $set[] = '`application_status` = '     . q_string        ($result['application_status'    ]);
	if (isset($result['payment_status'        ])) $set[] = '`payment_status` = '         . q_string        ($result['payment_status'        ]);
	if (isset($result['payment_type'          ])) $set[] = '`payment_type` = '           . q_string_or_null($result['payment_type'          ]);
	if (isset($result['payment_txn_id'        ])) $set[] = '`payment_txn_id` = '         . q_string_or_null($result['payment_txn_id'        ]);
	if (isset($result['payment_original_price'])) $set[] = '`payment_original_price` = ' . q_float_or_null ($result['payment_original_price']);
	if (isset($result['payment_final_price'   ])) $set[] = '`payment_final_price` = '    . q_float_or_null ($result['payment_final_price'   ]);
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
	$set[] = '`date_modified` = NOW()';
	return implode(', ', $set);
}

function decode_eventlet_staffer($result, $eventlet_info) {
	global $event_date_start;
	$id = (int)$result['id'];
	$id_string = 'E'.$id;
	$eventlet_id = (int)$result['eventlet_id'];
	$eventlet_id_string = 'EA'.$eventlet_id;
	$eventlet_info = $eventlet_info[$eventlet_id];
	$eventlet_name = $eventlet_info['eventlet_name'];
	$eventlet_description = $eventlet_info['eventlet_description'];
	$badge_id = $eventlet_info['badge_id'];
	$badge_id_string = 'EB'.$badge_id;
	$badge_name = $eventlet_info['badge_name'];
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
	$email_address = unpurify_string($result['email_address']);
	$phone_number = unpurify_string($result['phone_number']);
	$attendee_id = (int)$result['attendee_id'];
	$attendee_id_string = ($attendee_id ? ('A'.$attendee_id) : '');
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
	$search_content = strtolower(implode('||', array(
		$id,
		$eventlet_id,
		$eventlet_name,
		$eventlet_description,
		$badge_id,
		$badge_name,
		$real_name,
		$fandom_name,
		$date_of_birth,
		$email_address,
		$phone_number,
		$address_full,
	)));
	$application_status = $eventlet_info['application_status'];
	$application_status_string = $eventlet_info['application_status_string'];
	$application_status_html = $eventlet_info['application_status_html'];
	$payment_status = $eventlet_info['payment_status'];
	$payment_status_string = $eventlet_info['payment_status_string'];
	$payment_status_html = $eventlet_info['payment_status_html'];
	$print_count = (int)$result['print_count'];
	$print_time = $result['print_time'];
	$checkin_count = (int)$result['checkin_count'];
	$checkin_time = $result['checkin_time'];
	$date_created = $result['date_created'];
	$date_modified = $result['date_modified'];
	return array(
		'id' => $id,
		'id_string' => $id_string,
		'eventlet_id' => $eventlet_id,
		'eventlet_id_string' => $eventlet_id_string,
		'eventlet_name' => $eventlet_name,
		'eventlet_description' => $eventlet_description,
		'badge_id' => $badge_id,
		'badge_id_string' => $badge_id_string,
		'badge_name' => $badge_name,
		'group_id' => $eventlet_id,
		'group_id_string' => $eventlet_id_string,
		'group_name' => $eventlet_name,
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
		'email_address' => $email_address,
		'phone_number' => $phone_number,
		'attendee_id' => $attendee_id,
		'attendee_id_string' => $attendee_id_string,
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
		'search_content' => $search_content,
		'application_status' => $application_status,
		'application_status_string' => $application_status_string,
		'application_status_html' => $application_status_html,
		'payment_status' => $payment_status,
		'payment_status_string' => $payment_status_string,
		'payment_status_html' => $payment_status_html,
		'print_count' => $print_count,
		'print_time' => $print_time,
		'checkin_count' => $checkin_count,
		'checkin_time' => $checkin_time,
		'date_created' => $date_created,
		'date_modified' => $date_modified,
	);
}

function encode_eventlet_staffer($result) {
	$set = array();
	if (isset($result['eventlet_id'           ])) $set[] = '`eventlet_id` = '            . q_int           ($result['eventlet_id'           ]);
	if (isset($result['first_name'            ])) $set[] = '`first_name` = '             . q_string        ($result['first_name'            ]);
	if (isset($result['last_name'             ])) $set[] = '`last_name` = '              . q_string        ($result['last_name'             ]);
	if (isset($result['fandom_name'           ])) $set[] = '`fandom_name` = '            . q_string_or_null($result['fandom_name'           ]);
	if (isset($result['name_on_badge'         ])) $set[] = '`name_on_badge` = '          . q_string        ($result['name_on_badge'         ]);
	if (isset($result['date_of_birth'         ])) $set[] = '`date_of_birth` = '          . q_date          ($result['date_of_birth'         ]);
	if (isset($result['email_address'         ])) $set[] = '`email_address` = '          . q_string        ($result['email_address'         ]);
	if (isset($result['phone_number'          ])) $set[] = '`phone_number` = '           . q_string_or_null($result['phone_number'          ]);
	if (isset($result['attendee_id'           ])) $set[] = '`attendee_id` = '            . q_int           ($result['attendee_id'           ]);
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

function get_eventlet_badge_names($connection) {
	db_require_table('eventlet_badges', $connection);
	$badge_names = array();
	$results = mysql_query('SELECT * FROM '.db_table_name('eventlet_badges').' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$name = unpurify_string($result['name']);
		$badge_names[$id] = $name;
	}
	return $badge_names;
}

function get_eventlet_info($connection, $badge_names) {
	db_require_table('eventlets', $connection);
	$eventlet_info = array();
	$results = mysql_query('SELECT * FROM '.db_table_name('eventlets').' ORDER BY `id`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$id_string = 'EA'.$id;
		$eventlet_name = unpurify_string($result['eventlet_name']);
		$eventlet_description = unpurify_string($result['eventlet_description']);
		$badge_id = (int)$result['badge_id'];
		$badge_id_string = 'EB'.$badge_id;
		$badge_name = (isset($badge_names[$badge_id]) ? $badge_names[$badge_id] : 'Unknown');
		$application_status = $result['application_status'];
		$application_status_string = application_status_string($application_status);
		$application_status_html = application_status_html($application_status);
		$payment_status = $result['payment_status'];
		$payment_status_string = payment_status_string($payment_status);
		$payment_status_html = payment_status_html($payment_status);
		$eventlet_info[$id] = array(
			'id' => $id,
			'id_string' => $id_string,
			'eventlet_name' => $eventlet_name,
			'eventlet_description' => $eventlet_description,
			'badge_id' => $badge_id,
			'badge_id_string' => $badge_id_string,
			'badge_name' => $badge_name,
			'application_status' => $application_status,
			'application_status_string' => $application_status_string,
			'application_status_html' => $application_status_html,
			'payment_status' => $payment_status,
			'payment_status_string' => $payment_status_string,
			'payment_status_html' => $payment_status_html,
		);
	}
	return $eventlet_info;
}

function get_valid_eventlet_badges($connection) {
	global $event_date_start, $event_date_end;
	db_require_table('eventlet_badges', $connection);
	db_require_table('eventlets', $connection);
	$bb = db_table_name('eventlet_badges');
	$b = db_table_name('eventlets');
	$badge_info = array();
	$badge_query = (
		'SELECT * FROM '.$bb.
		' WHERE '.$bb.'.`active`'.
		' AND ('.$bb.'.`start_date` IS NULL OR '.$bb.'.`start_date` <= CURDATE())'.
		' AND ('.$bb.'.`end_date` IS NULL OR '.$bb.'.`end_date` >= CURDATE())'.
		' AND ('.$bb.'.`count` IS NULL OR '.$bb.'.`count` > ('.
		'  SELECT COUNT(*) FROM '.$b.
		'  WHERE '.$b.'.`badge_id` = '.$bb.'.`id`'.
		'  AND '.$b.'.`application_status` = \'Accepted\''.
		' ))'.
		' ORDER BY `order`'
	);
	$results = mysql_query($badge_query, $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$badge_info[$id] = decode_eventlet_badge($result);
	}
	return $badge_info;
}

function get_accepted_eventlet_badge_count($badge_id, $connection) {
	db_require_table('eventlets', $connection);
	$results = mysql_query(
		('SELECT COUNT(*)'.
		' FROM '.db_table_name('eventlets').
		' WHERE `badge_id` = '.(int)$badge_id.
		' AND `application_status` = \'Accepted\''),
		$connection
	);
	$result = mysql_fetch_assoc($results);
	return (int)$result['COUNT(*)'];
}