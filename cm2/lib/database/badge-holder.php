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
					return $apdb->get_applicant($context_id, false, true);
				}
			}
			return false;
		} else if ($context == 'staff') {
			return $this->cm_sdb->get_staff_member($context_id);
		} else {
			return false;
		}
	}

	public function list_indexes(&$list_def, $query = null, $sort_order = null, $offset = null, $length = null) {
		if (is_null($query)) $query = json_decode($_POST['cm-list-search-query'], true);
		if (is_null($sort_order)) $sort_order = json_decode($_POST['cm-list-sort-order'], true);
		if (is_null($offset)) $offset = (int)$_POST['cm-list-page-offset'];
		if (is_null($length)) $length = (int)$_POST['cm-list-page-length'];

		$ids = array();

		$results = $this->cm_atdb->cm_ldb->list_indexes($list_def, $query, $sort_order, 0, 0);
		foreach ($results['ids'] as $id) {
			$ids[] = array('context' => 'attendee', 'context-id' => $id);
		}
		foreach ($this->cm_apdb as $ctx => $apdb) {
			$context = 'applicant-' . strtolower($ctx);
			$results = $apdb->cm_atldb->list_indexes($list_def, $query, $sort_order, 0, 0);
			foreach ($results['ids'] as $id) {
				$ids[] = array('context' => $context, 'context-id' => $id);
			}
		}
		$results = $this->cm_sdb->cm_ldb->list_indexes($list_def, $query, $sort_order, 0, 0);
		foreach ($results['ids'] as $id) {
			$ids[] = array('context' => 'staff', 'context-id' => $id);
		}

		$match_count = count($ids);
		if ($length) $ids = array_slice($ids, $offset, $length);

		return array(
			'ok' => true,
			'ids' => $ids,
			'match-count' => $match_count
		);
	}

	public function badge_holder_printed($context, $context_id) {
		if ($context == 'attendee') {
			return $this->cm_atdb->attendee_printed($context_id);
		} else if (substr($context, 0, 10) == 'applicant-') {
			$context = substr($context, 10);
			foreach ($this->cm_apdb as $ctx => $apdb) {
				if ($context == strtolower($ctx)) {
					return $apdb->applicant_printed($context_id);
				}
			}
			return false;
		} else if ($context == 'staff') {
			return $this->cm_sdb->staff_printed($context_id);
		} else {
			return false;
		}
	}

	public function badge_holder_checked_in($context, $context_id) {
		if ($context == 'attendee') {
			return $this->cm_atdb->attendee_checked_in($context_id);
		} else if (substr($context, 0, 10) == 'applicant-') {
			$context = substr($context, 10);
			foreach ($this->cm_apdb as $ctx => $apdb) {
				if ($context == strtolower($ctx)) {
					return $apdb->applicant_checked_in($context_id);
				}
			}
			return false;
		} else if ($context == 'staff') {
			return $this->cm_sdb->staff_checked_in($context_id);
		} else {
			return false;
		}
	}

}