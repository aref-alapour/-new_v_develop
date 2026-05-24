/**
 * دکمه کپی لینک کوتاه در ویرایشگر پست/برگه/محصول و تاکسونومی‌ها (همراه با باندل admin.js).
 */
(function ($) {
	'use strict';

	$(function () {
		$('.ez-copy-shortlink').on('click', function (e) {
			e.preventDefault();

			var targetId = $(this).data('target');
			var input = $('#' + targetId);
			var textToCopy = input.val();

			if (!textToCopy) {
				alert('لینک کوتاه موجود نیست!');
				return;
			}

			var $button = $(this);
			var originalText = $button.text();

			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(textToCopy).then(function () {
					showSuccessMessage($button, originalText);
				}).catch(function (err) {
					console.error('Clipboard API failed:', err);
					fallbackCopy(textToCopy, $button, originalText);
				});
			} else {
				fallbackCopy(textToCopy, $button, originalText);
			}
		});

		function fallbackCopy(text, $button, originalText) {
			var textarea = document.createElement('textarea');
			textarea.value = text;
			textarea.style.position = 'fixed';
			textarea.style.left = '-999999px';
			textarea.style.top = '-999999px';
			document.body.appendChild(textarea);

			textarea.focus();
			textarea.select();

			try {
				var successful = document.execCommand('copy');
				if (successful) {
					showSuccessMessage($button, originalText);
				} else {
					manualCopyPrompt(text, $button, originalText);
				}
			} catch (err) {
				console.error('execCommand failed:', err);
				manualCopyPrompt(text, $button, originalText);
			}

			document.body.removeChild(textarea);
		}

		function manualCopyPrompt(text, $button, originalText) {
			var tempInput = document.createElement('input');
			tempInput.value = text;
			tempInput.style.position = 'fixed';
			tempInput.style.left = '-999999px';
			tempInput.style.top = '-999999px';
			document.body.appendChild(tempInput);

			tempInput.focus();
			tempInput.select();

			alert('لینک انتخاب شد. لطفاً Ctrl+C (یا Cmd+C در مک) را فشار دهید تا کپی شود.');

			document.body.removeChild(tempInput);

			showSuccessMessage($button, originalText);
		}

		function showSuccessMessage($button, originalText) {
			$button.text('کپی شد!').addClass('button-primary');

			setTimeout(function () {
				$button.text(originalText).removeClass('button-primary');
			}, 2000);
		}
	});
})(jQuery);
