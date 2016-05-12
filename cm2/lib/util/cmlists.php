<?php

require_once dirname(__FILE__).'/res.php';
require_once dirname(__FILE__).'/util.php';

function cm_list_head(&$list_def) {
	echo '<script type="text/javascript">';
		$function_names = array(
			'select-function',
			'edit-clear-function',
			'edit-load-function',
			'edit-save-function'
		);
		$function_bodies = array();
		foreach ($function_names as $k) {
			if (isset($list_def[$k])) {
				$function_bodies[$k] = $list_def[$k];
				unset($list_def[$k]);
			}
		}
		echo 'cm_list_def = (' . json_encode($list_def) . ');';
		foreach ($function_bodies as $k => $v) {
			echo " cm_list_def['" . $k . "'] = (" . $v . ');';
		}
	echo '</script>';
	echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js', false)) . '"></script>';
}

function cm_list_search_box(&$list_def) {
	echo '<div class="cm-search-box">';
		echo '<div class="cm-search-input">';
			echo '<label for="cm-search-input">Search';
			if (isset($list_def['search-criteria']) && $list_def['search-criteria']) {
				echo ' by ' . htmlspecialchars($list_def['search-criteria']);
			}
			echo ':</label>';
			echo '<input type="text" name="cm-search-input" id="cm-search-input">';
		echo '</div>';
		echo '<div class="cm-search-options">';
			echo '<button class="cm-search-first-page">&#xAB;</button>';
			echo '<button class="cm-search-prev-page">&#x2039;</button>';
			echo '<label>';
			echo '<b class="cm-search-vis-start">0</b> - ';
			echo '<b class="cm-search-vis-end">0</b> of ';
			echo '<b class="cm-search-vis-total">0</b>';
			echo '</label>';
			echo '<button class="cm-search-next-page">&#x203A;</button>';
			echo '<button class="cm-search-last-page">&#xBB;</button>';
			echo '<label>Results:</label>';
			echo '<select class="cm-search-max-results">';
				echo '<option value="5">5</option>';
				echo '<option value="10">10</option>';
				echo '<option value="20" selected>20</option>';
				echo '<option value="50">50</option>';
				echo '<option value="100">100</option>';
				echo '<option value="200">200</option>';
				echo '<option value="500">500</option>';
				echo '<option value="1000">1000</option>';
			echo '</select>';
		echo '</div>';
	echo '</div>';
}

function cm_list_table(&$list_def) {
	echo '<div class="cm-list-table">';
	echo '<table border="0" cellpadding="0" cellspacing="0">';
		$column_count = 0;
		echo '<thead>';
			echo '<tr>';
				if (isset($list_def['columns']) && $list_def['columns']) {
					foreach ($list_def['columns'] as $column) {
						$column_count++;
						$name = (isset($column['name']) && $column['name']) ? $column['name'] : '?';
						$type = (isset($column['type']) && $column['type']) ? $column['type'] : '?';
						switch ($type) {
							case 'numeric' : echo '<th class="td-numeric">'; break;
							case 'quantity': echo '<th class="td-numeric">'; break;
							case 'price'   : echo '<th class="td-numeric">'; break;
							default        : echo '<th>'; break;
						}
						echo htmlspecialchars($name);
						echo '</th>';
					}
				}
				if (isset($list_def['row-actions']) && $list_def['row-actions']) {
					$column_count++;
					echo '<th class="td-actions">Actions</th>';
				}
			echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '</tbody>';
		if (isset($list_def['table-actions']) && $list_def['table-actions']) {
			echo '<tfoot>';
				echo '<tr>';
					echo '<th colspan="' . $column_count . '">';
						if (in_array('add', $list_def['table-actions'])) {
							echo '<button class="add-button">Add</button>';
						}
					echo '</th>';
				echo '</tr>';
			echo '</tfoot>';
		}
	echo '</table>';
	echo '</div>';
}

function cm_list_row(&$list_def, &$entity) {
	/* Get key and active state */
	$key = (isset($list_def['row-key']) && $list_def['row-key']) ? $entity[$list_def['row-key']] : uniqid();
	$active = (isset($list_def['active-key']) && $list_def['active-key']) ? $entity[$list_def['active-key']] : true;
	$out = ($active ? '<tr' : '<tr class="inactive"') . ' id="rowid-' . htmlspecialchars($key) . '">';
	/* Render columns */
	if (isset($list_def['columns']) && $list_def['columns']) {
		foreach ($list_def['columns'] as $column) {
			$value = (isset($column['key']) && $column['key']) ? $entity[$column['key']] : '?';
			$value1 = (isset($column['key1']) && $column['key1']) ? $entity[$column['key1']] : '?';
			$value2 = (isset($column['key2']) && $column['key2']) ? $entity[$column['key2']] : '?';
			$type = (isset($column['type']) && $column['type']) ? $column['type'] : '?';
			switch ($type) {
				case 'html'       : $out .= '<td>' . $value                   . '</td>'; break;
				case 'text'       : $out .= '<td>' . htmlspecialchars($value) . '</td>'; break;
				case 'url'        : $out .= '<td>' . url_link($value)         . '</td>'; break;
				case 'url-short'  : $out .= '<td>' . url_link_short($value)   . '</td>'; break;
				case 'email'      : $out .= '<td>' . email_link($value)       . '</td>'; break;
				case 'email-short': $out .= '<td>' . email_link_short($value) . '</td>'; break;
				case 'date-range' : $out .= '<td>' . date_range_string($value1, $value2) . '</td>'; break;
				case 'age-range'  : $out .= '<td>' . age_range_string($value1, $value2)  . '</td>'; break;
				case 'numeric'    : $out .= '<td class="td-numeric">' . htmlspecialchars($value)                                 . '</td>'; break;
				case 'quantity'   : $out .= '<td class="td-numeric">' . htmlspecialchars(is_null($value) ? 'unlimited' : $value) . '</td>'; break;
				case 'price'      : $out .= '<td class="td-numeric">' . htmlspecialchars(price_string($value))                   . '</td>'; break;
				default           : $out .= '<td>?</td>'; break;
			}
		}
	}
	/* Render action buttons */
	if (isset($list_def['row-actions']) && $list_def['row-actions']) {
		$out .= '<td class="td-actions">';
			if (in_array('select', $list_def['row-actions'])) {
				$out .= '<button class="select-button">Select</button>';
			}
			if (in_array('switch', $list_def['row-actions'])) {
				$class = $active ? 'deactivate' : 'activate';
				$label = $active ? 'Deactivate' : 'Activate';
				$out .= '<button class="' . $class . '-button">' . $label . '</button>';
			}
			if (in_array('edit', $list_def['row-actions'])) {
				$out .= '<button class="edit-button">Edit</button>';
			}
			if (in_array('reorder', $list_def['row-actions'])) {
				$out .= '<button class="up-button">&#x2191;</button>';
				$out .= '<button class="down-button">&#x2193;</button>';
			}
			if (in_array('delete', $list_def['row-actions'])) {
				$out .= '<button class="delete-button">Delete</button>';
			}
			if (in_array('review', $list_def['row-actions'])) {
				$out .= '<button class="review-button">Review</button>';
			}
		$out .= '</td>';
	}
	$out .= '</tr>';
	return $out;
}

function cm_list_edit_dialog_start() {
	echo '<div class="dialog edit-dialog hidden">';
		echo '<div class="dialog-title">Edit</div>';
		echo '<div class="dialog-content">';
}

function cm_list_edit_dialog_end() {
		echo '</div>';
		echo '<div class="dialog-buttons">';
			echo '<button class="cancel-edit-button">Cancel</button>';
			echo '<button class="confirm-edit-button">Save</button>';
		echo '</div>';
	echo '</div>';
}

function cm_list_delete_dialog(&$list_def) {
	$type = (isset($list_def['entity-type']) && $list_def['entity-type']) ? $list_def['entity-type'] : 'item';
	$switchable = (isset($list_def['row-actions']) && $list_def['row-actions'] && in_array('switch', $list_def['row-actions']));
	echo '<div class="dialog delete-dialog hidden">';
		echo '<div class="dialog-title">Delete</div>';
		echo '<div class="dialog-content">';
			echo '<p>';
				echo 'Are you sure you want to delete the ';
				echo htmlspecialchars($type);
				echo ' <b class="delete-name"></b>?';
			echo '</p>';
			echo '<p>';
				echo 'This action cannot be undone.';
				if ($switchable) echo ' If you are unsure, it is better to mark it inactive.';
			echo '</p>';
		echo '</div>';
		echo '<div class="dialog-buttons">';
			echo '<button class="cancel-delete-button">Cancel</button>';
			if ($switchable) echo '<button class="soft-delete-button">Mark Inactive</button>';
			echo '<button class="confirm-delete-button">Delete</button>';
		echo '</div>';
	echo '</div>';
}