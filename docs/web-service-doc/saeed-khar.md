# گزارش تفصیلی توابع فایل saeed-codes.php

این سند گزارش تفصیلی توابع فایل `saeed-codes.php` است: محل استفاده، توابع بیکار، تکراری و چک‌لیست حذف کل فایل.

---

## خلاصه وضعیت

- **فایل:** `wp-content/themes/escapezoom-v2/inc/saeed-codes.php`
- **بارگذاری:** فقط از طریق `functions.php` با `include get_template_directory() . '/inc/saeed-codes.php';`
- **تعداد توابع (تقریبی):** حدود ۱۹۰+ تابع مستقل (بدون شمارش توابع داخلی JS/کلاس‌ها)

---

## ۱. توابعی که دقیقاً کجا استفاده می‌شوند و چه کاری می‌کنند

### تنظیمات اولیه و Admin Bar
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `disable_admin_bar_for_non_admins` | `add_action('after_setup_theme', ...)` | برای نقش‌های customer، compiler، sans_manager نوار ادمین را مخفی می‌کند. |

### دیباگ
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `saeed_store` | داخل همین فایل و thankyou.php، callbacks، actions-points و غیره | مقدار را با کلید microtime در options ذخیره می‌کند (برای بررسی در DB). |
| `saeed_print` | داخل همین فایل، content-product.php، admin-settings | چاپ `print_r` در `<pre>`؛ گاهی با `die`. |

### لیست سفارش ووکامرس
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `custom_shop_order_column` | فیلتر `manage_edit-shop_order_columns` | ستون‌های «سانس» و «سپرده» به لیست سفارش اضافه می‌کند. |
| `custom_shop_order_sortable_columns` | فیلتر `manage_edit-shop_order_sortable_columns` | همین ستون‌ها را قابل مرتب‌سازی می‌کند. |
| `custom_orders_list_column_content` | اکشن `manage_shop_order_posts_custom_column` | محتوای ستون سانس (از wp_zb_booking_history/Medoo) و سپرده (از _order_total_2/_order_total) را پر می‌کند. |

### Wrapperهای وب‌سرویس
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `ez_webservice` | در سراسر قالب و همین فایل | POST به web-service/web-service.php؛ تمام درخواست‌های sort_products_get، hottest_products_set، data_products_set و غیره. |
| `ez_reservation` | در سراسر قالب، thankyou، form-checkout، api/callbacks، cancellation_functions، reservation | POST به web-service/reservation.php؛ query_execution، get_sans_lock، add_sans_lock و غیره. |

### شورت‌کدها
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `product_query` | شورت‌کد `[product_query]` | فرم جستجو + AJAX به queryable.php؛ خروجی در #search_result2. اگر شورت‌کد در تمپلیت نباشد، فراخوانی مستقیم ندارد. |
| `thankyouco` | شورت‌کد `[thankyouco]` | محتوای صفحه تشکر پس از خرید. در جایی که این شورت‌کد درج شده استفاده می‌شود. |

### Cron و محاسبه لیست محصولات (فقط روی escapezoom.ir)
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `ez_queryable_set_hottest_products` | کرون `ez_queryable_set_hottest_cron` + woocommerce_after_register_post_type | جدول hottest_products را می‌خواند، امتیاز بیز + بازدید محاسبه می‌کند، لیست را به web-service می‌فرستد. |
| `ez_queryable_set_hottest_products2` | فقط داخل بلاک `if (isset($_GET['get_list_of_all_products']))` در همین فایل | فقط add_action به تابع اصلی می‌زند؛ **تکراری/کمکی**. |
| `ez_queryable_set_popular_products` | کرون + woocommerce_after_register_post_type | محصولات active، امتیاز و تعداد کامنت؛ popular_products_set. |
| `ez_queryable_set_popular_products2` | همان بلاک GET بالا | مثل بالا؛ **تکراری**. |
| `ez_queryable_set_topsale_products` / `ez_queryable_set_topsale_products2` | مشابه | از held_orders_list؛ topsale_products_set. |
| `ez_queryable_set_recent_products` / `ez_queryable_set_recent_products2` | مشابه | لیست محصولات active/updated؛ recent_products_set. |
| `ez_queryable_set_products_data` / `ez_queryable_set_products_data2` | مشابه | سینک products_data (فعال) با web-service. |
| `ez_queryable_set_products_data_nactive` / `ez_queryable_set_products_data2_nactive` | مشابه | سینک محصولات غیرفعال. |
| `ez_queryable_set_marketing_data` / `ez_queryable_set_marketing_data2` | init + بلاک GET | داده مارکتینگ برای وب‌سرویس. |
| `update_comments_stars` / `update_comments_stars2` | کرون + بلاک GET | به‌روزرسانی ستاره/امتیاز نظرات. |
| `wp_zb_booking_history_today_optimize` / `wp_zb_booking_history_today_optimize2` | کرون + بلاک GET | بهینه‌سازی جدول رزرو روز. |
| `ez_satisfaction_on_comments` / `ez_satisfaction_on_comments2` | کرون + بلاک GET | محاسبه رضایت از نظرات. |

### SMS و صف پیامک
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `ez_sms_sending_queue_schedule` | کرون `ez_sms_sending_queue_cron` | ارسال دسته‌ای پیامک‌های صف. |
| `ez_remove_expired_sms_queue_schedule` | کرون | حذف رکوردهای منقضی صف SMS. |
| `ez_sendpayamak3` | داخل همین فایل و لاجیک SMS | ارسال واقعی SMS با سرویس پیامک. |
| `add_to_sms_queue` | همین فایل، thankyou، cancellation_functions، comments_actions، callbacks | اضافه کردن یک پیام به جدول صف SMS. |
| `send_sms_scheduled` | داخل همین فایل | زمان‌بندی ارسال SMS برای تاریخ مشخص. |
| `delete_sms_schedule_row` / `update_sms_schedule_row` / `get_sms_schedule_rows` | داخل همین فایل و لاجیک صف | CRUD روی رکوردهای صف SMS. |

### رضایت و امتیاز نظرات
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `ez_update_product_satisfaction_stats` | از `my_approve_comment_callback` و لاجیک نظرات | به‌روزرسانی آمار رضایت محصول بعد از نظر. |
| `ez_update_order_satisfaction` | از لاجیک سفارش/نظر | به‌روزرسانی رضایت سفارش. |
| `ez_calculate_product_satisfaction` | داخل همین فایل | محاسبه امتیاز رضایت یک محصول. |
| `ez_rebuild_product_satisfaction_stats` | داخل همین فایل | بازسازی کامل آمار رضایت یک محصول. |

### اسلایدر و صفحه محصول (شورت‌کد / تمپلیت)
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `ez_product_cat_sliders` | شورت‌کد یا تمپلیتی که این تابع را صدا بزند | اسلایدر دسته‌بندی محصول؛ داده از `ez_webservice(sort_products_get)`. |
| `elite_rooms_of_tehran_func3` | داخل خروجی `ez_product_cat_sliders` (احتمالاً) | فیلتر/لیست اتاق‌های تهران. |
| `get_deactivated_rooms` | داخل همین فایل در بخش اسلایدر/فیلتر | لیست اتاق‌های غیرفعال برای حذف از نمایش. |

### چک‌اوت و پرداخت
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `sc_woocommerce_form_field_heading` | فیلتر `woocommerce_form_field_heading` | سفارشی‌سازی نمایش heading فیلد چک‌اوت. |
| `send_sms_comment_url` | اکشن `woocommerce_checkout_update_order_meta` | بعد از ثبت سفارش؛ ارسال لینک نظر به مشتری (SMS). |
| `ez_review_order_prices_table` | اکشن `woocommerce_review_order_after_order_total` | جدول قیمت‌ها در خلاصه سفارش (قبل از پرداخت). |
| `ez_final_payment_amount` | فیلتر `woocommerce_calculated_total` | محاسبه مبلغ نهایی (پیش‌پرداخت آنلاین). |
| `store_ez_payment_method` | اکشن `woocommerce_checkout_update_order_meta` | ذخیره روش پرداخت در متای سفارش. |
| `ez_get_coupon_discount_amount` | داخل همین فایل در لاجیک جمع کل | محاسبه مبلغ تخفیف کوپن. |
| `disable_multiple_coupons` | فیلتر `woocommerce_coupons_enabled` | غیرفعال کردن استفاده هم‌زمان چند کوپن. |
| `change_coupon_error_msg` | فیلتر `woocommerce_coupon_error` | متن خطای سفارشی برای کوپن. |
| `controle_before_place_order` | اکشن `woocommerce_before_calculate_totals` | اعتبارسنجی قبل از ثبت سفارش. |
| `conflict_before_place_order_validation` | اکشن `woocommerce_after_checkout_validation` | جلوگیری از تداخل سانس (تأیید قبل از place order). |

### قفل سانس و باز کردن بعد از پرداخت
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `ez_add_booking_lock` | از لاجیک چک‌اوت/رزرو | قفل کردن سانس در reservation. |
| `ez_remove_booking_lock` | از `visit_single_room_unlock_booking` و لاجیک | برداشتن قفل سانس. |
| `ez_get_booking_lock` | از همان بخش و AJAX | خواندن لیست قفل‌های سانس یک محصول. |
| `visit_single_room_unlock_booking` | اکشن `wp` | در صفحه تک‌محصول؛ باز کردن قفل بعد از بازدید/پرداخت. |
| `tracking_back_btn_in_checkout_page` | اکشن `wp` | دکمه بازگشت به پیگیری در صفحه چک‌اوت. |

### وضعیت سفارش و تسویه
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `my_change_status_function` | اکشن `woocommerce_payment_complete` | بعد از پرداخت؛ تغییر وضعیت و لاجیک مرتبط. |
| `if_order_status_changed` | اکشن `woocommerce_order_status_changed` | هر بار تغییر وضعیت سفارش؛ SMS، رزرو، حسابداری و غیره. |
| `ez_refund` | از AJAX/صفحه یا لاجیک بازگشت پرداخت | استرداد (زرین‌پال/زیبال و غیره). |

### AJAX سایت
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `ez_site_ajax_handler_callback` | اکشن‌های `wp_ajax_ez_site_ajax_handler` و `wp_ajax_nopriv_ez_site_ajax_handler` | هندلر مرکزی AJAX؛ خیلی از درخواست‌های فرانت و اپ از این رد می‌شوند. |

### احراز هویت و JWT
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `ez_authorization` | در api/callbacks و هر جایی که توکن لازم است | بررسی توکن و برگرداندن آن (یا خطا). |
| `get_user_id_by_token` | در callbacks بعد از `ez_authorization` | استخراج user_id از توکن. |
| `get_token_from_header` | داخل `ez_authorization` | خواندن توکن از هدر درخواست. |
| `generate_jwt_token` | داخل همین فایل / لاجیک لاگین | ساخت توکن JWT برای کاربر. |
| `get_user_role` | داخل همین فایل | نقش کاربر. |

### تماس و تیکتینگ
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `contact_us_declare` | اکشن `init` | ثبت REST/پست‌تایپ تماس با ما. |
| `ticketing_declare` | اکشن `init` | ثبت پست‌تایپ تیکت. |
| `add_submenu_to_ticketing` | اکشن `admin_menu` | زیرمنوی ادمین تیکت. |
| `ticket_monitoring_callback_func` | صفحه ادمین تیکت | صفحه نظارت تیکت‌ها. |
| `ticketing_messages_metabox_function` / `ticketing_messages_metabox_content_function` | متاباکس تیکت | محتوا و ذخیره پیام‌های تیکت. |
| `ticket_status_function` / `user_info_function` | داخل متاباکس تیکت | فیلدهای وضعیت و کاربر. |
| `program_date_box_save` | اکشن `save_post` | ذخیره تاریخ برنامه تیکت. |
| `ticketing_admin_seen` | اکشن `admin_head` | علامت‌زدن دیده‌شدن توسط ادمین. |
| `set_custom_edit_teacher_columns` / `custom_ticket_column` | لیست تیکت در ادمین | ستون‌های سفارشی لیست تیکت. |
| `ws_sortable_manufacturer_column` | فیلتر sortable columns تیکت | مرتب‌سازی ستون. |
| `pending_posts_bubble_wpse_89028` | اکشن `admin_menu` | حباب تعداد تیکت در انتظار. |
| `recursive_array_search_php_91365` | داخل `wpse246143_add_admin_quick_link` | جستجوی بازگشتی در منو. |
| `wpse246143_add_admin_quick_link` | فیلتر `views_edit-ticketing` | لینک سریع «در انتظار» در لیست تیکت. |
| `wpse246143_register_waiting` | اکشن `init` | ثبت وضعیت «در انتظار». |
| `wpse246143_map_waiting` | اکشن `parse_query` | فیلتر کوئری برای وضعیت در انتظار. |

### متاباکس محصول (رزرو، گزینه‌ها، ویدئو، تخفیف، انحصار، محتوا)
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `reservation_info_metabox` / `reservation_info_callback` | متاباکس محصول | اطلاعات رزرو (قیمت، سانس و غیره). |
| `process_price_field` | داخل `reservation_info_callback` | نرمال کردن فیلد قیمت. |
| `save_reservation_info` | اکشن `save_post` | ذخیره اطلاعات رزرو و سینک با ez_reservation. |
| `product_options_metabox` / `product_options_callback` / `save_product_options` | متاباکس گزینه‌های محصول | نوع محصول، شهر و غیره. |
| `get_day_type` / `get_sanses` | همین فایل، thankyou، callbacks، web-service (saeed، reservation، sans_management)، reserve_get_table | نوع روز از timestamp؛ لیست سانس‌های محصول از متا. |
| `product_videos_metabox` / `product_videos_metabox_frontend` / `save_product_videos_metabox_data` | متاباکس ویدئوهای محصول | آپلود و ذخیره ویدئوها. |
| `monopoly_metabox` / `monopoly_metabox_frontend` / `save_monopoly_metabox_data` | متاباکس انحصار | محصول انحصاری یا نه. |
| `product_content_metabox` + چند `*_frontend` + `save_product_content_metabox_data` | متاباکس محتوای محصول | معرفی، سناریو، قوانین، ویدئو معرفی. |
| `special_discount` / `special_discount_func` / `special_discount_save_func` | متاباکس تخفیف ویژه | درصد و تاریخ تخفیف ویژه؛ سینک با ez_webservice. |
| `add_fields_to_product_tag` / `save_fields_to_product_tag` | فیلدهای تگ محصول | فیلدهای اضافه برای taxonomy محصول. |
| `city_page_product_categories_meta_box` / `display_*` / `save_city_page_product_categories_meta_box` | متاباکس دسته‌بندی صفحه شهر | دسته‌بندی‌های نمایش در صفحه شهر. |

### سفارش و آیتم
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `custom_checkout_create_order_line_item` | اکشن `woocommerce_before_save_order_items` | هنگام ذخیره آیتم‌های سفارش؛ به‌روزرسانی quantity در wp_zb_booking_history (از ez_reservation). |
| `wc_make_processing_orders_editable` | فیلتر `wc_order_is_editable` | قابل ویرایش بودن سفارش در وضعیت processing. |

### کوپن و محدودیت کاربر
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `restrict_coupon_to_user_ids` | فیلتر `woocommerce_coupon_is_valid` | فقط کاربران مجاز بتوانند کوپن بزنند. |
| `first_bought_coupon` | فیلتر `woocommerce_coupon_is_valid` | کوپن فقط برای اولین خرید. |
| `coupon_validation_block_on_special_discount` | فیلتر `woocommerce_coupon_is_valid` | جلوگیری از کوپن وقتی تخفیف ویژه فعال است. |
| `add_usage_restriction_user_ids` | اکشن `woocommerce_coupon_options_usage_restriction` | فیلد محدودیت کاربر در ویرایش کوپن. |
| `save_coupon_usage_restriction` | اکشن `woocommerce_coupon_options_save` | ذخیره محدودیت کاربر. |
| `if_user_has_bought` | داخل همین فایل برای کوپن اول خرید | آیا کاربر قبلاً خریده یا نه. |
| `if_user_commented` | داخل همین فایل (یادآور نظر) | آیا این شماره برای این محصول نظر داده یا نه. |

### ادمین و پروفایل
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `remove_footer_admin` | فیلتر `admin_footer_text` | حذف متن فوتر ادمین. |
| `withdrawal_owner_profile_fields` / `save_withdrawal_owner_profile_fields` | پروفایل کاربر (مالک) | فیلدهای برداشت برای مالک. |
| `admin_hide_items_via_css` | اکشن `admin_head` | مخفی کردن آیتم‌های ادمین با CSS. |

### نظرات و امتیاز
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `comment_privacy_system_token_verify_redirect` | اکشن `wp` | ریدایرکت/اعتبارسنجی توکن حریم خصوصی نظر. |
| `rating_in_details_admin_metabox` / `rating_in_details_admin_html` / `saving_rating_in_details_admin` | متاباکس نظر در ادمین | امتیاز (ستاره) در جزئیات نظر. |
| `my_approve_comment_callback` | اکشن `transition_comment_status` | وقتی نظر تأیید می‌شود؛ به‌روزرسانی امتیاز و رضایت. |
| `ez_remove_product_comment` | اکشن `trash_comment` | هنگام حذف نظر؛ به‌روزرسانی آمار محصول. |
| `comment_reminder_sms_process` | کرون | ارسال پیامک یادآور برای نظر نداده‌ها. |

### کمکی و متن
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `randString` / `base64_url_encode` / `base64_url_decode` | داخل همین فایل (توکن، لینک و غیره) | رشته تصادفی؛ انکود/دیکد base64 URL-safe. |
| `trim_home_url` | single-product، city، callbacks، navbar، brands، comments، panel_sells، panel_orders، single | حذف دامنه از اول URL؛ فقط مسیر نسبی. |
| `get_term_link_flat` | single-product، navbar | لینک ترم به‌صورت مسیر تخت (بدون دامنه کامل). |
| `persianToEnglish` / `englishToPersian` / `normalizePhoneNumber` / `isValidIranianMobileNumber` | داخل همین فایل و لاجیک موبایل | تبدیل و نرمال اعداد/شماره موبایل ایران. |
| `ez_get_product_meta` | همین فایل، thankyou، ticket، callbacks و غیره | آبجکت متاهای محصول (نوع، سانس، شهر و غیره). |
| `change_product_short_description_title` | فیلتر `gettext` | ترجمه/تغییر عنوان توضیح کوتاه محصول. |
| `get_product_type_equivalent` | داخل همین فایل | نگاشت نوع محصول به مقدار معادل. |
| `get_parent_category_name_by_child_id` | داخل همین فایل | نام دسته والد از روی ID فرزند. |
| `get_bayesian_score` | داخل `ez_queryable_set_hottest_products` | امتیاز بیزی برای ترکیب rate و تعداد. |
| `encrypt_data` | داخل همین فایل در لاجیک حساس | رمزنگاری داده. |

### سبد و درگاه
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `fix_quantity_if_not_allowed` | اکشن `woocommerce_add_to_cart` | محدود کردن تعداد در سبد. |
| `switch_zarinpal_gateway_by_domain` | فیلتر `woocommerce_available_payment_gateways` | انتخاب درگاه زرین‌پال بر اساس دامنه. |
| `get_order_id_by_authority` | داخل لاجیک زرین‌پال | پیدا کردن order_id از authority. |
| `zarinpal_paid_transactions_process` / `zarinpal_co_paid_transactions_process` | کرون | پردازش تراکنش‌های پرداخت‌شده زرین‌پال. |
| `verify_zarinpal_payment` | داخل همان پردازش‌ها | تأیید پرداخت با API زرین‌پال. |

### حسابداری و برداشت (مدیر سانس)
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `held_status_accounting_management` | اکشن `admin_menu` | منوی ادمین حسابداری وضعیت held. |
| `held_status_accounting_management_ui_func` | صفحه آن منو | UI جدول/لیست. |
| `ez_calendar_ui_func` | داخل همین فایل (صفحه/جای ادمین) | تقویم رزرو برای محصول. |
| `update_held_sans_table_func` | از topsale و لاجیک held | به‌روزرسانی جدول held_orders_list. |
| `ez_withdrawal_ui_func` / `ez_withdrawal_paid_ui_func` / `ez_withdrawal_rejected_ui_func` | صفحات ادمین برداشت | لیست در انتظار / پرداخت‌شده / ردشده. |
| `single_schedule_changed` | هوک کامنت‌شده | به‌روزرسانی زمان سانس در وب‌سرویس (فعلاً غیرفعال). |

### سایر
| تابع | کجا استفاده | کار |
|------|-------------|-----|
| `ez_cm_add_phone` / `ez_cm_get_order_id` | از لاجیک نظر/سفارش | ثبت شماره برای محصول–سفارش؛ پیدا کردن order_id از product+phone. |
| `vpn_turn_off_msg` | احتمالاً شورت‌کد یا تمپلیت | پیام «VPN را خاموش کنید». |
| `prevent_submission_by_refresh` | شورت‌کد (اگر جایی درج شده باشد) | جلوگیری از ارسال دوباره با رفرش. |
| `ez_login_automatically` | احتمالاً فیلتر/اکشن لاگین | لاگین خودکار در شرایط خاص. |
| `ez_get_user_ip` | داخل همین فایل | گرفتن IP کاربر. |
| `process_team_reservations_batch` / `process_team_reservation_rewards` | از کرون یا اسکریپت دستی | بچ رزرو تیم؛ پاداش رزرو. |
| `get_term_link_flat` | single-product، navbar | لینک ترم تخت. |

---

## ۲. توابع بیکار (هیچ‌جا فراخوانی یا هوک نمی‌شوند)

- **`zardkooh_get_product_img`** — بدنه خالی `{}`؛ هیچ‌جا صدا زده نمی‌شود. **قابل حذف.**
- **`owner_comment_report_menu`** — هوکش با `//` غیرفعال است؛ تابع تعریف شده ولی به منو اضافه نمی‌شود. **بیکار مگر هوک را فعال کنی.**
- **`detect_zibal_payment_method_for_lock`** — هوکش کامنت شده؛ فقط در صورت فعال‌سازی استفاده می‌شود. **بیکار در وضع فعلی.**
- **`checkout_place_order_script`** — هوکش کامنت شده. **بیکار در وضع فعلی.**
- **`order_conflict_oops_page`** — شورت‌کدش کامنت شده. **بیکار.**
- **`order_conflict_handling`** — اکشنش کامنت شده. **بیکار در وضع فعلی.**
- **`custom_modify_tag_title`** — فیلتر `get_the_terms` کامنت شده. **بیکار.**
- **`single_schedule_changed`** — اکشنش کامنت شده. **بیکار.**

توابعی که **فقط با GET پارامتر خاص** اجرا می‌شوند (ابزار دستی/دیباگ):  
`saeedxxx` (شرط `$_GET['fill_table_with_order_info'] && 0` همیشه false)، `get_completed_and_partial_orders_phone`، و بلاک‌های `if (isset($_GET['...']))` — در استفاده عادی سایت «بیکار» هستند؛ برای اسکریپت دستی/دیباگ استفاده می‌شوند.

---

## ۳. توابع اضافی/تکراری (قابل حذف یا ادغام)

- **تابع‌های `*_2` (مثل `ez_queryable_set_hottest_products2`)** — فقط یک بار در بلاک `get_list_of_all_products` صدا زده می‌شوند و خودشان فقط `add_action(..., تابع_اصلی)` می‌زنند. می‌توان همان بلاک را طوری عوض کرد که مستقیم توابع اصلی را صدا بزند و این wrapperها حذف شوند.
- **`get_day_type` و `get_sanses`** — هم در `saeed-codes.php` هستن هم در `web-service/helper-functions.php`. اگر همه‌جا از یک منبع (مثلاً فقط helper-functions یا فقط یک فایل مشترک) استفاده شود، از تکرار جلوگیری می‌شود.
- **`trim_home_url`** — در قالب و در `web-service/helper-functions.php` هر دو تعریف شده. یکی را به‌عنوان منبع حقیقی انتخاب و بقیه را به آن ارجاع بده.
- **`saeed_print`** — در `web-service/helper-functions.php` هم نسخه‌ای هست؛ یک نام و یک پیاده‌سازی نگه دار.

---

## ۴. اگر کل فایل saeed-codes.php را حذف کنی چه کارهایی لازم است

1. **حذف include از قالب**  
   در `wp-content/themes/escapezoom-v2/functions.php` خط `include get_template_directory() . '/inc/saeed-codes.php';` را حذف یا کامنت کن.

2. **جایگزینی وابستگی‌ها**  
   هر جایی که از توابع این فایل استفاده شده (مثلاً `ez_webservice`, `ez_reservation`, `ez_get_product_meta`, `get_sanses`, `get_day_type`, `add_to_sms_queue`, `ez_authorization`, `ez_get_product_meta`, `trim_home_url`, `get_term_link_flat` و …) باید یا همان منطق را به فایل/کلاس دیگری منتقل کنی یا آن فایل‌ها را به ماژول جدید وابسته کنی. جستجو در پروژه با نام هر تابع لازم است.

3. **Hookها و شورت‌کدها**  
   همه `add_action`, `add_filter`, `add_shortcode` که در این فایل ثبت شده‌اند (لیست در بخش ۱) باید در فایل جدید دوباره ثبت شوند یا در قالب/پلاگین جدا تعریف شوند؛ وگرنه منوهای ادمین، ستون‌های سفارش، چک‌اوت، کرون، تیکتینگ، متاباکس‌ها و … از کار می‌افتند.

4. **Cronها**  
   کرون‌های تعریف‌شده در این فایل (hottest، popular، topsale، recent، data، nactive، marketing، comments_stars، satisfaction، sms_queue، remove_expired_sms، booking_history_optimize، comment_reminder، zarinpal و …) باید در یک فایل دیگر با همان نام eventها ثبت شوند؛ وگرنه jobهای زمان‌بندی شده دیگر اجرا نمی‌شوند.

5. **وابستگی به فایل‌های دیگر**  
   این فایل `require_once` به `api/api.php`, `medoo/init.php`, `jwt-authentication-for-wp-rest-api/jwt-auth.php` دارد. آن منطق و وابستگی‌ها باید در مسیر بارگذاری جدید حفظ شوند.

6. **دیتابیس و وب‌سرویس**  
   جداول و APIهایی که این فایل استفاده می‌کند (مثل `wp_zb_booking_history`, صف SMS، `held_orders_list`, وب‌سرویس‌های web-service.php و reservation.php) بدون تغییر باقی می‌مانند؛ فقط کدی که آن‌ها را صدا می‌زند باید جای دیگری باشد.

7. **تست**  
   بعد از انتقال: لیست سفارش ووکامرس، صفحه تشکر، چک‌اوت، صفحه تک‌محصول، جستجو، پنل تیکت، کرون‌ها و هر جایی که قبلاً از این فایل استفاده می‌کرد را دستی یا با تست خودکار چک کن.

---

*آخرین به‌روزرسانی: گزارش تکمیل شد.*
