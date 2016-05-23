<?php
setcookie('onsite_only', '', time()-3600, '/');
header('Location: index.php');