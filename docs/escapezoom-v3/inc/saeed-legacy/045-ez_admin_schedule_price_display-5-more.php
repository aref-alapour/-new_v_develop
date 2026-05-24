<?php
/**
 * ez_admin_schedule_price_display (+5 more)
 *
 * توابع: ez_admin_schedule_price_display, ez_reservation_info_enqueue_sortable, reservation_info_metabox, reservation_info_callback, process_price_field, save_reservation_info هوک‌ها: current_screen, add_meta_boxes, save_post
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 4049-4756)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * نمایش قیمت سانس در ادمین با جداکننده هزارگان (ذخیره بدون کاما در process_price_field).
 *
 * @param mixed $raw مقدار خام از پست متا.
 * @return string برای esc_attr در attribute اینپوت.
 */
function ez_admin_schedule_price_display($raw) {
    if ($raw === '' || $raw === null) {
        return '';
    }
    $s       = (string) $raw;
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $s       = str_replace($persian, $english, $s);
    $s       = str_replace([',', ' ', '،'], '', $s);
    if ($s === '' || !is_numeric($s)) {
        return esc_attr($raw);
    }
    $num = (float) $s;
    if (strpos((string) $raw, '.') !== false || strpos($s, '.') !== false) {
        $dec = strlen(rtrim(explode('.', $s, 2)[1] ?? '', '0'));
        $dec = min(max((int) $dec, 0), 4);

        return esc_attr(number_format($num, $dec, '.', ','));
    }

    return esc_attr(number_format($num, 0, '.', ','));
}

add_action('current_screen', 'ez_reservation_info_enqueue_sortable');
function ez_reservation_info_enqueue_sortable($screen) {
    if (!$screen || $screen->post_type !== 'product' || $screen->base !== 'post') {
        return;
    }
    wp_enqueue_script('jquery-ui-sortable');
}

add_action('add_meta_boxes', 'reservation_info_metabox');
function reservation_info_metabox() {
    add_meta_box(
        'reservation_info',
        'اطلاعات رزرواسیون',
        'reservation_info_callback',
        'product',
        'normal',
        'high'
    );
}
/*=========================================================================================*/
function reservation_info_callback($post) {

    $pish_pardakht_per_person   = get_post_meta($post->ID, 'pish_pardakht_per_person', true);
    $auto_disable               = get_post_meta($post->ID, 'auto_disable', true);
    $schedule_normals           = get_post_meta($post->ID, 'schedule_normals', true);
    $schedule_holidays          = get_post_meta($post->ID, 'schedule_holidays', true);
    if (!is_array($schedule_normals)) {
        $schedule_normals = [];
    }
    if (!is_array($schedule_holidays)) {
        $schedule_holidays = [];
    }
    $holidays_empty = count($schedule_holidays) === 0;
    ?>

    <div class="reservation_info_section_wrapper">
        <label style="width: 120px;display: inline-block;">تعداد بیعانه: </label>
        <select style="width: 100px;" name="pish_pardakht_per_person">
            <?php
            $ppl = [1, 2, 3, 4, 10];
            foreach ( $ppl as $val ) : ?>
                <option value="<?php echo $val ?>" <?php echo esc_attr($pish_pardakht_per_person) == $val ? 'selected' : ''; ?>><?php echo $val ?></option>
            <?php
            endforeach; ?>
        </select>
    </div>
    <hr>

    <div class="reservation_info_section_wrapper">
        <label style="width: 120px;display: inline-block;">زمان غیرفعال شدن: </label>

        <select name="auto_disable" style="width:120px;">
            <option value="15"  <?php echo ($auto_disable == 15)  ? 'selected' : ''; ?>>15 دقیقه</option>
            <option value="30"  <?php echo ($auto_disable == 30)  ? 'selected' : ''; ?>>30 دقیقه</option>
            <option value="60"  <?php echo ($auto_disable == 60)  ? 'selected' : ''; ?>>60 دقیقه</option>
            <option value="120" <?php echo ($auto_disable == 120) ? 'selected' : ''; ?>>120 دقیقه</option>
            <option value="180" <?php echo ($auto_disable == 180) ? 'selected' : ''; ?>>180 دقیقه</option>
        </select>
    </div>
    <hr>

    <div class="ez-schedule-grid">
        <div class="reservation_info_section_wrapper ez-schedule-card" id="reservation_info_normals_schedule">
            <h3 class="panel-title ez-schedule-card-title">سانس‌های عادی</h3>
            <p class="ez-schedule-card-hint">ردیف‌ها را با دسته بگیرید و بکشید تا ترتیب ذخیره شود.</p>

            <div class="list_wrapper ez-sans-sortable-list" data-ez-name-prefix="schedule_normals">

                <?php
                foreach ( $schedule_normals as $key => $normal_day ) : ?>

                    <div class="reservation_info_schedule_wrapper">
                        <div class="ez-sans-one-row">
                            <button type="button" class="ez-sans-drag-handle" aria-label="جابه‌جایی ردیف" title="کشیدن برای مرتب‌سازی">⋮⋮</button>
                            <div class="ez-sans-field-stacked ez-sans-field-time">
                                <label>ساعت</label>
                                <input name="schedule_normals[<?php echo esc_attr((string) $key); ?>][time]" type="time" value="<?php echo esc_attr($normal_day['time'] ?? ''); ?>" class="schedule_sans_time"/>
                            </div>
                            <div class="ez-sans-field-stacked">
                                <label>قیمت عادی</label>
                                <input autocomplete="off" name="schedule_normals[<?php echo esc_attr((string) $key); ?>][price]" type="text" value="<?php echo ez_admin_schedule_price_display($normal_day['price'] ?? ''); ?>" class="schedule_sans_price ez-price-input" inputmode="decimal"/>
                            </div>
                            <div class="ez-sans-field-stacked">
                                <label>قیمت تخفیف دار</label>
                                <input autocomplete="off" name="schedule_normals[<?php echo esc_attr((string) $key); ?>][off_price]" type="text" value="<?php echo ez_admin_schedule_price_display($normal_day['off_price'] ?? ''); ?>" class="schedule_sans_off_price ez-price-input" inputmode="decimal"/>
                            </div>
                            <div class="ez-sans-row-actions">
                                <button class="list_remove_button" type="button" aria-label="حذف ردیف">×</button>
                            </div>
                        </div>
                    </div>

                <?php
                endforeach; ?>

            </div>
            <div class="ez-schedule-toolbar">
                <button type="button" class="button button-small list_add_button">افزودن سانس</button>
            </div>
        </div>

        <div class="reservation_info_section_wrapper ez-schedule-card" id="reservation_info_holidays_schedule">
            <h3 class="panel-title ez-schedule-card-title">سانس‌های تعطیل</h3>
            <p class="ez-schedule-card-hint">پس از کپی می‌توانید هر فیلد را جداگانه ویرایش کنید.</p>

            <div class="ez-copy-from-normals-wrap" <?php echo $holidays_empty ? '' : 'style="display:none;"'; ?>>
                <button type="button" class="button button-secondary button-small ez-copy-sans-from-normals">کپی از سانس‌های عادی</button>
            </div>

            <div class="list_wrapper ez-sans-sortable-list" data-ez-name-prefix="schedule_holidays">

                <?php
                foreach ( $schedule_holidays as $key => $holidays_day ) : ?>

                    <div class="reservation_info_schedule_wrapper">
                        <div class="ez-sans-one-row">
                            <button type="button" class="ez-sans-drag-handle" aria-label="جابه‌جایی ردیف" title="کشیدن برای مرتب‌سازی">⋮⋮</button>
                            <div class="ez-sans-field-stacked ez-sans-field-time">
                                <label>ساعت</label>
                                <input name="schedule_holidays[<?php echo esc_attr((string) $key); ?>][time]" type="time" class="schedule_sans_time" value="<?php echo esc_attr($holidays_day['time'] ?? ''); ?>"/>
                            </div>
                            <div class="ez-sans-field-stacked">
                                <label>قیمت عادی</label>
                                <input autocomplete="off" name="schedule_holidays[<?php echo esc_attr((string) $key); ?>][price]" type="text" class="schedule_sans_price ez-price-input" value="<?php echo ez_admin_schedule_price_display($holidays_day['price'] ?? ''); ?>" inputmode="decimal"/>
                            </div>
                            <div class="ez-sans-field-stacked">
                                <label>قیمت تخفیف دار</label>
                                <input autocomplete="off" name="schedule_holidays[<?php echo esc_attr((string) $key); ?>][off_price]" type="text" class="schedule_sans_off_price ez-price-input" value="<?php echo ez_admin_schedule_price_display($holidays_day['off_price'] ?? ''); ?>" inputmode="decimal"/>
                            </div>
                            <div class="ez-sans-row-actions">
                                <button class="list_remove_button" type="button" aria-label="حذف ردیف">×</button>
                            </div>
                        </div>
                    </div>

                <?php
                endforeach; ?>

            </div>
            <div class="ez-schedule-toolbar">
                <button type="button" class="button button-small list_add_button">افزودن سانس</button>
            </div>
        </div>
    </div>

    <style>
        #poststuff .ez-schedule-grid .schedule_sans_time,
        #poststuff .ez-schedule-grid .schedule_sans_price,
        #poststuff .ez-schedule-grid .schedule_sans_off_price,
        .ez-schedule-card input,
        .ez-schedule-card select {
            border: 1px solid #c3c4c7 !important;
            border-radius: 4px !important;
            box-shadow: inset 0 1px 1px rgba(0,0,0,.06) !important;
            transition: border-color .15s ease, box-shadow .15s ease !important;
        }
        .ez-schedule-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            align-items: start;
            margin: 16px 0;
        }
        @media (max-width: 1100px) {
            .ez-schedule-grid {
                grid-template-columns: 1fr;
            }
        }
        .ez-schedule-card {
            margin: 0 !important;
            padding: 14px 14px 12px;
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .ez-schedule-card-title {
            margin: 0 0 6px;
            font-size: 14px;
        }
        .ez-schedule-card-hint {
            margin: 0 0 12px;
            font-size: 12px;
            color: #646970;
            line-height: 1.4;
        }
        .ez-copy-from-normals-wrap {
            margin-bottom: 12px;
        }
        .ez-schedule-toolbar {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f1;
        }
        .schedule_sans_time {
            width: 118px;
            max-width: 100%;
            display: block;
            box-sizing: border-box;
        }
        .schedule_sans_price,
        .schedule_sans_off_price {
            width: 88px;
            max-width: 100%;
            display: block;
            box-sizing: border-box;
        }
        .ez-sans-one-row {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: flex-end;
            gap: 10px;
            min-width: 0;
            width: 100%;
        }
        .ez-sans-field-stacked {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 4px;
            flex: 0 0 auto;
            min-width: 0;
        }
        .ez-sans-field-stacked label {
            font-size: 11px;
            font-weight: 600;
            color: #646970;
            margin: 0;
            line-height: 1.2;
        }
        .ez-sans-drag-handle {
            flex: 0 0 auto;
            align-self: flex-end;
            margin-bottom: 2px;
            width: 28px;
            height: 32px;
            padding: 0;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            background: #f6f7f7;
            color: #50575e;
            font-size: 12px;
            line-height: 1;
            cursor: grab;
            letter-spacing: -2px;
            touch-action: none;
        }
        .ez-sans-drag-handle:active {
            cursor: grabbing;
        }
        .ez-sans-sort-placeholder {
            border: 2px dashed #2271b1;
            background: #f0f6fc;
            border-radius: 8px;
            min-height: 56px;
            margin: 6px 0;
            box-sizing: border-box;
            visibility: visible !important;
        }
        #reservation_info .ui-sortable-helper {
            z-index: 100050 !important;
            pointer-events: none;
        }
        .ui-sortable-helper.reservation_info_schedule_wrapper {
            box-shadow: 0 8px 24px rgba(0,0,0,.18);
            background: #fff !important;
        }
        .list_add_button.button {
            min-height: 30px;
            line-height: 1.2;
        }
        .list_remove_button {
            width: 30px;
            height: 30px;
            padding: 0;
            border: 1px solid #d63638;
            background: #fcf0f1;
            color: #b32d2e;
            font-size: 18px;
            line-height: 1;
            border-radius: 4px;
            cursor: pointer;
            transition: background .12s ease, color .12s ease;
        }
        .list_remove_button:hover {
            background: #d63638;
            color: #fff;
        }
        .reservation_info_schedule_wrapper {
            margin: 6px 0;
            background: #f6f7f7;
            padding: 8px 10px;
            border: 1px solid #e8e8e8;
            border-radius: 8px;
        }
        .ez-sans-row-actions {
            flex: 0 0 auto;
            align-self: flex-end;
            margin-bottom: 2px;
            margin-inline-start: auto;
        }
        .ez-sans-row-actions .list_remove_button {
            margin: 0;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            var PREFIX_NORMALS = 'schedule_normals';
            var PREFIX_HOLIDAYS = 'schedule_holidays';

            function ezEscRegex(s) {
                return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function ezNormalizeDigits(str) {
                var persian = '۰۱۲۳۴۵۶۷۸۹';
                var english = '0123456789';
                var out = '';
                var i;
                var ch;
                var idx;
                for (i = 0; i < str.length; i++) {
                    ch = str.charAt(i);
                    idx = persian.indexOf(ch);
                    out += idx >= 0 ? english.charAt(idx) : ch;
                }
                return out;
            }

            function ezStripPriceCommas(s) {
                var t = ezNormalizeDigits(String(s));
                return t.replace(/,/g, '').replace(/\s/g, '').replace(/،/g, '');
            }

            function ezFormatPriceThousands(s) {
                var raw = ezStripPriceCommas(s);
                if (raw === '' || raw === '-') {
                    return '';
                }
                var neg = raw.charAt(0) === '-';
                if (neg) {
                    raw = raw.slice(1);
                }
                var dotPos = raw.indexOf('.');
                var intPart = (dotPos === -1 ? raw : raw.slice(0, dotPos)).replace(/\D/g, '');
                var decPart = dotPos === -1 ? '' : raw.slice(dotPos + 1).replace(/\D/g, '').slice(0, 4);
                if (intPart === '') {
                    intPart = '0';
                }
                intPart = intPart.replace(/^0+(?=\d)/, '');
                if (intPart === '') {
                    intPart = '0';
                }
                var withComma = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                var out = (neg ? '-' : '') + withComma;
                if (dotPos !== -1) {
                    out += '.' + decPart;
                }
                return out;
            }

            function ezRefreshSortable($list) {
                if ($list.length && $list.data('ui-sortable')) {
                    $list.sortable('refresh');
                }
            }

            function ezReindexScheduleRows($list) {
                var prefix = $list.data('ez-name-prefix');
                if (!prefix) {
                    return;
                }
                var esc = ezEscRegex(prefix);
                $list.children('.reservation_info_schedule_wrapper').each(function(index) {
                    $(this).find('input[name^="' + prefix + '"]').each(function() {
                        var $inp = $(this);
                        var name = $inp.attr('name');
                        if (!name) {
                            return;
                        }
                        var newName = name.replace(new RegExp('^' + esc + '\\[\\d+\\]'), prefix + '[' + index + ']');
                        $inp.attr('name', newName);
                    });
                });
            }

            function ezDestroySortable($list) {
                if ($list.length && $list.data('ui-sortable')) {
                    try {
                        $list.sortable('destroy');
                    } catch (e) {
                        /* noop */
                    }
                }
            }

            function ezInitScheduleSortable($list) {
                if (!$list.length || typeof $.fn.sortable !== 'function') {
                    return;
                }
                ezDestroySortable($list);
                $list.sortable({
                    items: '> .reservation_info_schedule_wrapper',
                    handle: '.ez-sans-drag-handle',
                    /* Default ui.mouse cancel includes "button"; drag handle is a button — override so handle works. */
                    cancel: 'input,textarea,select,option,.list_remove_button',
                    axis: 'y',
                    helper: 'clone',
                    opacity: 0.72,
                    scroll: true,
                    scrollSensitivity: 48,
                    placeholder: 'ez-sans-sort-placeholder',
                    tolerance: 'pointer',
                    forcePlaceholderSize: true,
                    cursor: 'move',
                    distance: 4,
                    start: function(event, ui) {
                        ui.item.css('background-color', '#eef0f2');
                    },
                    stop: function(event, ui) {
                        ui.item.removeAttr('style');
                    },
                    update: function() {
                        ezReindexScheduleRows($list);
                    }
                });
            }

            function ezBindSchedulePriceUi($root) {
                $root.find('.ez-price-input').each(function() {
                    var $inp = $(this);
                    var v = $inp.val();
                    if (v !== '') {
                        $inp.val(ezFormatPriceThousands(v));
                    }
                });
            }

            function ezUpdateHolidayCopyUi() {
                var empty = $('#reservation_info_holidays_schedule .list_wrapper').children('.reservation_info_schedule_wrapper').length === 0;
                $('.ez-copy-from-normals-wrap').toggle(empty);
            }

            function ezRowHtml(prefix, index) {
                var h = '';
                h += '<div class="reservation_info_schedule_wrapper">';
                h += '<div class="ez-sans-one-row">';
                h += '<button type="button" class="ez-sans-drag-handle" aria-label="جابه‌جایی ردیف" title="کشیدن برای مرتب‌سازی">⋮⋮</button>';
                h += '<div class="ez-sans-field-stacked ez-sans-field-time"><label>ساعت</label><input name="' + prefix + '[' + index + '][time]" type="time" class="schedule_sans_time"/></div>';
                h += '<div class="ez-sans-field-stacked"><label>قیمت عادی</label><input autocomplete="off" name="' + prefix + '[' + index + '][price]" type="text" class="schedule_sans_price ez-price-input" inputmode="decimal"/></div>';
                h += '<div class="ez-sans-field-stacked"><label>قیمت تخفیف دار</label><input autocomplete="off" name="' + prefix + '[' + index + '][off_price]" type="text" class="schedule_sans_off_price ez-price-input" inputmode="decimal"/></div>';
                h += '<div class="ez-sans-row-actions"><button class="list_remove_button" type="button" aria-label="حذف ردیف">×</button></div>';
                h += '</div></div>';
                return h;
            }

            var ezSortableRetries = 0;
            function ezTryInitSortableDeferred() {
                if (typeof $.fn.sortable === 'function') {
                    ezInitScheduleSortable($('#reservation_info_normals_schedule .list_wrapper'));
                    ezInitScheduleSortable($('#reservation_info_holidays_schedule .list_wrapper'));
                    return;
                }
                if (ezSortableRetries++ >= 80) {
                    return;
                }
                window.setTimeout(ezTryInitSortableDeferred, 50);
            }
            ezTryInitSortableDeferred();

            ezBindSchedulePriceUi($('.ez-schedule-grid'));

            $('body').on('focus', '.ez-schedule-grid .ez-price-input', function() {
                var $el = $(this);
                var stripped = ezStripPriceCommas($el.val());
                $el.val(stripped);
            });

            $('body').on('blur', '.ez-schedule-grid .ez-price-input', function() {
                var $el = $(this);
                var v = $el.val();
                if (v === '') {
                    return;
                }
                $el.val(ezFormatPriceThousands(v));
            });

            $('body').on('input', '.ez-schedule-grid .ez-price-input', function() {
                var el = this;
                var $el = $(el);
                var cur = $el.val();
                var fmt = ezFormatPriceThousands(cur);
                if (fmt !== cur) {
                    $el.val(fmt);
                }
            });

            $('body').on('click', '#reservation_info_normals_schedule .list_add_button', function() {
                var $list = $(this).closest('#reservation_info_normals_schedule').find('.list_wrapper');
                var nextIdx = $list.children('.reservation_info_schedule_wrapper').length;
                $list.append(ezRowHtml(PREFIX_NORMALS, nextIdx));
                ezReindexScheduleRows($list);
                ezRefreshSortable($list);
            });

            $('body').on('click', '#reservation_info_normals_schedule .list_remove_button', function() {
                if (!window.confirm('این سانس حذف شود؟')) {
                    return;
                }
                var $list = $(this).closest('#reservation_info_normals_schedule').find('.list_wrapper');
                $(this).closest('.reservation_info_schedule_wrapper').remove();
                ezReindexScheduleRows($list);
                ezRefreshSortable($list);
            });

            $('body').on('click', '#reservation_info_holidays_schedule .list_add_button', function() {
                var $list = $(this).closest('#reservation_info_holidays_schedule').find('.list_wrapper');
                var nextIdx = $list.children('.reservation_info_schedule_wrapper').length;
                $list.append(ezRowHtml(PREFIX_HOLIDAYS, nextIdx));
                ezReindexScheduleRows($list);
                ezRefreshSortable($list);
                ezUpdateHolidayCopyUi();
            });

            $('body').on('click', '#reservation_info_holidays_schedule .list_remove_button', function() {
                if (!window.confirm('این سانس حذف شود؟')) {
                    return;
                }
                var $list = $(this).closest('#reservation_info_holidays_schedule').find('.list_wrapper');
                $(this).closest('.reservation_info_schedule_wrapper').remove();
                ezReindexScheduleRows($list);
                ezRefreshSortable($list);
                ezUpdateHolidayCopyUi();
            });

            $('body').on('click', '.ez-copy-sans-from-normals', function() {
                var $normList = $('#reservation_info_normals_schedule .list_wrapper');
                var $holList = $('#reservation_info_holidays_schedule .list_wrapper');
                if (!$normList.children('.reservation_info_schedule_wrapper').length) {
                    window.alert('ابتدا برای «سانس‌های عادی» حداقل یک ردیف تعریف کنید.');
                    return;
                }
                if ($holList.children('.reservation_info_schedule_wrapper').length) {
                    return;
                }
                ezDestroySortable($holList);
                $normList.children('.reservation_info_schedule_wrapper').each(function(i) {
                    var $clone = $(this).clone();
                    var pat = new RegExp('^' + ezEscRegex(PREFIX_NORMALS) + '\\[\\d+\\]');
                    $clone.find('input[name^="' + PREFIX_NORMALS + '"]').each(function() {
                        var $inp = $(this);
                        var n = $inp.attr('name');
                        if (n) {
                            $inp.attr('name', n.replace(pat, PREFIX_HOLIDAYS + '[' + i + ']'));
                        }
                    });
                    $holList.append($clone);
                });
                ezReindexScheduleRows($holList);
                ezBindSchedulePriceUi($holList);
                ezInitScheduleSortable($holList);
                ezUpdateHolidayCopyUi();
            });

            $('form#post').on('submit', function() {
                $('.ez-schedule-grid .ez-price-input').each(function() {
                    var $inp = $(this);
                    $inp.val(ezStripPriceCommas($inp.val()));
                });
                ezReindexScheduleRows($('#reservation_info_normals_schedule .list_wrapper'));
                ezReindexScheduleRows($('#reservation_info_holidays_schedule .list_wrapper'));
            });
        });
    </script>

    <?php
}
/*=========================================================================================*/
// تابع کمکی برای پردازش فیلدهای قیمت
function process_price_field($price) {
    // حذف فاصله‌های اول و آخر
    $price = trim($price);

    // تبدیل اعداد فارسی به انگلیسی
    $persian_numbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english_numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    $price = str_replace($persian_numbers, $english_numbers, $price);

    // حذف کاما و فاصله‌های اضافی
    $price = str_replace([',', ' '], '', $price);

    return $price;
}

add_action('save_post', 'save_reservation_info', 10, 3);
function save_reservation_info($product_id, $post, $update) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $product_id)) return;

/**
 * POST: pish_pardakht_per_person
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/045-ez_admin_schedule_price_display-5-more.php:645
 */
    if (isset($_POST['pish_pardakht_per_person']))
        update_post_meta($product_id, 'pish_pardakht_per_person', sanitize_text_field($_POST['pish_pardakht_per_person']));

/**
 * POST: auto_disable
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/045-ez_admin_schedule_price_display-5-more.php:648
 */
    if (isset($_POST['auto_disable']))
        update_post_meta($product_id, 'auto_disable', sanitize_text_field($_POST['auto_disable']));

/**
 * POST: schedule_normals
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/045-ez_admin_schedule_price_display-5-more.php:651
 */
    if (isset($_POST['schedule_normals'])) {

        $temp = [];
        foreach ( $_POST['schedule_normals'] as $val ) {
            // پردازش فیلدهای قیمت قبل از ذخیره
            if (isset($val['price'])) {
                $val['price'] = process_price_field($val['price']);
            }
            if (isset($val['off_price'])) {
                $val['off_price'] = process_price_field($val['off_price']);
            }
            $temp[] = $val;
        }

        update_post_meta($product_id, 'schedule_normals', $temp);
    }

/**
 * POST: schedule_holidays
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/045-ez_admin_schedule_price_display-5-more.php:668
 */
    if (isset($_POST['schedule_holidays'])) {

        $temp = [];
        foreach ( $_POST['schedule_holidays'] as $val ) {
            // پردازش فیلدهای قیمت قبل از ذخیره
            if (isset($val['price'])) {
                $val['price'] = process_price_field($val['price']);
            }
            if (isset($val['off_price'])) {
                $val['off_price'] = process_price_field($val['off_price']);
            }
            $temp[] = $val;
        }

        update_post_meta($product_id, 'schedule_holidays', $temp);
    }

    if ( $post->post_type == 'product' && $update ) {

        $last_price         = [];
        $special_discount   = 1;
        $sanses             = get_sanses($product_id);

        // ممکنه محصول در تخفیف ویژه باشه
        if ( get_post_meta($product_id, 'special_discount_enable', true) ) {
            if ( get_post_meta($product_id, 'special_discount_date', true) > time() ) {
                $percentage = floatval(get_post_meta($product_id, 'special_discount_percentage', true));
                $special_discount = 1 - ($percentage / 100);
            }
        }

        $special_discount = floatval($special_discount);

        foreach ( $sanses as $sans_by_type )
            foreach ( $sans_by_type as $sans ) {
                $price = $sans['off_price'] ? $sans['off_price'] : $sans['price'];
                $last_price[] = (float) $price * (float) $special_discount;
            }

        foreach ( $sanses as $sans_by_type ) {
            foreach ( $sans_by_type as $sans ) {
                $base_price = $sans['off_price'] ?: $sans['price'];
                $base_price = floatval($base_price);

                $last_price[] = $base_price * $special_discount;
            }
        }

        if ( !empty($last_price) )
            update_post_meta($product_id, 'min_price', min($last_price));

        ez_reservation( array('type' => 'update_product_sub_data', 'data' => array('room_id' => $product_id, 'schedule' => $sanses, 'pish_person' => $_POST['pish_pardakht_per_person'])) );
    }
}
