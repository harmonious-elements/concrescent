<?php

require_once dirname(__FILE__).'/admin.php';

cm_admin_head('Home');
cm_admin_body('Home');

echo '<article>';
	echo '<div class="card">';
		echo '<div class="card-title">Placeholder Welcome Page</div>';
		echo '<div class="card-content">';
			echo '<p>Welcome to CONcrescent.</p>';
		echo '</div>';
	echo '</div>';
echo '</article>';

cm_admin_tail();