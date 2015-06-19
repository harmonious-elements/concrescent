<?php

require_once dirname(__FILE__).'/dal.php';
require_once dirname(__FILE__).'/../schema/mail.php';
require_once dirname(__FILE__).'/../base/sql.php';

function decode_mail_template($result) {
	$name = unpurify_string($result['name']);
	$contact_address = unpurify_string($result['contact_address']);
	$from = unpurify_string($result['from']);
	$bcc = unpurify_string($result['bcc']);
	$subject = unpurify_string($result['subject']);
	$body = unpurify_string($result['body']);
	return array(
		'name' => $name,
		'contact_address' => $contact_address,
		'from' => $from,
		'bcc' => $bcc,
		'subject' => $subject,
		'body' => $body,
	);
}

function encode_mail_template($result) {
	$set = array();
	if (isset($result['name'           ])) $set[] = '`name` = '            . q_string        ($result['name'           ]);
	if (isset($result['contact_address'])) $set[] = '`contact_address` = ' . q_string        ($result['contact_address']);
	if (isset($result['from'           ])) $set[] = '`from` = '            . q_string        ($result['from'           ]);
	if (isset($result['bcc'            ])) $set[] = '`bcc` = '             . q_string_or_null($result['bcc'            ]);
	if (isset($result['subject'        ])) $set[] = '`subject` = '         . q_string        ($result['subject'        ]);
	if (isset($result['body'           ])) $set[] = '`body` = '            . q_string        ($result['body'           ]);
	return implode(', ', $set);
}

function get_mail_contact($name, $connection) {
	db_require_table('mail_templates', $connection);
	$results = mysql_query('SELECT `contact_address` FROM '.db_table_name('mail_templates').' WHERE `name` = '.q_string($name), $connection);
	if ($result = mysql_fetch_assoc($results)) {
		return unpurify_string($result['contact_address']);
	} else {
		return null;
	}
}

function get_mail_template($name, $connection) {
	db_require_table('mail_templates', $connection);
	$results = mysql_query('SELECT * FROM '.db_table_name('mail_templates').' WHERE `name` = '.q_string($name), $connection);
	if ($result = mysql_fetch_assoc($results)) {
		return decode_mail_template($result);
	} else {
		return null;
	}
}

function set_mail_template($name, $result, $connection) {
	db_require_table('mail_templates', $connection);
	$result['name'] = $name;
	$set = encode_mail_template($result);
	mysql_query('INSERT INTO '.db_table_name('mail_templates').' SET '.$set.' ON DUPLICATE KEY UPDATE '.$set, $connection);
}