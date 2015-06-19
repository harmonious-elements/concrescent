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
	
	var calculateTextSize, fitText;
	calculateTextSize = function(text, fontFamily, fontSize, fontWeight, fontStyle) {
		var e = $('#calculateTextSize');
		if (!e.length) {
			e = $('<div id="calculateTextSize"/>');
			e.css({
				'position': 'absolute',
				'top': '0',
				'left': '0',
				'visibility': 'hidden',
				'white-space': 'nowrap'
			});
			$('body').append(e);
		}
		e.css({
			'font-family': fontFamily,
			'font-size': fontSize + 'px',
			'font-weight': fontWeight,
			'font-style': fontStyle
		});
		e.text(text);
		return [e.width(), e.height()];
	};
	fitText = function(e) {
		var text = e.text();
		var fontFamily = e.css('font-family');
		var fontWeight = e.css('font-weight');
		var fontStyle = e.css('font-style');
		var w = e.innerWidth() - 2;
		var h = e.innerHeight() - 2;
		var min = 0;
		var cur = h;
		var max = h*2;
		while (Math.abs(max-min) > 0.01) {
			var ts = calculateTextSize(text, fontFamily, cur, fontWeight, fontStyle);
			var tw = ts[0];
			var th = ts[1];
			if (tw > w || th > h) {
				// Too big, need to make it smaller.
				max = cur;
				cur = (min+max)/2;
			} else if (tw < w && th < h) {
				// Too small, need to make it bigger.
				min = cur;
				cur = (min+max)/2;
			} else {
				break;
			}
		}
		e.css('font-size', cur + 'px');
		return cur;
	};
	
	var focusedOnInput;
	focusedOnInput = function() {
		var e = document.activeElement;
		if (e) {
			var n = e.nodeName;
			if (n) {
				var nn = n.toLowerCase();
				return (nn == 'a' || nn == 'select' || nn == 'input');
			}
		}
		return false;
	};
	
	return {
		showButterbar: showButterbar,
		showButterbarPersistent: showButterbarPersistent,
		hideButterbar: hideButterbar,
		
		showDialog: showDialog,
		hideDialog: hideDialog,
		
		calculateTextSize: calculateTextSize,
		fitText: fitText,
		
		focusedOnInput: focusedOnInput,
	};
})(jQuery,window,document);