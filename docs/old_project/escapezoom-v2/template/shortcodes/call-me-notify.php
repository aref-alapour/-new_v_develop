<?php

/**
 * Shortcode: [call_me_notify subject="موضوع کمپین"]
 * Displays a phone number input box with notify button
 */

add_shortcode('call_me_notify', 'call_me_notify_shortcode');

function call_me_notify_shortcode($atts)
{
    $atts = shortcode_atts([
        'subject' => 'عمومی',
        'text_color' => '#ffffff',
        'button_bg_color' => '#fbbf24',
        'icon_color' => '#000000',
        'text' => 'برای اطلاع از شروع کمپین، شماره موبایل خود را وارد کنید',
    ], $atts);

    $subject = sanitize_text_field($atts['subject']);
    $text_color = sanitize_hex_color($atts['text_color']) ?: '#ffffff';
    $button_bg_color = sanitize_hex_color($atts['button_bg_color']) ?: '#fbbf24';
    $icon_color = sanitize_hex_color($atts['icon_color']) ?: '#000000';
    $text = !empty($atts['text']) ? $atts['text'] : 'برای اطلاع از شروع کمپین، شماره موبایل خود را وارد کنید';

    // Generate unique ID for this instance
    $unique_id = 'call-me-' . wp_generate_password(6, false);

    ob_start();
?>
    <div class="call-me-notify-box <?php echo esc_attr($unique_id); ?>" data-subject="<?php echo esc_attr($subject); ?>">
        <?php if (!empty($text)): ?>
            <p class="call-me-text" style="color: <?php echo esc_attr($text_color); ?>; margin-bottom: 12px; font-size: 18px; text-align: center;">
                <?php echo esc_html($text); ?>
            </p>
        <?php endif; ?>
        <div class="call-me-notify-form">
            <div class="call-me-input-wrapper">
                <input
                    type="tel"
                    class="call-me-phone-input"
                    placeholder="شماره موبایل خود را وارد کنید"
                    maxlength="11"
                    dir="ltr">
                <button type="button" class="call-me-submit-btn" title="ارسال">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 21L23 12L2 3V10L17 12L2 14V21Z" fill="currentColor" />
                    </svg>
                </button>
                <div class="call-me-error"></div>
            </div>
        </div>
    </div>

    <!-- Success message fixed at bottom left -->
    <div class="call-me-success <?php echo esc_attr($unique_id); ?>-success" style="display: none;">
        ✓ شماره شما با موفقیت ثبت شد
    </div>

    <style>
        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?> {
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
        }

        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?>.call-me-notify-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?>.call-me-input-wrapper {
            display: flex;
            gap: 8px;
            align-items: stretch;
            position: relative;
        }

        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?>.call-me-error {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 2px;
            color: #fca5a5;
            font-size: 14px;
            text-align: right;
            display: none;
            z-index: 10;
            padding: 6px 12px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
        }

        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?>.call-me-phone-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid <?php echo esc_attr($button_bg_color); ?>;
            border-radius: 12px;
            font-size: 18px;
            background: white;
            color: #1f2937;
            outline: none;
            transition: all 0.3s;
            height: 56px;
            box-sizing: border-box;
        }

        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?>.call-me-phone-input:focus {
            border-color: <?php echo esc_attr($button_bg_color); ?>;
            box-shadow: 0 0 0 3px <?php echo esc_attr($button_bg_color); ?>33;
        }

        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?>.call-me-submit-btn {
            width: 56px;
            height: 56px;
            min-width: 56px;
            padding: 0;
            background: <?php echo esc_attr($button_bg_color); ?>;
            color: <?php echo esc_attr($icon_color); ?>;
            border: 2px solid <?php echo esc_attr($button_bg_color); ?>;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            transform: scale(1);
            box-sizing: border-box;
            flex-shrink: 0;
        }

        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?>.call-me-submit-btn:hover:not(:disabled) {
            background: <?php echo esc_attr($button_bg_color); ?>;
            opacity: 0.9;
            transform: scale(1.05);
        }

        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?>.call-me-submit-btn:active:not(:disabled) {
            transform: scale(0.95);
        }

        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?>.call-me-submit-btn:disabled {
            background: #9ca3af !important;
            border-color: #9ca3af !important;
            color: #6b7280 !important;
            cursor: not-allowed;
            transform: scale(1);
            opacity: 0.6;
        }

        .call-me-notify-box.<?php echo esc_attr($unique_id) . ' '; ?>.call-me-submit-btn svg {
            width: 24px;
            height: 24px;
        }

        /* Success message - Fixed at bottom left */
        .call-me-success.<?php echo esc_attr($unique_id); ?>-success {
            position: fixed;
            bottom: 20px;
            left: 20px;
            color: #ffffff;
            font-size: 16px;
            padding: 12px 20px;
            background: rgba(34, 197, 94, 0.95);
            border-radius: 8px;
            border: 1px solid rgba(34, 197, 94, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            white-space: nowrap;
            font-weight: 500;
            animation: slideInLeft 0.3s ease-out;
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            var $box = $('.call-me-notify-box.<?php echo esc_js($unique_id); ?>');
            var $input = $box.find('.call-me-phone-input');
            var $btn = $box.find('.call-me-submit-btn');
            var $error = $box.find('.call-me-error');
            var $success = $('.call-me-success.<?php echo esc_js($unique_id); ?>-success');
            var subject = $box.data('subject');

            function validateMobile(value) {
                if (!value) {
                    return 'شماره موبایل ضروری میباشد';
                }

                value = value.replace(/\s/g, '');

                if (value.length < 10 || value.length > 11) {
                    return 'شماره موبایل صحیح نیست';
                }

                if (!/^(\+98|0|0098)?9\d{9}$/.test(value)) {
                    return 'شماره موبایل صحیح نیست';
                }

                return '';
            }

            $input.on('input', function() {
                var value = $(this).val();
                var error = validateMobile(value);

                if (error) {
                    $error.show().text(error);
                    $btn.prop('disabled', true);
                } else {
                    $error.hide();
                    $btn.prop('disabled', false);
                }
            });

            $btn.on('click', function() {
                var phone = $input.val().replace(/\s/g, '');
                var error = validateMobile(phone);

                if (error) {
                    $error.show().text(error);
                    return;
                }

                $btn.prop('disabled', true);
                $error.hide();
                $success.hide();

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'call_me_save_request',
                        phone: phone,
                        subject: subject
                    },
                    success: function(response) {
                        if (response.success) {
                            $success.show();
                            $input.val('');
                            $error.hide();
                            $btn.prop('disabled', false);
                            setTimeout(function() {
                                $success.fadeOut();
                            }, 3000);
                        } else {
                            $error.show().text(response.data || 'خطایی رخ داد');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        $error.show().text('خطا در ارتباط با سرور');
                        $btn.prop('disabled', false);
                    }
                });
            });

            // Only allow numbers and + at start
            $input.on('keypress', function(e) {
                var char = String.fromCharCode(e.which);
                if (!/[0-9]/.test(char)) {
                    if (char === '+' && this.selectionStart === 0 && this.value.indexOf('+') === -1) {
                        return true;
                    }
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
<?php
    return ob_get_clean();
}

// AJAX: Save notification request
add_action('wp_ajax_call_me_save_request', 'call_me_save_request_callback');
add_action('wp_ajax_nopriv_call_me_save_request', 'call_me_save_request_callback');

function call_me_save_request_callback()
{
    $phone = sanitize_text_field($_POST['phone']);
    $subject = sanitize_text_field($_POST['subject']);

    // Validate phone
    if (empty($phone)) {
        wp_send_json_error('شماره موبایل ضروری میباشد');
    }

    $phone = preg_replace('/\s+/', '', $phone);

    if (strlen($phone) < 10 || strlen($phone) > 11) {
        wp_send_json_error('شماره موبایل صحیح نیست');
    }

    if (!preg_match('/^(\+98|0|0098)?9\d{9}$/', $phone)) {
        wp_send_json_error('شماره موبایل صحیح نیست');
    }

    // Normalize phone number
    if (strlen($phone) == 11 && substr($phone, 0, 2) == '09') {
        $phone = substr($phone, 1);
    } elseif (substr($phone, 0, 4) == '+989') {
        $phone = substr($phone, 3);
    } elseif (substr($phone, 0, 5) == '00989') {
        $phone = substr($phone, 4);
    }

    if (empty($subject)) {
        $subject = 'عمومی';
    }

    try {
        global $wpdb;
        $table_name = $wpdb->prefix . 'call_me';

        // Create table if not exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            create_call_me_table();
        }

        $medoo = medoo();

        // Check if already exists with same subject and pending status
        $exists = $medoo->get($table_name, '*', [
            'phone' => $phone,
            'subject' => $subject,
            'status' => 0
        ]);

        if ($exists) {
            wp_send_json_error('شما قبلاً برای این موضوع ثبت نام کرده‌اید');
        }

        // Insert new record
        $medoo->insert($table_name, [
            'subject' => $subject,
            'phone' => $phone,
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        wp_send_json_success('شماره شما با موفقیت ثبت شد');
    } catch (Exception $e) {
        wp_send_json_error('خطا در ثبت اطلاعات: ' . $e->getMessage());
    }
}
