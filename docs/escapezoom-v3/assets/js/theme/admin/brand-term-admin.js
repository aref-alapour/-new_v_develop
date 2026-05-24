/**
 * مدیریت ردیف‌های اعضای برند و مرتب‌سازی در صفحهٔ ویرایش ترم product_brand.
 */
(function ($) {
	'use strict';

	function bindRowActions($row) {
		$row.find('.ez-brand-team-pick-image').off('click').on('click', function (e) {
			e.preventDefault();
			var $current = $(this).closest('tr');
			var frame = window.wp.media({
				title: 'انتخاب تصویر عضو',
				button: { text: 'استفاده از تصویر' },
				multiple: false
			});
			frame.on('select', function () {
				var attachment = frame.state().get('selection').first();
				if (!attachment) {
					return;
				}
				var json = attachment.toJSON();
				var thumb = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) ? json.sizes.thumbnail.url : (json.url || '');
				$current.find('.ez-brand-team-image-id').val(json.id || 0);
				$current.find('.ez-brand-team-thumb').attr('src', thumb).removeClass('is-hidden').show();
			});
			frame.open();
		});
	}

	function createRow() {
		var tpl = document.getElementById('ez-brand-team-row-template');
		if (!tpl || !tpl.content || !tpl.content.firstElementChild) {
			return $();
		}
		var frag = document.importNode(tpl.content, true);
		var $row = $(frag.firstElementChild);
		bindRowActions($row);
		return $row;
	}

	$(function () {
		var $tbody = $('#ez-brand-team-rows');
		if (!$tbody.length) {
			return;
		}

		$tbody.find('tr').each(function () {
			bindRowActions($(this));
		});

		if ($.fn.sortable && $tbody.find('tr').length) {
			$tbody.sortable({
				handle: '.ez-brand-team-sort-handle',
				axis: 'y',
				items: '> tr',
				opacity: 0.92,
				cursor: 'grabbing',
				tolerance: 'pointer',
				placeholder: 'ez-brand-team-sort-placeholder'
			});
		}

		$('#ez-brand-team-add').on('click', function (e) {
			e.preventDefault();
			var $row = createRow();
			if (!$row.length) {
				return;
			}
			$tbody.append($row);
			if ($.fn.sortable && $tbody.hasClass('ui-sortable')) {
				$tbody.sortable('refresh');
			}
		});

		$tbody.on('click', '.ez-brand-team-remove', function (e) {
			e.preventDefault();
			if ($tbody.find('tr').length <= 1) {
				$(this).closest('tr').find('input[type="text"]').val('');
				$(this).closest('tr').find('.ez-brand-team-image-id').val('0');
				$(this).closest('tr').find('.ez-brand-team-thumb').attr('src', '').addClass('is-hidden').hide();
				return;
			}
			$(this).closest('tr').remove();
			if ($.fn.sortable && $tbody.hasClass('ui-sortable')) {
				$tbody.sortable('refresh');
			}
		});
	});
})(jQuery);
