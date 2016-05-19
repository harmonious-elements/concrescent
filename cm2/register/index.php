<?php

require_once dirname(__FILE__).'/register.php';

$url = cm_reg_cart_count() ? 'cart.php' : 'edit.php';
header('Location: ' . $url);