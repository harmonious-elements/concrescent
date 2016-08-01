(function($,window,document,cmui){
	var doAjax = function(request, done) {
		window.setTimeout(function() {
			$.post('', request, function(response) {
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
			reindexIndex(0, 50);
		});
	};
	reindexIndex = function(offset, length) {
		setStatus(true, 'Indexing...', true);
		doAjax({
			'action': 'index',
			'offset': offset,
			'length': length
		}, function(response) {
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