<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/dal.php';
require_once dirname(__FILE__).'/questions.php';
require_once dirname(__FILE__).'/../schema/booths.php';
require_once dirname(__FILE__).'/../base/sql.php';
require_once dirname(__FILE__).'/../base/util.php';
require_once dirname(__FILE__).'/../cmbase/res.php';
require_once dirname(__FILE__).'/../cmbase/util.php';

function decode_booth_table($result) {
	$id = unpurify_string($result['id']);
	$x = (int)$result['x'] / 1000.0;
	$y = (int)$result['y'] / 1000.0;
	return array(
		'id' => $id,
		'x' => $x,
		'y' => $y,
	);
}

function encode_booth_table($result) {
	$set = array();
	if (isset($result['id'])) $set[] = '`id` = ' . q_string($result['id']       );
	if (isset($result['x' ])) $set[] = '`x` = '  . q_int   ($result['x' ] * 1000);
	if (isset($result['y' ])) $set[] = '`y` = '  . q_int   ($result['y' ] * 1000);
	return implode(', ', $set);
}

function decode_booth_badge($result) {
	$id = (int)$result['id'];
	$id_string = 'BB'.$id;
	$name = unpurify_string($result['name']);
	$description = unpurify_string($result['description']);
	$description_html = safe_html_string($description);
	$start_date = $result['start_date'];
	$end_date = $result['end_date'];
	$count = (int)$result['count'];
	$active = !!$result['active'];
	$max_tables = (int)$result['max_tables'];
	$max_staffers = (int)$result['max_staffers'];
	$price_per_table = (float)$result['price_per_table'];
	$price_per_table_string = price_string($price_per_table);
	$price_per_staffer = (float)$result['price_per_staffer'];
	$price_per_staffer_string = price_string($price_per_staffer);
	$staffers_in_table_price = (int)$result['staffers_in_table_price'];
	$max_prereg_discount = $result['max_prereg_discount'];
	switch ($max_prereg_discount) {
		case 'None':
			$max_prereg_discount_string = 'No Discount';
			break;
		case 'StafferPrice':
			$max_prereg_discount_string = 'Price of Badge';
			break;
		case 'TablePrice':
			$max_prereg_discount_string = 'Price of Table';
			break;
		case 'TotalPrice':
			$max_prereg_discount_string = 'Total Payment Amount';
			break;
	}
	$require_permit = !!$result['require_permit'];
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
		'max_tables' => $max_tables,
		'max_staffers' => $max_staffers,
		'price_per_table' => $price_per_table,
		'price_per_table_string' => $price_per_table_string,
		'price_per_staffer' => $price_per_staffer,
		'price_per_staffer_string' => $price_per_staffer_string,
		'staffers_in_table_price' => $staffers_in_table_price,
		'max_prereg_discount' => $max_prereg_discount,
		'max_prereg_discount_string' => $max_prereg_discount_string,
		'require_permit' => $require_permit,
		'order' => $order,
	);
}

function encode_booth_badge($result) {
	$set = array();
	if (isset($result['name'                   ])) $set[] = '`name` = '                    . q_string        ($result['name'                   ]);
	if (isset($result['description'            ])) $set[] = '`description` = '             . q_string_or_null($result['description'            ]);
	if (isset($result['start_date'             ])) $set[] = '`start_date` = '              . q_date_or_null  ($result['start_date'             ]);
	if (isset($result['end_date'               ])) $set[] = '`end_date` = '                . q_date_or_null  ($result['end_date'               ]);
	if (isset($result['count'                  ])) $set[] = '`count` = '                   . q_int_or_null   ($result['count'                  ]);
	if (isset($result['active'                 ])) $set[] = '`active` = '                  . q_boolean       ($result['active'                 ]);
	if (isset($result['max_tables'             ])) $set[] = '`max_tables` = '              . q_int_or_null   ($result['max_tables'             ]);
	if (isset($result['max_staffers'           ])) $set[] = '`max_staffers` = '            . q_int_or_null   ($result['max_staffers'           ]);
	if (isset($result['price_per_table'        ])) $set[] = '`price_per_table` = '         . q_float         ($result['price_per_table'        ]);
	if (isset($result['price_per_staffer'      ])) $set[] = '`price_per_staffer` = '       . q_float         ($result['price_per_staffer'      ]);
	if (isset($result['staffers_in_table_price'])) $set[] = '`staffers_in_table_price` = ' . q_int           ($result['staffers_in_table_price']);
	if (isset($result['max_prereg_discount'    ])) $set[] = '`max_prereg_discount` = '     . q_string        ($result['max_prereg_discount'    ]);
	if (isset($result['require_permit'         ])) $set[] = '`require_permit` = '          . q_boolean       ($result['require_permit'         ]);
	return implode(', ', $set);
}

function decode_booth($result, $badge_names) {
	$id = (int)$result['id'];
	$id_string = 'BA'.$id;
	$replaced_by = (int)$result['replaced_by'];
	$contact_first_name = unpurify_string($result['contact_first_name']);
	$contact_last_name = unpurify_string($result['contact_last_name']);
	$contact_real_name = trim(trim($contact_first_name) . ' ' . trim($contact_last_name));
	$contact_email_address = unpurify_string($result['contact_email_address']);
	$contact_phone_number = unpurify_string($result['contact_phone_number']);
	$badge_id = (int)$result['badge_id'];
	$badge_id_string = 'BB'.$badge_id;
	$badge_name = (isset($badge_names[$badge_id]) ? $badge_names[$badge_id] : 'Unknown');
	$business_name = unpurify_string($result['business_name']);
	$booth_name = unpurify_string($result['booth_name']);
	$num_tables = (int)$result['num_tables'];
	$num_staffers = (int)$result['num_staffers'];
	$application_status = $result['application_status'];
	$application_status_string = application_status_string($application_status);
	$application_status_html = application_status_html($application_status);
	$table_id = explode(',', unpurify_string($result['table_id']));
	$permit_number = unpurify_string($result['permit_number']);
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
	$order_url = get_base_url() . 'apply-booth/order.php?';
	$confirm_payment_url = $payment_lookup_key ? ($order_url.'id='.$id.'&key='.$payment_lookup_key) : null;
	$review_order_url = ($payment_txn_id && $payment_lookup_key) ? ($order_url.'txn='.$payment_txn_id.'&key='.$payment_lookup_key) : null;
	$order_url = $review_order_url ? $review_order_url : $confirm_payment_url;
	$search_content = strtolower(implode('||', array(
		$id,
		$contact_real_name,
		$contact_email_address,
		$contact_phone_number,
		$badge_name,
		$business_name,
		$booth_name,
		$application_status_string,
		implode('||', $table_id),
		$permit_number,
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
		'business_name' => $business_name,
		'booth_name' => $booth_name,
		'num_tables' => $num_tables,
		'num_staffers' => $num_staffers,
		'application_status' => $application_status,
		'application_status_string' => $application_status_string,
		'application_status_html' => $application_status_html,
		'table_id' => $table_id,
		'permit_number' => $permit_number,
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

function encode_booth($result) {
	$set = array();
	if (isset($result['replaced_by'           ])) $set[] = '`replaced_by` = '            . q_int_or_null   ($result['replaced_by'           ]);
	if (isset($result['contact_first_name'    ])) $set[] = '`contact_first_name` = '     . q_string        ($result['contact_first_name'    ]);
	if (isset($result['contact_last_name'     ])) $set[] = '`contact_last_name` = '      . q_string        ($result['contact_last_name'     ]);
	if (isset($result['contact_email_address' ])) $set[] = '`contact_email_address` = '  . q_string        ($result['contact_email_address' ]);
	if (isset($result['contact_phone_number'  ])) $set[] = '`contact_phone_number` = '   . q_string_or_null($result['contact_phone_number'  ]);
	if (isset($result['badge_id'              ])) $set[] = '`badge_id` = '               . q_int           ($result['badge_id'              ]);
	if (isset($result['business_name'         ])) $set[] = '`business_name` = '          . q_string        ($result['business_name'         ]);
	if (isset($result['booth_name'            ])) $set[] = '`booth_name` = '             . q_string        ($result['booth_name'            ]);
	if (isset($result['num_tables'            ])) $set[] = '`num_tables` = '             . q_int           ($result['num_tables'            ]);
	if (isset($result['num_staffers'          ])) $set[] = '`num_staffers` = '           . q_int           ($result['num_staffers'          ]);
	if (isset($result['application_status'    ])) $set[] = '`application_status` = '     . q_string        ($result['application_status'    ]);
	if (isset($result['permit_number'         ])) $set[] = '`permit_number` = '          . q_string_or_null($result['permit_number'         ]);
	if (isset($result['payment_status'        ])) $set[] = '`payment_status` = '         . q_string        ($result['payment_status'        ]);
	if (isset($result['payment_type'          ])) $set[] = '`payment_type` = '           . q_string_or_null($result['payment_type'          ]);
	if (isset($result['payment_txn_id'        ])) $set[] = '`payment_txn_id` = '         . q_string_or_null($result['payment_txn_id'        ]);
	if (isset($result['payment_original_price'])) $set[] = '`payment_original_price` = ' . q_float_or_null ($result['payment_original_price']);
	if (isset($result['payment_final_price'   ])) $set[] = '`payment_final_price` = '    . q_float_or_null ($result['payment_final_price'   ]);
	if (isset($result['payment_details'       ])) $set[] = '`payment_details` = '        . q_string_or_null($result['payment_details'       ]);
	if (isset($result['table_id'])) {
		$table_id = implode(',', $result['table_id']);
		$set[] = '`table_id` = ' . q_string($table_id);
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
	$set[] = '`date_modified` = NOW()';
	return implode(', ', $set);
}

function decode_booth_staffer($result, $booth_info) {
	global $event_date_start;
	$id = (int)$result['id'];
	$id_string = 'B'.$id;
	$booth_id = (int)$result['booth_id'];
	$booth_id_string = 'BA'.$booth_id;
	$booth_info = $booth_info[$booth_id];
	$business_name = $booth_info['business_name'];
	$booth_name = $booth_info['booth_name'];
	$badge_id = $booth_info['badge_id'];
	$badge_id_string = 'BB'.$badge_id;
	$badge_name = $booth_info['badge_name'];
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
		$booth_id,
		$business_name,
		$booth_name,
		$badge_id,
		$badge_name,
		$real_name,
		$fandom_name,
		$date_of_birth,
		$email_address,
		$phone_number,
		$address_full,
	)));
	$application_status = $booth_info['application_status'];
	$application_status_string = $booth_info['application_status_string'];
	$application_status_html = $booth_info['application_status_html'];
	$table_id = $booth_info['table_id'];
	$permit_number = $booth_info['permit_number'];
	$payment_status = $booth_info['payment_status'];
	$payment_status_string = $booth_info['payment_status_string'];
	$payment_status_html = $booth_info['payment_status_html'];
	$print_count = (int)$result['print_count'];
	$print_time = $result['print_time'];
	$checkin_count = (int)$result['checkin_count'];
	$checkin_time = $result['checkin_time'];
	$date_created = $result['date_created'];
	$date_modified = $result['date_modified'];
	return array(
		'id' => $id,
		'id_string' => $id_string,
		'booth_id' => $booth_id,
		'booth_id_string' => $booth_id_string,
		'business_name' => $business_name,
		'booth_name' => $booth_name,
		'badge_id' => $badge_id,
		'badge_id_string' => $badge_id_string,
		'badge_name' => $badge_name,
		'group_id' => $booth_id,
		'group_id_string' => $booth_id_string,
		'group_name' => $booth_name,
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
		'table_id' => $table_id,
		'permit_number' => $permit_number,
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

function encode_booth_staffer($result) {
	$set = array();
	if (isset($result['booth_id'              ])) $set[] = '`booth_id` = '               . q_int           ($result['booth_id'              ]);
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

function get_booth_tables($connection, $badge_names) {
	db_require_table('booth_tables', $connection);
	db_require_table('booths', $connection);
	$tables = array();
	$results = mysql_query('SELECT * FROM '.db_table_name('booth_tables'), $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = unpurify_string($result['id']);
		$x = (int)$result['x'] / 1000.0;
		$y = (int)$result['y'] / 1000.0;
		$tables[$id] = array(
			'id' => $id,
			'x' => $x,
			'y' => $y,
		);
	}
	$results = mysql_query('SELECT * FROM '.db_table_name('booths'), $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_booth($result, $badge_names);
		if ($result['table_id']) {
			foreach ($result['table_id'] as $id) {
				if (isset($tables[$id])) {
					$tables[$id]['booth'] = $result;
				}
			}
		}
	}
	uksort($tables, 'strnatcasecmp');
	return $tables;
}

function get_booth_badge_names($connection) {
	db_require_table('booth_badges', $connection);
	$badge_names = array();
	$results = mysql_query('SELECT * FROM '.db_table_name('booth_badges').' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$name = unpurify_string($result['name']);
		$badge_names[$id] = $name;
	}
	return $badge_names;
}

function get_booth_info($connection, $badge_names) {
	db_require_table('booths', $connection);
	$booth_info = array();
	$results = mysql_query('SELECT * FROM '.db_table_name('booths').' ORDER BY `id`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$id_string = 'BA'.$id;
		$business_name = unpurify_string($result['business_name']);
		$booth_name = unpurify_string($result['booth_name']);
		$badge_id = (int)$result['badge_id'];
		$badge_id_string = 'BB'.$badge_id;
		$badge_name = (isset($badge_names[$badge_id]) ? $badge_names[$badge_id] : 'Unknown');
		$application_status = $result['application_status'];
		$application_status_string = application_status_string($application_status);
		$application_status_html = application_status_html($application_status);
		$table_id = explode(',', unpurify_string($result['table_id']));
		$permit_number = unpurify_string($result['permit_number']);
		$payment_status = $result['payment_status'];
		$payment_status_string = payment_status_string($payment_status);
		$payment_status_html = payment_status_html($payment_status);
		$booth_info[$id] = array(
			'id' => $id,
			'id_string' => $id_string,
			'business_name' => $business_name,
			'booth_name' => $booth_name,
			'badge_id' => $badge_id,
			'badge_id_string' => $badge_id_string,
			'badge_name' => $badge_name,
			'application_status' => $application_status,
			'application_status_string' => $application_status_string,
			'application_status_html' => $application_status_html,
			'table_id' => $table_id,
			'permit_number' => $permit_number,
			'payment_status' => $payment_status,
			'payment_status_string' => $payment_status_string,
			'payment_status_html' => $payment_status_html,
		);
	}
	return $booth_info;
}

function get_valid_booth_badges($connection) {
	global $event_date_start, $event_date_end;
	db_require_table('booth_badges', $connection);
	db_require_table('booths', $connection);
	$bb = db_table_name('booth_badges');
	$b = db_table_name('booths');
	$badge_info = array();
	$badge_query = (
		'SELECT * FROM '.$bb.
		' WHERE '.$bb.'.`active`'.
		' AND ('.$bb.'.`start_date` IS NULL OR '.$bb.'.`start_date` <= CURDATE())'.
		' AND ('.$bb.'.`end_date` IS NULL OR '.$bb.'.`end_date` >= CURDATE())'.
		' AND ('.$bb.'.`count` IS NULL OR '.$bb.'.`count` > ('.
		'  SELECT IFNULL(SUM('.$b.'.`num_tables`), 0) FROM '.$b.
		'  WHERE '.$b.'.`badge_id` = '.$bb.'.`id`'.
		'  AND '.$b.'.`application_status` = \'Accepted\''.
		' ))'.
		' ORDER BY `order`'
	);
	$results = mysql_query($badge_query, $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$badge_info[$id] = decode_booth_badge($result);
	}
	return $badge_info;
}

function get_accepted_booth_badge_count($badge_id, $connection) {
	db_require_table('booths', $connection);
	$results = mysql_query(
		('SELECT SUM(`num_tables`)'.
		' FROM '.db_table_name('booths').
		' WHERE `badge_id` = '.(int)$badge_id.
		' AND `application_status` = \'Accepted\''),
		$connection
	);
	$result = mysql_fetch_assoc($results);
	return (int)$result['SUM(`num_tables`)'];
}

function upload_booth_map($file) {
	if (!$file['error']) {
		$image_url = config_file_path('maps/tables.dat');
		if (move_uploaded_file($file['tmp_name'], $image_url)) {
			return 'Image upload succeeded.';
		} else {
			return 'Error uploading image. Perhaps permissions-related?';
		}
	} else {
		return 'Error uploading image. Please try again with a different image.';
	}
}

function echo_booth_map() {
	$image_url = config_file_path('maps/tables.dat');
	if ($image_url) {
		$image_type = exif_imagetype($image_url);
		if ($image_type) {
			$mime_type = image_type_to_mime_type($image_type);
			if ($mime_type) {
				header('Content-Type: ' . $mime_type);
				readfile($image_url);
				return true;
			}
		}
	}
	return false;
}

function booth_map_aspect_ratio() {
	$image_url = config_file_path('maps/tables.dat');
	if ($image_url) {
		$image_size = getimagesize($image_url);
		if ($image_size) {
			return $image_size[1] * 100 / $image_size[0];
		}
	}
	return 75;
}