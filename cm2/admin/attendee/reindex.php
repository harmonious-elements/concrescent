<?php

ini_set('max_execution_time', 300);
ini_set('memory_limit', '1024M');

require_once dirname(__FILE__).'/../../lib/database/attendee.php';
require_once dirname(__FILE__).'/../admin.php';

$atdb = new cm_attendee_db($db);

if (isset($_POST['action'])) {
	header('Content-type: text/plain');
	if ($_POST['action'] == 'init') {
		$_SESSION['attendee_reindex_time'] = microtime(true);
		$_SESSION['attendee_reindex_entities'] = $atdb->list_attendees();
		$_SESSION['attendee_reindex_done'] = 0;
		$_SESSION['attendee_reindex_total'] = count($_SESSION['attendee_reindex_entities']);
	}
	if ($_POST['action'] == 'drop') {
		$atdb->cm_ldb->drop_index();
	}
	if ($_POST['action'] == 'index') {
		$offset = (int)$_POST['offset'];
		$length = (int)$_POST['length'];
		$entities = array_slice($_SESSION['attendee_reindex_entities'], $offset, $length);
		foreach ($entities as $entity) {
			$atdb->cm_ldb->add_entity($entity);
			$_SESSION['attendee_reindex_done']++;
		}
	}
	$response = array(
		'ok' => true,
		'done' => $_SESSION['attendee_reindex_done'],
		'total' => $_SESSION['attendee_reindex_total'],
		'time' => microtime(true) - $_SESSION['attendee_reindex_time']
	);
	echo json_encode($response);
	if ($_POST['action'] == 'done') {
		unset($_SESSION['attendee_reindex_time']);
		unset($_SESSION['attendee_reindex_entities']);
		unset($_SESSION['attendee_reindex_done']);
		unset($_SESSION['attendee_reindex_total']);
	}
	exit(0);
}

cm_admin_head('Rebuild Attendee Search Index');

?><style>
	.progress-track {
		display: inline-block;
		position: relative;
		width: 200px;
		height: 12px;
		background: #ccf;
		border: solid 1px black;
		margin: -3px 2em -3px 2em;
	}
	.progress-bar {
		display: block;
		position: absolute;
		top: 0;
		left: 0;
		width: 0;
		bottom: 0;
		background: #444;
	}
</style><?php

?><script>
	(function($,window,document,cmui){
		var doAjax = function(request, done) {
			window.setTimeout(function() {
				$.post('reindex.php', request, function(response) {
					if (!response['ok']) {
						cmui.showButterbarPersistent('An error occurred. Please try again.');
					} else {
						var progress = Math.floor(100 * response['done'] / response['total']) + '%';
						var time = Math.round(100 * response['time']) / 100;
						$('.progress-bar').css('width', progress);
						$('.progress-label').text(progress);
						$('.time-label').text(time);
						window.setTimeout(function() {
							done(response);
						}, 10);
					}
				}, 'json');
			}, 10);
		};
		var setStatus = function(inProgress, statusLabel, showProgress) {
			if (inProgress) {
				$('.status-label').text(statusLabel);
				if (showProgress) {
					$('.progress-track').removeClass('hidden');
					$('.progress-label').removeClass('hidden');
				} else {
					$('.progress-track').addClass('hidden');
					$('.progress-label').addClass('hidden');
				}
				$('.cm-warning-box').addClass('hidden');
				$('.cm-error-box').removeClass('hidden');
				$('.cm-note-box').removeClass('hidden');
				$('.cm-success-box').addClass('hidden');
			} else {
				$('.cm-warning-box').removeClass('hidden');
				$('.cm-error-box').addClass('hidden');
				$('.cm-note-box').addClass('hidden');
				$('.cm-success-box').removeClass('hidden');
			}
		};

		var reindexInit, reindexDrop, reindexIndex, reindexDone;
		reindexInit = function() {
			$('button').unbind('click');
			$('button').prop('disabled', true);
			$(window).bind('beforeunload', function() {
				return 'Reindexing is still in progress. Leaving this page will result in a partial index.';
			});
			setStatus(true, 'Starting...', false);
			doAjax({'action': 'init'}, function(response) {
				reindexDrop();
			});
		};
		reindexDrop = function() {
			setStatus(true, 'Dropping...', false);
			doAjax({'action': 'drop'}, function(response) {
				reindexIndex(0, 100);
			});
		};
		reindexIndex = function(offset, length) {
			setStatus(true, 'Indexing...', true);
			doAjax({'action': 'index', 'offset': offset, 'length': length}, function(response) {
				if ((1 * response['done']) >= (1 * response['total'])) {
					reindexDone();
				} else {
					reindexIndex(offset + length, length);
				}
			});
		};
		reindexDone = function() {
			setStatus(true, 'Finishing...', false);
			doAjax({'action': 'done'}, function(response) {
				setStatus(false, null, false);
				$(window).unbind('beforeunload');
				$('button').prop('disabled', false);
				$('button').bind('click', reindexInit);
			});
		};

		$(document).ready(function() {
			$('button').bind('click', reindexInit);
		});
	})(jQuery,window,document,cmui);
</script><?php

cm_admin_body('Rebuild Attendee Search Index');
cm_admin_nav('attendee-reindex');

echo '<article>';
	echo '<div class="card">';
		echo '<div class="card-content">';
			echo '<p class="cm-warning-box">';
				echo 'Rebuilding the attendee search index takes on the order of minutes.';
			echo '</p>';
			echo '<p class="cm-error-box hidden">';
				echo '<b>DO NOT</b> leave this page while the reindexing is in progress.';
			echo '</p>';
			echo '<p class="cm-note-box hidden">';
				echo '<span class="status-label">Starting...</span>';
				echo '<span class="progress-track hidden"><span class="progress-bar"></span></span>';
				echo '<span class="progress-label hidden">0%</span>';
			echo '</p>';
			echo '<p class="cm-success-box hidden">';
				echo 'Attendee search index rebuilt in <b class="time-label">0</b> seconds.';
			echo '</p>';
			echo '<p>';
				echo '<button>Rebuild Attendee Search Index</button>';
			echo '</p>';
		echo '</div>';
	echo '</div>';
echo '</article>';

cm_admin_dialogs();
cm_admin_tail();