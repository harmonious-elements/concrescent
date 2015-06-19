<?php

require_once dirname(__FILE__).'/util.php';

function purify_string($x) {
	$special = array("=", "\\'", "\\\"", "\\\\", "'", "\"", "\\", "%", "_", "?", "*");
	$rep = array("=E=", "=A=", "=Q=", "=B=", "=A=", "=Q=", "=B=", "=P=", "=U=", "=H=", "=S=");
	return str_replace($special, $rep, $x);
}

function unpurify_string($x) {
	$rep = array("=B==B=", "=B==Q=", "=B==A=", "=A=", "=Q=", "=B=", "=P=", "=U=", "=H=", "=S=", "=E=");
	$special = array("\\", "\"", "'", "'", "\"", "\\", "%", "_", "?", "*", "=");
	return str_replace($rep, $special, $x);
}

function q_string($x) {
	return '\'' . purify_string($x) . '\'';
}

function q_string_or_null($x) {
	if ($x) {
		return '\'' . purify_string($x) . '\'';
	} else {
		return 'NULL';
	}
}

function q_int($x) {
	return (int)$x;
}

function q_int_or_null($x) {
	if ((int)$x) {
		return (int)$x;
	} else {
		return 'NULL';
	}
}

function q_float($x) {
	return (float)$x;
}

function q_float_or_null($x) {
	if ($x) {
		return (float)$x;
	} else {
		return 'NULL';
	}
}

function q_boolean($x) {
	return $x ? 'TRUE' : 'FALSE';
}

function q_date($x) {
	$x = trim($x);
	if ($x) {
		if ($x == 'NOW()') return 'NOW()';
		$x = parse_date($x);
		if ($x) return '\'' . $x . '\'';
	}
	return '\'\'';
}

function q_date_or_null($x) {
	$x = trim($x);
	if ($x) {
		if ($x == 'NOW()') return 'NOW()';
		$x = parse_date($x);
		if ($x) return '\'' . $x . '\'';
	}
	return 'NULL';
}