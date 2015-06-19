<?php

require_once dirname(__FILE__).'/registration.php';

$cart = get_cart();

if ($cart && count($cart)) {
	header('Location: cart.php');
} else {
	header('Location: register.php');
}