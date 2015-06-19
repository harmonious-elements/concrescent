entityPage = (function($,document){
	return function() {
		$(document).ready(function() {
			$('.subhead').each(function() {
				var head = $(this);
				var body = $('.' + head.attr('id'));
				var tri = $('<div/>').text(body.hasClass('hidden') ? '\u25B8' : '\u25BE');
				head.addClass('toggle-collapsed');
				head.append(tri);
				head.click(function() {
					body.toggleClass('hidden');
					tri.text(body.hasClass('hidden') ? '\u25B8' : '\u25BE');
				});
			});
			$('body').bind('keydown', function(event) {
				if (event.which == 27) {
					window.close();
					event.stopPropagation();
					event.preventDefault();
				}
			});
		});
	};
})(jQuery,document);