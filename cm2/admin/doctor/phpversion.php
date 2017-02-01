<?php

error_reporting(0);
header('Content-Type: text/plain');

if (version_compare(PHP_VERSION, '5.5') >= 0) {
	echo 'OK PHP version is 5.5 or above.';
} else if (version_compare(PHP_VERSION, '5') >= 0) {
	echo 'WN PHP version is 5.0 or above, but not 5.5 or above. 5.5 or above is recommended.';
} else {
	echo 'NG PHP version is below 5.0. CONcrescent will not run. Other tests may fail or never finish.';
}