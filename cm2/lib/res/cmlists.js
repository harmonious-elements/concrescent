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
		'quantity'   : numericCompare,
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

	$(document).ready(function() {
		var rows = [];
		var matches = [];
		var sortable = isSortable();
		var sortOrder = listdef['sort-order'] || [];
		var offset = 0;
		var visible = [];
		var html = '';
		var htmlInit;
		var entityIdUnderEdit;
		var entityUnderEdit;
		var entityNameUnderEdit;
		var entityIdUnderDelete;
		var entityUnderDelete;
		var entityNameUnderDelete;

		var doFilter = function() {
			/* Filter */
			var query = queryParse($('.cm-search-input input').val() || '');
			if (query) {
				matches = [];
				for (var i = 0, n = rows.length; i < n; ++i) {
					if (rows[i]['search'] && rows[i]['entity']) {
						if (queryMatches(query, ':', rows[i]['search'], rows[i]['entity'])) {
							matches.push(rows[i]);
						}
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
			if (htmlInit) htmlInit();
		};
		var nextId = ('start-id' in listdef) ? listdef['start-id'] : 1;
		var doLoad = function() {
			var entityType = listdef['entity-type-pl'] || listdef['entity-type'];
			cmui.showButterbar(entityType ? ('Loading ' + entityType + '...') : 'Loading...');
			$.post(
				(listdef['ajax-url'] || ''),
				{'cm-list-action': 'list', 'cm-list-start-id': nextId},
				function(data) {
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
				},
				'json'
			);
		};
		var indexOfEntity = function(entityKey) {
			var rowKey = listdef['row-key'] || 'id';
			for (var i = 0, n = rows.length; i < n; ++i) {
				if (rows[i]['entity'][rowKey] == entityKey) {
					return i;
				}
			}
			return -1;
		};
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
				if ($('.dialog-cover').hasClass('hidden')) {
					searchField.val('');
					searchFieldChanged();
					searchField.focus();
					event.stopPropagation();
					event.preventDefault();
				}
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
			var updateSortIndicators = function() {
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
					updateSortIndicators();
					doFilter();
					offset = 0;
					doPage();
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
				var index = indexOfEntity(id);
				var entity = (index >= 0) ? rows[index]['entity'] : null;
				var name = entity ? (entity[listdef['name-key'] || 'name'] || id) : id;
				if (selectable) {
					tr.find('.select-button').bind('click', function() {
						if (listdef['select-function']) {
							listdef['select-function'](id, entity);
						}
					});
				}
				if (switchable) {
					var update = function(data) {
						if (data['row']) {
							var i = indexOfEntity(id);
							if (i >= 0) {
								rows[i] = data['row'];
								doFilter();
								doPage();
							}
						}
					};
					tr.find('.activate-button').bind('click', function() {
						doAjax('Activating ' + name + '...', {
							'cm-list-action': 'activate',
							'cm-list-key': id,
							'cm-list-entity': JSON.stringify(entity)
						}, update);
					});
					tr.find('.deactivate-button').bind('click', function() {
						doAjax('Deactivating ' + name + '...', {
							'cm-list-action': 'deactivate',
							'cm-list-key': id,
							'cm-list-entity': JSON.stringify(entity)
						}, update);
					});
				}
				if (editable) {
					tr.find('.edit-button').bind('click', function() {
						if (listdef['edit-url']) {
							window.location.href = listdef['edit-url'] + id;
						} else {
							entityIdUnderEdit = id;
							entityUnderEdit = entity;
							entityNameUnderEdit = name;
							$('.edit-dialog .dialog-title').text(listdef['edit-title'] || 'Edit');
							if (listdef['edit-load-function']) {
								listdef['edit-load-function'](id, entity);
							}
							cmui.showDialog('edit');
						}
					});
				}
				if (reorderable) {
					var reorder = function(direction) {
						return function() {
							var i = indexOfEntity(id);
							if (i >= 0) {
								var row = rows[i];
								rows.splice(i, 1);
								i += direction;
								if (i < 0) i = 0;
								if (i > rows.length) i = rows.length;
								rows.splice(i, 0, row);
								doFilter();
								doPage();
							}
						};
					};
					tr.find('.up-button').bind('click', function() {
						doAjax('Moving ' + name + '...', {
							'cm-list-action': 'reorder',
							'cm-list-key': id,
							'cm-list-entity': JSON.stringify(entity),
							'cm-list-reorder-direction': -1
						}, reorder(-1));
					});
					tr.find('.down-button').bind('click', function() {
						doAjax('Moving ' + name + '...', {
							'cm-list-action': 'reorder',
							'cm-list-key': id,
							'cm-list-entity': JSON.stringify(entity),
							'cm-list-reorder-direction': +1
						}, reorder(+1));
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
				if (reviewable) {
					tr.find('.review-button').bind('click', function() {
						if (listdef['review-url']) {
							window.location.href = listdef['review-url'] + id;
						}
					});
				}
			});
		};
		/* Table Action Buttons */
		if (listdef['table-actions']) {
			if (listdef['table-actions'].indexOf('add') >= 0) {
				$('.cm-list-table .add-button').bind('click', function() {
					if (listdef['add-url']) {
						window.location.href = listdef['add-url'];
					} else {
						entityIdUnderEdit = null;
						entityUnderEdit = null;
						entityNameUnderEdit = null;
						$('.edit-dialog .dialog-title').text(listdef['add-title'] || 'Add');
						if (listdef['edit-clear-function']) {
							listdef['edit-clear-function']();
						}
						cmui.showDialog('edit');
					}
				});
			}
		}

		/* Dialogs */
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
							if (data['row']) {
								if (entityUnderEdit) {
									var i = indexOfEntity(entityIdUnderEdit);
									if (i >= 0) {
										rows[i] = data['row'];
										doFilter();
										doPage();
									}
								} else {
									rows.push(data['row']);
									doFilter();
									doPage();
								}
							}
						});
					}
				}
			});
		}
		if (listdef['row-actions'] && (listdef['row-actions'].indexOf('delete') >= 0)) {
			$('.delete-dialog .cancel-delete-button').bind('click', cmui.hideDialog);
			$('.delete-dialog .soft-delete-button').bind('click', function() {
				cmui.hideDialog();
				doAjax('Deactivating ' + entityNameUnderDelete + '...', {
					'cm-list-action': 'deactivate',
					'cm-list-key': entityIdUnderDelete,
					'cm-list-entity': JSON.stringify(entityUnderDelete)
				}, function(data) {
					if (data['row']) {
						var i = indexOfEntity(entityIdUnderDelete);
						if (i >= 0) {
							rows[i] = data['row'];
							doFilter();
							doPage();
						}
					}
				});
			});
			$('.delete-dialog .confirm-delete-button').bind('click', function() {
				cmui.hideDialog();
				doAjax('Deleting ' + entityNameUnderDelete + '...', {
					'cm-list-action': 'delete',
					'cm-list-key': entityIdUnderDelete,
					'cm-list-entity': JSON.stringify(entityUnderDelete)
				}, function(data) {
					var i = indexOfEntity(entityIdUnderDelete);
					if (i >= 0) {
						rows.splice(i, 1);
						doFilter();
						doPage();
					}
				});
			});
		}

		doLoad();
	});
})(jQuery,window,document,cmui,cm_list_def);