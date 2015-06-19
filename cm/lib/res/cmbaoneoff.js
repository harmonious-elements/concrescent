var selected_badge_id;
function set_print_url() {
	var data = {};
	$('.badge-info-tbody input').each(function() {
		var e = $(this);
		var id = e.attr('id');
		var val = e.val();
		data[id] = val;
	});
	data['age'] = $('input[name=age]:checked').val();
	data = encodeURIComponent(JSON.stringify(data));
	var href = 'badge_print.php?ba=' + selected_badge_id + '&data=' + data;
	$('.print-button').attr('href', href);
}
function list_fields(ba) {
	selected_badge_id = ba;
	$('.artwork').removeClass('active');
	$('.artwork.ba'+ba).addClass('active');
	$('.badge-info').addClass('hidden');
	$('.badge-info-tbody input').unbind('change').unbind('keydown').unbind('keyup');
	$('input[name=age]').unbind('change').unbind('keydown').unbind('keyup').unbind('mousedown').unbind('mouseup');
	$('.badge-info-tbody').html('');
	jQuery.post('badge_oneoffprinting.php', {
		'action': 'list_fields',
		'ba': ba
	}, function(r) {
		$('.badge-info-tbody').html(r);
		$('.badge-info-tbody input').bind('change', set_print_url).bind('keydown', set_print_url).bind('keyup', set_print_url);
		$('input[name=age]').bind('change', set_print_url).bind('keydown', set_print_url).bind('keyup', set_print_url).bind('mousedown', set_print_url).bind('mouseup', set_print_url);
		set_print_url();
		$('.badge-info').removeClass('hidden');
	});
}