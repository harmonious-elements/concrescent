<?php
setcookie('onsite_only', '1', time()+60*60*24*30, '/');
header('Location: index.php');