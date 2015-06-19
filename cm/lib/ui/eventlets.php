<?php

require_once dirname(__FILE__).'/../base/util.php';

function render_eventlet_badge_editor() {
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
		echo '<th><label for="edit-max-staffers">Max Panelists/Hosts Per App:</label></th>';
		echo '<td><input type="number" name="edit-max-staffers" class="edit-max-staffers" min="1"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-price-per-eventlet">Price Per Application:</label></th>';
		echo '<td><input type="number" name="edit-price-per-eventlet" class="edit-price-per-eventlet" min="0" step="0.01"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-price-per-staffer">Price Per Panelist/Host:</label></th>';
		echo '<td><input type="number" name="edit-price-per-staffer" class="edit-price-per-staffer" min="0" step="0.01"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-staffers-in-eventlet-price">Panelists/Hosts Included in Application Price:</label></th>';
		echo '<td><input type="number" name="edit-staffers-in-eventlet-price" class="edit-staffers-in-eventlet-price" min="0"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-max-prereg-discount">Max Discount for Already Registered Panelists/Hosts:</label></th>';
		echo '<td>';
			echo '<select name="edit-max-prereg-discount" class="edit-max-prereg-discount">';
				echo '<option value="None">No Discount</option>';
				echo '<option value="StafferPrice">Price of Badge</option>';
				echo '<option value="EventletPrice">Price of Application</option>';
				echo '<option value="TotalPrice">Total Payment Amount</option>';
			echo '</select>';
		echo '</td>';
	echo '</tr>';
}