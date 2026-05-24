<?php /** @noinspection PhpVoidFunctionResultUsedInspection */

date_default_timezone_set('Asia/Tehran');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

define('COMMENT_NEW_VER_TIMESTAMP', 1746044999);

// if (file_exists('api/api.php'))
require_once 'api/api.php';
require_once 'medoo/init.php';

require_once 'jwt-authentication-for-wp-rest-api/jwt-auth.php';

/** Shop: checkout, booking pipeline, ZarinPal verify, AJAX (extracted modules). Must load after Medoo/API bootstrap above. */
require_once __DIR__ . '/shop/loader.php';

/** Legacy slices from former monolithic saeed-codes.php (see inc/saeed-legacy/). */
// [17-29] disable_admin_bar_for_non_admins
require_once __DIR__ . '/saeed-legacy/002-disable_admin_bar_for_non_admins.php';
// [30-43] saeed_store
require_once __DIR__ . '/saeed-legacy/003-saeed_store.php';
// [44-48] saeed_print
require_once __DIR__ . '/saeed-legacy/004-saeed_print.php';
// [49-130] product_query
require_once __DIR__ . '/saeed-legacy/005-product_query.php';
// [131-190] hooks: cron_schedules, ez_queryable_set_hottest_cron, ez_queryable_set_popular_cron, ez_queryable_set_topsale_cron
require_once __DIR__ . '/saeed-legacy/006-hooks-cron_schedules-ez_queryable_set_hottest_cr.php';
// [191-201] hooks: init
require_once __DIR__ . '/saeed-legacy/007-hooks-init.php';
// [202-299] ez_queryable_set_hottest_products2 (+1 more)
require_once __DIR__ . '/saeed-legacy/008-ez_queryable_set_hottest_products2-1-more.php';
// [300-390] ez_queryable_set_popular_products2 (+1 more)
require_once __DIR__ . '/saeed-legacy/009-ez_queryable_set_popular_products2-1-more.php';
// [391-452] ez_queryable_set_topsale_products2 (+1 more)
require_once __DIR__ . '/saeed-legacy/010-ez_queryable_set_topsale_products2-1-more.php';
// [453-487] ez_queryable_set_recent_products2 (+1 more)
require_once __DIR__ . '/saeed-legacy/011-ez_queryable_set_recent_products2-1-more.php';
// [488-627] ez_queryable_set_products_data2 (+1 more)
require_once __DIR__ . '/saeed-legacy/012-ez_queryable_set_products_data2-1-more.php';
// [628-765] ez_queryable_set_products_data2_nactive (+1 more)
require_once __DIR__ . '/saeed-legacy/013-ez_queryable_set_products_data2_nactive-1-more.php';
// [766-867] update_comments_stars2 (+1 more)
require_once __DIR__ . '/saeed-legacy/014-update_comments_stars2-1-more.php';
// [868-1002] ez_queryable_set_marketing_data2 (+1 more)
require_once __DIR__ . '/saeed-legacy/015-ez_queryable_set_marketing_data2-1-more.php';
// [1003-1016] wp_zb_booking_history_today_optimize2 (+1 more)
require_once __DIR__ . '/saeed-legacy/016-wp_zb_booking_history_today_optimize2-1-more.php';
// [1017-1060] GET: update_list_hottest, update_list_popular, update_list_topsale, update_recent, …
require_once __DIR__ . '/saeed-legacy/017-get-update_list_hottest-update_list_popular-upda.php';
// [1061-1083] ez_sms_sending_queue_schedule
require_once __DIR__ . '/saeed-legacy/018-ez_sms_sending_queue_schedule.php';
// [1084-1094] ez_satisfaction_on_comments2 (+1 more)
require_once __DIR__ . '/saeed-legacy/019-ez_satisfaction_on_comments2-1-more.php';
// [1095-1128] ez_update_product_satisfaction_stats
require_once __DIR__ . '/saeed-legacy/020-ez_update_product_satisfaction_stats.php';
// [1129-1155] ez_update_order_satisfaction
require_once __DIR__ . '/saeed-legacy/021-ez_update_order_satisfaction.php';
// [1156-1186] ez_calculate_product_satisfaction
require_once __DIR__ . '/saeed-legacy/022-ez_calculate_product_satisfaction.php';
// [1187-1200] ez_rebuild_product_satisfaction_stats
require_once __DIR__ . '/saeed-legacy/023-ez_rebuild_product_satisfaction_stats.php';
// [1201-1210] ez_remove_expired_sms_queue_schedule
require_once __DIR__ . '/saeed-legacy/024-ez_remove_expired_sms_queue_schedule.php';
// [1211-1216] GET: ez_sms_sending_queue_schedule
require_once __DIR__ . '/saeed-legacy/025-get-ez_sms_sending_queue_schedule.php';
// [1217-1244] ez_sendpayamak3
require_once __DIR__ . '/saeed-legacy/026-ez_sendpayamak3.php';
// [1245-2172] ez_product_cat_sliders
require_once __DIR__ . '/saeed-legacy/027-ez_product_cat_sliders.php';
// [2173-3368] elite_rooms_of_tehran_func3
require_once __DIR__ . '/saeed-legacy/028-elite_rooms_of_tehran_func3.php';
// [3369-3508] get_deactivated_rooms
require_once __DIR__ . '/saeed-legacy/029-get_deactivated_rooms.php';
// [3509-3557] comment_privacy_system_token_verify_redirect
require_once __DIR__ . '/saeed-legacy/030-comment_privacy_system_token_verify_redirect.php';
// [3558-3561] rating_in_details_admin_metabox
require_once __DIR__ . '/saeed-legacy/031-rating_in_details_admin_metabox.php';
// [3562-3636] rating_in_details_admin_html
require_once __DIR__ . '/saeed-legacy/032-rating_in_details_admin_html.php';
// [3637-3658] saving_rating_in_details_admin
require_once __DIR__ . '/saeed-legacy/033-saving_rating_in_details_admin.php';
// [3659-3670] owner_comment_report_menu
require_once __DIR__ . '/saeed-legacy/034-owner_comment_report_menu.php';
// [3671-3683] save_owner_comment_report_message
require_once __DIR__ . '/saeed-legacy/035-save_owner_comment_report_message.php';
// [3684-3693] vpn_turn_off_msg (+1 more)
require_once __DIR__ . '/saeed-legacy/036-vpn_turn_off_msg-1-more.php';
// [3694-3701] hooks: wp
require_once __DIR__ . '/saeed-legacy/037-hooks-wp.php';
// [3702-3757] hooks: admin_init
require_once __DIR__ . '/saeed-legacy/038-hooks-admin_init.php';
// [3758-3761] single_schedule_changed
require_once __DIR__ . '/saeed-legacy/039-single_schedule_changed.php';
// [3762-3854] update_held_sans_table_func
require_once __DIR__ . '/saeed-legacy/040-update_held_sans_table_func.php';
// [3855-3867] ez_get_user_ip
require_once __DIR__ . '/saeed-legacy/041-ez_get_user_ip.php';
// [3868-3916] ez_remove_product_comment
require_once __DIR__ . '/saeed-legacy/042-ez_remove_product_comment.php';
// [3917-3992] my_approve_comment_callback
require_once __DIR__ . '/saeed-legacy/043-my_approve_comment_callback.php';
// [3993-4048] add_fields_to_product_tag (+1 more)
require_once __DIR__ . '/saeed-legacy/044-add_fields_to_product_tag-1-more.php';
// [4049-4756] ez_admin_schedule_price_display (+5 more)
require_once __DIR__ . '/saeed-legacy/045-ez_admin_schedule_price_display-5-more.php';
// [4757-4892] product_options_metabox (+2 more)
require_once __DIR__ . '/saeed-legacy/046-product_options_metabox-2-more.php';
// [4893-4905] hooks: admin_init
require_once __DIR__ . '/saeed-legacy/047-hooks-admin_init.php';
// [4906-4949] GET: put_cms_blacklist
require_once __DIR__ . '/saeed-legacy/048-get-put_cms_blacklist.php';
// [4950-4977] saeedxxx
require_once __DIR__ . '/saeed-legacy/049-saeedxxx.php';
// [4978-5069] GET: update_comment_stars
require_once __DIR__ . '/saeed-legacy/050-get-update_comment_stars.php';
// [5070-5163] GET: get_games_points_list, p
require_once __DIR__ . '/saeed-legacy/051-get-get_games_points_list-p.php';
// [5164-5294] get_completed_and_partial_orders_phone
require_once __DIR__ . '/saeed-legacy/052-get_completed_and_partial_orders_phone.php';
// [5295-5317] generate_jwt_token
require_once __DIR__ . '/saeed-legacy/053-generate_jwt_token.php';
// [5318-5331] ez_authorization
require_once __DIR__ . '/saeed-legacy/054-ez_authorization.php';
// [5332-5338] get_user_id_by_token
require_once __DIR__ . '/saeed-legacy/055-get_user_id_by_token.php';
// [5339-5368] get_token_from_header
require_once __DIR__ . '/saeed-legacy/056-get_token_from_header.php';
// [5369-5399] contact_us_declare
require_once __DIR__ . '/saeed-legacy/057-contact_us_declare.php';
// [5400-5437] ticketing_declare
require_once __DIR__ . '/saeed-legacy/058-ticketing_declare.php';
// [5438-5442] hooks: admin_menu
require_once __DIR__ . '/saeed-legacy/059-hooks-admin_menu.php';
// [5443-5454] add_submenu_to_ticketing
require_once __DIR__ . '/saeed-legacy/060-add_submenu_to_ticketing.php';
// [5455-5515] ticket_monitoring_callback_func
require_once __DIR__ . '/saeed-legacy/061-ticket_monitoring_callback_func.php';
// [5516-5542] ticketing_messages_metabox_function
require_once __DIR__ . '/saeed-legacy/062-ticketing_messages_metabox_function.php';
// [5543-5801] ticketing_messages_metabox_content_function
require_once __DIR__ . '/saeed-legacy/063-ticketing_messages_metabox_content_function.php';
// [5802-5811] ticket_status_function
require_once __DIR__ . '/saeed-legacy/064-ticket_status_function.php';
// [5812-5824] user_info_function
require_once __DIR__ . '/saeed-legacy/065-user_info_function.php';
// [5825-5841] program_date_box_save
require_once __DIR__ . '/saeed-legacy/066-program_date_box_save.php';
// [5842-5848] ticketing_admin_seen
require_once __DIR__ . '/saeed-legacy/067-ticketing_admin_seen.php';
// [5849-5860] set_custom_edit_teacher_columns
require_once __DIR__ . '/saeed-legacy/068-set_custom_edit_teacher_columns.php';
// [5861-5912] custom_ticket_column
require_once __DIR__ . '/saeed-legacy/069-custom_ticket_column.php';
// [5913-5920] ws_sortable_manufacturer_column
require_once __DIR__ . '/saeed-legacy/070-ws_sortable_manufacturer_column.php';
// [5921-6004] pending_posts_bubble_wpse_89028 (+4 more)
require_once __DIR__ . '/saeed-legacy/071-pending_posts_bubble_wpse_89028-4-more.php';
// [6005-6012] change_default_upload_dir_for_customer_files
require_once __DIR__ . '/saeed-legacy/072-change_default_upload_dir_for_customer_files.php';
// [6013-6020] change_default_upload_dir_for_customer_files_self_destruct
require_once __DIR__ . '/saeed-legacy/073-change_default_upload_dir_for_customer_files_sel.php';
// [6021-6023] customer_files_name
require_once __DIR__ . '/saeed-legacy/074-customer_files_name.php';
// [6024-6031] customer_files_self_destruct_function
require_once __DIR__ . '/saeed-legacy/075-customer_files_self_destruct_function.php';
// [6032-6097] special_discount (+2 more)
require_once __DIR__ . '/saeed-legacy/076-special_discount-2-more.php';
// [6098-6105] misc
require_once __DIR__ . '/saeed-legacy/077-misc.php';
// [6106-6109] remove_footer_admin
require_once __DIR__ . '/saeed-legacy/078-remove_footer_admin.php';
// [6110-6116] prevent_submission_by_refresh
require_once __DIR__ . '/saeed-legacy/079-prevent_submission_by_refresh.php';
// [6117-6121] trim_home_url
require_once __DIR__ . '/saeed-legacy/080-trim_home_url.php';
// [6122-6259] product_videos_metabox (+2 more)
require_once __DIR__ . '/saeed-legacy/081-product_videos_metabox-2-more.php';
// [6260-6293] monopoly_metabox (+2 more)
require_once __DIR__ . '/saeed-legacy/082-monopoly_metabox-2-more.php';
// [6294-6349] product_content_metabox (+5 more)
require_once __DIR__ . '/saeed-legacy/083-product_content_metabox-5-more.php';
// [6350-6366] normalizePhoneNumber
require_once __DIR__ . '/saeed-legacy/084-normalizephonenumber.php';
// [6367-6369] isValidIranianMobileNumber
require_once __DIR__ . '/saeed-legacy/085-isvalidiranianmobilenumber.php';
// [6370-6402] ez_login_automatically
require_once __DIR__ . '/saeed-legacy/086-ez_login_automatically.php';
// [6403-6431] ez_get_product_meta
require_once __DIR__ . '/saeed-legacy/087-ez_get_product_meta.php';
// [6432-6438] change_product_short_description_title
require_once __DIR__ . '/saeed-legacy/088-change_product_short_description_title.php';
// [6439-6448] hooks: wpseo_sitemap_exclude_empty_terms, wpseo_sitemap_entry
require_once __DIR__ . '/saeed-legacy/089-hooks-wpseo_sitemap_exclude_empty_terms-wpseo_si.php';
// [6449-6460] emergency_phones_change
require_once __DIR__ . '/saeed-legacy/090-emergency_phones_change.php';
// [6461-6477] get_product_type_equivalent
require_once __DIR__ . '/saeed-legacy/091-get_product_type_equivalent.php';
// [6478-6484] disable_gutenberg_for_pages
require_once __DIR__ . '/saeed-legacy/092-disable_gutenberg_for_pages.php';
// [6485-6494] city_page_product_categories_meta_box
require_once __DIR__ . '/saeed-legacy/093-city_page_product_categories_meta_box.php';
// [6495-6545] display_city_page_product_categories_meta_box
require_once __DIR__ . '/saeed-legacy/094-display_city_page_product_categories_meta_box.php';
// [6546-6557] save_city_page_product_categories_meta_box
require_once __DIR__ . '/saeed-legacy/095-save_city_page_product_categories_meta_box.php';
// [6558-6569] get_parent_category_name_by_child_id
require_once __DIR__ . '/saeed-legacy/096-get_parent_category_name_by_child_id.php';
// [6570-6585] GET: w
require_once __DIR__ . '/saeed-legacy/097-get-w.php';
// [6586-6594] hooks: comment_form_default_fields
require_once __DIR__ . '/saeed-legacy/098-hooks-comment_form_default_fields.php';
// [6595-6603] hooks: wpseo_canonical
require_once __DIR__ . '/saeed-legacy/099-hooks-wpseo_canonical.php';
// [6604-6624] fix_quantity_if_not_allowed
require_once __DIR__ . '/saeed-legacy/100-fix_quantity_if_not_allowed.php';
// [6625-6646] admin_hide_items_via_css
require_once __DIR__ . '/saeed-legacy/101-admin_hide_items_via_css.php';
// [6653-6669] if_user_commented
require_once __DIR__ . '/saeed-legacy/103-if_user_commented.php';
// [6670-6673] delete_sms_schedule_row
require_once __DIR__ . '/saeed-legacy/104-delete_sms_schedule_row.php';
// [6674-6677] update_sms_schedule_row
require_once __DIR__ . '/saeed-legacy/105-update_sms_schedule_row.php';
// [6678-6696] get_sms_schedule_rows
require_once __DIR__ . '/saeed-legacy/106-get_sms_schedule_rows.php';
// [6697-6752] comment_reminder_sms_process
require_once __DIR__ . '/saeed-legacy/107-comment_reminder_sms_process.php';
// [6753-6755] get_term_link_flat
require_once __DIR__ . '/saeed-legacy/108-get_term_link_flat.php';
// [6756-8110] init: upload_file_test, get_duplicate_transactions, 1402_topsales, bak_test, update_product_brand
require_once __DIR__ . '/saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php';
