/**
 * مودال مشاهدهٔ اعضای برند در لیست taxonomy product_brand.
 */
(function ($) {
	'use strict';

	function closeModal() {
		$('#ez-brand-members-modal').hide();
	}

	function escapeHtml(s) {
		var div = document.createElement('div');
		div.textContent = s;
		return div.innerHTML;
	}

	function renderMembers(members) {
		if (!members || !members.length) {
			return '<p class="description ez-brand-members-modal__empty">' +
				escapeHtml('برای این برند اعضایی مشخص نشده است.') +
				'</p>';
		}
		var html = '<ul class="ez-brand-members-modal__list">';
		$.each(members, function (i, m) {
			var name = m.name || '';
			var pos = m.position || '';
			var img = m.thumb || '';
			html += '<li class="ez-brand-members-modal__item">';
			if (img) {
				html += '<img class="ez-brand-members-modal__avatar" src="' + escapeHtml(img) + '" alt="" width="48" height="48">';
			} else {
				html += '<span class="dashicons dashicons-admin-users ez-brand-members-modal__placeholder-icon" aria-hidden="true"></span>';
			}
			html += '<div><strong>' + escapeHtml(name) + '</strong>';
			if (pos) {
				html += '<br><span class="ez-brand-members-modal__position">' + escapeHtml(pos) + '</span>';
			}
			html += '</div></li>';
		});
		html += '</ul>';
		return html;
	}

	$(function () {
		var $modal = $('#ez-brand-members-modal');
		if (!$modal.length) {
			return;
		}

		$(document).on('click', '.ez-brand-view-members', function (e) {
			e.preventDefault();
			var raw = $(this).attr('data-members') || '[]';
			var members;
			try {
				members = JSON.parse(raw);
			} catch (err) {
				members = [];
			}
			var listHtml = renderMembers(Array.isArray(members) ? members : []);
			$modal.find('.ez-brand-members-modal__body').html(listHtml);
			$modal.show();
		});

		$modal.on('click', '.ez-brand-members-modal__backdrop, .ez-brand-members-modal__close', function (e) {
			e.preventDefault();
			closeModal();
		});

		$(document).on('keydown', function (e) {
			if (e.key === 'Escape' && $modal.is(':visible')) {
				closeModal();
			}
		});
	});
})(jQuery);
