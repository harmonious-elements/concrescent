listPage = (function($,cmui,document){
	return function(o) {
		$(document).ready(function() {
			var entities, offset, matches, doFilter, nextId, loadEntities, doAjax, listInit;
			if (o.searchable) {
				entities = [];
				offset = 0;
				matches = 0;
				doFilter = function() {
					var results = [];
					var filterText = ($(o.searchFieldSelector || '.search-filter').val() || '').trim().toLowerCase();
					var maxResults = ($(o.maxResultsFieldSelector || '.search-max-results').val() || o.maxResults || 1000);
					matches = 0;
					for (var i = entities.length - 1; i >= 0; i--) {
						if (entities[i].search_content.indexOf(filterText) >= 0) {
							matches++;
							if (matches > offset && results.length < maxResults) {
								results.push(entities[i].html_content);
							}
						}
					}
					$(o.visStartSelector || '.search-vis-start').text(1 * offset + (results.length ? 1 : 0));
					$(o.visEndSelector || '.search-vis-end').text(1 * offset + results.length);
					$(o.visTotalSelector || '.search-vis-total').text(matches);
					$(o.listSelector || 'table.entity-list tbody').html(results.join(''));
					if (listInit) listInit();
				};
			} else if (o.progressive) {
				entities = [];
				offset = null;
				matches = null;
				doFilter = function() {
					var results = [];
					for (var i = entities.length - 1; i >= 0; i--) {
						results.push(entities[i].html_content);
						if (o.maxResults && (results.length >= o.maxResults)) break;
					}
					$(o.listSelector || 'table.entity-list tbody').html(results.join(''));
					if (listInit) listInit();
				};
			} else {
				entities = null;
				offset = null;
				matches = null;
				doFilter = null;
			}
			if (o.progressive) {
				nextId = ('startId' in o) ? o.startId : 1;
				loadEntities = function(done) {
					cmui.showButterbar(o.entityType ? ('Loading '+o.entityType+'...') : 'Loading...');
					$.post((o.ajaxUrl || ''), {'action': 'list', 'start_id': nextId}, function(data) {
						if (data.entities && data.entities.length) {
							entities = entities.concat(data.entities);
							nextId = data.next_start_id;
							doFilter();
							setTimeout(function() { loadEntities(done); }, 10);
						} else {
							cmui.hideButterbar();
							if (done) done();
							setTimeout(function() { loadEntities(null); }, 5000);
						}
					}, 'json');
				};
				doAjax = function(data, done) {
					$.post((o.ajaxUrl || ''), data, function() {
						$(o.listSelector || 'table.entity-list tbody').html('');
						entities = [];
						nextId = 1;
						loadEntities(done);
					});
				};
			} else if (o.searchable) {
				nextId = null;
				loadEntities = function(done) {
					cmui.showButterbar(o.entityType ? ('Loading '+o.entityType+'...') : 'Loading...');
					$.post((o.ajaxUrl || ''), {'action': 'list'}, function(data) {
						if (data.entities && data.entities.length) {
							entities = data.entities;
							doFilter();
						}
						cmui.hideButterbar();
						if (done) done();
					}, 'json');
				};
				doAjax = function(data, done) {
					$.post((o.ajaxUrl || ''), data, function() {
						$(o.listSelector || 'table.entity-list tbody').html('');
						entities = [];
						loadEntities(done);
					});
				};
			} else {
				nextId = null;
				loadEntities = null;
				doAjax = function(data, done) {
					$.post((o.ajaxUrl || ''), data, function(r) {
						$(o.listSelector || 'table.entity-list tbody').html(r);
						if (listInit) listInit();
						if (done) done();
					});
				};
			}
			
			if (o.listItemInit || o.selectable || o.switchable || o.deleteable || o.reorderable || o.editDialog) {
				listInit = function() {
					$(o.listItemSelector || 'table.entity-list tbody tr').each(function() {
						var self = $(this);
						if (self.hasClass('inited')) return;
						var id = self.find(o.listItemIdSelector || '.ea-id').val();
						var name = self.find(o.listItemNameSelector || '.ea-name').val();
						if (o.listItemInit) o.listItemInit(self, id, name);
						/* Initialize Select Button */
						if (o.selectable) {
							self.find('.select-button').click(function() {
								if (o.selectAction) o.selectAction(self, id, name);
							});
						}
						/* Initialize Activate/Deactivate Button */
						if (o.switchable) {
							self.find('.activate-button').click(function() {
								cmui.showButterbar('Activating ' + name + '...');
								doAjax({'action': 'activate', 'id': id}, cmui.hideButterbar);
							});
							self.find('.deactivate-button').click(function() {
								cmui.showButterbar('Deactivating ' + name + '...');
								doAjax({'action': 'deactivate', 'id': id}, cmui.hideButterbar);
							});
						}
						/* Initialize Delete Button */
						if (o.deleteable) {
							self.find('.delete-button').click(function() {
								$('.delete-id').val(id);
								$('.delete-name').text(name);
								cmui.showDialog('delete');
							});
						}
						/* Initialize Up/Down Button */
						if (o.reorderable) {
							self.find('.up-button').click(function() {
								cmui.showButterbar('Moving ' + name + '...');
								doAjax({'action': 'reorder', 'id': id, 'direction': -1}, cmui.hideButterbar);
							});
							self.find('.down-button').click(function() {
								cmui.showButterbar('Moving ' + name + '...');
								doAjax({'action': 'reorder', 'id': id, 'direction': +1}, cmui.hideButterbar);
							});
						}
						/* Initialize Edit Button */
						if (o.editDialog) {
							self.find('.edit-button').click(function() {
								$('.edit-dialog .dialog-title').text(o.editDialogTitle || 'Edit');
								if (o.editDialogStart) o.editDialogStart(self, id, name);
								cmui.showDialog('edit');
							});
						}
						self.addClass('inited');
					});
				};
			} else {
				listInit = null;
			}
			
			/* Initialize List Items */
			if (listInit) listInit();
			if (loadEntities) loadEntities(null);
			/* Initialize Search Filter */
			if (o.searchable) {
				var searchField = $(o.searchFieldSelector || '.search-filter');
				var firstPageButton = $(o.firstPageButtonSelector || '.search-first-page');
				var prevPageButton = $(o.prevPageButtonSelector || '.search-prev-page');
				var nextPageButton = $(o.nextPageButtonSelector || '.search-next-page');
				var lastPageButton = $(o.lastPageButtonSelector || '.search-last-page');
				var maxResultsField = $(o.maxResultsFieldSelector || '.search-max-results');
				/* Search Field */
				var oldSearchFieldVal = searchField.val();
				var searchFieldChange = function() {
					var newSearchFieldVal = searchField.val();
					if (newSearchFieldVal != oldSearchFieldVal) {
						oldSearchFieldVal = newSearchFieldVal;
						offset = 0;
						doFilter();
					}
				};
				searchField.unbind('change').bind('change', searchFieldChange);
				searchField.unbind('keydown').bind('keydown', searchFieldChange);
				searchField.unbind('keyup').bind('keyup', searchFieldChange);
				/* Prev/Next Button */
				firstPageButton.unbind('click').bind('click', function() {
					offset = 0;
					doFilter();
				});
				prevPageButton.unbind('click').bind('click', function() {
					var maxResults = maxResultsField.val() || o.maxResults || 1000;
					offset = 1 * offset - 1 * maxResults;
					if (offset < 0) offset = 0;
					doFilter();
				});
				nextPageButton.unbind('click').bind('click', function() {
					var maxResults = maxResultsField.val() || o.maxResults || 1000;
					offset = 1 * offset + 1 * maxResults;
					if (offset >= matches) offset = Math.floor((matches - 1) / maxResults) * maxResults;
					doFilter();
				});
				lastPageButton.unbind('click').bind('click', function() {
					var maxResults = maxResultsField.val() || o.maxResults || 1000;
					offset = Math.floor((matches - 1) / maxResults) * maxResults;
					doFilter();
				});
				/* Max Results Field */
				maxResultsField.val(o.maxResults || 1000);
				var oldMaxResultsVal = maxResultsField.val();
				var maxResultsChange = function() {
					var newMaxResultsVal = maxResultsField.val();
					if (newMaxResultsVal != oldMaxResultsVal) {
						oldMaxResultsVal = newMaxResultsVal;
						offset = 0;
						doFilter();
					}
				};
				maxResultsField.unbind('change').bind('change', maxResultsChange);
				maxResultsField.unbind('keydown').bind('keydown', maxResultsChange);
				maxResultsField.unbind('keyup').bind('keyup', maxResultsChange);
				maxResultsField.unbind('mousedown').bind('mousedown', maxResultsChange);
				maxResultsField.unbind('mouseup').bind('mouseup', maxResultsChange);
				/* Keyboard Bindings */
				$('body').bind('keydown', function(event) {
					if (event.which == 27) {
						searchField.val('');
						offset = 0;
						doFilter();
						searchField.focus();
						event.stopPropagation();
						event.preventDefault();
					}
					if (event.which == 33 && event.shiftKey) {
						prevPageButton.click();
						event.stopPropagation();
						event.preventDefault();
					}
					if (event.which == 34 && event.shiftKey) {
						nextPageButton.click();
						event.stopPropagation();
						event.preventDefault();
					}
					if (event.which == 35 && event.shiftKey) {
						lastPageButton.click();
						event.stopPropagation();
						event.preventDefault();
					}
					if (event.which == 36 && event.shiftKey) {
						firstPageButton.click();
						event.stopPropagation();
						event.preventDefault();
					}
				});
			}
			/* Initialize Add Button */
			if (o.addDialog) {
				$('.add-button').click(function() {
					$('.edit-dialog .dialog-title').text(o.addDialogTitle || 'Add');
					if (o.addDialogStart) o.addDialogStart();
					cmui.showDialog('edit');
				});
			}
			/* Initialize Delete Dialog */
			if (o.deleteable) {
				$('.cancel-delete-button').click(cmui.hideDialog);
				if (o.switchable) {
					$('.soft-delete-button').click(function() {
						cmui.hideDialog();
						cmui.showButterbar('Deactivating ' + $('.delete-name').text() + '...');
						doAjax({'action': 'deactivate', 'id': $('.delete-id').val()}, cmui.hideButterbar);
					});
				}
				$('.confirm-delete-button').click(function() {
					cmui.hideDialog();
					cmui.showButterbar('Deleting ' + $('.delete-name').text() + '...');
					doAjax({'action': 'delete', 'id': $('.delete-id').val()}, cmui.hideButterbar);
				});
			}
			/* Initialize Edit Dialog */
			if (o.addDialog || o.editDialog) {
				if (o.addEditDialogInit) o.addEditDialogInit();
				$('.cancel-edit-button').click(cmui.hideDialog);
				$('.confirm-edit-button').click(function() {
					cmui.hideDialog();
					var id = $(o.addEditDialogIdSelector || '.edit-id').val();
					var name = $(o.addEditDialogNameSelector || '.edit-name').val();
					cmui.showButterbar('Saving ' + name + '...');
					var data = o.addEditDialogGetSaveData ? o.addEditDialogGetSaveData(id, name) : {};
					data.action = 'save';
					doAjax(data, function() {
						var message = o.addEditDialogGetSaveMessage ?
						              o.addEditDialogGetSaveMessage(id, name) :
						              'Changes saved.';
						cmui.showButterbarPersistent(message);
					});
				});
			}
			/* Initialize Page */
			if (o.pageInit) o.pageInit();
		});
	};
})(jQuery,cmui,document);