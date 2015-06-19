<?php

$db_schema = array();

function db_schema($schema) {
	global $db_schema;
	$db_schema += $schema;
}