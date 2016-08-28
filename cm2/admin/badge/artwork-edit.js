(function($,window,document,cmui,fieldKeys,artwork){
	var getXY = function(self, event) {
		var offset = self.offset();
		var x = (event.pageX - offset.left) / (self.innerWidth() - 1);
		var y = (event.pageY - offset.top) / (self.innerHeight() - 1);
		if (x < 0) x = 0; if (x > 1) x = 1;
		if (y < 0) y = 0; if (y > 1) y = 1;
		return [x, y];
	};
	var setRect = function(element, x1, y1, x2, y2) {
		element.css('top', (Math.min(y1, y2) * 100) + '%');
		element.css('left', (Math.min(x1, x2) * 100) + '%');
		element.css('right', ((1 - Math.max(x1, x2)) * 100) + '%');
		element.css('bottom', ((1 - Math.max(y1, y2)) * 100) + '%');
	};
})(jQuery,window,document,cmui,cm_badge_artwork_field_keys,cm_badge_artwork);