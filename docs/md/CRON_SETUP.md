# راهنمای تنظیم Cron Job برای check_wallet_orders

## روش 1: استفاده از wp-cron.php (پیشنهادی و استاندارد)

وردپرس به صورت پیش‌فرض فایل `wp-cron.php` را دارد که می‌تواند توسط cron سرور فراخوانی شود. این روش بهترین و استانداردترین روش است.

### تنظیم در crontab:

```bash
# باز کردن crontab
crontab -e

# اضافه کردن این خط (هر ساعت در دقیقه 0)
# این دستور تمام cron job های وردپرس از جمله check_wallet_orders را اجرا می‌کند
0 * * * * curl -s https://yourdomain.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
```

یا با wget:
```bash
0 * * * * wget -q -O - https://yourdomain.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
```

**مزایا:**
- استفاده از سیستم cron استاندارد وردپرس
- تمام cron job های وردپرس را اجرا می‌کند
- نیازی به فایل اضافی نیست

## روش 2: استفاده از فایل wp-cron-server.php

فایل `wp-cron-server.php` در root وردپرس ایجاد شده است که می‌تواند مستقیماً اجرا شود.

### تنظیم در crontab:

```bash
# باز کردن crontab
crontab -e

# اضافه کردن این خط (هر ساعت در دقیقه 0)
# مسیر را با مسیر واقعی وردپرس خود جایگزین کنید
0 * * * * /usr/bin/php /path/to/your/wordpress/wp-cron-server.php >> /var/log/wp-cron.log 2>&1
```

یا با curl (اگر فایل در root وردپرس باشد):
```bash
0 * * * * curl -s https://yourdomain.com/wp-cron-server.php > /dev/null 2>&1
```

## روش 3: غیرفعال کردن wp-cron پیش‌فرض و استفاده از cron سرور (پیشنهادی برای سرورهای production)

اگر می‌خواهید wp-cron پیش‌فرض را کاملاً غیرفعال کنید و فقط از cron سرور استفاده کنید (مشابه Laravel jobs):

### 1. اضافه کردن به wp-config.php:

در فایل `wp-config.php` قبل از خط `/* That's all, stop editing! Happy publishing. */` این خط را اضافه کنید:

```php
// غیرفعال کردن wp-cron پیش‌فرض - استفاده از cron سرور
define('DISABLE_WP_CRON', true);
```

### 2. تنظیم crontab:

```bash
# باز کردن crontab
crontab -e

# هر 5 دقیقه wp-cron.php را اجرا کن (برای اجرای تمام cron job ها)
# این روش بهتر است چون تمام cron job های وردپرس را اجرا می‌کند
*/5 * * * * curl -s https://yourdomain.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
```

یا هر ساعت (فقط برای check_wallet_orders):
```bash
0 * * * * curl -s https://yourdomain.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
```

**مزایای این روش:**
- اجرای واقعی و مستقل از درخواست HTTP
- عملکرد بهتر (کمتر بار روی سرور)
- کنترل کامل بر زمان اجرا
- مشابه سیستم job در Laravel

## تست کردن

برای تست کردن cron job به صورت دستی:

```bash
# تست با curl
curl -s https://yourdomain.com/wp-cron.php?doing_wp_cron

# یا تست فایل wp-cron-server.php
php wp-cron-server.php
```

## بررسی لاگ‌ها

برای بررسی اینکه cron job اجرا شده یا نه:

```bash
# بررسی لاگ crontab
grep CRON /var/log/syslog

# یا اگر لاگ را به فایل می‌فرستید
tail -f /var/log/wp-cron.log
```

## نکات مهم

1. **امنیت**: اگر از curl/wget استفاده می‌کنید، مطمئن شوید که فایل `wp-cron-server.php` در دسترس عموم نباشد یا با authentication محافظت شود.

2. **زمان‌بندی**: `0 * * * *` یعنی هر ساعت در دقیقه 0 (مثلاً 1:00، 2:00، 3:00 و...)

3. **مسیر PHP**: مسیر `/usr/bin/php` ممکن است در سرور شما متفاوت باشد. برای پیدا کردن مسیر PHP:
   ```bash
   which php
   ```

4. **دسترسی**: مطمئن شوید که کاربر cron دسترسی لازم برای اجرای فایل‌ها را دارد.

