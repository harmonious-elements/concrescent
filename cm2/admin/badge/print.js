(function($,window,document,cmui,globalConfig,localConfig,artwork,entity){
	$(document).ready(function() {
		setTimeout(function() {
			$('.field').each(function() {
				var self = $(this);
				var id = 1 * self.attr('id').substring(6);
				var size = cmui.fitText(self);
				artwork['fields'][id]['font-size'] = size;
			});
			setTimeout(function() {
				if (localConfig['post-url']) {
					$.post(
						localConfig['post-url'], {
							'global-config': JSON.stringify(globalConfig),
							'local-config': JSON.stringify(localConfig),
							'artwork': JSON.stringify(artwork),
							'entity': JSON.stringify(entity)
						}, function(response) {
							var i = setInterval(function(){ window.close(); }, 100);
							window.cm_stfu = function(){ clearInterval(i); };
						}, 'json'
					);
				} else {
					window.print();
					var i = setInterval(function(){ window.close(); }, 100);
					window.cm_stfu = function(){ clearInterval(i); };
				}
			}, 100);
		}, 100);
	});
})(jQuery,window,document,cmui,cm_print_global_config,
cm_print_local_config,cm_print_artwork,cm_print_entity);