<?php

error_reporting(0);
header('Content-Type: text/plain');

if (get_magic_quotes_gpc()) {
	echo 'WN Magic Quotes is ON. CONcrescent will run but not as efficiently.';
} else {
	echo 'OK Magic Quotes is OFF.';
}