<?php
global $wpdb;

// ۱. استخراج تمام کالکشن‌ها و آیدی سازنده آن‌ها 
$collections_query = $wpdb->get_results("SELECT ID, user_id FROM collections");
$collection_map = [];
if ($collections_query) {
    foreach ($collections_query as $col) {
        $collection_map[$col->ID] = $col->user_id;
    }
}

// ۲. دریافت متای تمام کاربرانی که حداقل یک کالکشن را لایک کرده‌اند
$users_likes = $wpdb->get_results("SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'liked_collections'");

$updated_users_count = 0;

if ($users_likes) {
    foreach ($users_likes as $row) {
        $user_id = $row->user_id;
        $liked_collections = maybe_unserialize($row->meta_value);

        if ( ! is_array($liked_collections) ) continue;

        $liked_authors = [];

        // ۳. پیدا کردن آیدی نویسنده برای هر کالکشن
        foreach ($liked_collections as $col_id) {
            if ( isset($collection_map[$col_id]) ) {
                $liked_authors[] = (int) $collection_map[$col_id];
            }
        }

        // ۴. حذف تکراری‌ها و ذخیره متای جدید
        if ( ! empty($liked_authors) ) {
            $unique_authors = array_values( array_unique($liked_authors) );
            update_user_meta( $user_id, 'liked_collection_authors', $unique_authors );
            $updated_users_count++;
        }
    }
}


//////////////////////////////////////////////

// ۱. استخراج کاربرانی که حداقل یک بار بابت ساخت کالکشن امتیاز گرفته‌اند
$users_with_points = $wpdb->get_results("
    SELECT user_id, COUNT(ID) as points_count 
    FROM points 
    WHERE action = 'ایجاد کالکشن' 
    GROUP BY user_id
");

$total_deleted_points = 0;
$fixed_users_count = 0;

if ($users_with_points) {
    foreach ($users_with_points as $row) {
        $user_id = (int) $row->user_id;
        $points_count = (int) $row->points_count; // تعداد دفعاتی که کاربر امتیاز ساخت کالکشن گرفته

        // ۲. محاسبه تعداد واقعی کالکشن‌های فعلی کاربر در دیتابیس
        $actual_collections_count = (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(ID) 
            FROM collections 
            WHERE user_id = %d
        ", $user_id));

        // ۳. بررسی تخلف (تعداد امتیازات بیشتر از تعداد کالکشن‌های موجود)
        if ($points_count > $actual_collections_count) {
            
            // محاسبه تعداد امتیازی که باید حذف شود
            $excess_count = $points_count - $actual_collections_count;

            // ۴. استخراج آیدیِ ردیف‌های اضافی 
            // (جدیدترین امتیازاتی که گرفته را حذف می‌کنیم تا امتیازات قدیمی و اصلی‌اش باقی بمانند)
            $excess_points = $wpdb->get_results($wpdb->prepare("
                SELECT ID 
                FROM points 
                WHERE user_id = %d AND action = 'ایجاد کالکشن' 
                ORDER BY ID DESC 
                LIMIT %d
            ", $user_id, $excess_count));

            $ids_to_delete = [];
            foreach ($excess_points as $pt) {
                $ids_to_delete[] = (int) $pt->ID;
            }

            // ۵. حذف ردیف‌های اضافی
            if (!empty($ids_to_delete)) {
                $ids_string = implode(',', $ids_to_delete);
                $wpdb->query("DELETE FROM points WHERE ID IN ($ids_string)");
                
                // ۶. بروزرسانی محدودیت امتیاز کاربر در وردپرس 
                // تا سیستم بداند کاربر دقیقاً بابت چند کالکشن امتیاز گرفته است
                update_user_meta($user_id, 'collections_count_valid_points', $actual_collections_count);

                $total_deleted_points += count($ids_to_delete);
                $fixed_users_count++;
            }
        }
    }
}

///////////////////////////////////////

// ۱. استخراج تمام کالکشن‌ها به همراه آیدی نویسنده و لیست لایک‌کنندگان
$collections = $wpdb->get_results("SELECT user_id, users FROM collections");

// این آرایه تعداد واقعی افراد یکتایی که به هر نویسنده لایک داده‌اند را نگه می‌دارد
$valid_likes_per_owner = [];

if ($collections) {
    foreach ($collections as $col) {
        $owner_id = (int) $col->user_id;
        $likers = maybe_unserialize($col->users); // استخراج لیست لایک‌کنندگان
        
        // اگر هنوز سبدی برای این نویسنده نساخته‌ایم، آن را ایجاد کن
        if ( ! isset($valid_likes_per_owner[$owner_id]) ) {
            $valid_likes_per_owner[$owner_id] = [];
        }

        // اگر لیست لایک‌کنندگان آرایه معتبری است
        if ( is_array($likers) && !empty($likers) ) {
            foreach ($likers as $liker_id) {
                // ترفند برنامه‌نویسی برای حذف تکراری‌ها: 
                // وقتی آیدی کاربر را به عنوان "کلید" (Key) آرایه قرار می‌دهیم،
                // اگر قبلاً در کالکشنِ دیگری لایک کرده باشد، فقط کلیدِ قبلی روپوشانی می‌شود 
                // و هرگز یک کاربر دوبار شمرده نمی‌شود.
                $valid_likes_per_owner[$owner_id][$liker_id] = true;
            }
        }
    }
}

// ۲. استخراج لیست کاربرانی که در جدول امتیازات بابت لایک شدن، امتیاز گرفته‌اند
$users_with_points = $wpdb->get_results("
    SELECT user_id, COUNT(ID) as points_count 
    FROM points 
    WHERE action = 'لایک گرفتن کالکشن' 
    GROUP BY user_id
");

$total_deleted_points = 0;
$fixed_users_count = 0;

if ($users_with_points) {
    foreach ($users_with_points as $row) {
        $owner_id = (int) $row->user_id;
        $points_count = (int) $row->points_count; // تعداد امتیازی که تا الان گرفته

        // محاسبه تعداد واقعی اشخاصی که به این نویسنده لایک داده‌اند
        // با شمردن تعداد کلیدهای آرایه‌ای که در مرحله قبل ساختیم
        $valid_count = isset($valid_likes_per_owner[$owner_id]) ? count($valid_likes_per_owner[$owner_id]) : 0;

        // ۳. بررسی تخلف (تعداد امتیازات بیشتر از تعداد لایک‌کنندگان متمایز است)
        if ($points_count > $valid_count) {
            
            $excess_count = $points_count - $valid_count;

            // ۴. استخراج آیدیِ ردیف‌های اضافی (حذف از جدیدترین به قدیمی‌ترین)
            $excess_points = $wpdb->get_results($wpdb->prepare("
                SELECT ID 
                FROM points 
                WHERE user_id = %d AND action = 'لایک گرفتن کالکشن' 
                ORDER BY ID DESC 
                LIMIT %d
            ", $owner_id, $excess_count));

            $ids_to_delete = [];
            foreach ($excess_points as $pt) {
                $ids_to_delete[] = (int) $pt->ID;
            }

            // ۵. حذف ردیف‌های اضافی لایک از دیتابیس
            if (!empty($ids_to_delete)) {
                $ids_string = implode(',', $ids_to_delete);
                $wpdb->query("DELETE FROM points WHERE ID IN ($ids_string)");
                
                $total_deleted_points += count($ids_to_delete);
                $fixed_users_count++;
            }
        }
    }
}