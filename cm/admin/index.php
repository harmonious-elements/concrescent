<?php

require_once dirname(__FILE__).'/admin.php';

render_admin_head('Home');
render_admin_body('Home');

echo '<div class="card">';
	if ($admin_links_authorized && count($admin_links_authorized)) {
		echo '<div class="card-title">Welcome to CONcrescent</div>';
		echo '<div class="card-content spaced">';
			foreach ($admin_links_authorized as $admin_link) {
				if (isset($admin_link['---'])) {
					echo '<hr>';
				} else {
					echo '<h3>';
					echo '<a href="' . htmlspecialchars($admin_link['href']) . '">';
					echo htmlspecialchars($admin_link['text']);
					echo '</a>';
					echo '</h3>';
					if (isset($admin_link['description']) && $admin_link['description']) {
						echo '<p>';
						echo htmlspecialchars($admin_link['description']);
						echo '</p>';
					}
				}
			}
		echo '</div>';
	} else {
		echo '<div class="card-content">';
			echo '<p>You do not have permission to view any pages.</p>';
		echo '</div>';
	}
echo '</div>';

render_admin_dialogs();
render_admin_tail();