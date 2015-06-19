<?php

require_once dirname(__FILE__).'/../base/util.php';

function render_list_search($what, $class = '', $style = '') {
	echo '<div';
	if ($class) echo ' class="'.htmlspecialchars($class).'"';
	if ($style) echo ' style="'.htmlspecialchars($style).'"';
	echo '>';
		echo '<div class="search-input">';
			echo '<div class="search-input-group">';
				echo '<label>Search';
				if ($what) echo ' by '.htmlspecialchars($what);
				echo ':</label>';
				echo '<input type="text" class="search-filter">';
			echo '</div>';
		echo '</div>';
		echo '<div class="search-options">';
			echo '<div class="search-input-group">';
				echo '<button class="search-first-page">&#xAB;</button>';
				echo '<button class="search-prev-page">&#x2039;</button>';
				echo '<label>';
				echo '<b class="search-vis-start">0</b> - ';
				echo '<b class="search-vis-end">0</b> of ';
				echo '<b class="search-vis-total">0</b>';
				echo '</label>';
				echo '<button class="search-next-page">&#x203A;</button>';
				echo '<button class="search-last-page">&#xBB;</button>';
			echo '</div>';
			echo '<div class="search-input-group">';
				echo '<label>Results:</label>';
				echo '<select class="search-max-results">';
				echo '<option value="5">5</option>';
				echo '<option value="10">10</option>';
				echo '<option value="20">20</option>';
				echo '<option value="50">50</option>';
				echo '<option value="100">100</option>';
				echo '<option value="200">200</option>';
				echo '<option value="500">500</option>';
				echo '<option value="1000">1000</option>';
				echo '</select>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
}

function render_list_table($columns, $render, $add, $connection, $actions = true) {
	// Deciding behavior based on (is_array($)) and ($ === true); this is such a PHP function. ;)
	echo '<table border="0" cellpadding="0" cellspacing="0" class="entity-list">';
		echo '<thead>';
			echo '<tr>';
				foreach ($columns as $column) {
					if (is_array($column)) {
						if (isset($column['class'])) {
							echo '<th class="td-'.htmlspecialchars($column['class']).'">';
						} else {
							echo '<th>';
						}
						if (isset($column['name'])) {
							echo htmlspecialchars($column['name']);
						}
						echo '</th>';
					} else {
						echo '<th>'.htmlspecialchars($column).'</th>';
					}
				}
				if ($actions) echo '<th class="td-actions">Actions</th>';
			echo '</tr>';
		echo '</thead>';
		echo '<tbody class="questions">';
			if ($render) $render($connection);
		echo '</tbody>';
		if ($add) {
			echo '<tfoot>';
				echo '<tr>';
					echo '<td colspan="'.count($columns).'"></td>';
					echo '<td class="td-actions">';
					if ($add === true) {
						echo '<button class="add-button">Add</button>';
					} else {
						echo '<a href="'.htmlspecialchars($add).'" target="_blank" role="button" class="a-button add-button">Add</a>';
					}
					echo '</td>';
				echo '</tr>';
			echo '</tfoot>';
		}
	echo '</table>';
}

function render_list_row($columns, $fields, $selectable, $switchable, $active, $deleteable, $reorderable, $edit, $review) {
	$out = '';
	$out .= ($switchable && !$active) ? '<tr class="inactive">' : '<tr>';
	foreach ($columns as $column) {
		if (is_array($column)) {
			if (isset($column['class'])) {
				$out .= '<td class="td-'.htmlspecialchars($column['class']).'">';
			} else {
				$out .= '<td>';
			}
			if (isset($column['html'])) {
				$out .= $column['html'];
			} else if (isset($column['value'])) {
				$out .= htmlspecialchars($column['value']);
			}
			$out .= '</td>';
		} else {
			$out .= '<td>'.htmlspecialchars($column).'</td>';
		}
	}
	if (($fields && count($fields)) || $selectable || $switchable || $deleteable || $reorderable || $edit || $review) {
		$out .= '<td class="td-actions">';
		if ($fields && count($fields)) {
			foreach ($fields as $name => $value) {
				$out .= '<input type="hidden" class="'.htmlspecialchars($name).'" value="'.htmlspecialchars($value).'">';
			}
		}
		if ($selectable) {
			$out .= '<button class="select-button">Select</button>';
		}
		if ($switchable) {
			$out .= '<button class="'.($active ? 'deactivate' : 'activate').'-button">';
			$out .= $active ? 'Deactivate' : 'Activate';
			$out .= '</button>';
		}
		if ($edit) {
			if ($edit === true) {
				$out .= '<button class="edit-button">Edit</button>';
			} else {
				$out .= '<a href="'.htmlspecialchars($edit).'" target="_blank" role="button" class="a-button edit-button">Edit</a>';
			}
		}
		if ($reorderable) {
			$out .= '<button class="up-button">&#x2191;</button>';
			$out .= '<button class="down-button">&#x2193;</button>';
		}
		if ($deleteable) {
			$out .= '<button class="delete-button">Delete</button>';
		}
		if ($review) {
			if ($review === true) {
				$out .= '<button class="review-button">Review</button>';
			} else {
				$out .= '<a href="'.htmlspecialchars($review).'" target="_blank" role="button" class="a-button review-button">Review</a>';
			}
		}
		$out .= '</td>';
	}
	$out .= '</tr>';
	return $out;
}

function render_delete_dialog($type, $switchable) {
	echo '<div class="dialog delete-dialog hidden">';
		echo '<div class="dialog-title">Delete</div>';
		echo '<div class="dialog-content">';
			echo 'Are you sure you want to delete the '.htmlspecialchars($type).' <b class="delete-name"></b>?';
			echo '<br><br>This action cannot be undone.';
			if ($switchable) echo ' If you are unsure, it is better to mark it inactive.';
		echo '</div>';
		echo '<div class="dialog-buttons">';
			echo '<input type="hidden" class="delete-id">';
			echo '<button class="cancel-delete-button">Cancel</button>';
			if ($switchable) echo '<button class="soft-delete-button">Mark Inactive</button>';
			echo '<button class="confirm-delete-button">Delete</button>';
		echo '</div>';
	echo '</div>';
}

function render_edit_dialog_start() {
	echo '<div class="dialog edit-dialog hidden">';
		echo '<div class="dialog-title">Edit</div>';
		echo '<div class="dialog-content">';
			echo '<table border="0" cellpadding="0" cellspacing="0" class="form">';
}

function render_edit_dialog_end() {
			echo '</table>';
		echo '</div>';
		echo '<div class="dialog-buttons">';
			echo '<button class="cancel-edit-button">Cancel</button>';
			echo '<button class="confirm-edit-button">Save</button>';
		echo '</div>';
	echo '</div>';
}