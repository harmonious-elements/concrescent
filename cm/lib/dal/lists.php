<?php

require_once dirname(__FILE__).'/dal.php';
require_once dirname(__FILE__).'/../schema/schema.php';
require_once dirname(__FILE__).'/../base/sql.php';

function get_entities($table_name, $order_by, $start_id, $batch_size, $ignore_replaced, $connection) {
	db_require_table($table_name, $connection);
	$q = 'SELECT * FROM '.db_table_name($table_name);
	if ($start_id) $q .= ' WHERE `id` >= '.(int)$start_id;
	if ($ignore_replaced) $q .= ($start_id ? ' AND ' : ' WHERE ') . '(`replaced_by` IS NULL OR `replaced_by` = 0)';
	if ($order_by) $q .= ' ORDER BY `'.$order_by.'`';
	if ($batch_size) $q .= ' LIMIT '.(int)$batch_size;
	return mysql_query($q, $connection);
}

function get_entity($results) {
	return mysql_fetch_assoc($results);
}

function activate_entity($table_name, $id, $connection) {
	db_require_table($table_name, $connection);
	mysql_query('UPDATE '.db_table_name($table_name).' SET `active` = TRUE WHERE `id` = '.(int)$id, $connection);
}

function deactivate_entity($table_name, $id, $connection) {
	db_require_table($table_name, $connection);
	mysql_query('UPDATE '.db_table_name($table_name).' SET `active` = FALSE WHERE `id` = '.(int)$id, $connection);
}

function reorder_entities($table_name, $id, $direction, $connection) {
	db_require_table($table_name, $connection);
	$ids = array();
	$index = -1;
	$results = mysql_query('SELECT `id`, `order` FROM '.db_table_name($table_name).' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$cid = (int)$result['id'];
		$cindex = count($ids);
		$ids[] = $cid;
		if ($id == $cid) $index = $cindex;
	}
	if ($index >= 0) {
		while ($direction < 0 && $index > 0) {
			$ids[$index] = $ids[$index - 1];
			$ids[$index - 1] = $id;
			$direction++;
			$index--;
		}
		while ($direction > 0 && $index < (count($ids) - 1)) {
			$ids[$index] = $ids[$index + 1];
			$ids[$index + 1] = $id;
			$direction--;
			$index++;
		}
		foreach ($ids as $cindex => $cid) {
			mysql_query('UPDATE '.db_table_name($table_name).' SET `order` = '.$cindex.' WHERE `id` = '.(int)$cid, $connection);
		}
	}
}

function next_entity_order($table_name, $connection) {
	db_require_table($table_name, $connection);
	$max_order = mysql_query('SELECT MAX(`order`) FROM '.db_table_name($table_name), $connection);
	$max_order = mysql_fetch_assoc($max_order);
	$max_order = (int)$max_order['MAX(`order`)'];
	return $max_order + 1;
}

function upsert_ordered_entity($table_name, $id, $set, $connection) {
	db_require_table($table_name, $connection);
	if ($id) {
		$q = 'UPDATE '.db_table_name($table_name).' SET '.$set.' WHERE `id` = '.(int)$id;
	} else {
		$next_order = next_entity_order($table_name, $connection);
		$q = 'INSERT INTO '.db_table_name($table_name).' SET '.$set.', `order` = '.$next_order;
	}
	mysql_query($q, $connection);
	return $id ? $id : (int)mysql_insert_id($connection);
}

function upsert_unordered_entity($table_name, $id, $set, $connection) {
	db_require_table($table_name, $connection);
	if ($id) {
		$q = 'UPDATE '.db_table_name($table_name).' SET '.$set.' WHERE `id` = '.(int)$id;
	} else {
		$q = 'INSERT INTO '.db_table_name($table_name).' SET '.$set;
	}
	mysql_query($q, $connection);
	return $id ? $id : (int)mysql_insert_id($connection);
}

function delete_entity($table_name, $id, $connection) {
	db_require_table($table_name, $connection);
	mysql_query('DELETE FROM '.db_table_name($table_name).' WHERE `id` = '.(int)$id, $connection);
}