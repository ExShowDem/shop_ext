(function($) {
	$(document).ready(function () {
		var rows = $('table.js-redshopb-tree-order tbody tr'), levels = [], missingParents = [];
		rows.each(function(key, value) {
			var $tr = $(value);
			levels.push($tr.data('level'));
			if(rows.filter('[data-id="'+$tr.data('parent')+'"]').length == 0) {
				if(rows.filter('[data-parent="'+$tr.data('id')+'"]').length == 0) {
					missingParents.push($tr.data('id'))
				}
			}
		});

		rows.each(function(key, tr) {
			var $tr = $(tr);

			// Shows every toggler of rows with children
			if(rows.filter('[data-parent="'+$tr.data('id')+'"]').length) {
				$tr.find('td.js-redshopb-tree .js-redshop-children')
					.css('display', 'inline-block')
					.data('collapsed', false);
			}

		});

		function collapseTree(id, buttonRow, tr) {
			buttonRow.data('collapsed', true)
				.find('i')
					.removeClass('icon-chevron-up')
					.addClass('icon-chevron-down');

			childrenRows = rows.filter('[data-parent="'+id+'"]')
				.data('collapsed', true)
				.fadeOut()
				.each(function (key, value) {
					var trChild = $(value);
					var idChild = trChild.data('id');
					var buttonChild = trChild.find('.js-redshop-children');

					collapseTree(idChild, buttonChild, trChild);
				});
		}

		function expandTree(id, buttonRow, tr) {
			buttonRow.data('collapsed', false)
				.find('i')
					.removeClass('icon-chevron-down')
					.addClass('icon-chevron-up');

			childrenRows = rows.filter('[data-parent="'+id+'"]')
				.data('collapsed', false)
				.fadeIn();
		}

		$('table.js-redshopb-tree-order').on('click', '.js-redshop-children', function(e) {
			e.preventDefault();
			var $button = $(this), $tr = $button.closest('tr'),
				parentId = $tr.data('id'), parents = '';

			if($button.data('collapsed')) {
				expandTree(parentId, $button, $tr);
			} else {
				collapseTree(parentId, $button, $tr);
			}
		});
	});
})(jQuery)
