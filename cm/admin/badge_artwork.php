<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/cmbase/util.php';
require_once dirname(__FILE__).'/../lib/dal/badges.php';
require_once dirname(__FILE__).'/../lib/dal/lists.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';

function render_badge_artwork($connection) {
	$results = get_entities('badge_artwork', 'filename', 0, 0, false, $connection);
	while ($result = get_entity($results)) {
		$result = decode_badge_artwork($result);
		echo render_list_row(
			array($result['filename']),
			array(
				'ea-id' => $result['id'],
				'ea-name' => $result['filename'],
			),
			/*  selectable = */ false,
			/*  switchable = */ false,
			/*      active = */ false,
			/*  deleteable = */ true,
			/* reorderable = */ false,
			/*        edit = */ ('badge_artwork_edit.php?id='.$result['id']),
			/*      review = */ false
		);
	}
}

$conn = get_db_connection();

$message = null;
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'upload':
			$message = upload_badge_artwork($_FILES['file'], $conn);
			break;
		case 'delete':
			delete_badge_artwork((int)$_POST['id'], $conn);
			render_badge_artwork($conn);
			exit(0);
			break;
	}
}

render_admin_head('Badge Artwork');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'badge_artwork.php',
	deleteable: true,
	addDialog: true,
	addDialogTitle: 'Upload Badge Artwork',
});</script><?php

render_admin_body('Badge Artwork');

if ($message) {
	echo '<div class="card">';
		echo '<div class="card-content spaced">';
			echo '<div class="notification">' . htmlspecialchars($message) . '</div>';
		echo '</div>';
	echo '</div>';
}

echo '<div class="card entity-list-card">';
render_list_table(array('File Name'), 'render_badge_artwork', true, $conn);
echo '</div>';

render_admin_dialogs();

render_delete_dialog('badge artwork', false);

echo '<div class="dialog edit-dialog hidden">';
	echo '<div class="dialog-title"></div>';
	echo '<form action="badge_artwork.php" method="post" enctype="multipart/form-data">';
		echo '<div class="dialog-content">';
			echo '<input type="hidden" name="action" value="upload">';
			echo '<label for="file">Upload image:</label>';
			echo '&nbsp;&nbsp;<input type="file" name="file" id="file">';
		echo '</div>';
		echo '<div class="dialog-buttons">';
			echo '<input type="reset" value="Cancel" class="cancel-edit-button">';
			echo '<input type="submit" value="Upload">';
		echo '</div>';
	echo '</form>';
echo '</div>';

render_admin_tail();