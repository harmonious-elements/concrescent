<?php

require_once dirname(__FILE__).'/../base/util.php';

function render_attendee_badge_editor() {
	echo '<input type="hidden" name="edit-id" class="edit-id">';
	echo '<tr>';
		echo '<th><label for="edit-name">Name:</label></th>';
		echo '<td><input type="text" name="edit-name" class="edit-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-description">Description:</label></th>';
		echo '<td><textarea name="edit-description" class="edit-description"></textarea></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-active">Active:</label></th>';
		echo '<td><label><input type="checkbox" name="edit-active" class="edit-active">Active</label></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-start-date">Dates Available:</label></th>';
		echo '<td>';
			echo '<input type="date" name="edit-start-date" class="edit-start-date">';
			echo '&nbsp;&nbsp;through&nbsp;&nbsp;';
			echo '<input type="date" name="edit-end-date" class="edit-end-date">';
		echo '</td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-min-age">Age Range:</label></th>';
		echo '<td>';
			echo '<input type="number" name="edit-min-age" class="edit-min-age" min="1" max="999">';
			echo '&nbsp;&nbsp;through&nbsp;&nbsp;';
			echo '<input type="number" name="edit-max-age" class="edit-max-age" min="1" max="999">';
		echo '</td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-count">Number Available:</label></th>';
		echo '<td><input type="number" name="edit-count" class="edit-count" min="1"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-price">Price:</label></th>';
		echo '<td><input type="number" name="edit-price" class="edit-price" min="0" step="0.01"></td>';
	echo '</tr>';
}

function render_promo_code_editor($badge_names) {
	echo '<input type="hidden" name="edit-id" class="edit-id">';
	echo '<tr>';
		echo '<th><label for="edit-code">Code:</label></th>';
		echo '<td><input type="text" name="edit-code" class="edit-code"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-description">Description:</label></th>';
		echo '<td><textarea name="edit-description" class="edit-description"></textarea></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-active">Active:</label></th>';
		echo '<td><label><input type="checkbox" name="edit-active" class="edit-active">Active</label></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-badge-id">Valid For:</label></th>';
		echo '<td><select name="edit-badge-id" class="edit-badge-id">';
			echo '<option value="">All</option>';
			foreach ($badge_names as $badge_id => $badge_name) {
				echo '<option value="'.$badge_id.'">'.htmlspecialchars($badge_name).'</option>';
			}
		echo '</select></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-start-date">Dates Available:</label></th>';
		echo '<td>';
			echo '<input type="date" name="edit-start-date" class="edit-start-date">';
			echo '&nbsp;&nbsp;through&nbsp;&nbsp;';
			echo '<input type="date" name="edit-end-date" class="edit-end-date">';
		echo '</td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-limit">Limit Per Customer:</label></th>';
		echo '<td><input type="number" name="edit-limit" class="edit-limit" min="1"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-price">Discount:</label></th>';
		echo '<td>';
			echo '<input type="number" name="edit-price" class="edit-price" min="0" step="0.01">';
			echo '&nbsp;&nbsp;<label><input type="radio" name="edit-percentage" value="false" class="edit-percentage-false">Fixed Amount</label>';
			echo '&nbsp;&nbsp;<label><input type="radio" name="edit-percentage" value="true" class="edit-percentage-true">Percentage</label>';
		echo '</td>';
	echo '</tr>';
}

function render_attendee_blacklist_editor() {
	echo '<input type="hidden" name="edit-id" class="edit-id">';
	echo '<tr>';
		echo '<th><label for="edit-first-name">First Name:</label></th>';
		echo '<td><input type="text" name="edit-first-name" class="edit-first-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-last-name">Last Name:</label></th>';
		echo '<td><input type="text" name="edit-last-name" class="edit-last-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-fandom-name">Fandom Name:</label></th>';
		echo '<td><input type="text" name="edit-fandom-name" class="edit-fandom-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-email-address">Email Address:</label></th>';
		echo '<td><input type="text" name="edit-email-address" class="edit-email-address"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-phone-number">Phone Number:</label></th>';
		echo '<td><input type="text" name="edit-phone-number" class="edit-phone-number"></td>';
	echo '</tr>';
}