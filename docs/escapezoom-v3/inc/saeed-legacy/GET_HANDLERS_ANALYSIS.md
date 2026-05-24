# تحلیل GET/POST handlerهای saeed-legacy

تولید خودکار؛ وضعیت‌ها پیشنهادی هستند.

## دسته ۱: ابزار توسعه / تست (کاندید حذف در production)

- `get_games_points_list` (GET, 051-get-get_games_points_list-p.php:14)
- `get_completed_and_partial_orders_phone` (GET, 052-get_completed_and_partial_orders_phone.php:16)
- `w` (GET, 097-get-w.php:23)
- `upload_file_test` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:14)
- `get_duplicate_transactions` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:38)
- `bak_test` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:121)
- `redirect_to_payment_url` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:214)
- `reservation_webservice_test` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:219)
- `get_jwt_token_by_user` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:245)
- `lat` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:260)
- `data_products_set2` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:271)
- `get_single_product` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:313)
- `telegramx` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:543)
- `medoo` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:844)
- `update_comment_list_table` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:1212)


## دسته ۲: نگهداری / batch (نیاز به guard)

- `update_list_hottest` (GET, 017-get-update_list_hottest-update_list_popular-upda.php:14)
- `update_list_popular` (GET, 017-get-update_list_hottest-update_list_popular-upda.php:19)
- `update_list_topsale` (GET, 017-get-update_list_hottest-update_list_popular-upda.php:24)
- `update_recent` (GET, 017-get-update_list_hottest-update_list_popular-upda.php:29)
- `update_product_data` (GET, 017-get-update_list_hottest-update_list_popular-upda.php:34)
- `update_product_data_nactive` (GET, 017-get-update_list_hottest-update_list_popular-upda.php:39)
- `update_marketing_data` (GET, 017-get-update_list_hottest-update_list_popular-upda.php:44)
- `update_comments_stars` (GET, 017-get-update_list_hottest-update_list_popular-upda.php:54)
- `ez_sms_sending_queue_schedule` (GET, 025-get-ez_sms_sending_queue_schedule.php:14)
- `update_comment_stars` (GET, 050-get-update_comment_stars.php:14)
- `brands_list` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:334)
- `customer_user` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:361)
- `tickets_sold` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:431)
- `get_owners_info` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:461)
- `cm_300` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:568)
- `get_hottest` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:680)
- `update_min_price` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:806)
- `auto_dis_proc` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:887)
- `points_purge` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:1003)
- `collection_likes` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:1051)
- `points_points_purying` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:1075)
- `satis_rebuild` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:1127)
- `get_unverified_list` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:1137)
- `if_user_commented` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:1142)


## دسته ۳: مهاجرت یا منسوخ (کاندید حذف)

- `ez_satisfaction_on_comments` (GET, 019-ez_satisfaction_on_comments2-1-more.php:14)
- `put_cms_blacklist` (GET, 048-get-put_cms_blacklist.php:14)
- `fill_table_with_order_info` (GET, 049-saeedxxx.php:16)
- `1402_topsales` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:76)
- `update_product_brand` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:154)
- `get_list_of_all_products` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:185)
- `ashoora` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:267)
- `update_user_shaba` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:289)
- `rate_power_user` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:529)
- `sms_send` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:637)
- `brands_of_products_update` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:752)
- `get_brands_name` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:783)
- `refunded_points` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:920)


## دسته ۴: منطق فعال (انتقال به ماژول دامنه)

- `cm` (GET, 030-comment_privacy_system_token_verify_redirect.php:19)
- `waiting` (GET, 071-pending_posts_bubble_wpse_89028-4-more.php:58)
- `shrt` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:744)
- `team_reservation` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:1147)


## دسته ۵: بدون برچسب — نیاز به تصمیم تیم

- `fazasazi` (POST, 033-saving_rating_in_details_admin.php:22)
- `cm_report_subject` (POST, 035-save_owner_comment_report_message.php:16)
- `cm_id` (POST, 035-save_owner_comment_report_message.php:18)
- `pish_pardakht_per_person` (POST, 045-ez_admin_schedule_price_display-5-more.php:645)
- `auto_disable` (POST, 045-ez_admin_schedule_price_display-5-more.php:648)
- `schedule_normals` (POST, 045-ez_admin_schedule_price_display-5-more.php:651)
- `schedule_holidays` (POST, 045-ez_admin_schedule_price_display-5-more.php:668)
- `product_options` (POST, 046-product_options_metabox-2-more.php:147)
- `special_discount_enable` (POST, 076-special_discount-2-more.php:56)
- `special_discount_percentage` (POST, 076-special_discount-2-more.php:61)
- `special_discount_date` (POST, 076-special_discount-2-more.php:64)
- `special_discount_enable` (POST, 076-special_discount-2-more.php:67)
- `tax_input` (POST, 076-special_discount-2-more.php:77)
- `introduction` (POST, 081-product_videos_metabox-2-more.php:66)
- `teaser` (POST, 081-product_videos_metabox-2-more.php:109)
- `city_page_product_categories` (POST, 095-save_city_page_product_categories_meta_box.php:16)
- `assign_as_city_page` (POST, 095-save_city_page_product_categories_meta_box.php:21)
- `limit` (GET, 109-init-upload_file_test-get_duplicate_transactions.php:1206)

