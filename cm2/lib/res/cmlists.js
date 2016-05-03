(function($,window,document,cmui,listdef){
	var naturalTokenize = function(text) {
		var tokens = [];
		var token = '';
		var tokenNumeric;
		for (var i = 0, ch = text.charAt(0); ch; ch = text.charAt(++i)) {
			var cp = ch.charCodeAt(0);
			var numeric = (cp >= 48 && cp <= 57);
			if (token) {
				if (tokenNumeric == numeric) {
					token += ch;
					continue;
				}
				tokens.push(token);
			}
			token = ch;
			tokenNumeric = numeric;
		}
		if (token) tokens.push(token);
		return tokens;
	};
	var naturalCompare = function(a, b) {
		var aa = naturalTokenize(String(a).toLowerCase());
		var bb = naturalTokenize(String(b).toLowerCase());
		for (var i = 0; aa[i] && bb[i]; ++i) {
			if (aa[i] != bb[i]) {
				var an = Number(aa[i]);
				var bn = Number(bb[i]);
				if (isFinite(an) && isFinite(bn)) {
					return (an < bn) ? -1 : 1;
				} else {
					return (aa[i] < bb[i]) ? -1 : 1;
				}
			}
		}
		return aa.length - bb.length;
	};
	var numericCompare = function(a, b) {
		var an = Number(a), bn = Number(b);
		return (an < bn) ? -1 : (an > bn) ? 1 : 0;
	};
	var sortFunctions = {
		'text'       : naturalCompare,
		'url'        : naturalCompare,
		'url-short'  : naturalCompare,
		'email'      : naturalCompare,
		'email-short': naturalCompare,
		'numeric'    : numericCompare,
		'price'      : numericCompare
	};
	var isSortable = function() {
		if (listdef['row-actions']) {
			if (listdef['row-actions'].indexOf('reorder') >= 0) {
				return false;
			}
		}
		if (listdef['columns']) {
			for (var i = 0, n = listdef['columns'].length; i < n; ++i) {
				var type = listdef['columns'][i]['type'];
				if (type && sortFunctions[type]) {
					return true;
				}
			}
		}
		return false;
	};
	$(document).ready(function() {
		var rows = [];
		var matches = [];
		var sortable = isSortable();
		var sortOrder = [];
		var offset = 0;
		var visible = [];
		var html = '';
		var doFilter = function() {
			/* Filter */
			var filterText = ($('.cm-search-input input').val() || '').trim().toLowerCase();
			if (filterText) {
				matches = [];
				var rowMatches = function(row) {
					if (row['search']) {
						for (var i = 0, n = row['search'].length; i < n; ++i) {
							var rowText = String(row['search'][i]).trim().toLowerCase();
							if (rowText.indexOf(filterText) >= 0) return true;
						}
					}
					return false;
				};
				for (var i = 0, n = rows.length; i < n; ++i) {
					if (rowMatches(rows[i])) {
						matches.push(rows[i]);
					}
				}
			} else {
				matches = rows.slice();
			}
			/* Sort */
			if (sortable && sortOrder && sortOrder.length) {
				for (var i = 0, n = sortOrder.length; i < n; ++i) {
					var columnIndex = sortOrder[i];
					var descending = (columnIndex < 0);
					if (descending) columnIndex = ~columnIndex;
					var column = listdef['columns'][columnIndex];
					var compare = sortFunctions[column['type']];
					var key = column['key'];
					matches.sort(function(a, b) {
						var av = a['entity'][key];
						var bv = b['entity'][key];
						var cmp = compare(av, bv);
						if (descending) cmp = -cmp;
						return cmp;
					});
				}
			}
		};
		var doPage = function() {
			/* Page */
			var maxResults = (1 * $('.cm-search-max-results').val());
			if (maxResults) {
				visible = matches.slice(offset, offset + maxResults);
				$('.cm-search-vis-start').text(offset + (visible.length ? 1 : 0));
				$('.cm-search-vis-end').text(offset + visible.length);
			} else {
				visible = matches.slice();
				$('.cm-search-vis-start').text(visible.length ? 1 : 0);
				$('.cm-search-vis-end').text(visible.length);
			}
			$('.cm-search-vis-total').text(matches.length);
			/* Render */
			html = '';
			for (var i = 0, n = visible.length; i < n; ++i) {
				html += visible[i]['html'];
			}
			$('.cm-list-table tbody').html(html);
		};
		var nextId = ('start-id' in listdef) ? listdef['start-id'] : 1;
		var doLoad = function() {
			var entityType = listdef['entity-type'];
			cmui.showButterbar(entityType ? ('Loading ' + entityType + '...') : 'Loading...');
			var ajaxUrl = (listdef['ajax-url'] || '');
			$.post(ajaxUrl, {'cm-list-action': 'list', 'cm-list-start-id': nextId}, function(data) {
				if (!data['ok']) {
					cmui.showButterbarPersistent('An error occurred. Please reload the page.');
				} else {
					var hasRows = (data['rows'] && data['rows'].length);
					if (hasRows) {
						rows = rows.concat(data['rows']);
						doFilter();
						doPage();
					} else {
						cmui.hideButterbar();
					}
					nextId = data['next-start-id'];
					if (nextId) {
						setTimeout(doLoad, hasRows ? 10 : 5000);
					} else {
						cmui.hideButterbar();
					}
				}
			}, 'json');
		};

		/* Search Text Field */
		var searchField = $('.cm-search-input input');
		var searchFieldOldVal = searchField.val();
		var searchFieldChanged = function() {
			var searchFieldNewVal = searchField.val();
			if (searchFieldNewVal != searchFieldOldVal) {
				searchFieldOldVal = searchFieldNewVal;
				doFilter();
				offset = 0;
				doPage();
			}
		};
		searchField.bind('change', searchFieldChanged);
		searchField.bind('keydown', searchFieldChanged);
		searchField.bind('keyup', searchFieldChanged);
		/* Prev/Next Buttons */
		var firstPageButton = $('.cm-search-first-page');
		var prevPageButton = $('.cm-search-prev-page');
		var nextPageButton = $('.cm-search-next-page');
		var lastPageButton = $('.cm-search-last-page');
		firstPageButton.bind('click', function() {
			offset = 0;
			doPage();
		});
		prevPageButton.bind('click', function() {
			var maxResults = (1 * $('.cm-search-max-results').val());
			offset = (maxResults && (offset > maxResults)) ? (offset - maxResults) : 0;
			doPage();
		});
		nextPageButton.bind('click', function() {
			var maxResults = (1 * $('.cm-search-max-results').val());
			offset = maxResults ? (((offset + maxResults) < matches.length) ? (offset + maxResults) : offset) : 0;
			doPage();
		});
		lastPageButton.bind('click', function() {
			var maxResults = (1 * $('.cm-search-max-results').val());
			offset = maxResults ? (Math.floor((matches.length - 1) / maxResults) * maxResults) : 0;
			doPage();
		});
		/* Result Count Selector */
		var maxResultsField = $('.cm-search-max-results');
		var maxResultsFieldOldVal = maxResultsField.val();
		var maxResultsFieldChanged = function() {
			var maxResultsFieldNewVal = maxResultsField.val();
			if (maxResultsFieldNewVal != maxResultsFieldOldVal) {
				maxResultsFieldOldVal = maxResultsFieldNewVal;
				offset = 0;
				doPage();
			}
		};
		maxResultsField.bind('change', maxResultsFieldChanged);
		maxResultsField.bind('keydown', maxResultsFieldChanged);
		maxResultsField.bind('keyup', maxResultsFieldChanged);
		maxResultsField.bind('mousedown', maxResultsFieldChanged);
		maxResultsField.bind('mouseup', maxResultsFieldChanged);
		/* Keyboard Navigation */
		$('body').bind('keydown', function(event) {
			if (event.which == 27) {
				searchField.val('');
				searchFieldChanged();
				searchField.focus();
				event.stopPropagation();
				event.preventDefault();
			}
			if (event.which == 33) {
				prevPageButton.click();
				event.stopPropagation();
				event.preventDefault();
			}
			if (event.which == 34) {
				nextPageButton.click();
				event.stopPropagation();
				event.preventDefault();
			}
			if (event.which == 35) {
				lastPageButton.click();
				event.stopPropagation();
				event.preventDefault();
			}
			if (event.which == 36) {
				firstPageButton.click();
				event.stopPropagation();
				event.preventDefault();
			}
		});
		/* Sort Headers */
		if (sortable) {
			var makeSortFunction = function(index) {
				return function() {
					if (!sortOrder || !sortOrder.length) {
						sortOrder = [index];
					} else if (sortOrder[sortOrder.length - 1] == index) {
						sortOrder[sortOrder.length - 1] = ~index;
					} else if (sortOrder[sortOrder.length - 1] == ~index) {
						sortOrder[sortOrder.length - 1] = index;
					} else {
						var oa = sortOrder.indexOf(index);
						if (oa >= 0) sortOrder.splice(oa, 1);
						var od = sortOrder.indexOf(~index);
						if (od >= 0) sortOrder.splice(od, 1);
						sortOrder.push(index);
					}
					$('.cm-list-table thead th').removeClass('th-sort-ascending');
					$('.cm-list-table thead th').removeClass('th-sort-descending');
					$('.cm-list-table thead th').removeClass('th-sort-primary');
					for (var i = 0, n = sortOrder.length; i < n; ++i) {
						var columnIndex = sortOrder[i];
						var descending = (columnIndex < 0);
						if (descending) columnIndex = ~columnIndex;
						var header = $('.cm-list-table thead th:eq(' + columnIndex + ')');
						header.addClass(descending ? 'th-sort-descending' : 'th-sort-ascending');
						if ((i + 1) == n) header.addClass('th-sort-primary');
					}
					doFilter();
					offset = 0;
					doPage();
				};
			};
			for (var i = 0, n = listdef['columns'].length; i < n; ++i) {
				var type = listdef['columns'][i]['type'];
				if (type && sortFunctions[type]) {
					var header = $('.cm-list-table thead th:eq(' + i + ')');
					header.addClass('th-sortable');
					header.bind('click', makeSortFunction(i));
				}
			}
		}

		doLoad();
	});
})(jQuery,window,document,cmui,cm_list_def);