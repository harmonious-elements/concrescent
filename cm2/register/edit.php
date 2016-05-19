<?php

require_once dirname(__FILE__).'/register.php';

if (isset($_GET['index'])) {
	$new = false;
	$index = (int)$_GET['index'];
} else {
	$new = true;
	$index = -1;
}

cm_reg_head($new ? 'Add Badge' : 'Edit Badge');
cm_reg_body($new ? 'Add Badge' : 'Edit Badge');

echo '<article>';
echo '<div class="card"><div class="card-content"><p>Content goes here.</p></div></div>';
echo '</article>';

cm_reg_tail();