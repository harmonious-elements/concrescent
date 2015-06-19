<?php

require_once dirname(__FILE__).'/../base/util.php';

function render_guest_badge_editor() {
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
		echo '<th><label for="edit-count">Slots Available:</label></th>';
		echo '<td><input type="number" name="edit-count" class="edit-count" min="1"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-max-supporters">Max Guests / Supporters Per App:</label></th>';
		echo '<td><input type="number" name="edit-max-supporters" class="edit-max-supporters" min="1"></td>';
	echo '</tr>';
}