<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../schema/schema.php';
require_once dirname(__FILE__).'/../base/sql.php';

$db_table_exists = array();

function get_db_connection() {
	global $db_host, $db_username, $db_password, $db_name, $db_time_zone;
	$connection = mysql_connect($db_host, $db_username, $db_password);
	mysql_select_db($db_name, $connection);
	mysql_query('set names utf8', $connection);
	mysql_query('set character set utf8', $connection);
	mysql_query('set time_zone = \''.$db_time_zone.'\'', $connection);
	return $connection;
}

function db_table_name($name) {
	global $db_table_exists, $db_table_prefix;
	if (!isset($db_table_exists[$name])) {
		error_log('Database table accessed without db_require_table: '.$name);
	}
	$table_name = '`' . $db_table_prefix . $name . '`';
	return $table_name;
}

function db_table_exists($name, $connection) {
	global $db_table_exists, $db_name, $db_table_prefix;
	if (isset($db_table_exists[$name]) && $db_table_exists[$name]) {
		return true;
	} else {
		$database_name = '\'' . $db_name . '\'';
		$table_name = '\'' . $db_table_prefix . $name . '\'';
		$results = mysql_query(
			('SELECT *'.
			' FROM information_schema.tables'.
			' WHERE table_schema = '.$database_name.
			' AND table_name = '.$table_name.
			' LIMIT 1'),
			$connection
		);
		$result = mysql_fetch_assoc($results);
		return ($db_table_exists[$name] = !!$result);
	}
}

function db_require_table($name, $connection) {
	global $db_schema, $db_table_prefix;
	if (!db_table_exists($name, $connection)) {
		if (isset($db_schema[$name])) {
			$table_name = '`' . $db_table_prefix . $name . '`';
			$table_schema = $db_schema[$name];
			mysql_query('CREATE TABLE IF NOT EXISTS '.$table_name.' ('.$table_schema.')', $connection);
		}
	}
}