# فهرست توابع و شورتکدهای بخش ahmadreza

آنالیز جداگانه برای **هر تابع و هر شورتکد** در `wp-content/themes/escapezoom-v2/ahmadreza`.

هر فایل MD شامل: **آدرس دقیق فایل و خط**، **کار تابع/شورتکد**، **محل استفاده در سایت**، **نحوه تغییر**، **تابع/کد مشابه برای ادغام**، **پیشنهاد بهینه‌سازی**.

---

## توابع init.php

| فایل | تابع | منبع (خط تقریبی) |
|------|------|-------------------|
| [write_log.md](write_log.md) | `write_log($data)` | ahmadreza/init.php ~3 |
| [has_role.md](has_role.md) | `has_role(...$roles)` | ahmadreza/init.php ~501 |
| [is_wc_login_page.md](is_wc_login_page.md) | `is_wc_login_page()` | ahmadreza/init.php ~510 |
| [get_replies.md](get_replies.md) | `get_replies($items)` | ahmadreza/init.php ~642 |
| [GetYoastTitle.md](GetYoastTitle.md) | `GetYoastTitle()` | ahmadreza/init.php ~717 |
| [change_schema_date_published.md](change_schema_date_published.md) | `change_schema_date_published($data)` | ahmadreza/init.php ~818 |

---

## توابع jdate.php

| فایل | تابع | منبع (خط تقریبی) |
|------|------|-------------------|
| [jdate.md](jdate.md) | `jdate(...)` | ahmadreza/jdate.php ~12 |
| [jstrftime.md](jstrftime.md) | `jstrftime(...)` | ahmadreza/jdate.php ~292 |
| [jmktime.md](jmktime.md) | `jmktime(...)` | ahmadreza/jdate.php ~594 |
| [jgetdate.md](jgetdate.md) | `jgetdate(...)` | ahmadreza/jdate.php ~658 |
| [jcheckdate.md](jcheckdate.md) | `jcheckdate($jm,$jd,$jy)` | ahmadreza/jdate.php ~685 |
| [tr_num.md](tr_num.md) | `tr_num($str,$mod,$mf)` | ahmadreza/jdate.php ~698 |
| [jdate_words.md](jdate_words.md) | `jdate_words($array,$mod)` | ahmadreza/jdate.php ~708 |
| [gregorian_to_jalali.md](gregorian_to_jalali.md) | `gregorian_to_jalali(...)` | ahmadreza/jdate.php ~931 |
| [jalali_to_gregorian.md](jalali_to_gregorian.md) | `jalali_to_gregorian(...)` | ahmadreza/jdate.php ~966 |

---

## شورتکدها

| فایل | شورتکد | منبع |
|------|--------|------|
| [shortcode-esadv.md](shortcode-esadv.md) | `[esadv id="" desc=""]` | ahmadreza/init.php ~668 |
| [shortcode-single-product-days.md](shortcode-single-product-days.md) | `[single-product-days]` | ahmadreza/shortcodes/single-product-days.php |
| [shortcode-home-trend-rooms.md](shortcode-home-trend-rooms.md) | `[home-trend-rooms]` | ahmadreza/shortcodes/home-trend-rooms.php |
| [shortcode-home-discounts-event.md](shortcode-home-discounts-event.md) | `[home-discounts-event]` | ahmadreza/shortcodes/home-discounts-event.php |
| [shortcode-home-scary-cinema.md](shortcode-home-scary-cinema.md) | `[home-scary-cinema]` | ahmadreza/shortcodes/home-scary-cinema.php |
| [shortcode-city-escape-rooms.md](shortcode-city-escape-rooms.md) | `[city-escape-rooms]` | ahmadreza/shortcodes/city-escape-rooms.php |
| [shortcode-home-cities-lasertag.md](shortcode-home-cities-lasertag.md) | `[home-cities-lasertag]` | ahmadreza/shortcodes/home-cities-lasertag.php |

---

## ACF

| فایل | موضوع | منبع |
|------|--------|------|
| [acf-product-category-fields.md](acf-product-category-fields.md) | گروه فیلد «اطلاعات دسته بندی» | ahmadreza/acf/product-category-fields.php |

---

**مسیر پایه:** `wp-content/themes/escapezoom-v2/ahmadreza/`
