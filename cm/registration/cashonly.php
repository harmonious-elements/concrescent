<?php
setcookie('payment_methods', 'cash', time()+60*60*24*30, '/');
header('Location: index.php');