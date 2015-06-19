$(document).ready(function() {
	var openTagForm = function(event) {
		var isOpen = !$('.tag-form').hasClass('hidden') || !$('.untag-form').hasClass('hidden');
		$('.tag-form').addClass('hidden');
		$('.untag-form').addClass('hidden');
		if (!isOpen) {
			var self = $(this);
			var offset = self.offset();
			var x = (event.pageX - offset.left) * 100.0 / (self.innerWidth() - 1);
			var y = (event.pageY - offset.top) * 100.0 / (self.innerHeight() - 1);
			var form = $('.tag-form');
			form.attr('style', 'left: '+x+'%; top: '+y+'%;');
			form.find('.tag-form-x').val(x);
			form.find('.tag-form-y').val(y);
			var input = form.find('.tag-form-input');
			input.val('');
			form.removeClass('hidden');
			input.focus();
		}
		event.stopPropagation();
		event.preventDefault();
	};
	var openUntagForm = function(event) {
		var isOpen = !$('.tag-form').hasClass('hidden') || !$('.untag-form').hasClass('hidden');
		$('.tag-form').addClass('hidden');
		$('.untag-form').addClass('hidden');
		if (!isOpen) {
			var self = $(this);
			var form = $('.untag-form');
			form.attr('style', self.attr('style'));
			form.find('.untag-form-id').val(self.text());
			form.removeClass('hidden');
		}
		event.stopPropagation();
		event.preventDefault();
	};
	var cancelForm = function(event) {
		$('.tag-form').addClass('hidden');
		$('.untag-form').addClass('hidden');
		event.stopPropagation();
		event.preventDefault();
	};
	var acceptTagForm = function(event) {
		var form = $('.tag-form');
		if (!form.hasClass('hidden')) {
			var name = form.find('.tag-form-input').val();
			if (name) {
				var x = form.find('.tag-form-x').val();
				var y = form.find('.tag-form-y').val();
				jQuery.post('booth_tables.php', {
					'action': 'tag',
					'id': name,
					'x': x,
					'y': y,
				}, function(r) {
					$('.tags').html(r);
					$('.tag').mousedown(openUntagForm);
					form.addClass('hidden');
					jQuery.post('booth_tables.php', { 'action': 'assignments' }, function(r) {
						$('.assignments-div').html(r);
					});
				});
			} else {
				form.addClass('hidden');
			}
		}
		event.stopPropagation();
		event.preventDefault();
	};
	var acceptUntagForm = function(event) {
		var form = $('.untag-form');
		if (!form.hasClass('hidden')) {
			var name = form.find('.untag-form-id').val();
			if (name) {
				jQuery.post('booth_tables.php', {
					'action': 'untag',
					'id': name,
				}, function(r) {
					$('.tags').html(r);
					$('.tag').mousedown(openUntagForm);
					form.addClass('hidden');
					jQuery.post('booth_tables.php', { 'action': 'assignments' }, function(r) {
						$('.assignments-div').html(r);
					});
				});
			} else {
				form.addClass('hidden');
			}
		}
		event.stopPropagation();
		event.preventDefault();
	};
	$('.tag-map').mousedown(openTagForm);
	$('.tag').mousedown(openUntagForm);
	$('body').keydown(function(event) {
		switch (event.which) {
			case 27:
				cancelForm(event);
				break;
			case 10: case 13:
				acceptTagForm(event);
				break;
			case 8: case 46: case 127:
				acceptUntagForm(event);
				break;
		}
	});
	$('.untag-form button').mousedown(function(event) { event.stopPropagation(); });
	$('.untag-form button').click(acceptUntagForm);
});