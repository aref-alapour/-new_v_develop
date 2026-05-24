<?php

/**
 * فایل Helper Functions برای EscapeZoom Core
 * 
 * این فایل شامل توابع کمکی برای استفاده راحت‌تر از Eloquent و مدل‌های پروژه است
 */

if (!function_exists('ez_query')) {
    /**
     * اجرای یک query مستقیم روی دیتابیس default
     * 
     * @param string $query
     * @param array $bindings
     * @return mixed
     */
    function ez_query(string $query, array $bindings = []) {
        return \EscapeZoom\Core\Database::raw($query, $bindings);
    }
}

if (!function_exists('ez_external_query')) {
    /**
     * اجرای یک query مستقیم روی دیتابیس external
     * 
     * @param string $query
     * @param array $bindings
     * @return mixed
     */
    function ez_external_query(string $query, array $bindings = []) {
        return \EscapeZoom\Core\Database::rawExternal($query, $bindings);
    }
}

if (!function_exists('ez_marketing_by_order')) {
    /**
     * دریافت اطلاعات مارکتینگ یک سفارش
     * 
     * @param int $orderId
     * @return \EscapeZoom\Core\Models\Marketing|null
     */
    function ez_marketing_by_order(int $orderId): ?\EscapeZoom\Core\Models\Marketing {
        return \EscapeZoom\Core\Models\Marketing::find($orderId);
    }
}

if (!function_exists('ez_booking_by_order')) {
    /**
     * دریافت اطلاعات رزرو یک سفارش
     * 
     * @param int $orderId
     * @return \EscapeZoom\Core\Models\BookingHistory|null
     */
    function ez_booking_by_order(int $orderId): ?\EscapeZoom\Core\Models\BookingHistory {
        return \EscapeZoom\Core\Models\BookingHistory::byOrder($orderId)->first();
    }
}

if (!function_exists('ez_product_by_id')) {
    /**
     * دریافت اطلاعات یک محصول از products_data
     * 
     * @param int $productId
     * @return \EscapeZoom\Core\Models\ProductData|null
     */
    function ez_product_by_id(int $productId): ?\EscapeZoom\Core\Models\ProductData {
        return \EscapeZoom\Core\Models\ProductData::where('product_id', $productId)->first();
    }
}

if (!function_exists('ez_search_products')) {
    /**
     * جستجوی محصولات بر اساس عنوان
     * 
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function ez_search_products(string $query, int $limit = 10) {
        return \EscapeZoom\Core\Models\ProductData::searchByTitle($query)
            ->active()
            ->limit($limit)
            ->get();
    }
}

if (!function_exists('ez_orders_by_status')) {
    /**
     * دریافت سفارشات بر اساس وضعیت
     * 
     * @param string $status
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function ez_orders_by_status(string $status, int $limit = 50) {
        return \EscapeZoom\Core\Models\Marketing::byStatus($status)
            ->orderBy('order_created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

if (!function_exists('ez_orders_by_date_range')) {
    /**
     * دریافت سفارشات در یک بازه زمانی
     * 
     * @param string $from
     * @param string $to
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function ez_orders_by_date_range(string $from, string $to) {
        return \EscapeZoom\Core\Models\Marketing::byDateRange($from, $to)
            ->orderBy('order_created_at', 'desc')
            ->get();
    }
}

if (!function_exists('ez_create_marketing_record')) {
    /**
     * ایجاد یک رکورد جدید در جدول مارکتینگ
     * 
     * @param array $data
     * @return \EscapeZoom\Core\Models\Marketing
     */
    function ez_create_marketing_record(array $data): \EscapeZoom\Core\Models\Marketing {
        return \EscapeZoom\Core\Models\Marketing::create($data);
    }
}

if (!function_exists('ez_update_marketing')) {
    /**
     * آپدیت اطلاعات مارکتینگ یک سفارش
     * 
     * @param int $orderId
     * @param array $data
     * @return bool
     */
    function ez_update_marketing(int $orderId, array $data): bool {
        return \EscapeZoom\Core\Models\Marketing::where('order_id', $orderId)
            ->update($data) > 0;
    }
}

if (!function_exists('ez_create_booking')) {
    /**
     * ایجاد یک رزرو جدید
     * 
     * @param array $data
     * @return \EscapeZoom\Core\Models\BookingHistory
     */
    function ez_create_booking(array $data): \EscapeZoom\Core\Models\BookingHistory {
        return \EscapeZoom\Core\Models\BookingHistory::create($data);
    }
}

if (!function_exists('ez_bookings_by_time_range')) {
    /**
     * دریافت رزروها در یک بازه زمانی
     * 
     * @param int $from Unix timestamp
     * @param int $to Unix timestamp
     * @param int|null $roomId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function ez_bookings_by_time_range(int $from, int $to, ?int $roomId = null) {
        $query = \EscapeZoom\Core\Models\BookingHistory::byTimeRange($from, $to);
        
        if ($roomId !== null) {
            $query->byRoom($roomId);
        }
        
        return $query->orderBy('booking_time', 'asc')->get();
    }
}

if (!function_exists('ez_has_booking')) {
    /**
     * چک کردن وجود رزرو برای یک سفارش
     * 
     * @param int $orderId
     * @return bool
     */
    function ez_has_booking(int $orderId): bool {
        return \EscapeZoom\Core\Models\BookingHistory::byOrder($orderId)->exists();
    }
}

if (!function_exists('ez_get_booking_time')) {
    /**
     * دریافت زمان رزرو یک سفارش
     * 
     * @param int $orderId
     * @return int|null Unix timestamp
     */
    function ez_get_booking_time(int $orderId): ?int {
        $booking = \EscapeZoom\Core\Models\BookingHistory::byOrder($orderId)->first();
        return $booking ? $booking->booking_time : null;
    }
}

if (!function_exists('ez_capsule')) {
    /**
     * دریافت Capsule instance
     * 
     * @return \Illuminate\Database\Capsule\Manager|null
     */
    function ez_capsule(): ?\Illuminate\Database\Capsule\Manager {
        return \EscapeZoom\Core\Database::getCapsule();
    }
}
