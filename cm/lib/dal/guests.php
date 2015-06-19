<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/dal.php';
require_once dirname(__FILE__).'/questions.php';
require_once dirname(__FILE__).'/../schema/guests.php';
require_once dirname(__FILE__).'/../base/sql.php';
require_once dirname(__FILE__).'/../base/util.php';
require_once dirname(__FILE__).'/../cmbase/util.php';

function decode_guest_badge($result) {
	$id = (int)$result['id'];
	$id_string = 'GB'.$id;
	$name = unpurify_string($result['name']);
	$description = unpurify_string($result['description']);
	$description_html = safe_html_string($description);
	$start_date = $result['start_date'];
	$end_date = $result['end_date'];
	$count = (int)$result['count'];
	$active = !!$result['active'];
	$max_supporters = (int)$result['max_supporters'];
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
		'max_supporters' => $max_supporters,
		'order' => $order,
	);
}

function encode_guest_badge($result) {
	$set = array();
	if (isset($result['name'          ])) $set[] = '`name` = '           . q_string        ($result['name'          ]);
	if (isset($result['description'   ])) $set[] = '`description` = '    . q_string_or_null($result['description'   ]);
	if (isset($result['start_date'    ])) $set[] = '`start_date` = '     . q_date_or_null  ($result['start_date'    ]);
	if (isset($result['end_date'      ])) $set[] = '`end_date` = '       . q_date_or_null  ($result['end_date'      ]);
	if (isset($result['count'         ])) $set[] = '`count` = '          . q_int_or_null   ($result['count'         ]);
	if (isset($result['active'        ])) $set[] = '`active` = '         . q_boolean       ($result['active'        ]);
	if (isset($result['max_supporters'])) $set[] = '`max_supporters` = ' . q_int_or_null   ($result['max_supporters']);
	return implode(', ', $set);
}

function decode_guest($result, $badge_names) {
	$id = (int)$result['id'];
	$id_string = 'GA'.$id;
	$replaced_by = (int)$result['replaced_by'];
	$contact_first_name = unpurify_string($result['contact_first_name']);
	$contact_last_name = unpurify_string($result['contact_last_name']);
	$contact_real_name = trim(trim($contact_first_name) . ' ' . trim($contact_last_name));
	$contact_email_address = unpurify_string($result['contact_email_address']);
	$contact_phone_number = unpurify_string($result['contact_phone_number']);
	$badge_id = (int)$result['badge_id'];
	$badge_id_string = 'GB'.$badge_id;
	$badge_name = (isset($badge_names[$badge_id]) ? $badge_names[$badge_id] : 'Unknown');
	$guest_name = unpurify_string($result['guest_name']);
	$guest_description = unpurify_string($result['guest_description']);
	$num_supporters = (int)$result['num_supporters'];
	$application_status = $result['application_status'];
	$application_status_string = application_status_string($application_status);
	$application_status_html = application_status_html($application_status);
	$contract_status = $result['contract_status'];
	$contract_status_string = contract_status_string($contract_status);
	$contract_status_html = contract_status_html($contract_status);
	$search_content = strtolower(implode('||', array(
		$id,
		$contact_real_name,
		$contact_email_address,
		$contact_phone_number,
		$badge_name,
		$guest_name,
		$guest_description,
		$application_status_string,
		$contract_status_string,
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
		'guest_name' => $guest_name,
		'guest_description' => $guest_description,
		'num_supporters' => $num_supporters,
		'application_status' => $application_status,
		'application_status_string' => $application_status_string,
		'application_status_html' => $application_status_html,
		'contract_status' => $contract_status,
		'contract_status_string' => $contract_status_string,
		'contract_status_html' => $contract_status_html,
		'search_content' => $search_content,
		'date_created' => $date_created,
		'date_modified' => $date_modified,
	);
}

function encode_guest($result) {
	$set = array();
	if (isset($result['replaced_by'          ])) $set[] = '`replaced_by` = '           . q_int_or_null   ($result['replaced_by'          ]);
	if (isset($result['contact_first_name'   ])) $set[] = '`contact_first_name` = '    . q_string        ($result['contact_first_name'   ]);
	if (isset($result['contact_last_name'    ])) $set[] = '`contact_last_name` = '     . q_string        ($result['contact_last_name'    ]);
	if (isset($result['contact_email_address'])) $set[] = '`contact_email_address` = ' . q_string        ($result['contact_email_address']);
	if (isset($result['contact_phone_number' ])) $set[] = '`contact_phone_number` = '  . q_string_or_null($result['contact_phone_number' ]);
	if (isset($result['badge_id'             ])) $set[] = '`badge_id` = '              . q_int           ($result['badge_id'             ]);
	if (isset($result['guest_name'           ])) $set[] = '`guest_name` = '            . q_string        ($result['guest_name'           ]);
	if (isset($result['guest_description'    ])) $set[] = '`guest_description` = '     . q_string        ($result['guest_description'    ]);
	if (isset($result['num_supporters'       ])) $set[] = '`num_supporters` = '        . q_int           ($result['num_supporters'       ]);
	if (isset($result['application_status'   ])) $set[] = '`application_status` = '    . q_string        ($result['application_status'   ]);
	if (isset($result['contract_status'      ])) $set[] = '`contract_status` = '       . q_string        ($result['contract_status'      ]);
	$set[] = '`date_modified` = NOW()';
	return implode(', ', $set);
}

function decode_guest_supporter($result, $guest_info) {
	global $event_date_start;
	$id = (int)$result['id'];
	$id_string = 'G'.$id;
	$guest_id = (int)$result['guest_id'];
	$guest_id_string = 'GA'.$guest_id;
	$guest_info = $guest_info[$guest_id];
	$guest_name = $guest_info['guest_name'];
	$guest_description = $guest_info['guest_description'];
	$badge_id = $guest_info['badge_id'];
	$badge_id_string = 'GB'.$badge_id;
	$badge_name = $guest_info['badge_name'];
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
		$guest_id,
		$guest_name,
		$guest_description,
		$badge_id,
		$badge_name,
		$real_name,
		$fandom_name,
		$date_of_birth,
		$email_address,
		$phone_number,
		$address_full,
	)));
	$application_status = $guest_info['application_status'];
	$application_status_string = $guest_info['application_status_string'];
	$application_status_html = $guest_info['application_status_html'];
	$contract_status = $guest_info['contract_status'];
	$contract_status_string = $guest_info['contract_status_string'];
	$contract_status_html = $guest_info['contract_status_html'];
	$print_count = (int)$result['print_count'];
	$print_time = $result['print_time'];
	$checkin_count = (int)$result['checkin_count'];
	$checkin_time = $result['checkin_time'];
	$date_created = $result['date_created'];
	$date_modified = $result['date_modified'];
	return array(
		'id' => $id,
		'id_string' => $id_string,
		'guest_id' => $guest_id,
		'guest_id_string' => $guest_id_string,
		'guest_name' => $guest_name,
		'guest_description' => $guest_description,
		'badge_id' => $badge_id,
		'badge_id_string' => $badge_id_string,
		'badge_name' => $badge_name,
		'group_id' => $guest_id,
		'group_id_string' => $guest_id_string,
		'group_name' => $guest_name,
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
		'contract_status' => $contract_status,
		'contract_status_string' => $contract_status_string,
		'contract_status_html' => $contract_status_html,
		'print_count' => $print_count,
		'print_time' => $print_time,
		'checkin_count' => $checkin_count,
		'checkin_time' => $checkin_time,
		'date_created' => $date_created,
		'date_modified' => $date_modified,
	);
}

function encode_guest_supporter($result) {
	$set = array();
	if (isset($result['guest_id'              ])) $set[] = '`guest_id` = '               . q_int           ($result['guest_id'              ]);
	if (isset($result['first_name'            ])) $set[] = '`first_name` = '             . q_string        ($result['first_name'            ]);
	if (isset($result['last_name'             ])) $set[] = '`last_name` = '              . q_string        ($result['last_name'             ]);
	if (isset($result['fandom_name'           ])) $set[] = '`fandom_name` = '            . q_string_or_null($result['fandom_name'           ]);
	if (isset($result['name_on_badge'         ])) $set[] = '`name_on_badge` = '          . q_string        ($result['name_on_badge'         ]);
	if (isset($result['date_of_birth'         ])) $set[] = '`date_of_birth` = '          . q_date          ($result['date_of_birth'         ]);
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

function get_guest_badge_names($connection) {
	db_require_table('guest_badges', $connection);
	$badge_names = array();
	$results = mysql_query('SELECT * FROM '.db_table_name('guest_badges').' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$name = unpurify_string($result['name']);
		$badge_names[$id] = $name;
	}
	return $badge_names;
}

function get_guest_info($connection, $badge_names) {
	db_require_table('guests', $connection);
	$guest_info = array();
	$results = mysql_query('SELECT * FROM '.db_table_name('guests').' ORDER BY `id`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$id = (int)$result['id'];
		$id_string = 'GA'.$id;
		$guest_name = unpurify_string($result['guest_name']);
		$guest_description = unpurify_string($result['guest_description']);
		$badge_id = (int)$result['badge_id'];
		$badge_id_string = 'GB'.$badge_id;
		$badge_name = (isset($badge_names[$badge_id]) ? $badge_names[$badge_id] : 'Unknown');
		$application_status = $result['application_status'];
		$application_status_string = application_status_string($application_status);
		$application_status_html = application_status_html($application_status);
		$contract_status = $result['contract_status'];
		$contract_status_string = contract_status_string($contract_status);
		$contract_status_html = contract_status_html($contract_status);
		$guest_info[$id] = array(
			'id' => $id,
			'id_string' => $id_string,
			'guest_name' => $guest_name,
			'guest_description' => $guest_description,
			'badge_id' => $badge_id,
			'badge_id_string' => $badge_id_string,
			'badge_name' => $badge_name,
			'application_status' => $application_status,
			'application_status_string' => $application_status_string,
			'application_status_html' => $application_status_html,
			'contract_status' => $contract_status,
			'contract_status_string' => $contract_status_string,
			'contract_status_html' => $contract_status_html,
		);
	}
	return $guest_info;
}

function get_valid_guest_badges($connection) {
	global $event_date_start, $event_date_end;
	db_require_table('guest_badges', $connection);
	db_require_table('guests', $connection);
	$bb = db_table_name('guest_badges');
	$b = db_table_name('guests');
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
		$badge_info[$id] = decode_guest_badge($result);
	}
	return $badge_info;
}

function get_accepted_guest_badge_count($badge_id, $connection) {
	db_require_table('guests', $connection);
	$results = mysql_query(
		('SELECT COUNT(*)'.
		' FROM '.db_table_name('guests').
		' WHERE `badge_id` = '.(int)$badge_id.
		' AND `application_status` = \'Accepted\''),
		$connection
	);
	$result = mysql_fetch_assoc($results);
	return (int)$result['COUNT(*)'];
}