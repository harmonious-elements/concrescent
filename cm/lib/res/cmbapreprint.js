function list_holders(t, badge_id, accepted_only, paid_only) {
	$('.badge-preprinting-holders').html('<tr class="loading-holders"><td colspan="3" class="loading-holders">Loading...</td></tr>');
	$('.badge-preprinting-artwork').html('');
	jQuery.post('badge_preprinting.php', {
		'action': 'list_holders',
		't': t,
		'badge_id': badge_id,
		'accepted_only': (accepted_only ? 1 : 0),
		'paid_only': (paid_only ? 1 : 0)
	}, function(r) {
		$('.badge-preprinting-holders').html(r);
	});
}
function list_artwork(t, id, badge_id) {
	$('.badge-preprinting-artwork').html('<div class="loading-artwork">Loading...</div>');
	jQuery.post('badge_preprinting.php', {
		'action': 'list_artwork',
		't': t,
		'id': id,
		'badge_id': badge_id
	}, function(r) {
		$('.badge-preprinting-artwork').html(r);
	});
}