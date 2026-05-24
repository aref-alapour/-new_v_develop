# مستندات پوشه app

پوشه `app` در تم escapezoom-v2 نقطه ورود AJAX و توابع کمکی تم است. این مستندات برای هر فایل و تابع اصلی، مسیر، نقش، وابستگی‌ها و پیشنهاد بهینه‌سازی را شرح می‌دهد.

## ساختار پوشه app

```
app/
├── init.php              # بارگذاری ajax/init و functions/init
├── index.php             # خالی (امنیت)
├── ajax/
│   ├── init.php          # هندلر واحد AJAX و بارگذاری داینامیک callbacks
│   └── callbacks/        # فایل‌های callback به ازای هر action (۵۴ فایل)
└── functions/
    ├── init.php          # بارگذاری helperها و create_cancellation_tables
    ├── create_cancellation_tables.php
    ├── create_call_me_table.php   # در init بارگذاری نمی‌شود
    └── helper/
        ├── product-options.php
        ├── reply-comments.php
        ├── get_order_ids.php
        ├── get_user_points.php
        ├── set-timestamp.php
        ├── text-align.php
        ├── cities_type.php
        ├── add-point.php
        ├── custom_product-tag-image_field.php
        ├── api.php               # در init بارگذاری نمی‌شود
        ├── get-banks-list.php
        ├── get-city-by-state.php
        └── user_level_system/
            ├── actions-points.php
            └── functions.php
```

## فهرست مستندات

### ورودی و هسته
| فایل | توضیح |
|------|--------|
| [00-app-overview.md](00-app-overview.md) | آنالیز `app/init.php`، `app/ajax/init.php`، `app/functions/init.php` |

### functions (ایجاد جداول و init)
| فایل | توضیح |
|------|--------|
| [functions-create_cancellation_tables.md](functions-create_cancellation_tables.md) | جداول درخواست/لاگ لغو رزرو |
| [functions-create_call_me_table.md](functions-create_call_me_table.md) | جدول «تماس با من» (در init لود نمی‌شود) |

### functions/helper
| فایل | توضیح |
|------|--------|
| [helper-product-options.md](helper-product-options.md) | `printOptions` — آپشن‌های محصول و آیکون |
| [helper-reply-comments.md](helper-reply-comments.md) | `get_post_reply_comments` — ریپلای کامنت‌ها |
| [helper-get_order_ids.md](helper-get_order_ids.md) | `get_orders_ids_by_product_id`, `get_owner_id_by_product_id` |
| [helper-get_user_points.md](helper-get_user_points.md) | `get_user_points` |
| [helper-set-timestamp.md](helper-set-timestamp.md) | timezone و `getStartAndEndTimestamps` |
| [helper-text-align.md](helper-text-align.md) | `getTextDirection` — RTL/LTR |
| [helper-cities_type.md](helper-cities_type.md) | `cities_type` و AJAX شهرها |
| [helper-add-point.md](helper-add-point.md) | `add_point` — امتیاز کاربر |
| [helper-custom_product-tag-image_field.md](helper-custom_product-tag-image_field.md) | فیلد تصویر تگ محصول |
| [helper-user_level_system-actions-points.md](helper-user_level_system-actions-points.md) | نقشه امتیاز و هوک‌های امتیاز (سفارش، نظر، کالکشن، لایک) |
| [helper-user_level_system-functions.md](helper-user_level_system-functions.md) | سطح کاربر، badge، تخفیف، دسترسی قابلیت‌ها، add_new_point |
| [helper-get-banks-list.md](helper-get-banks-list.md) | لیست بانک‌ها |
| [helper-get-city-by-state.md](helper-get-city-by-state.md) | شهر بر اساس استان |
| [helper-api.md](helper-api.md) | توابع عمومی و Escapezoom_Checkout (در init لود نمی‌شود) |

### AJAX Callbacks (گروه‌بندی)
| فایل | Callbacks پوشش‌داده‌شده |
|------|-------------------------|
| [callbacks-01-auth.md](callbacks-01-auth.md) | auth_login, auth_login_password, auth_new_otp, auth_signup, auth_verify_otp |
| [callbacks-02-panel.md](callbacks-02-panel.md) | panel_* (سفارشات، پروفایل، کیف پول، کالکشن، اعلان، دعوت، کامنت، سانس، فروش و…) |
| [callbacks-03-product.md](callbacks-03-product.md) | product_* و get_suggested_games, get_promotional_games, reserve_get_table |
| [callbacks-04-post-blog.md](callbacks-04-post-blog.md) | post_*, page_blog_get_posts, category_get_posts, get_author_posts, blog-cat-slider |
| [callbacks-05-profile-misc.md](callbacks-05-profile-misc.md) | profile_*, invite, check_comment_form, user_cancellation_*, user_edit_pass, top_users, و سایر |

---

**نکته:** فراخوانی AJAX از فرانت با action `v2_ajax_handler` و پارامتر `callback` (نام فایل بدون `.php`) انجام می‌شود؛ مثلاً `callback=panel_orders_get` → `app/ajax/callbacks/panel_orders_get.php`.
