<?php
define('THEME_ASSETS_URL', 'https://escapezoom.ir/wp-content/themes/escapezoom-v2/assets');

require $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/escapezoom-v2/inc/medoo/init.php';

$medoo = medoo();

$page_num           = isset($_POST['page']) ? $_POST['page'] : 1;
$current_user_roles = isset($_POST['current_user_roles']) ? [$_POST['current_user_roles']] : [];
$term               = isset($_POST['term']) ? trim($_POST['term']) : '';
$filters            = $_POST['filters'] ?? [];

if ($page_num < 1)
    $page_num = 1;

$posts_per_page = 50;

$offset = ($page_num - 1) * $posts_per_page;

$columns = [
    "order_id",
    "order_created_at",
    "customer_id",
    "customer_level",
    "customer_firstname",
    "customer_lastname",
    "customer_phone",
    "game_name",
    "order_tickets_quantity",
    "order_transaction_id",
    "order_paid",
    "order_sans_time",
    "order_sans_date",
    "order_status",
    "order_happycall",
    "order_coupon_used",
    "order_phones",
    "complete_change_flag",
    "order_deposit",
];

$where = [];

// اگر سرچ وجود داشت
if ($term !== '') {
    if (strpos($term, ' ') !== false) {
        // چندکلمه‌ای (اسم و فامیل)
        $parts     = preg_split('/\s+/', $term, 2);
        $firstname = $parts[0];
        $lastname  = isset($parts[1]) ? $parts[1] : '';

        $where['OR'] = [
            // حالت اسم + فامیل
            "AND" => [
                "customer_firstname[~]" => $firstname,
                "customer_lastname[~]"  => $lastname,
            ],
            // حالت اسم بازی
            "game_name[~]" => $term,
        ];
    } else {
        // تک‌کلمه‌ای (ممکنه شماره، اسم، فامیل یا اسم بازی باشه)
        $phone_term = $term;
        if (preg_match('/^0?\d{10}$/', $term)) {
            if (strpos($term, '0') === 0) {
                $phone_term = substr($term, 1);
            } else {
                $phone_term = '0' . $term;
            }
        }

        $where['OR'] = [
            "order_id[~]"           => $term,
            "customer_firstname[~]" => $term,
            "customer_lastname[~]"  => $term,
            "customer_phone[~]"     => [$term, $phone_term],
            "game_name[~]"          => $term,
        ];
    }
}

// فیلترها
if (!empty($filters)) {

    // فیلتر وضعیت سفارش
    if (isset($filters['orderStatus']) && $filters['orderStatus'] !== 'all') {
        $status = $filters['orderStatus'];
        $where["order_status"] = [$status, "wc-" . $status];
    }

    // فیلتر سشن‌های مشکل‌دار
    if (isset($filters['problematicSessions']) && $filters['problematicSessions'] !== 'all') {
        $statusPartiallyPaid = ['partially-paid', 'wc-partially-paid'];

        if ($filters['problematicSessions'] === 'problematic') {
            $where["AND"] = [
                "order_status" => $statusPartiallyPaid,
                "OR" => [
                    "order_sans_date"   => null,
                    "order_sans_time"   => null,
                    "order_sans_day"    => null
                ]
            ];
        }
    }


    // فیلتر تعداد نفرات
    if (isset($filters['numberOfPeople']) && $filters['numberOfPeople'] !== 'all') {

        $values = array_map('intval', explode(',', $filters['numberOfPeople']));

        if (count($values) === 2)
            $where["order_tickets_quantity[<>]"] = [$values[0], $values[1]];
        else
            $where["order_tickets_quantity"] = $values[0];
    }
}

$where["ORDER"] = ["order_created_at" => "DESC"];
$where["LIMIT"] = [$offset, $posts_per_page];

$orders = $medoo->select("wp_markting", $columns, $where);

$count_where = $where;
unset($count_where["ORDER"], $count_where["LIMIT"]);

$total       = $medoo->count("wp_markting", "*", $count_where);
$total_pages = (int) ceil($total / $posts_per_page);

$order_status_name = [
    'pending' => [
        'name'  => 'در انتظار پرداخت',
        'color' => '#FD7013'
    ],
    'wc-pending' => [
        'name'  => 'در انتظار پرداخت',
        'color' => '#FD7013'
    ],
    'cancelled' => [
        'name'  => 'لغو شده',
        'color' => '#F21543'
    ],
    'wc-cancelled' => [
        'name'  => 'لغو شده',
        'color' => '#F21543'
    ],
    'refunded' => [
        'name'  => 'مسترد شده',
        'color' => '#F21543'
    ],
    'wc-refunded' => [
        'name'  => 'مسترد شده',
        'color' => '#F21543'
    ],
    'conflict' => [
        'name'  => 'تداخل',
        'color' => '#F21543'
    ],
    'wc-conflict' => [
        'name'  => 'تداخل',
        'color' => '#F21543'
    ],
    'admin-cancelled' => [
        'name'  => 'لغو ادمین',
        'color' => '#F21543'
    ],
    'wc-admin-cancelled' => [
        'name'  => 'لغو ادمین',
        'color' => '#F21543'
    ],
    'completed' => [
        'name'  => 'تکمیل شده',
        'color' => '#049654'
    ],
    'wc-completed' => [
        'name'  => 'تکمیل شده',
        'color' => '#049654'
    ],
    'partially-paid' => [
        'name'  => 'پیش پرداخت',
        'color' => '#049654'
    ],
    'wc-partially-paid' => [
        'name'  => 'پیش پرداخت',
        'color' => '#049654'
    ],
    'completed-paid' => [
        'name'  => 'پرداخت کامل',
        'color' => '#A020F0'
    ],
    'wc-completed-paid' => [
        'name'  => 'پرداخت کامل',
        'color' => '#A020F0'
    ],
    'walletx' => [
        'name'  => 'واریز به کیف پول',
        'color' => '#049654'
    ],
    'wc-walletx' => [
        'name'  => 'واریز به کیف پول',
        'color' => '#049654'
    ],
]; 
// رنگ سطح کاربری (سازمانی) برای نام خریدار
$customer_level_colors = [
    1 => '#959798', // تازه وارد
    2 => '#049654', // نوپا
    3 => '#3F7FF5', // با تجربه
    4 => '#FD7013', // کارکشته
];
?>

<section class="justify-center items-center mt-7 mx-auto">
    <div class="w-full py-4 rounded-t-2.5xl bg-[#E4EBF0]">
        <div class="grid" style="grid-template-columns: 2fr 2fr 4fr 2fr 5fr 1fr 2fr 2fr 2fr 5fr 1fr 3fr 2fr">
            <p class="text-center mx-auto">کد رزرو</p>
            <p class="text-center mx-auto">تاریخ رزرو</p>
            <p class="text-center mx-auto">نام</p>
            <p class="text-center mx-auto">تماس</p>
            <p class="text-center mx-auto">بازی</p>
            <p class="text-center mx-auto">تعداد</p>
            <p class="text-center mx-auto">شماره تراکنش</p>
            <p class="text-center mx-auto">سپرده</p>
            <p class="text-center mx-auto">کد تخفیف</p>
            <p class="text-center mx-auto">سانس</p>
            <p class="text-center mx-auto">هپی</p>
            <p class="text-center mx-auto">وضعیت</p>
            <p class="text-center mx-auto">عملیات</p>
        </div>
    </div>

    <div class="w-full h-full rounded-t-2.5xlb" id="ordersTable">

        <?php
        foreach ($orders as $order) {

            static $row_index = 0;

            $order_id   = $order['order_id'];
            $sans_date  = $order['order_sans_time'] . ' ' . $order['order_sans_date'];

            if (! empty($order['order_sans_time']))
                $sans_date = parsidate('H:i___Y-m-d', strtotime($order['order_sans_time'] . ' ' . $order['order_sans_date']));
            else
                $sans_date = '---';

            $happy_call_status = $order['order_happycall'] == 1 ? '1' : '0';

            $background = $happy_call_status ? '#5091FB' : '#fff';
            $span_display = $happy_call_status ? 'flex' : 'none';
            $checked_attr = $happy_call_status ? 'checked' : '';

            $paid = $order['order_paid'];
            // if( $order_status_name[$order['order_status']]['name'] == 'پیش پرداخت' and $order['complete_change_flag'] ) // سفارش کاملی که به پیش پرداخت تبدیل شده پس پیش پرداخت هم باید مطابق پیش پرداخت ها باشه
            //     $paid = $order['order_deposit'];

            // استخراج نام و شماره هم‌تیمی‌ها از order_phones
            $teammate_data = [];
            if (!empty($order['order_phones'])) {
                $order_phones_data = $order['order_phones'];
                
                // اگر به صورت serialized string است، unserialize کن
                if (is_string($order_phones_data)) {
                    $unserialized = @unserialize($order_phones_data);
                    if ($unserialized !== false) {
                        $order_phones_data = $unserialized;
                    } else {
                        // اگر JSON است
                        $json_decoded = @json_decode($order_phones_data, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $order_phones_data = $json_decoded;
                        }
                    }
                }
                
                // استخراج نام و شماره
                if (is_array($order_phones_data)) {
                    foreach ($order_phones_data as $item) {
                        if (is_array($item) && isset($item['phone'])) {
                            // اگر آرایه‌ای از آرایه‌ها با کلید phone و name است
                            $teammate_data[] = [
                                'name' => isset($item['name']) ? $item['name'] : '',
                                'phone' => $item['phone']
                            ];
                        } elseif (is_string($item)) {
                            // اگر آرایه‌ای از رشته‌ها است (فقط شماره)
                            $teammate_data[] = [
                                'name' => '',
                                'phone' => $item
                            ];
                        }
                    }
                }
            }
            
            // تبدیل به JSON برای استفاده در data-phones
            $teammate_phones_json = htmlspecialchars(json_encode($teammate_data), ENT_QUOTES, 'UTF-8'); ?>

            <div class="orders_table_row grid <?= (++$row_index % 2 == 0) ? 'bg-gray-100/50' : ''; ?> text-center px-4 py-2.5" data-id="<?php echo $order_id ?>"
                style="grid-template-columns: 1fr 2fr 4fr 2fr 4fr 1fr 2fr 2fr 2fr 4fr 1fr 3fr 1fr">
                <p class="text-base content-center text-[#889BAD] col-order_id"><?php echo isset($order_id) ? $order_id : '---'; ?></p>
                <p class="text-base content-center text-navyBlue"><?php echo human_time_diff(strtotime($order['order_created_at']), time()) . ' قبل' ?></p>
                 <?php
                $customer_level = isset($order['customer_level']) ? max(1, min(4, (int) $order['customer_level'])) : 1;
                $name_color = $customer_level_colors[$customer_level] ?? $customer_level_colors[1];
                ?>
                <p class="text-base content-center col-name font-bold" style="color: <?= $name_color ?>"><?= $order['customer_firstname'] . ' ' . $order['customer_lastname'] ?></p>
                <p class="text-base content-center text-navyBlue col-phone"><?php echo $order['customer_phone'] ?></p>
                <p class="text-base content-center text-navyBlue col-game"><?php echo $order['game_name'] ?></p>
                <p class="text-base content-center text-navyBlue <?php echo $order_status_name[$order['order_status']]['name'] != 'پرداخت کامل' ? 'quantity' : '' ?>"><?php echo $order['order_tickets_quantity'] ?></p>
                <p class="text-base content-center text-navyBlue"><?php echo $order['order_transaction_id'] ?: '---' ?></p>
                <p class="text-base content-center text-navyBlue"><?php echo number_format(intval($paid)); ?></p>
                <p class="text-base content-center text-navyBlue"><?php echo !empty($order['order_coupon_used']) ? $order['order_coupon_used'] : '-'; ?></p>
                <p class="text-base content-center text-navyBlue"><?php echo $sans_date; ?></p>

                <p class="text-base content-center text-navyBlue">
                    <label class="flex items-center justify-center relative" style="position: relative;">
                        <input type="checkbox" class="happycall" value="1" <?php echo $checked_attr; ?>
                            style="appearance: none; width: 24px; height: 24px; border-radius: 50%; border: 2px solid #D1D5DB; background: <?php echo $background; ?>; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; position: relative; z-index: 1;">
                        <span style="pointer-events: none; position: absolute; width: 24px; height: 24px; display: <?php echo $span_display; ?>; align-items: center; justify-content: center; left: 13px; top: 0; z-index: 2;">
                            <svg class="checkmark" style="width: 16px; height: 16px; margin: auto;" viewBox="0 0 16 16" fill="none">
                                <path d="M4 8.5L7 11.5L12 5.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                    </label>
                </p>

                <p class="text-base font-bold content-center" style="color:<?= $order_status_name[$order['order_status']]['color'] ?>"><?php echo @$order_status_name[$order['order_status']]['name'] ?></p>

                <div class="mainContent transition-all duration-300 flex gap-2">

                    <?php
                    if (array_intersect(['administrator', 'supervisor', 'poshtiban'], $current_user_roles)) : ?>
                        <button alt="CRM" class="openCrmModal cursor-pointer hover:opacity-80 w-7 h-7 m-auto transition" data-id="<?= $order_id ?>" data-phones="<?= $teammate_phones_json ?>" data-happy-call="<?= $happy_call_status ?>">
                            <img src="<?= THEME_ASSETS_URL . '/images/crm-btn.png' ?>" alt="CRM" class="w-7 h-7">
                        </button>
                    <?php
                    endif;

                    if (array_intersect(['administrator', 'supervisor', 'accounting'], $current_user_roles)) : 
                        $complete_change_flag = $order['complete_change_flag']; 
                        $order_status = $order['order_status']; ?>

                        <button alt="Mali" class="openMaliModal cursor-pointer hover:opacity-80 w-7 h-7 m-auto transition" data-id="<?= $order_id ?>" data-order-status="<?= htmlspecialchars($order_status, ENT_QUOTES, 'UTF-8') ?>" complete_change_flag="<?= $complete_change_flag ? '1' : '0' ?>">
                            <img src="<?= THEME_ASSETS_URL . '/images/mali-btn.png' ?>" alt="Mali" class="w-7 h-7">
                        </button>
                    <?php
                    endif; ?>

                </div>

            </div>

        <?php
        } ?>

    </div>
</section>

<?php
if ($total_pages > 1) { ?>
    <div class="flex justify-center mt-6">
        <div class="flex items-center space-x-2 space-x-reverse pagination">

            <?php
            if ($page_num > 1) { ?>
                <button data-page="<?php echo $page_num - 1 ?>" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700">قبلی</button>
            <?php
            }

            if ($page_num > 3) { ?>
                <button data-page="1" class="px-3 py-2 text-sm font-medium border rounded-lg text-gray-500 bg-white border-gray-300 hover:bg-gray-50 hover:text-gray-700">1</button>
                <?php if ($page_num > 4) { ?>
                    <span class="px-2">...</span>
                <?php }
            }

            $start_page = max(1, $page_num - 2);
            $end_page   = min($total_pages, $page_num + 2);

            for ($i = $start_page; $i <= $end_page; $i++) {
                $active_class = $i == $page_num ? 'text-white bg-orange-500 border-orange-500' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50 hover:text-gray-700'; ?>
                <button data-page="<?php echo $i ?>" class="px-3 py-2 text-sm font-medium border rounded-lg <?php echo $active_class ?>"><?php echo $i ?></button>
                <?php
            }

            if ($page_num < $total_pages - 2) {
                if ($page_num < $total_pages - 3) { ?>
                    <span class="px-2">...</span>
                <?php } ?>
                <button data-page="<?php echo $total_pages ?>" class="px-3 py-2 text-sm font-medium border rounded-lg text-gray-500 bg-white border-gray-300 hover:bg-gray-50 hover:text-gray-700"><?php echo $total_pages ?></button>
            <?php
            }

            if ($page_num < $total_pages) { ?>
                <button data-page="<?php echo ($page_num + 1) ?>" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700">بعدی</button>
            <?php
            }
            ?>


        </div>
    </div>

<?php
}
?>
<!-----------CRM Modal------------------------------------->
<section class="flex justify-center items-center fixed inset-0 modal-bg z-50 transition-opacity" id="crmModal" data-id="" style="display: none;">
    <div class="rounded-xl bg-white border border-[#DBE2EA] shadow-[0px_1px_0px_0px_#DBE2EA] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 absolute p-5 w-[442px]">
        <div class="mb-4">
            <div class="flex justify-between items-center cursor-pointer" id="teammatesToggle">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M8 8C9.65685 8 11 6.65685 11 5C11 3.34315 9.65685 2 8 2C6.34315 2 5 3.34315 5 5C5 6.65685 6.34315 8 8 8Z" fill="#6B7280" />
                        <path d="M8 9C5.79086 9 4 10.7909 4 13V14H12V13C12 10.7909 10.2091 9 8 9Z" fill="#6B7280" />
                    </svg>
                    <span class="text-grayy text-sm">مشاهده هم تیمی ها</span>
                </div>
                <svg class="mx-0 transition-transform duration-300" id="teammatesArrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M4 6L8 10L12 6" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <!-- Teammates Dropdown -->
            <div id="teammatesDropdown" class="mt-2 hidden">
                <div class="max-h-48 overflow-y-auto">
                    <div class="space-y-1">
                        <!-- Phone numbers will be populated here via JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <hr class="mb-4" />

        <div class="grid grid-cols-2 gap-x-4">
            <button class="cancellation_request px-4 py-1.5 rounded-lg text-white cursor-pointer bg-gray-500 text-sm font-bold w-full h-12.5" data-type="customer">کنسلی برای پلیر</button>
            <button class="cancellation_request px-4 py-1.5 rounded-lg text-white cursor-pointer bg-gray-500 text-sm font-bold w-full h-12.5" data-type="owner">کنسلی برای مجموعه</button>
        </div>

        <!-- Owner Cancellation Reason Box (Sliding from bottom) -->
        <div id="ownerCancellationBox" class="mt-4 p-4 bg-white hidden">
            <h5 class="text-base font-bold mb-2">چرا می خواهید سانس را لغو کنید؟</h5>
            <p class="text-sm text-grayy mb-4">لطفاً یک گزینه را انتخاب کنید:</p>
            <hr class="mb-4 bg-gray-200 h-[2px]">
            <div class="space-y-3 mb-4">

                <?php
                foreach (cancellation_reasons() as $key => $reason) : ?>

                    <label class="flex items-center gap-x-2 cursor-pointer">
                        <div class="relative">
                            <input type="radio" name="cancellationReason" value="<?php echo $key ?>" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 opacity-0 absolute">
                            <div class="w-4 h-4 border-2 border-gray-300 rounded-full flex items-center justify-center radio-custom">
                                <div class="w-2 h-2 bg-blue-600 rounded-full hidden radio-check"></div>
                            </div>
                        </div>
                        <span class="text-sm"><?php echo $reason ?></span>
                    </label>

                <?php
                endforeach; ?>

            </div>

            <div class="flex items-start gap-2 mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm">در صورت لغو کمتر از ۲۴ ساعت پیش از سانس، امتیاز و عملکرد برند تحت تأثیر قرار می گیرد.</p>
            </div>

            <button id="submitCancellation" class="w-full bg-orange-500 text-white px-4 py-2 rounded-md text-sm font-bold hover:bg-orange-600 h-12.5">ثبت</button>
        </div>
    </div>
</section>

<!-----------Mali Modal------------------------------------->
<section class="flex justify-center items-center fixed inset-0 modal--center items-center fixed inset-0 modal-bg z-50 transition-opacity" id="maliModal" data-id="" style="display: none;">
    <div class="rounded-xl bg-white border border-[#DBE2EA] shadow-[0px_1px_0px_0px_#DBE2EA] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 absolute p-5">
        <div class="grid grid-cols-4 gap-x-2">
            <button class="order_status_change px-2 py-1.5 rounded-lg text-white cursor-pointer bg-red-500 text-sm font-bold w-full" data-action="trash">زباله دان</button>
            <button class="order_status_change px-2 py-1.5 rounded-lg text-white cursor-pointer bg-[#1C398E] text-sm font-bold w-full" data-action="walletx">کیف پول</button>
            <button class="order_status_change px-2 py-1.5 rounded-lg text-white cursor-pointer bg-[#FF6900] text-sm font-bold w-full" data-action="refunded">مسترد</button>
            <button class="order_status_change px-2 py-1.5 rounded-lg text-white cursor-pointer bg-[#F21543] text-sm font-bold w-full" data-action="admin-cancelled">لغو ادمین</button>
            <button id="convert_complete_to_partial" class="x-2 py-1.5 rounded-lg text-white cursor-pointer bg-[#10B981] text-sm font-bold w-full" data-action="convert_complete_to_partial" style="display: none;">تغییر به پیش پرداخت</button>
        </div>
    </div>
</section>

<?php
function cancellation_reasons()
{
    return [
        1 => 'تداخل سانس',
        2 => 'نقص فنی غیرمنتظره',
        3 => 'تعطیل بودن مجموعه',
        4 => 'قطعی برق',
        5 => 'عدم تکمیل کادر پرسنل',
        6 => 'سایر',
    ];
}

function human_time_diff($from, $to = 0)
{
    if (empty($to)) {
        $to = time();
    }

    $diff = (int) abs($to - $from);

    if ($diff < 60) {
        $secs = $diff;
        if ($secs <= 1) {
            $secs = 1;
        }
        $since = sprintf('%s ثانیه', $secs);
    } elseif ($diff < 60 * 60) {
        $mins = round($diff / 60);
        if ($mins <= 1) {
            $mins = 1;
        }
        $since = sprintf('%s دقیقه', $mins);
    } elseif ($diff < 24 * 60 * 60) {
        $hours = round($diff / (60 * 60));
        if ($hours <= 1) {
            $hours = 1;
        }
        $since = sprintf('%s ساعت', $hours);
    } elseif ($diff < 7 * 24 * 60 * 60) {
        $days = round($diff / (24 * 60 * 60));
        if ($days <= 1) {
            $days = 1;
        }
        $since = sprintf('%s روز', $days);
    } elseif ($diff < 30 * 24 * 60 * 60) {
        $weeks = round($diff / (7 * 24 * 60 * 60));
        if ($weeks <= 1) {
            $weeks = 1;
        }
        $since = sprintf('%s هفته', $weeks);
    } elseif ($diff < 365 * 24 * 60 * 60) {
        $months = round($diff / (30 * 24 * 60 * 60));
        if ($months <= 1) {
            $months = 1;
        }
        $since = sprintf('%s ماه', $months);
    } else {
        $years = round($diff / (365 * 24 * 60 * 60));
        if ($years <= 1) {
            $years = 1;
        }
        $since = sprintf('%s سال', $years);
    }

    return $since;
}

class bn_parsidate
{
    protected static $instance;
    public $persian_month_names = array(
        '',
        'فروردین',
        'اردیبهشت',
        'خرداد',
        'تیر',
        'مرداد',
        'شهریور',
        'مهر',
        'آبان',
        'آذر',
        'دی',
        'بهمن',
        'اسفند'
    );
    public $persian_short_month_names = array(
        '',
        'فروردین',
        'اردیبهشت',
        'خرداد',
        'تیر',
        'مرداد',
        'شهریور',
        'مهر',
        'آبان',
        'آذر',
        'دی',
        'بهمن',
        'اسفند'
    );
    public $sesson = array('بهار', 'تابستان', 'پاییز', 'زمستان');

    public $persian_day_names = array('یکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه');
    public $persian_day_small = array('ی', 'د', 'س', 'چ', 'پ', 'ج', 'ش');

    public $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
    private $j_days_sum_month = array(0, 0, 31, 62, 93, 124, 155, 186, 216, 246, 276, 306, 336);

    private $g_days_sum_month = array(0, 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);


    /**
     * Constructor
     */
    function __construct() {}

    /**
     * bn_parsidate::IsPerLeapYear()
     * check year is leap
     *
     * @param mixed $year
     *
     * @return boolean
     */
    public function IsPerLeapYear($year)
    {
        $mod = $year % 33;

        if ($mod == 1 or $mod == 5 or $mod == 9 or $mod == 13 or $mod == 17 or $mod == 22 or $mod == 26 or $mod == 30) {
            return true;
        }
        return false;
    }

    /**
     * bn_parsidate::IsLeapYear()
     * check year is leap
     *
     * @param mixed $year
     *
     * @return boolean
     */
    private function IsLeapYear($year)
    {
        if ((($year % 4) == 0 && ($year % 100) != 0) || (($year % 400) == 0) && ($year % 100) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * bn_parsidate::persian_date()
     * convert gregorian datetime to persian datetime
     *
     * @param mixed $format
     * @param string $date
     * @param string $lang
     *
     * @return string
     */
    public function persian_date($format, $date = 'now', $lang = 'per')
    {

        $j_days_in_month = array(31, 62, 93, 124, 155, 186, 216, 246, 276, 306, 336, 365);
        $timestamp = is_numeric($date) && (int)$date == $date ? $date : strtotime($date);
        $date = getdate($timestamp);
        list($date['year'], $date['mon'], $date['mday']) = self::gregorian_to_persian($date['year'], $date['mon'], $date['mday']);
        $date['mon'] = (int)$date['mon'];
        $date['mday'] = (int)$date['mday'];
        $out = '';
        $len = strlen($format);
        for ($i = 0; $i < $len; $i++) {
            switch ($format[$i]) {
                //day
                case 'd':
                    $out .= ($date['mday'] < 10) ? '0' . $date['mday'] : $date['mday'];
                    break;
                case 'D':
                    $out .= $this->persian_day_small[$date['wday']];
                    break;
                case 'l':
                    $out .= $this->persian_day_names[$date['wday']];
                    break;
                case 'j':
                    $out .= $date['mday'];
                    break;
                case 'N':
                    $out .= $this->week_day($date['wday']) + 1;
                    break;
                case 'w':
                    $out .= $this->week_day($date['wday']);
                    break;
                case 'z':
                    if ($date['mon'] == 12 && self::IsPerLeapYear($date['year']))
                        $out .= 30 + $date['mday'];
                    else
                        $out .= $this->j_days_in_month[$date['mon']] + $date['mday'];
                    break;
                //week
                case 'W':
                    $yday = $this->j_days_sum_month[$date['mon'] - 1] + $date['mday'];
                    $out .= intval($yday / 7);
                    break;
                //month
                case 'f':
                    $mon = $date['mon'];
                    switch ($mon) {
                        case ($mon < 4):
                            $out .= $this->sesson[0];
                            break;
                        case ($mon < 7):
                            $out .= $this->sesson[1];
                            break;
                        case ($mon < 10):
                            $out .= $this->sesson[2];
                            break;
                        case ($mon > 9):
                            $out .= $this->sesson[3];
                            break;
                    }
                    break;
                case 'F':
                    $out .= $this->persian_month_names[(int)$date['mon']];
                    break;
                case 'm':
                    $out .= ($date['mon'] < 10) ? '0' . $date['mon'] : $date['mon'];
                    break;
                case 'M':
                    $out .= $this->persian_short_month_names[(int)$date['mon']];
                    break;
                case 'n':
                    $out .= $date['mon'];
                    break;
                case 'S':
                    $out .= 'ام';
                    break;
                case 't':
                    if ($date['mon'] == 12 && self::IsPerLeapYear($date['year']))
                        $out .= 30;
                    else
                        $out .= $this->j_days_in_month[(int)$date['mon'] - 1];
                    break;
                //year
                case 'L':
                    $out .= (($date['year'] % 4) == 0) ? 1 : 0;
                    break;
                case 'o':
                case 'Y':
                    $out .= $date['year'];
                    break;
                case 'y':
                    $out .= substr($date['year'], 2, 2);
                    break;
                //time
                case 'a':
                    $out .= ($date['hours'] < 12) ? 'ق.ظ' : 'ب.ظ';
                    break;
                case 'A':
                    $out .= ($date['hours'] < 12) ? 'قبل از ظهر' : 'بعد از ظهر';
                    break;
                case 'B':
                    $out .= (int)(1 + ($date['mon'] / 3));
                    break;
                case 'g':
                    $out .= ($date['hours'] > 12) ? $date['hours'] - 12 : $date['hours'];
                    break;
                case 'G':
                    $out .= $date['hours'];
                    break;
                case 'h':
                    $hour = ($date['hours'] > 12) ? $date['hours'] - 12 : $date['hours'];
                    $out .= ($hour < 10) ? '0' . $hour : $hour;
                    break;
                case 'H':
                    $out .= ($date['hours'] < 10) ? '0' . $date['hours'] : $date['hours'];
                    break;
                case 'i':
                    $out .= ($date['minutes'] < 10) ? '0' . $date['minutes'] : $date['minutes'];
                    break;
                case 's':
                    $out .= ($date['seconds'] < 10) ? '0' . $date['seconds'] : $date['seconds'];
                    break;
                //full date time
                case 'c':
                    $out = $date['year'] . '/' . $date['mon'] . '/' . $date['mday'] . ' ' . $date['hours'] . ':' . (($date['minutes'] < 10) ? '0' . $date['minutes'] : $date['minutes']) . ':' . (($date['seconds'] < 10) ? '0' . $date['seconds'] : $date['seconds']); //2004-02-12T15:19:21+00:00
                    break;
                case 'r':
                    $out = $this->persian_day_names[$date['wday']] . ',' . $date['mday'] . ' ' . $this->persian_month_names[(int)$date['mon']] . ' ' . $date['year'] . ' ' . $date['hours'] . ':' . (($date['minutes'] < 10) ? '0' . $date['minutes'] : $date['minutes']) . ':' . (($date['seconds'] < 10) ? '0' . $date['seconds'] : $date['seconds']); //Thu, 21 Dec 2000 16:01:07
                    break;
                case 'U':
                    $out = $timestamp;
                    break;
                //others
                case 'e':
                case 'I':
                case 'O':
                case 'P':
                case 'T':
                case 'Z':
                case 'u':
                    break;
                default:
                    $out .= $format[$i];
            }
        }

        if (strtolower($format) != 'u' && $lang == 'per') {
            return self::trim_number($out);
        } else {
            return $out;
        }
    }

    /**
     * bn_parsidate::gregorian_to_persian()
     * convert gregorian date to persian date
     *
     * @param mixed $gy
     * @param mixed $gm
     * @param mixed $gd
     *
     * @return array
     */
    function gregorian_to_persian($gy, $gm, $gd)
    {
        $dayOfYear = $this->g_days_sum_month[(int)$gm] + $gd;
        if (self::IsLeapYear($gy) and $gm > 2) {
            $dayOfYear++;
        }
        $d_33 = (int)((($gy - 16) % 132) * 0.0305);
        $leap = $gy % 4;
        $a = (($d_33 == 1 or $d_33 == 2) and ($d_33 == $leap or $leap == 1)) ? 78 : (($d_33 == 3 and $leap == 0) ? 80 : 79);
        $b = ($d_33 == 3 or $d_33 < ($leap - 1) or $leap == 0) ? 286 : 287;
        if ((int)(($gy - 10) / 63) == 30) {
            $b--;
            $a++;
        }
        if ($dayOfYear > $a) {
            $jy = $gy - 621;
            $jd = $dayOfYear - $a;
        } else {
            $jy = $gy - 622;
            $jd = $dayOfYear + $b;
        }
        for ($i = 0; $i < 11 and $jd > $this->j_days_in_month[$i]; $i++) {
            $jd -= $this->j_days_in_month[$i];
        }
        $jm = ++$i;

        return array($jy, strlen($jm) == 1 ? '0' . $jm : $jm, strlen($jd) == 1 ? '0' . $jd : $jd);
    }

    /**
     * Get day of the week shamsi/jalali
     * @param int $wday
     *
     * @return       int
     * @author       Parsa Kafi
     *
     */
    private function week_day($wday)
    {
        return $wday == 6 ? 0 : ++$wday;
    }

    /**
     * bn_parsidate::trim_number()
     * convert english number to persian number
     *
     * @param mixed $num
     * @param string $sp
     *
     * @return string
     */
    public function trim_number($num, $sp = '٫')
    {
        $eng = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.');
        $per = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', $sp);
        $number = filter_var($num, FILTER_SANITIZE_NUMBER_INT);

        return empty($number) ? str_replace($per, $eng, $num) : str_replace($eng, $per, $num);
    }

    /**
     * bn_parsidate::getInstance()
     * create instance of bn_parsidate class
     *
     * @return bn_parsidate
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * bn_parsidate::gregorian_date()
     * convert persian datetime to gregorian datetime
     *
     * @param mixed $format
     * @param mixed $persiandate
     *
     * @return mixed
     */
    public function gregorian_date($format, $persiandate)
    {
        preg_match_all('!\d+!', $persiandate, $matches);
        $matches = $matches[0];
        list($year, $mon, $day) = self::persian_to_gregorian($matches[0], $matches[1], $matches[2]);

        return date($format, mktime((isset($matches[3]) ? $matches[3] : 0), (isset($matches[4]) ? $matches[4] : 0), (isset($matches[5]) ? $matches[5] : 0), $mon, $day, $year));
    }

    /**
     * bn_parsidate::persian_to_gregorian()
     * convert persian date to gregorian date
     *
     * @param mixed $jy
     * @param mixed $jm
     * @param mixed $jd
     *
     * @return array
     */
    public function persian_to_gregorian($jy, $jm, $jd)
    {
        $doyj = ($jm - 2 > -1 ? $this->j_days_sum_month[(int)$jm] + $jd : $jd);
        $d4 = ($jy + 1) % 4;
        $d33 = (int)((($jy - 55) % 132) * .0305);
        $a = ($d33 != 3 and $d4 <= $d33) ? 287 : 286;
        $b = (($d33 == 1 or $d33 == 2) and ($d33 == $d4 or $d4 == 1)) ? 78 : (($d33 == 3 and $d4 == 0) ? 80 : 79);
        if ((int)(($jy - 19) / 63) == 20) {
            $a--;
            $b++;
        }
        if ($doyj <= $a) {
            $gy = $jy + 621;
            $gd = $doyj + $b;
        } else {
            $gy = $jy + 622;
            $gd = $doyj - $a;
        }
        foreach (array(0, 31, ($gy % 4 == 0) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31) as $gm => $days) {
            if ($gd <= $days) {
                break;
            }
            $gd -= $days;
        }
        return array($gy, $gm, $gd);
    }
}
function parsidate($input, $datetime = 'now', $lang = 'per')
{
    $bndate = bn_parsidate::getInstance();
    $bndate = $bndate->persian_date($input, $datetime, $lang);

    return $bndate;
}
