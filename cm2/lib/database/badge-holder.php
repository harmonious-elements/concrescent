<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/database.php';
require_once dirname(__FILE__).'/attendee.php';
require_once dirname(__FILE__).'/application.php';
require_once dirname(__FILE__).'/staff.php';

class cm_badge_holder_db {

	public $cm_db;
	public $cm_atdb;
	public $cm_apdb;
	public $cm_sdb;

	public function __construct($cm_db) {
		$this->cm_db = $cm_db;
		$this->cm_atdb = new cm_attendee_db($cm_db);
		$this->cm_apdb = array();
		foreach ($GLOBALS['cm_config']['application_types'] as $ctx => $x) {
			$this->cm_apdb[$ctx] = new cm_application_db($cm_db, $ctx);
		}
		$this->cm_sdb = new cm_staff_db($cm_db);
	}

	public function list_badge_type_names() {
		$badge_types = array();
		$names = $this->cm_atdb->list_badge_type_names();
		foreach ($names as $name) {
			$badge_types[] = array(
				'context' => 'attendee',
				'context-id' => $name['id'],
				'id-string' => 'AB' . $name['id'],
				'name' => $name['name']
			);
		}
		foreach ($this->cm_apdb as $ctx => $apdb) {
			$names = $apdb->list_badge_type_names();
			foreach ($names as $name) {
				$badge_types[] = array(
					'context' => 'application-' . strtolower($ctx),
					'context-id' => $name['id'],
					'id-string' => strtoupper($ctx) . 'B' . $name['id'],
					'name' => $name['name']
				);
			}
		}
		$names = $this->cm_sdb->list_badge_type_names();
		foreach ($names as $name) {
			$badge_types[] = array(
				'context' => 'staff',
				'context-id' => $name['id'],
				'id-string' => 'SB' . $name['id'],
				'name' => $name['name']
			);
		}
		return $badge_types;
	}

	public function get_badge_holder($context, $context_id) {
		if ($context == 'attendee') {
			return $this->cm_atdb->get_attendee($context_id);
		} else if (substr($context, 0, 10) == 'applicant-') {
			$context = substr($context, 10);
			foreach ($this->cm_apdb as $ctx => $apdb) {
				if ($context == strtolower($ctx)) {
					return $apdb->get_applicant($context_id);
				}
			}
			return false;
		} else if ($context == 'staff') {
			return $this->cm_sdb->get_staff_member($context_id);
		} else {
			return false;
		}
	}

}