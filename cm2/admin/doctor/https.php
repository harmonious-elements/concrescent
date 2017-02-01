<?php

error_reporting(0);
header('Content-Type: text/plain');

if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) {
	echo 'OK HTTPS is ON. Connections to CONcrescent are secure.';
} else {
	echo 'WN HTTPS is OFF. Connections to CONcrescent are NOT secure.';
}