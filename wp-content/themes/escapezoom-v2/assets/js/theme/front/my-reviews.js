jQuery(function ($) {
    if (typeof MyReviewsObject === 'undefined') {
        return;
    }

    if (!document.getElementById('my-reviews-spin-style')) {
        $('<style id="my-reviews-spin-style">@keyframes myReviewsSpin{to{transform:rotate(360deg)}}</style>').appendTo('head');
    }

    const Toast = typeof Swal !== 'undefined' ? Swal.mixin({
        toast: true,
        position: 'bottom-start',
        showConfirmButton: false,
        timer: 3000,
    }) : null;

    function ezProductReviewAjaxMessage(data) {
        if (data && typeof data === 'object' && data.message) {
            return data.message;
        }
        return data;
    }

    const rateColors = {
        1: '#E76262',
        2: '#DBAE41',
        3: '#9FC537',
        4: '#77BE39',
        5: '#02C96F',
    };

    const $detailOverlay = $('#my-reviews-detail-overlay');
    const $editOverlay = $('#my-reviews-edit-overlay');

    $detailOverlay.appendTo('body');
    $editOverlay.appendTo('body');

    function closeModals() {
        $detailOverlay.hide();
        $editOverlay.hide();
    }

    function parsePayload(el) {
        const raw = $(el).attr('data-review-json');
        if (!raw) {
            return null;
        }
        try {
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function applyPresetToRoot($root, preset) {
        $root.find('[data-rating-item].my-reviews-rate-btn').removeClass('active').removeAttr('style');
        if (!preset || typeof preset !== 'object') {
            return;
        }
        Object.keys(preset).forEach(function (key) {
            const rateNum = parseInt(preset[key], 10);
            if (!rateNum) {
                return;
            }
            const keyStr = String(key);
            const $btn = $root.find('.my-reviews-rate-btn[data-rating-item="' + keyStr + '"][data-rate="' + rateNum + '"]');
            if ($btn.length) {
                $btn.addClass('active').css({
                    background: rateColors[rateNum / 20],
                    color: '#FFFFFF',
                });
            }
        });
    }

    function mountRatingTemplate(productTypeName) {
        const isEscape = productTypeName === 'اتاق فرار';
        const sel = isEscape ? '[data-template="escape"]' : '[data-template="simple"]';
        const html = $('#my-reviews-rating-templates').find(sel).html();
        $('#my-reviews-rating-root').html(html || '');
    }

    $(document).on('click', '.my-reviews-open-detail', function () {
        const d = parsePayload(this);
        if (!d) {
            return;
        }
        $('#my-reviews-detail-title').text(d.title || '');
        $('#my-reviews-detail-status').text(d.status_label || '');
        $('#my-reviews-detail-body').text(d.full_content || '');
        const $ul = $('#my-reviews-detail-rates').empty();
        (d.rates_lines || []).forEach(function (line) {
            $ul.append($('<li>').text(line));
        });
        if (d.rating_avg) {
            $('#my-reviews-detail-avg').text(d.rating_avg);
            $('#my-reviews-detail-avg-wrap').show();
        } else {
            $('#my-reviews-detail-avg-wrap').hide();
        }
        $detailOverlay.css('display', 'flex');
    });

    $(document).on('click', '.my-reviews-open-edit', function () {
        const d = parsePayload(this);
        if (!d || !d.can_edit) {
            return;
        }
        $('#my-reviews-edit-product-title').text(d.title || '');
        $('#my-reviews-review_comment_id').val(d.comment_id || 0);
        $('#my-reviews-edit-content').val(d.full_content || '');
        mountRatingTemplate(d.product_type_name || '');
        applyPresetToRoot($('#my-reviews-rating-root'), d.preset_rates);
        $editOverlay.css('display', 'flex');
    });

    $(document).on('click', '.my-reviews-modal-close', function (e) {
        e.preventDefault();
        closeModals();
    });

    $detailOverlay.on('click', function (e) {
        if (e.target === this) {
            closeModals();
        }
    });
    $editOverlay.on('click', function (e) {
        if (e.target === this) {
            closeModals();
        }
    });

    $(document).on('click', '#my-reviews-edit-overlay .my-reviews-rate-btn[data-rating-item]', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const ratingItem = String($btn.data('rating-item'));
        const rate = parseInt($btn.attr('data-rate'), 10);
        const $root = $btn.closest('#my-reviews-rating-root');
        if (!$root.length || !rate) {
            return;
        }
        $root.find('.my-reviews-rate-btn[data-rating-item="' + ratingItem + '"]')
            .removeClass('active')
            .removeAttr('style');
        const step = rate / 20;
        $btn.addClass('active').css({
            background: rateColors[step],
            color: '#FFFFFF',
        });
    });

    $(document).on('submit', '.my-reviews-edit-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const rate = {};
        $form.find('#my-reviews-rating-root .my-reviews-rate-btn.active').each(function () {
            const $b = $(this);
            rate[String($b.data('rating-item'))] = parseInt($b.attr('data-rate'), 10);
        });
        const ajaxData = {
            action: 'v2_ajax_handler',
            nonce: MyReviewsObject.nonce,
            callback: 'product_edit_comment',
            content: $form.find('#my-reviews-edit-content').val(),
            rate: Object.assign({}, rate),
            comment_id: parseInt($form.find('#my-reviews-review_comment_id').val(), 10) || 0,
        };
        const $submit = $form.find('.my-reviews-submit');
        const submitHtml = $submit.data('original-html');
        if (typeof submitHtml === 'undefined') {
            $submit.data('original-html', $submit.html());
        }
        const spinnerHtml = '<div class="mx-auto flex items-center justify-center py-1" aria-hidden="true"><div style="border:4px solid rgba(255,255,255,0.35);border-top-color:#fff;width:28px;height:28px;border-radius:50%;animation:myReviewsSpin 0.9s linear infinite"></div></div>';
        $.ajax({
            url: MyReviewsObject.ajaxUrl,
            type: 'POST',
            data: ajaxData,
            beforeSend: function () {
                $submit.prop('disabled', true).html(spinnerHtml);
            },
            success: function (response) {
                const msg = ezProductReviewAjaxMessage(response.data);
                if (Toast) {
                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: typeof msg === 'string' ? msg : (response.success ? 'OK' : 'خطا'),
                    });
                }
                if (response.success) {
                    closeModals();
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 400);
                }
            },
            error: function () {
                if (Toast) {
                    Toast.fire({ icon: 'error', title: 'خطا در ارتباط با سرور' });
                }
            },
            complete: function () {
                $submit.prop('disabled', false).html($submit.data('original-html') || 'ذخیره تغییرات');
            },
        });
    });
});
