<?php

require_once dirname(__FILE__).'/../base/util.php';

function render_admin_user_editor($admin_links) {
	echo '<input type="hidden" name="edit-id" class="edit-id">';
	echo '<tr>';
		echo '<th><label for="edit-name">Name:</label></th>';
		echo '<td><input type="text" name="edit-name" class="edit-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-username">Username:</label></th>';
		echo '<td><input type="text" name="edit-username" class="edit-username"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-password">Password:</label></th>';
		echo '<td><input type="password" name="edit-password" class="edit-password"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label>Permissions:</label></th>';
		echo '<td>';
			foreach ($admin_links as $admin_link) {
				if (isset($admin_link['---'])) {
					echo '<hr>';
				} else {
					echo '<p><label>';
					echo '<input type="checkbox" name="edit-permissions-'.htmlspecialchars($admin_link['href']).'" class="edit-permissions">';
					echo htmlspecialchars($admin_link['text']);
					echo '</label></p>';
				}
			}
			echo '<hr><p><label><input type="checkbox" name="edit-permissions-*" class="edit-permissions">ALL</label></p>';
		echo '</td>';
	echo '</tr>';
}