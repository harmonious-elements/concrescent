<?php

require_once dirname(__FILE__).'/res.php';
require_once dirname(__FILE__).'/util.php';

function cm_list_head(&$list_def) {
	echo '<script type="text/javascript">';
		$function_names = array(
			'select-function',
			'edit-clear-function',
			'edit-load-function',
			'edit-save-function',
			'review-function'
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
				$options = array(5, 10, 20, 50, 100, 200, 500, 1000);
				$max_results = (
					( isset($list_def['max-results']) &&
					  in_array((int)$list_def['max-results'], $options) )
					? (int)$list_def['max-results'] : 20
				);
				foreach ($options as $option) {
					echo '<option value="' . $option .'"';
					if ($max_results == $option) echo ' selected';
					echo '>' . $option . '</option>';
				}
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
							case 'html-numeric':
							case 'numeric':
							case 'quantity':
							case 'price':
								echo '<th class="td-numeric">';
								break;
							default:
								echo '<th>';
								break;
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
					echo '<th colspan="' . $column_count . '" class="td-actions">';
						if (in_array('add', $list_def['table-actions'])) {
							$label = (isset($list_def['add-label']) ? htmlspecialchars($list_def['add-label']) : 'Add');
							if (isset($list_def['add-url']) && $list_def['add-url']) {
								echo '<a href="' . htmlspecialchars($list_def['add-url']) . '" target="_blank" role="button" class="button add-button">' . $label . '</a>';
							} else {
								echo '<button class="add-button">' . $label . '</button>';
							}
						}
					echo '</th>';
				echo '</tr>';
			echo '</tfoot>';
		}
	echo '</table>';
	echo '</div>';
}

function cm_list_row(&$list_def, &$entity) {
	$key = (isset($list_def['row-key']) && $list_def['row-key']) ? $entity[$list_def['row-key']] : uniqid();
	$active = (isset($list_def['active-key']) && $list_def['active-key']) ? $entity[$list_def['active-key']] : true;
	$subscribed = (isset($entity['subscribed']) && $entity['subscribed']);
	$out = ($active ? '<tr' : '<tr class="inactive"') . ' id="rowid-' . htmlspecialchars($key) . '">';
	if (isset($list_def['columns']) && $list_def['columns']) {
		foreach ($list_def['columns'] as $column) {
			$value = (isset($column['key']) && $column['key'] && isset($entity[$column['key']])) ? $entity[$column['key']] : '';
			$value1 = (isset($column['key1']) && $column['key1'] && isset($entity[$column['key1']])) ? $entity[$column['key1']] : '';
			$value2 = (isset($column['key2']) && $column['key2'] && isset($entity[$column['key2']])) ? $entity[$column['key2']] : '';
			$type = (isset($column['type']) && $column['type']) ? $column['type'] : '?';
			switch ($type) {
				case 'html'        : $out .= '<td>' . $value                   . '</td>'; break;
				case 'text'        : $out .= '<td>' . htmlspecialchars($value) . '</td>'; break;
				case 'url'         : $out .= '<td>' . url_link($value)         . '</td>'; break;
				case 'url-short'   : $out .= '<td>' . url_link_short($value)   . '</td>'; break;
				case 'email'       : $out .= '<td>' . email_link($value)       . '</td>'; break;
				case 'email-short' : $out .= '<td>' . email_link_short($value) . '</td>'; break;
				case 'date-range'  : $out .= '<td>' . date_range_string($value1, $value2)             . '</td>'; break;
				case 'age-range'   : $out .= '<td>' . age_range_string($value1, $value2)              . '</td>'; break;
				case 'array'       : $out .= '<td>' . htmlspecialchars(cm_array_string($value))       . '</td>'; break;
				case 'array-short' : $out .= '<td>' . htmlspecialchars(cm_array_string_short($value)) . '</td>'; break;
				case 'email-subbed': $out .= '<td>' . cm_email_subbed($subscribed, $value)            . '</td>'; break;
				case 'status-label': $out .= '<td>' . cm_status_label($value)                         . '</td>'; break;
				case 'html-numeric': $out .= '<td class="td-numeric">' . $value                                    . '</td>'; break;
				case 'numeric'     : $out .= '<td class="td-numeric">' . htmlspecialchars($value)                  . '</td>'; break;
				case 'quantity'    : $out .= '<td class="td-numeric">' . htmlspecialchars(quantity_string($value)) . '</td>'; break;
				case 'price'       : $out .= '<td class="td-numeric">' . htmlspecialchars(price_string($value))    . '</td>'; break;
				default            : $out .= '<td>?</td>'; break;
			}
		}
	}
	if (isset($list_def['row-actions']) && $list_def['row-actions']) {
		$out .= '<td class="td-actions">';
			if (in_array('select', $list_def['row-actions'])) {
				$label = (isset($list_def['select-label']) ? htmlspecialchars($list_def['select-label']) : 'Select');
				$out .= '<button class="select-button">' . $label . '</button>';
			}
			if (in_array('switch', $list_def['row-actions'])) {
				$class = $active ? 'deactivate' : 'activate';
				$label = $active ? 'Deactivate' : 'Activate';
				$out .= '<button class="' . $class . '-button">' . $label . '</button>';
			}
			if (in_array('edit', $list_def['row-actions'])) {
				$label = (isset($list_def['edit-label']) ? htmlspecialchars($list_def['edit-label']) : 'Edit');
				if (isset($list_def['edit-url']) && $list_def['edit-url']) {
					$out .= '<a href="' . htmlspecialchars($list_def['edit-url'] . $key) . '" target="_blank" role="button" class="button edit-button">' . $label . '</a>';
				} else {
					$out .= '<button class="edit-button">' . $label . '</button>';
				}
			}
			if (in_array('reorder', $list_def['row-actions'])) {
				$out .= '<button class="up-button">&#x2191;</button>';
				$out .= '<button class="down-button">&#x2193;</button>';
			}
			if (in_array('delete', $list_def['row-actions'])) {
				$label = (isset($list_def['delete-label']) ? htmlspecialchars($list_def['delete-label']) : 'Delete');
				$out .= '<button class="delete-button">' . $label . '</button>';
			}
			if (in_array('review', $list_def['row-actions'])) {
				$label = (isset($list_def['review-label']) ? htmlspecialchars($list_def['review-label']) : 'Review');
				if (isset($list_def['review-url']) && $list_def['review-url']) {
					$out .= '<a href="' . htmlspecialchars($list_def['review-url'] . $key) . '" target="_blank" role="button" class="button review-button">' . $label . '</a>';
				} else {
					$out .= '<button class="review-button">' . $label . '</button>';
				}
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

function cm_list_dialogs(&$list_def) {
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
	echo '<div class="dialog shortcuts-dialog hidden">';
		echo '<div class="dialog-title">Keyboard Shortcuts</div>';
		echo '<div class="dialog-content">';
			echo '<table border="0" cellpadding="0" cellspacing="0">';
				echo '<tr><th colspan="2">List Pages</th></tr>';
				echo '<tr><td><span class="kbd kbdw">esc</span></td><td>Clear and focus on search box</td></tr>';
				echo '<tr><td><span class="kbd kbdw">home</span></td><td>Go to first page of results</td></tr>';
				echo '<tr><td><span class="kbd kbdw">pgup</span></td><td>Go to previous page of results</td></tr>';
				echo '<tr><td><span class="kbd kbdw">pgdn</span></td><td>Go to next page of results</td></tr>';
				echo '<tr><td><span class="kbd kbdw">end</span></td><td>Go to last page of results</td></tr>';
				echo '<tr><td><span class="kbd">ctrl</span> <span class="kbd">shift</span> <span class="kbd">/</span></td><td>Show keyboard shortcuts</td></tr>';
				echo '<tr><td><span class="kbd">ctrl</span> <span class="kbd">shift</span> <span class="kbd">A</span></td><td>Add</td></tr>';
				echo '<tr><th colspan="2">Single Search Result</th></tr>';
				echo '<tr><td><span class="kbd">ctrl</span> <span class="kbd">shift</span> <span class="kbd">D</span></td><td>Delete</td></tr>';
				echo '<tr><td><span class="kbd">ctrl</span> <span class="kbd">shift</span> <span class="kbd">E</span></td><td>Edit</td></tr>';
				echo '<tr><td><span class="kbd">ctrl</span> <span class="kbd">shift</span> <span class="kbd">R</span></td><td>Review</td></tr>';
				echo '<tr><td><span class="kbd">ctrl</span> <span class="kbd">shift</span> <span class="kbd">S</span></td><td>Select</td></tr>';
				echo '<tr><td><span class="kbd">ctrl</span> <span class="kbd">shift</span> <span class="kbd">X</span></td><td>Activate / Deactivate</td></tr>';
				echo '<tr><th colspan="2">Dialog Boxes</th></tr>';
				echo '<tr><td><span class="kbd kbdw">esc</span></td><td>Cancel / Close</td></tr>';
				echo '<tr><td><span class="kbd">ctrl</span> <span class="kbd">shift</span> <span class="kbd">D</span></td><td>Delete</td></tr>';
				echo '<tr><td><span class="kbd">ctrl</span> <span class="kbd">shift</span> <span class="kbd">S</span></td><td>Save</td></tr>';
				echo '<tr><td><span class="kbd">ctrl</span> <span class="kbd">shift</span> <span class="kbd">X</span></td><td>Mark Inactive</td></tr>';
			echo '</table>';
		echo '</div>';
	echo '</div>';
}

function cm_list_make_row(&$list_def, &$entity) {
	$search = isset($entity['search-content']) ? $entity['search-content'] : null;
	return array(
		'entity' => $entity,
		'html' => cm_list_row($list_def, $entity),
		'search' => $search
	);
}

function cm_list_process_entities(&$list_def, &$entities) {
	$rows = array();
	foreach ($entities as $entity) {
		$rows[] = cm_list_make_row($list_def, $entity);
	}
	return array(
		'ok' => true,
		'rows' => $rows
	);
}