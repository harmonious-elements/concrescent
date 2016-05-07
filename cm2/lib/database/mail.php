<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../util/util.php';
require_once dirname(__FILE__).'/database.php';

class cm_mail_db {

	public $event_info;
	public $cm_db;

	public function __construct($cm_db) {
		$this->event_info = $GLOBALS['cm_config']['event'];
		$this->cm_db = $cm_db;
		$this->cm_db->table_def('mail_templates', (
			'`name` VARCHAR(255) NOT NULL PRIMARY KEY,'.
			'`contact_address` VARCHAR(255) NOT NULL,'.
			'`from` VARCHAR(255) NOT NULL,'.
			'`bcc` VARCHAR(255) NULL,'.
			'`subject` VARCHAR(255) NOT NULL,'.
			'`type` ENUM(\'Text\',\'Simple HTML\',\'Full HTML\') NOT NULL,'.
			'`body` TEXT NOT NULL'
		));
		if ($this->cm_db->table_is_empty('mail_templates')) {
			$stmt = $this->cm_db->connection->prepare(
				'INSERT INTO '.$this->cm_db->table_name('mail_templates').' SET '.
				'`name` = ?, `contact_address` = ?, `from` = ?, `bcc` = ?, `subject` = ?, `type` = ?, `body` = ?'
			);
			$name = 'attendee-paid';
			$contact_address = 'registration@' . $_SERVER['SERVER_NAME'];
			$from = $contact_address;
			$bcc = $contact_address;
			$subject = 'Your registration for [[event-name]]';
			$type = 'Simple HTML';
			$body = (
				"Greetings,\n\n".
				"Thank you for registering for <b>[[event-name]]</b>. ".
				"Your [[badge-name]] registration for <b>[[display-name]]</b> has been completed.\n\n".
				"Your badge will be available for pickup at the event. ".
				"Please bring a photo ID and a printout of this email message with you.\n\n".
				"<img src=\"https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=[[qr-data]]\">\n\n".
				"You can review your order at any time at the following URL:\n\n".
				"<a href=\"[[order-url]]\">[[order-url]]</a>\n\n".
				"Thanks again,\n[[event-name]] Registration"
			);
			$stmt->bind_param('sssssss', $name, $contact_address, $from, $bcc, $subject, $type, $body);
			$stmt->execute();
			$stmt->close();
		}
	}

	public function get_mail_template($name) {
		if (!$name) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `name`, `contact_address`, `from`, `bcc`, `subject`, `type`, `body`'.
			' FROM '.$this->cm_db->table_name('mail_templates').
			' WHERE `name` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $name);
		$stmt->execute();
		$stmt->bind_result($name, $contact_address, $from, $bcc, $subject, $type, $body);
		if ($stmt->fetch()) {
			$result = array(
				'name' => $name,
				'contact-address' => $contact_address,
				'from' => $from,
				'bcc' => $bcc,
				'subject' => $subject,
				'type' => $type,
				'body' => $body,
				'search-content' => array($name, $contact_address, $from, $bcc, $subject)
			);
			$stmt->close();
			return $result;
		}
		$stmt->close();
		return false;
	}

	public function list_mail_templates() {
		$templates = array();
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `name`, `contact_address`, `from`, `bcc`, `subject`, `type`, `body`'.
			' FROM '.$this->cm_db->table_name('mail_templates').
			' ORDER BY `name`'
		);
		$stmt->execute();
		$stmt->bind_result($name, $contact_address, $from, $bcc, $subject, $type, $body);
		while ($stmt->fetch()) {
			$templates[] = array(
				'name' => $name,
				'contact-address' => $contact_address,
				'from' => $from,
				'bcc' => $bcc,
				'subject' => $subject,
				'type' => $type,
				'body' => $body,
				'search-content' => array($name, $contact_address, $from, $bcc, $subject)
			);
		}
		$stmt->close();
		return $templates;
	}

	public function set_mail_template($template) {
		if (!$template || !isset($template['name']) || !$template['name']) return false;
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('mail_templates').' SET '.
			'`name` = ?, `contact_address` = ?, `from` = ?, `bcc` = ?, `subject` = ?, `type` = ?, `body` = ?'.
			' ON DUPLICATE KEY UPDATE '.
			'`name` = ?, `contact_address` = ?, `from` = ?, `bcc` = ?, `subject` = ?, `type` = ?, `body` = ?'
		);
		$stmt->bind_param(
			'ssssssssssssss',
			$template['name'], $template['contact-address'], $template['from'], $template['bcc'], $template['subject'], $template['type'], $template['body'],
			$template['name'], $template['contact-address'], $template['from'], $template['bcc'], $template['subject'], $template['type'], $template['body']
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function clear_mail_template($name) {
		if (!$name) return false;
		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('mail_templates').
			' WHERE `name` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $name);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function send_mail($to, $template, $entity) {
		if ($to && $template && isset($template['body']) && trim($template['body']) && $entity) {
			$mail_fields = array();
			foreach ($this->event_info as $k => $v) {
				$mail_fields['event-' . strtolower(str_replace('_', '-', $k))] = $v;
				$mail_fields['event_' . strtolower(str_replace('-', '_', $k))] = $v;
			}
			$mail_fields['contact-address'] = $template['contact-address'];
			$mail_fields['contact_address'] = $template['contact-address'];
			foreach ($entity as $k => $v) {
				$mail_fields[strtolower(str_replace('_', '-', $k))] = $v;
				$mail_fields[strtolower(str_replace('-', '_', $k))] = $v;
			}

			$mail_subject = mail_merge($template['subject'], $mail_fields);
			$mail_subject = str_replace("\r\n", " ", $mail_subject);
			$mail_subject = str_replace("\r", " ", $mail_subject);
			$mail_subject = str_replace("\n", " ", $mail_subject);

			switch ($template['type']) {
				case 'Full HTML':
					$content_type = 'text/html; charset=UTF-8';
					$mail_body = mail_merge_html($template['body'], $mail_fields);
					break;
				case 'Simple HTML':
					$content_type = 'text/html; charset=UTF-8';
					$mail_body = mail_merge_html(safe_html_string($template['body']), $mail_fields);
					break;
				default:
					$content_type = 'text/plain; charset=UTF-8';
					$mail_body = mail_merge($template['body'], $mail_fields);
					break;
			}

			$mail_headers = array();
			if ($template['from']) $mail_headers[] = 'From: ' . $template['from'];
			if ($template['bcc']) $mail_headers[] = 'Bcc: ' . $template['bcc'];
			$mail_headers[] = 'X-Mailer: CONcrescent/2.0 PHP/' . phpversion();
			$mail_headers[] = 'MIME-Version: 1.0';
			$mail_headers[] = 'Content-Type: ' . $content_type;
			$mail_headers = implode("\r\n", $mail_headers);

			return mail($to, $mail_subject, $mail_body, $mail_headers);
		}
	}

}