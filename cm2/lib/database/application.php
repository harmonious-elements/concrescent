<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../util/util.php';
require_once dirname(__FILE__).'/../util/res.php';
require_once dirname(__FILE__).'/database.php';
require_once dirname(__FILE__).'/lists.php';
require_once dirname(__FILE__).'/forms.php';

class cm_application_db {

	public $application_statuses = array(
		'Submitted',
		'Cancelled',
		'Accepted',
		'Waitlisted',
		'Rejected'
	);
	public $payment_statuses = array(
		'Incomplete',
		'Cancelled',
		'Rejected',
		'Completed',
		'Refunded'
	);
	public $names_on_badge = array(
		'Fandom Name Large, Real Name Small',
		'Real Name Large, Fandom Name Small',
		'Fandom Name Only',
		'Real Name Only'
	);

	public $event_info;
	public $cm_db;
	public $ctx_uc;
	public $ctx_lc;
	public $ctx_info;
	public $cm_anldb;
	public $cm_atldb;

	public function __construct($cm_db, $context) {
		$this->event_info = $GLOBALS['cm_config']['event'];
		$this->cm_db = $cm_db;
		$this->cm_db->table_def('rooms_and_tables', (
			'`id` VARCHAR(255) NOT NULL PRIMARY KEY,'.
			'`x1` DECIMAL(7,6) NOT NULL,'.
			'`y1` DECIMAL(7,6) NOT NULL,'.
			'`x2` DECIMAL(7,6) NOT NULL,'.
			'`y2` DECIMAL(7,6) NOT NULL'
		));
		if ($context) {
			$this->ctx_uc = strtoupper($context);
			$this->ctx_lc = strtolower($context);
			$this->ctx_info = $GLOBALS['cm_config']['application_types'][$this->ctx_uc];
			$this->cm_anldb = new cm_lists_db($this->cm_db, 'application_search_index_' . $this->ctx_lc);
			$this->cm_atldb = new cm_lists_db($this->cm_db, 'applicant_search_index_' . $this->ctx_lc);
		} else {
			$this->ctx_uc = null;
			$this->ctx_lc = null;
			$this->ctx_info = null;
			$this->cm_anldb = null;
			$this->cm_atldb = null;
		}
	}

	public function get_room_or_table($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `x1`, `y1`, `x2`, `y2`'.
			' FROM '.$this->cm_db->table_name('rooms_and_tables').
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $id);
		$stmt->execute();
		$stmt->bind_result($id, $x1, $y1, $x2, $y2);
		if ($stmt->fetch()) {
			$result = array(
				'id' => $id,
				'x1' => $x1,
				'y1' => $y1,
				'x2' => $x2,
				'y2' => $y2
			);
			$stmt->close();
			return $result;
		}
		$stmt->close();
		return false;
	}

	public function list_rooms_and_tables() {
		$rooms_and_tables = array();
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `x1`, `y1`, `x2`, `y2`'.
			' FROM '.$this->cm_db->table_name('rooms_and_tables')
		);
		$stmt->execute();
		$stmt->bind_result($id, $x1, $y1, $x2, $y2);
		while ($stmt->fetch()) {
			$rooms_and_tables[] = array(
				'id' => $id,
				'x1' => $x1,
				'y1' => $y1,
				'x2' => $x2,
				'y2' => $y2
			);
		}
		$stmt->close();
		usort($rooms_and_tables, function($a, $b) {
			return strnatcasecmp($a['id'], $b['id']);
		});
		return $rooms_and_tables;
	}

	public function create_room_or_table($rt) {
		if (!$rt || !isset($rt['id']) || !$rt['id']) return false;
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('rooms_and_tables').' SET '.
			'`id` = ?, `x1` = ?, `y1` = ?, `x2` = ?, `y2` = ?'.
			' ON DUPLICATE KEY UPDATE '.
			'`id` = ?, `x1` = ?, `y1` = ?, `x2` = ?, `y2` = ?'
		);
		$stmt->bind_param(
			'sddddsdddd',
			$rt['id'], $rt['x1'], $rt['y1'], $rt['x2'], $rt['y2'],
			$rt['id'], $rt['x1'], $rt['y1'], $rt['x2'], $rt['y2']
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function update_room_or_table($id, $rt) {
		if (!$id || !$rt || !isset($rt['id']) || !$rt['id']) return false;
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('rooms_and_tables').' SET '.
			'`id` = ?, `x1` = ?, `y1` = ?, `x2` = ?, `y2` = ?'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param(
			'sdddds',
			$rt['id'], $rt['x1'], $rt['y1'], $rt['x2'], $rt['y2'],
			$id
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function delete_room_or_table($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('rooms_and_tables').
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $id);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function upload_rooms_and_tables($file) {
		if (!$file) return false;
		$in = fopen($file, 'r');
		if (!$in) return false;
		while (($row = fgetcsv($in))) {
			if (count($row) < 5) continue;
			$this->create_room_or_table(array(
				'x1' => (float)$row[1],
				'y1' => (float)$row[2],
				'x2' => (float)$row[3],
				'y2' => (float)$row[4],
				'id' => trim($row[0])
			));
		}
		fclose($in);
		return true;
	}

	public function download_rooms_and_tables() {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=rooms-and-tables.csv');
		header('Pragma: no-cache');
		header('Expires: 0');
		$out = fopen('php://output', 'w');
		$rooms_and_tables = $this->list_rooms_and_tables();
		foreach ($rooms_and_tables as $rt) {
			$row = array($rt['id'], $rt['x1'], $rt['y1'], $rt['x2'], $rt['y2']);
			fputcsv($out, $row);
		}
		fclose($out);
		exit(0);
	}

	public function delete_rooms_and_tables() {
		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('rooms_and_tables')
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

}