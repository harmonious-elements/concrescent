<?php

require_once dirname(__FILE__).'/../lib/util/res.php';
require_once dirname(__FILE__).'/../lib/util/util.php';

function cm_admin_list_page_head(&$list_def) {
	echo '<script type="text/javascript">cm_list_def = (' . json_encode($list_def) . ');</script>';
	echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js', false)) . '"></script>';
}

function cm_admin_search_box(&$list_def) {
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

function cm_admin_list_table(&$list_def) {
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
							case 'numeric': echo '<th class="td-numeric">'; break;
							case 'price'  : echo '<th class="td-numeric">'; break;
							default       : echo '<th>'; break;
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

function cm_admin_list_row(&$list_def, &$entity) {
	/* Get key and active state */
	$key = (isset($list_def['row-key']) && $list_def['row-key']) ? $entity[$list_def['row-key']] : uniqid();
	$active = (isset($list_def['active-key']) && $list_def['active-key']) ? $entity[$list_def['active-key']] : true;
	$out = ($active ? '<tr' : '<tr class="inactive"') . ' id="rowid-' . htmlspecialchars($key) . '">';
	/* Render columns */
	if (isset($list_def['columns']) && $list_def['columns']) {
		foreach ($list_def['columns'] as $column) {
			$value = (isset($column['key']) && $column['key']) ? $entity[$column['key']] : '?';
			$type = (isset($column['type']) && $column['type']) ? $column['type'] : '?';
			switch ($type) {
				case 'html'       : $out .= '<td>' . $value                   . '</td>'; break;
				case 'text'       : $out .= '<td>' . htmlspecialchars($value) . '</td>'; break;
				case 'url'        : $out .= '<td>' . url_link($value)         . '</td>'; break;
				case 'url-short'  : $out .= '<td>' . url_link_short($value)   . '</td>'; break;
				case 'email'      : $out .= '<td>' . email_link($value)       . '</td>'; break;
				case 'email-short': $out .= '<td>' . email_link_short($value) . '</td>'; break;
				case 'numeric'    : $out .= '<td class="td-numeric">' . htmlspecialchars($value)               . '</td>'; break;
				case 'price'      : $out .= '<td class="td-numeric">' . htmlspecialchars(price_string($value)) . '</td>'; break;
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