(function($,window,document,cmui,listdef){

	/* Sorting Utilities */

	var naturalTokenize = function(text) {
		var tokens = [];
		var re = /[0-9]+|[^0-9]+/g;
		var m = re.exec(text);
		while (m) {
			tokens.push(m[0]);
			m = re.exec(text);
		}
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
		var an = Number(a);
		var bn = Number(b);
		return (an < bn) ? -1 : (an > bn) ? 1 : 0;
	};
	var sortFunctions = {
		'text'        : naturalCompare,
		'url'         : naturalCompare,
		'url-short'   : naturalCompare,
		'email'       : naturalCompare,
		'email-short' : naturalCompare,
		'email-subbed': naturalCompare,
		'status-label': naturalCompare,
		'numeric'     : numericCompare,
		'quantity'    : numericCompare,
		'price'       : numericCompare
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

	/* Filtering Utilities */

	var queryTokenChar = function(ch) {
		if ('()|:=<>'.indexOf(ch) >= 0) return false;
		if (ch == '"') return false;
		var cp = ch.charCodeAt(0);
		if (cp <= 32) return false;
		if (cp < 127) return true;
		if (cp <= 160) return false;
		return true;
	};
	var queryTokenize = function(text) {
		var tokens = [];
		for (var i = 0, ch = text.charAt(0); ch; ch = text.charAt(++i)) {
			if ('()|:=<>-'.indexOf(ch) >= 0) {
				tokens.push([ch]);
			} else if (ch == '"') {
				var j = i + 1;
				while (text.charAt(j) && text.charAt(j) != '"') ++j;
				tokens.push([ch, text.substring(i + 1, j)]);
				i = j;
			} else if (queryTokenChar(ch)) {
				var j = i + 1;
				while (text.charAt(j) && queryTokenChar(text.charAt(j))) ++j;
				tokens.push(['"', text.substring(i, j)]);
				i = j - 1;
			}
		}
		return tokens;
	};
	var queryParse = function(text) {
		var tokens = queryTokenize(text);
		var i = 0, n = tokens.length;
		var parseExpression;
		var parseFactor = function(depth) {
			if (i >= n || tokens[i][0] == '|' || (depth && tokens[i][0] == ')')) {
				return null;
			} else if (tokens[i][0] == '(') {
				++i;
				var expression = parseExpression(depth + 1);
				if (i < n && tokens[i][0] == ')') ++i;
				return expression;
			} else if (tokens[i][0] == '-') {
				++i;
				var factor = parseFactor(depth);
				return factor ? ['-', factor] : null;
			} else if (tokens[i][0] == '"') {
				var key = tokens[i][1];
				++i;
				if (i < n && ':=<>'.indexOf(tokens[i][0]) >= 0) {
					var operation = tokens[i][0];
					++i;
					if (('<>'.indexOf(operation) >= 0) && i < n && tokens[i][0] == '=') {
						operation += '=';
						++i;
					}
					var value = parseFactor(depth);
					if (value) return [operation, key, value];
				}
				return ['"', key];
			} else {
				++i;
				return null;
			}
		};
		var parseTerm = function(depth) {
			var factors = ['&'];
			var factor = parseFactor(depth);
			if (factor) factors.push(factor);
			while (i < n && !(tokens[i][0] == '|' || (depth && tokens[i][0] == ')'))) {
				factor = parseFactor(depth);
				if (factor) factors.push(factor);
			}
			switch (factors.length) {
				case 1: return null;
				case 2: return factors[1];
				default: return factors;
			}
		};
		parseExpression = function(depth) {
			var terms = ['|'];
			var term = parseTerm(depth);
			if (term) terms.push(term);
			while (i < n && tokens[i][0] == '|') {
				++i;
				term = parseTerm(depth);
				if (term) terms.push(term);
			}
			switch (terms.length) {
				case 1: return null;
				case 2: return terms[1];
				default: return terms;
			}
		};
		return parseExpression(0);
	};
	var queryMatchOperation = function(a, op, b) {
		switch (op) {
			case '<':
				var an = Number(a), bn = Number(b);
				if (isFinite(an) && isFinite(bn)) return (an < bn);
				return naturalCompare(a, b) < 0;
			case '>':
				var an = Number(a), bn = Number(b);
				if (isFinite(an) && isFinite(bn)) return (an > bn);
				return naturalCompare(a, b) > 0;
			case '<=':
				var an = Number(a), bn = Number(b);
				if (isFinite(an) && isFinite(bn)) return (an <= bn);
				return naturalCompare(a, b) <= 0;
			case '>=':
				var an = Number(a), bn = Number(b);
				if (isFinite(an) && isFinite(bn)) return (an >= bn);
				return naturalCompare(a, b) >= 0;
			case '=':
				var al = String(a).toLowerCase();
				var bl = String(b).toLowerCase();
				return al == bl;
			default:
				var al = String(a).toLowerCase();
				var bl = String(b).toLowerCase();
				return al.indexOf(bl) >= 0;
		}
	};
	var queryMatches = function(query, operation, searchContent, entity) {
		if (!query) {
			return true;
		} else if (query[0] == '"') {
			for (var i = 0, n = searchContent.length; i < n; ++i) {
				if (queryMatchOperation(searchContent[i], operation, query[1])) {
					return true;
				}
			}
			return false;
		} else if (query[0] == '-') {
			return !queryMatches(query[1], operation, searchContent, entity);
		} else if (query[0] == '&') {
			for (var i = 1, n = query.length; i < n; ++i) {
				if (!queryMatches(query[i], operation, searchContent, entity)) {
					return false;
				}
			}
			return true;
		} else if (query[0] == '|') {
			for (var i = 1, n = query.length; i < n; ++i) {
				if (queryMatches(query[i], operation, searchContent, entity)) {
					return true;
				}
			}
			return false;
		} else {
			var newOperation = query[0];
			var newEntity = entity[query[1]];
			var newQuery = query[2];
			if (newEntity == null) {
				return false;
			} else if ($.isPlainObject(newEntity)) {
				var newSearchContent = newEntity['search-content'];
				if (newSearchContent == null) {
					return queryMatches(newQuery, newOperation, [String(newEntity)], newEntity);
				} else if ($.isArray(newSearchContent)) {
					return queryMatches(newQuery, newOperation, newSearchContent, newEntity);
				} else {
					return queryMatches(newQuery, newOperation, [String(newSearchContent)], newEntity);
				}
			} else if ($.isArray(newEntity)) {
				return queryMatches(newQuery, newOperation, newEntity, newEntity);
			} else {
				return queryMatches(newQuery, newOperation, [String(newEntity)], newEntity);
			}
		}
	};

	/* Miscellaneous Utilities */

	var doAjax = function(message, request, done) {
		cmui.showButterbar(message);
		$.post(
			(listdef['ajax-url'] || ''),
			request,
			function(response) {
				if (!response['ok']) {
					cmui.showButterbarPersistent('An error occurred. Please try again.');
				} else {
					done(response);
					cmui.hideButterbar();
				}
			},
			'json'
		);
	};
	var qrEnabled = function() {
		if (listdef['qr'] == 'off' || (window.localStorage && window.localStorage.qr == 'off')) return false;
		if (listdef['qr'] == 'on' || (window.localStorage && window.localStorage.qr == 'on')) return true;
		if (listdef['qr'] == 'auto' && (window.localStorage && window.localStorage.qr == 'auto')) return true;
		return false;
	};

	/* Simple Loader */

	var makeSimpleLoader = function(setPageCounter, setListHTML) {
		var visibleRows = [];
		var doRender = function() {
			var html = '';
			for (var i = 0, n = visibleRows.length; i < n; ++i) {
				html += visibleRows[i]['html'];
			}
			setListHTML(html);
		};

		var matchedRows = [];
		var doPage = function(offset, length) {
			if (length) {
				visibleRows = matchedRows.slice(offset, offset + length);
				setPageCounter(
					offset + (visibleRows.length ? 1 : 0),
					offset + visibleRows.length,
					matchedRows.length,
					0
				);
			} else {
				visibleRows = matchedRows.slice();
				setPageCounter(
					(visibleRows.length ? 1 : 0),
					visibleRows.length,
					matchedRows.length,
					0
				);
			}
		};

		var sortable = isSortable();
		var doSort = function(sortOrder) {
			if (sortable && sortOrder && sortOrder.length) {
				for (var i = 0, n = sortOrder.length; i < n; ++i) {
					var columnIndex = sortOrder[i];
					var descending = (columnIndex < 0);
					if (descending) columnIndex = ~columnIndex;
					var column = listdef['columns'][columnIndex];
					var compare = sortFunctions[column['type']];
					var key = column['key'];
					matchedRows.sort(function(a, b) {
						var av = a['entity'][key];
						var bv = b['entity'][key];
						var cmp = compare(av, bv);
						if (descending) cmp = -cmp;
						return cmp;
					});
				}
			}
		};

		var allRows = [];
		var doFilter = function(query) {
			if (query) {
				matchedRows = [];
				for (var i = 0, n = allRows.length; i < n; ++i) {
					var search = allRows[i]['search'];
					var entity = allRows[i]['entity'];
					if (search && entity) {
						if (queryMatches(query, ':', search, entity)) {
							matchedRows.push(allRows[i]);
						}
					}
				}
			} else {
				matchedRows = allRows.slice();
			}
		};

		var entityType = listdef['entity-type-pl'] || listdef['entity-type'];
		var message = (entityType ? ('Loading ' + entityType + '...') : 'Loading...');
		var url = listdef['ajax-url'] || '';
		var request = {'cm-list-action': 'list'};
		var doLoad = function(done) {
			cmui.showButterbar(message);
			$.post(url, request, function(data) {
				if (!data['ok']) {
					cmui.showButterbarPersistent('An error occurred. Please reload the page.');
				} else {
					allRows = data['rows'] || [];
					if (done) done();
					cmui.hideButterbar();
				}
			}, 'json');
		};

		var indexOfEntity = function(entityKey) {
			var rowKey = listdef['row-key'] || 'id';
			for (var i = 0, n = allRows.length; i < n; ++i) {
				if (allRows[i]['entity'][rowKey] == entityKey) {
					return i;
				}
			}
			return -1;
		};
		var getEntity = function(id) {
			var index = indexOfEntity(id);
			return (index >= 0) ? allRows[index]['entity'] : null;
		};
		var appendEntity = function(row) {
			if (row) allRows.push(row);
		};
		var replaceEntity = function(id, row) {
			if (!row) return;
			var index = indexOfEntity(id);
			if (index >= 0) allRows[index] = row;
		};
		var removeEntity = function(id) {
			var index = indexOfEntity(id);
			if (index >= 0) allRows.splice(index, 1);
		};
		var reorderEntity = function(id, direction) {
			var index = indexOfEntity(id);
			if (index < 0) return;
			var row = allRows[index];
			allRows.splice(index, 1);
			index += direction;
			if (index < 0) index = 0;
			if (index > allRows.length) index = allRows.length;
			allRows.splice(index, 0, row);
		};

		return {
			'doLoad': doLoad,
			'doFilter': doFilter,
			'doSort': doSort,
			'doPage': doPage,
			'doRender': doRender,
			'getEntity': getEntity,
			'appendEntity': appendEntity,
			'replaceEntity': replaceEntity,
			'removeEntity': removeEntity,
			'reorderEntity': reorderEntity
		};
	};

	/* Server-Side Loader */

	var makeServerSideLoader = function(setPageCounter, setListHTML) {
		var entityType = listdef['entity-type-pl'] || listdef['entity-type'];
		var message = (entityType ? ('Loading ' + entityType + '...') : 'Loading...');
		var url = listdef['ajax-url'] || '';
		var request = {'cm-list-action': 'list'};
		var visibleRows = [];

		var doLoad = function(done) {
			if (done) done();
		};
		var doFilter = function(query) {
			request['cm-list-search-query'] = JSON.stringify(query);
		};
		var doSort = function(sortOrder) {
			request['cm-list-sort-order'] = JSON.stringify(sortOrder);
		};
		var doPage = function(offset, length) {
			request['cm-list-page-offset'] = offset;
			request['cm-list-page-length'] = length;
		};
		var doRender = function() {
			cmui.showButterbar(message);
			$.post(url, request, function(data) {
				if (!data['ok']) {
					cmui.showButterbarPersistent('An error occurred. Please reload the page.');
				} else {
					visibleRows = data['rows'] || [];

					var offset = request['cm-list-page-offset'];
					var length = request['cm-list-page-length'];
					if (length) {
						setPageCounter(
							offset + (visibleRows.length ? 1 : 0),
							offset + visibleRows.length,
							data['match-count'],
							data['time']
						);
					} else {
						setPageCounter(
							(visibleRows.length ? 1 : 0),
							visibleRows.length,
							data['match-count'],
							data['time']
						);
					}

					var html = '';
					for (var i = 0, n = visibleRows.length; i < n; ++i) {
						html += visibleRows[i]['html'];
					}
					setListHTML(html);

					cmui.hideButterbar();
				}
			}, 'json');
		};

		var getEntity = function(id) {
			var rowKey = listdef['row-key'] || 'id';
			for (var i = 0, n = visibleRows.length; i < n; ++i) {
				if (visibleRows[i]['entity'][rowKey] == id) {
					return visibleRows[i]['entity'];
				}
			}
			return null;
		};

		return {
			'doLoad': doLoad,
			'doFilter': doFilter,
			'doSort': doSort,
			'doPage': doPage,
			'doRender': doRender,
			'getEntity': getEntity,
			'appendEntity': function(){},
			'replaceEntity': function(){},
			'removeEntity': function(){},
			'reorderEntity': function(){}
		};
	};

	/* Page Controller */

	$(document).ready(function() {
		/* Initial Parameters */
		var query = queryParse($('.cm-search-input input').val() || '');
		var sortable = isSortable();
		var sortOrder = listdef['sort-order'] || [];
		var offset = 0;
		var maxResults = (1 * $('.cm-search-max-results').val());
		var resultCount = 0;
		var htmlInit;

		/* Loader */
		var setPageCounter = function(start, end, total, time) {
			$('.cm-search-vis-start').text(start);
			$('.cm-search-vis-end').text(end);
			$('.cm-search-vis-total').text(total);
			resultCount = (1 * total);
			if (time > 0) {
				time = (Math.round(time * 1000) / 1000);
				$('.cm-search-vis-total').attr('title', 'in ' + time + ' seconds');
			}
		};
		var setListHTML = function(html) {
			$('.cm-list-table tbody').html(html);
			if (htmlInit) htmlInit();
		};
		var loader;
		switch (listdef['loader']) {
			default:
				loader = makeSimpleLoader(setPageCounter, setListHTML);
				break;
			case 'server-side':
				loader = makeServerSideLoader(setPageCounter, setListHTML);
				break;
		}

		/* Convenience Functions */
		var doLoad = function() {
			loader.doLoad(function() {
				loader.doFilter(query);
				loader.doSort(sortOrder);
				loader.doPage(offset, maxResults);
				loader.doRender();
			});
		};
		var doFilter = function() {
			loader.doFilter(query);
			loader.doSort(sortOrder);
			loader.doPage(offset, maxResults);
			loader.doRender();
		};
		var doSort = function() {
			loader.doSort(sortOrder);
			loader.doPage(offset, maxResults);
			loader.doRender();
		};
		var doPage = function() {
			loader.doPage(offset, maxResults);
			loader.doRender();
		};

		/* Search Text Field */
		var searchDelay = listdef['search-delay'];
		var searchTimeout = null;
		var searchField = $('.cm-search-input input');
		var searchFieldOldVal = searchField.val();
		var searchFieldChanged = function() {
			var searchFieldNewVal = searchField.val();
			if (searchFieldNewVal != searchFieldOldVal) {
				searchFieldOldVal = searchFieldNewVal;
				query = queryParse(searchFieldNewVal || '');
				offset = 0;
				if (searchDelay) {
					window.clearTimeout(searchTimeout);
					searchTimeout = window.setTimeout(doFilter, searchDelay);
				} else {
					doFilter();
				}
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
			offset = (maxResults && (offset > maxResults)) ? (offset - maxResults) : 0;
			doPage();
		});
		nextPageButton.bind('click', function() {
			offset = maxResults ? (((offset + maxResults) < resultCount) ? (offset + maxResults) : offset) : 0;
			doPage();
		});
		lastPageButton.bind('click', function() {
			offset = maxResults ? (Math.floor((resultCount - 1) / maxResults) * maxResults) : 0;
			doPage();
		});

		/* Result Count Selector */
		var maxResultsField = $('.cm-search-max-results');
		var maxResultsFieldOldVal = maxResultsField.val();
		var maxResultsFieldChanged = function() {
			var maxResultsFieldNewVal = maxResultsField.val();
			if (maxResultsFieldNewVal != maxResultsFieldOldVal) {
				maxResultsFieldOldVal = maxResultsFieldNewVal;
				maxResults = (1 * maxResultsFieldNewVal);
				doPage();
			}
		};
		maxResultsField.bind('change', maxResultsFieldChanged);
		maxResultsField.bind('keydown', maxResultsFieldChanged);
		maxResultsField.bind('keyup', maxResultsFieldChanged);
		maxResultsField.bind('mousedown', maxResultsFieldChanged);
		maxResultsField.bind('mouseup', maxResultsFieldChanged);

		/* Sort Headers */
		if (sortable) {
			var updateSortIndicators = function() {
				$('.cm-list-table thead th')
					.removeClass('th-sort-ascending')
					.removeClass('th-sort-descending')
					.removeClass('th-sort-primary');
				for (var i = 0, n = sortOrder.length; i < n; ++i) {
					var columnIndex = sortOrder[i];
					var descending = (columnIndex < 0);
					if (descending) columnIndex = ~columnIndex;
					var header = $('.cm-list-table thead th:eq(' + columnIndex + ')');
					header.addClass(descending ? 'th-sort-descending' : 'th-sort-ascending');
					if ((i + 1) == n) header.addClass('th-sort-primary');
				}
			};
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
					offset = 0;
					updateSortIndicators();
					doSort();
				};
			};
			updateSortIndicators();
			for (var i = 0, n = listdef['columns'].length; i < n; ++i) {
				var type = listdef['columns'][i]['type'];
				if (type && sortFunctions[type]) {
					var header = $('.cm-list-table thead th:eq(' + i + ')');
					header.addClass('th-sortable');
					header.bind('click', makeSortFunction(i));
				}
			}
		}

		/* Action Parameters */
		var entityIdUnderEdit;
		var entityUnderEdit;
		var entityNameUnderEdit;
		var entityIdUnderDelete;
		var entityUnderDelete;
		var entityNameUnderDelete;

		/* Row Action Buttons */
		htmlInit = function() {
			if (!listdef['row-actions']) return;
			var selectable  = (listdef['row-actions'].indexOf('select' ) >= 0);
			var switchable  = (listdef['row-actions'].indexOf('switch' ) >= 0);
			var editable    = (listdef['row-actions'].indexOf('edit'   ) >= 0);
			var reorderable = (listdef['row-actions'].indexOf('reorder') >= 0);
			var deleteable  = (listdef['row-actions'].indexOf('delete' ) >= 0);
			var reviewable  = (listdef['row-actions'].indexOf('review' ) >= 0);
			$('.cm-list-table tbody tr').each(function() {
				var tr = $(this);
				var id = tr.attr('id').substring(6);
				var entity = loader.getEntity(id);
				var name = entity ? (entity[listdef['name-key'] || 'name'] || id) : id;
				if (selectable) {
					tr.find('.select-button').bind('click', function() {
						if (listdef['select-function']) {
							listdef['select-function'](id, entity);
						}
					});
				}
				if (switchable) {
					tr.find('.activate-button').bind('click', function() {
						doAjax('Activating ' + name + '...', {
							'cm-list-action': 'activate',
							'cm-list-key': id,
							'cm-list-entity': JSON.stringify(entity)
						}, function(data) {
							loader.replaceEntity(id, data['row']);
							doFilter();
						});
					});
					tr.find('.deactivate-button').bind('click', function() {
						doAjax('Deactivating ' + name + '...', {
							'cm-list-action': 'deactivate',
							'cm-list-key': id,
							'cm-list-entity': JSON.stringify(entity)
						}, function(data) {
							loader.replaceEntity(id, data['row']);
							doFilter();
						});
					});
				}
				if (editable && !listdef['edit-url']) {
					tr.find('.edit-button').bind('click', function() {
						entityIdUnderEdit = id;
						entityUnderEdit = entity;
						entityNameUnderEdit = name;
						$('.edit-dialog .dialog-title').text(listdef['edit-title'] || 'Edit');
						if (listdef['edit-load-function']) {
							listdef['edit-load-function'](id, entity);
						}
						cmui.showDialog('edit');
					});
				}
				if (reorderable) {
					tr.find('.up-button').bind('click', function() {
						doAjax('Moving ' + name + '...', {
							'cm-list-action': 'reorder',
							'cm-list-key': id,
							'cm-list-entity': JSON.stringify(entity),
							'cm-list-reorder-direction': -1
						}, function() {
							loader.reorderEntity(id, -1);
							doFilter();
						});
					});
					tr.find('.down-button').bind('click', function() {
						doAjax('Moving ' + name + '...', {
							'cm-list-action': 'reorder',
							'cm-list-key': id,
							'cm-list-entity': JSON.stringify(entity),
							'cm-list-reorder-direction': +1
						}, function() {
							loader.reorderEntity(id, +1);
							doFilter();
						});
					});
				}
				if (deleteable) {
					tr.find('.delete-button').bind('click', function() {
						entityIdUnderDelete = id;
						entityUnderDelete = entity;
						entityNameUnderDelete = name;
						$('.delete-dialog .dialog-title').text(listdef['delete-title'] || 'Delete');
						$('.delete-dialog .delete-name').text(name);
						cmui.showDialog('delete');
					});
				}
				if (reviewable && !listdef['review-url']) {
					tr.find('.review-button').bind('click', function() {
						if (listdef['review-function']) {
							listdef['review-function'](id, entity);
						}
					});
				}
			});
		};

		/* Table Action Buttons */
		if (listdef['table-actions']) {
			var addable = (listdef['table-actions'].indexOf('add') >= 0);
			if (addable && !listdef['add-url']) {
				$('.cm-list-table .add-button').bind('click', function() {
					entityIdUnderEdit = null;
					entityUnderEdit = null;
					entityNameUnderEdit = null;
					$('.edit-dialog .dialog-title').text(listdef['add-title'] || 'Add');
					if (listdef['edit-clear-function']) {
						listdef['edit-clear-function']();
					}
					cmui.showDialog('edit');
				});
			}
		}

		/* Edit Dialog */
		if (
			(listdef['row-actions'] && (listdef['row-actions'].indexOf('edit') >= 0)) ||
			(listdef['table-actions'] && (listdef['table-actions'].indexOf('add') >= 0))
		) {
			$('.edit-dialog .cancel-edit-button').bind('click', cmui.hideDialog);
			$('.edit-dialog .confirm-edit-button').bind('click', function() {
				cmui.hideDialog();
				if (listdef['edit-save-function']) {
					var newEntity = listdef['edit-save-function'](entityIdUnderEdit, entityUnderEdit);
					if (newEntity) {
						var newId = newEntity[listdef['row-key'] || 'id'] || entityIdUnderEdit;
						var newName = newEntity[listdef['name-key'] || 'name'] || entityNameUnderEdit || newId;
						doAjax('Saving ' + newName + '...', {
							'cm-list-action': (entityUnderEdit ? 'update' : 'create'),
							'cm-list-key': entityIdUnderEdit,
							'cm-list-entity': JSON.stringify(newEntity)
						}, function(data) {
							if (entityUnderEdit) {
								loader.replaceEntity(entityIdUnderEdit, data['row']);
							} else {
								loader.appendEntity(data['row']);
							}
							doFilter();
						});
					}
				}
			});
		}

		/* Delete Dialog */
		if (listdef['row-actions'] && (listdef['row-actions'].indexOf('delete') >= 0)) {
			$('.delete-dialog .cancel-delete-button').bind('click', cmui.hideDialog);
			$('.delete-dialog .soft-delete-button').bind('click', function() {
				cmui.hideDialog();
				doAjax('Deactivating ' + entityNameUnderDelete + '...', {
					'cm-list-action': 'deactivate',
					'cm-list-key': entityIdUnderDelete,
					'cm-list-entity': JSON.stringify(entityUnderDelete)
				}, function(data) {
					loader.replaceEntity(entityIdUnderDelete, data['row']);
					doFilter();
				});
			});
			$('.delete-dialog .confirm-delete-button').bind('click', function() {
				cmui.hideDialog();
				doAjax('Deleting ' + entityNameUnderDelete + '...', {
					'cm-list-action': 'delete',
					'cm-list-key': entityIdUnderDelete,
					'cm-list-entity': JSON.stringify(entityUnderDelete)
				}, function(data) {
					loader.removeEntity(entityIdUnderDelete);
					doFilter();
				});
			});
		}

		/* Keyboard Navigation */
		$('body').bind('keydown', function(event) {
			if (!$('.dialog-cover').hasClass('hidden')) return;
			switch (event.which) {
				case 13:
					if (event.target != searchField[0]) return;
					window.clearTimeout(searchTimeout);
					doFilter();
					break;
				case 27:
					searchField.val('');
					searchFieldChanged();
					searchField.focus();
					break;
				case 33:
					prevPageButton.click();
					break;
				case 34:
					nextPageButton.click();
					break;
				case 35:
					lastPageButton.click();
					break;
				case 36:
					firstPageButton.click();
					break;
				case 65:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					var e = $('.add-button');
					if (e.length != 1) break;
					if (e.is('a')) e[0].click();
					else e.click();
					break;
				case 68:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					var e = $('.delete-button');
					if (e.length == 1) e.click();
					break;
				case 69:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					var e = $('.edit-button');
					if (e.length != 1) break;
					if (e.is('a')) e[0].click();
					else e.click();
					break;
				case 82:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					var e = $('.review-button');
					if (e.length != 1) break;
					if (e.is('a')) e[0].click();
					else e.click();
					break;
				case 83:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					var e = $('.select-button');
					if (e.length == 1) e.click();
					break;
				case 88:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					var e1 = $('.activate-button');
					var e2 = $('.deactivate-button');
					if (e1.length == 1 && e2.length == 0) e1.click();
					if (e1.length == 0 && e2.length == 1) e2.click();
					break;
				case 191:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					cmui.showDialog('shortcuts');
					break;
				default:
					return;
			}
			event.stopPropagation();
			event.preventDefault();
		});

		/* QR Code Support */
		if (qrEnabled()) {
			var qrState = 0;
			$('body').bind('keypress', function(event) {
				switch (qrState) {
					case 0:
						if (event.which == 67) qrState = 1;
						break;
					case 1:
						if (event.which == 77) qrState = 2;
						else qrState = 0;
						break;
					case 2:
						if (event.which == 42) {
							searchField.val('qr-data:CM*');
							searchFieldChanged();
							searchField.focus();
							event.stopPropagation();
							event.preventDefault();
						}
						qrState = 0;
						break;
				}
			});
		}

		/* Load */
		doLoad();
	});

})(jQuery,window,document,cmui,cm_list_def);