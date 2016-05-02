cmui = (function($,window,document){
	var butterbarTimeout, showButterbar, showButterbarPersistent, hideButterbar;
	butterbarTimeout = null;
	showButterbar = function(text) {
		if (butterbarTimeout) clearTimeout(butterbarTimeout);
		butterbarTimeout = setTimeout(function() {
			$('.butterbar').text(text || 'Working...');
			$('.butterbar').removeClass('hidden');
		}, 1000);
	};
	showButterbarPersistent = function(text) {
		if (butterbarTimeout) clearTimeout(butterbarTimeout);
		$('.butterbar').text(text || 'Done.');
		$('.butterbar').removeClass('hidden');
		butterbarTimeout = setTimeout(hideButterbar, 5000);
	};
	hideButterbar = function() {
		if (butterbarTimeout) {
			clearTimeout(butterbarTimeout);
			butterbarTimeout = null;
		}
		$('.butterbar').addClass('hidden');
	};
	
	var showDialog, hideDialog, escapeDialog;
	showDialog = function(name) {
		$('.dialog-cover').removeClass('hidden');
		$('.dialog').addClass('hidden');
		$('.'+name+'-dialog').removeClass('hidden');
		$('body').bind('keydown', escapeDialog);
	};
	hideDialog = function() {
		$('.dialog').addClass('hidden');
		$('.dialog-cover').addClass('hidden');
		$('body').unbind('keydown', escapeDialog);
	};
	escapeDialog = function(event) {
		if (event.which == 27) {
			hideDialog();
			event.stopPropagation();
			event.preventDefault();
		}
	};
	
	return {
		showButterbar: showButterbar,
		showButterbarPersistent: showButterbarPersistent,
		hideButterbar: hideButterbar,
		showDialog: showDialog,
		hideDialog: hideDialog,
	};
})(jQuery,window,document);