<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../base/util.php';

function mail_send($to, $template, $entity) {
	global $event_name, $event_date_start, $event_date_end;
	if ($to && $template && trim($template['body']) && $entity) {
		$mail_fields = array_merge(array(
			'event_name' => $event_name,
			'event_date_start' => $event_date_start,
			'event_date_end' => $event_date_end,
			'contact_address' => $template['contact_address'],
		), $entity);
		
		$mail_subject = mail_merge($template['subject'], $mail_fields);
		$mail_subject = str_replace("\r\n", " ", $mail_subject);
		$mail_subject = str_replace("\r", " ", $mail_subject);
		$mail_subject = str_replace("\n", " ", $mail_subject);
		
		$mail_body = mail_merge($template['body'], $mail_fields);
		$mail_body = str_replace("\r\n", "\n", $mail_body);
		$mail_body = str_replace("\r", "\n", $mail_body);
		$mail_body = str_replace("\n", "\r\n", $mail_body);
		
		$mail_headers = array();
		if ($template['from']) $mail_headers[] = 'From: ' . $template['from'];
		if ($template['bcc']) $mail_headers[] = 'Bcc: ' . $template['bcc'];
		$mail_headers = implode("\r\n", $mail_headers);
		
		mail($to, $mail_subject, $mail_body, $mail_headers);
		// error_log("MAIL SENT:\r\n\r\n$mail_headers\r\nTo: $to\r\nSubject: $mail_subject\r\n\r\n$mail_body");
	}
}

function render_mail_editor($name, $display_name, $template) {
	echo '<h3><label>' . htmlspecialchars($display_name) . '</label></h3>';
	
	$email = $template['body'];
	$email = str_replace("\r\n", "\n", $email);
	$email = str_replace("\r", "\n", $email);
	
	echo '<p><label for="' . htmlspecialchars($name) . '_contact">Contact:</label> ';
	echo '<input type="email" name="' . htmlspecialchars($name) . '_contact" ';
	echo 'value="' . htmlspecialchars($template['contact_address']) . '"></p>';
	
	echo '<p><label for="' . htmlspecialchars($name) . '_from">From:</label> ';
	echo '<input type="email" name="' . htmlspecialchars($name) . '_from" ';
	echo 'value="' . htmlspecialchars($template['from']) . '"></p>';
	
	echo '<p><label for="' . htmlspecialchars($name) . '_bcc">Bcc:</label> ';
	echo '<input type="email" name="' . htmlspecialchars($name) . '_bcc" ';
	echo 'value="' . htmlspecialchars($template['bcc']) . '"></p>';
	
	echo '<p><label for="' . htmlspecialchars($name) . '_subject">Subject:</label> ';
	echo '<input type="text" name="' . htmlspecialchars($name) . '_subject" ';
	echo 'value="' . htmlspecialchars($template['subject']) . '"></p>';
	
	echo '<p><textarea name="' . htmlspecialchars($name) . '_body">';
	echo htmlspecialchars($email);
	echo '</textarea></p>';
	
	echo '<hr>';
}

function get_posted_mail_template($name) {
	$email = trim($_POST[$name.'_body']);
	$email = str_replace("\r\n", "\n", $email);
	$email = str_replace("\r", "\n", $email);
	$email = str_replace("\n", "\r\n", $email);
	return array(
		'name' => $name,
		'contact_address' => trim($_POST[$name.'_contact']),
		'from' => trim($_POST[$name.'_from']),
		'bcc' => trim($_POST[$name.'_bcc']),
		'subject' => trim($_POST[$name.'_subject']),
		'body' => $email,
	);
}