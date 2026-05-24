# مستندات توابع saeed-codes.php

هر فایل MD در این پوشه مربوط به **یک گروه از توابع** فایل `saeed-codes.php` است.

در هر فایل برای هر تابع آمده است:
- **تابع در saeed-codes.php:** نام تابع
- **جایگزین چه تابعی:** اگر این تابع جایگزین تابع دیگری شده (وگرنه: تابع اصلی همین است)
- **کارایی:** دقیقاً چه کاری انجام می‌دهد و کجا استفاده می‌شود
- **بهینه‌سازی:** چطور می‌شود کد را تمیزتر/سریع‌تر/قابل نگهداری‌تر کرد

---

## فهرست فایل‌ها

| فایل | توابع پوشش‌داده‌شده |
|------|---------------------|
| [01-admin-bar.md](01-admin-bar.md) | disable_admin_bar_for_non_admins |
| [02-debug.md](02-debug.md) | saeed_store, saeed_print |
| [03-order-columns.md](03-order-columns.md) | custom_shop_order_column, custom_shop_order_sortable_columns, custom_orders_list_column_content |
| [04-webservice.md](04-webservice.md) | ez_webservice, ez_reservation |
| [05-shortcodes.md](05-shortcodes.md) | product_query, thankyouco |
| [06-cron-sync.md](06-cron-sync.md) | ez_queryable_set_* (hottest, popular, topsale, recent, data, marketing, comments_stars, satisfaction, booking_history) |
| [07-sms.md](07-sms.md) | ez_sms_sending_queue_schedule, ez_remove_expired_sms_queue_schedule, ez_sendpayamak3, add_to_sms_queue, send_sms_scheduled, delete/update/get_sms_schedule_row(s) |
| [08-satisfaction.md](08-satisfaction.md) | ez_update_product_satisfaction_stats, ez_update_order_satisfaction, ez_calculate_product_satisfaction, ez_rebuild_product_satisfaction_stats |
| [09-sliders.md](09-sliders.md) | ez_product_cat_sliders, elite_rooms_of_tehran_func3, get_deactivated_rooms |
| [10-checkout.md](10-checkout.md) | sc_woocommerce_form_field_heading, send_sms_comment_url, ez_review_order_prices_table, ez_final_payment_amount, store_ez_payment_method, ez_get_coupon_discount_amount, disable_multiple_coupons, change_coupon_error_msg, controle_before_place_order, conflict_before_place_order_validation |
| [11-booking-lock.md](11-booking-lock.md) | ez_add_booking_lock, ez_remove_booking_lock, ez_get_booking_lock, visit_single_room_unlock_booking, tracking_back_btn_in_checkout_page |
| [12-order-status.md](12-order-status.md) | my_change_status_function, if_order_status_changed, ez_refund |
| [13-ajax.md](13-ajax.md) | ez_site_ajax_handler_callback |
| [14-auth-jwt.md](14-auth-jwt.md) | ez_authorization, get_user_id_by_token, get_token_from_header, generate_jwt_token, get_user_role |
| [15-ticketing.md](15-ticketing.md) | contact_us_declare, ticketing_declare, add_submenu_to_ticketing, ticket_* (monitoring, messages, status, user_info, program_date_box_save, admin_seen), set_custom_edit_teacher_columns, custom_ticket_column, ws_sortable_manufacturer_column, pending_posts_bubble_wpse_89028, recursive_array_search_php_91365, wpse246143_* |
| [16-metaboxes.md](16-metaboxes.md) | reservation_info_*, product_options_*, get_day_type, get_sanses, product_videos_*, monopoly_*, product_content_*, special_discount_*, add_fields_to_product_tag, save_fields_to_product_tag, city_page_product_categories_* |
| [17-order-line-item.md](17-order-line-item.md) | custom_checkout_create_order_line_item, wc_make_processing_orders_editable |
| [18-coupons.md](18-coupons.md) | restrict_coupon_to_user_ids, first_bought_coupon, coupon_validation_block_on_special_discount, add_usage_restriction_user_ids, save_coupon_usage_restriction, if_user_has_bought, if_user_commented |
| [19-admin-misc.md](19-admin-misc.md) | remove_footer_admin, withdrawal_owner_profile_fields, save_withdrawal_owner_profile_fields, admin_hide_items_via_css |
| [20-comments.md](20-comments.md) | comment_privacy_system_token_verify_redirect, rating_in_details_admin_*, saving_rating_in_details_admin, my_approve_comment_callback, ez_remove_product_comment, comment_reminder_sms_process |
| [21-helpers.md](21-helpers.md) | randString, base64_url_encode, base64_url_decode, trim_home_url, get_term_link_flat, persianToEnglish, englishToPersian, normalizePhoneNumber, isValidIranianMobileNumber, ez_get_product_meta, change_product_short_description_title, get_product_type_equivalent, get_parent_category_name_by_child_id, get_bayesian_score, encrypt_data |
| [22-zarinpal.md](22-zarinpal.md) | fix_quantity_if_not_allowed, switch_zarinpal_gateway_by_domain, get_order_id_by_authority, zarinpal_paid_transactions_process, zarinpal_co_paid_transactions_process, verify_zarinpal_payment |
| [23-accounting.md](23-accounting.md) | held_status_accounting_management, held_status_accounting_management_ui_func, ez_calendar_ui_func, update_held_sans_table_func, ez_withdrawal_ui_func, ez_withdrawal_paid_ui_func, ez_withdrawal_rejected_ui_func, single_schedule_changed |
| [24-misc.md](24-misc.md) | ez_cm_add_phone, ez_cm_get_order_id, vpn_turn_off_msg, prevent_submission_by_refresh, ez_login_automatically, ez_get_user_ip, emergency_phones_change, disable_gutenberg_for_pages, change_default_upload_dir_*, customer_files_*, process_team_reservations_batch, process_team_reservation_rewards, توابع بیکار و wrapperهای *_2 |
