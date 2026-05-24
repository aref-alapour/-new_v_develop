<?php
// فعال‌سازی نمایش خطاها برای عیب‌یابی
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// // جلوگیری از خروجی‌های ناخواسته
// ob_start();
// header('Content-Type: application/json; charset=utf-8');

// try {
//     // بارگذاری Medoo
//     $medooPath = __DIR__ . '/Medoo.php';
//     if (!file_exists($medooPath)) {
//         throw new Exception('فایل Medoo.php یافت نشد');
//     }
//     require_once $medooPath;

//     // اتصال به دیتابیس
//     $database = new Medoo\Medoo([
//         'type'      => 'mysql',
//         'host'      => 'localhost',
//         'database'  => 'escapezo_ez9920',
//         'username'  => 'escapezo_ez9920',
//         'password'  => 'te}c^4&^NmCE',
//         'charset'   => 'utf8mb4'
//     ]);

//     // تابع اصلی واکشی داده‌ها با فیلترهای پیچیده
//     function fetchData($filters) {
//         global $database;

//         $where = [];

//         // ستون‌های پیش‌فرض
//         $columns = [
//             'customer_id',
//             'customer_firstname',
//             'customer_lastname',
//             'customer_phone',
//             'customer_registered_at',
//             'order_id',
//             'order_status',
//             'order_sans_time',
//             'order_sans_day',
//             'order_sans_date',
//             'order_tickets_quantity',
//             'order_paid',
//             'order_net_profit',
//             'order_phones',
//             'order_refrerr',
//             'order_created_at',
//             'game_id',
//             'game_name',
//             'game_city',
//             'game_area',
//             'game_product_type',
//             'game_genres',
//             'game_duration',
//             'game_created_at'
//         ];

//         // نمونه فیلترهای قابل استفاده (می‌توانید توسعه بدهید)
//         if (isset($filters['game_name[~]'])) {
//             $where['game_name[~]'] = $filters['game_name[~]'];
//             $columns = ['game_id', 'game_name']; // محدود به بازی‌ها
//         }
//         if (isset($filters['customer_firstname'])) {
//             $where['customer_firstname[~]'] = '%' . $filters['customer_firstname'] . '%';
//         }
//         if (isset($filters['customer_lastname'])) {
//             $where['customer_lastname[~]'] = '%' . $filters['customer_lastname'] . '%';
//         }

//         if (!empty($filters['customer_id'])) {
//             $where['customer_id'] = $filters['customer_id'];
//         }
//         if (!empty($filters['customer_phone'])) {
//             $where['customer_phone[~]'] = '%' . $filters['customer_phone'] . '%';
//         }
//         if (!empty($filters['order_id'])) {
//             $where['order_id'] = $filters['order_id'];
//         }
//         if (!empty($filters['order_refrerr'])) {
//             $where['order_refrerr[~]'] = '%' . $filters['order_refrerr'] . '%';
//         }
//         if (!empty($filters['order_sans_day'])) {
//             $where['order_sans_day'] = $filters['order_sans_day'];
//         }
//         if (!empty($filters['game_id'])) {
//             $where['game_id'] = $filters['game_id'];
//         }

//         // بازه‌ها
//         if (!empty($filters['customer_registered_at_from'])) {
//             $where['customer_registered_at[>=]'] = $filters['customer_registered_at_from'];
//         }
//         if (!empty($filters['customer_registered_at_to'])) {
//             $where['customer_registered_at[<=]'] = $filters['customer_registered_at_to'];
//         }
//         if (!empty($filters['order_sans_time_from'])) {
//             $where['order_sans_time[>=]'] = $filters['order_sans_time_from'];
//         }
//         if (!empty($filters['order_sans_time_to'])) {
//             $where['order_sans_time[<=]'] = $filters['order_sans_time_to'];
//         }
//         if (!empty($filters['order_sans_date_from'])) {
//             $where['order_sans_date[>=]'] = $filters['order_sans_date_from'];
//         }
//         if (!empty($filters['order_sans_date_to'])) {
//             $where['order_sans_date[<=]'] = $filters['order_sans_date_to'];
//         }
//         if (!empty($filters['order_tickets_quantity_from'])) {
//             $where['order_tickets_quantity[>=]'] = $filters['order_tickets_quantity_from'];
//         }
//         if (!empty($filters['order_tickets_quantity_to'])) {
//             $where['order_tickets_quantity[<=]'] = $filters['order_tickets_quantity_to'];
//         }
//         if (!empty($filters['order_created_at_from'])) {
//             $where['order_created_at[>=]'] = $filters['order_created_at_from'];
//         }
//         if (!empty($filters['order_created_at_to'])) {
//             $where['order_created_at[<=]'] = $filters['order_created_at_to'];
//         }

//         if (!empty($filters['order_status'])) {
//             $where['order_status'] = $filters['order_status'];
//         }

//         // صفحه‌بندی
//         if (!empty($filters['LIMIT'])) {
//             // اگر مستقیم LIMIT ست شده بود
//             $where['LIMIT'] = $filters['LIMIT'];
//         } else {
//             $perPage = isset($filters['per_page']) && is_numeric($filters['per_page']) && (int)$filters['per_page'] > 0 ? (int)$filters['per_page'] : 10;
        
//             if (isset($filters['offset_page']) && is_numeric($filters['offset_page']) && (int)$filters['offset_page'] >= 0) {
//                 // اگر offset_page ست شده بود، از اون استفاده کن به عنوان offset
//                 $offset = (int)$filters['offset_page'];
//             } else {
//                 // وگرنه با توجه به page و perPage محاسبه کن
//                 $page = isset($filters['page']) ? max(1, (int)$filters['page']) : 1;
//                 $offset = ($page - 1) * $perPage;
//             }
//             $where['LIMIT'] = [$offset, $perPage];
//         }


//         // گروه‌بندی و ترتیب
//         if (!empty($filters['GROUP'])) {
//             $where['GROUP'] = $filters['GROUP'];
//         }
//         $where['ORDER'] = ['order_id' => 'DESC'];

//         // اجرای کوئری
//         return $database->select('wp_markting', $columns, $where);
//     }

//     // واکشی بازی‌ها با امکان جستجوی نام بازی
//     function fetchGames($filters) {
//         global $database;
//         $where = ['post_type' => 'product'];
//         $columns = ['ID', 'post_title'];
//         if (!empty($filters['game_name'])) {
//             $where['post_title[~]'] = $filters['game_name'];
//         }
//         return $database->select('wp_posts', $columns, $where);
//     }

//     // گرفتن پارامتر action از query string
//     $action = isset($_GET['action']) ? $_GET['action'] : null;

//     // دریافت فیلترها
//     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//         $filters = isset($_POST['filters']) ? json_decode($_POST['filters'], true) : [];
//     } else {
//         $filters = $_GET;
//         unset($filters['action']);
//     }

//     // اجرای تابع متناسب با action
//     switch ($action) {
//         case 'get_games':
//             $result = fetchGames($filters);
//             break;

//         case 'fetch_data':
//         default:
//             $result = fetchData($filters);
//             break;
//     }

//     ob_end_clean();
//     echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

// } catch (Exception $e) {
//     ob_end_clean();
//     http_response_code(500);
//     echo json_encode(['error' => 'خطا در fetch_data: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
// }
