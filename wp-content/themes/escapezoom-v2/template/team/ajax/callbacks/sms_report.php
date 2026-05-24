<?php

// دریافت مقادیر فرم
$location      = isset($_POST['location']) ? intval($_POST['location']) : -1; // تغییر پیش‌فرض به -1 برای همه پیام‌ها
$mobile_filter = isset($_POST['from']) ? sanitize_text_field($_POST['from']) : '';
$raw_date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
$raw_date_to   = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';

// دریافت ایندکس شروع برای صفحه‌بندی
$api_index     = isset($_POST['api_index']) ? intval($_POST['api_index']) : 0;

// تبدیل تاریخ‌های شمسی ورودی به میلادی برای مقایسه با دیتای API
$date_from_gregorian = '';
$date_to_gregorian   = '';

if (!empty($raw_date_from)) {
    $parts = explode('/', $raw_date_from);
    if (count($parts) == 3) {
        $temp_date = jalali_to_gregorian((int)$parts[0], (int)$parts[1], (int)$parts[2], '-');
        // استانداردسازی به فرمت دقیق YYYY-MM-DD
        $date_from_gregorian = date('Y-m-d', strtotime($temp_date));
    }
}

if (!empty($raw_date_to)) {
    $parts = explode('/', $raw_date_to);
    if (count($parts) == 3) {
        $temp_date = jalali_to_gregorian((int)$parts[0], (int)$parts[1], (int)$parts[2], '-');
        // استانداردسازی به فرمت دقیق YYYY-MM-DD
        $date_to_gregorian = date('Y-m-d', strtotime($temp_date));
    }
}

$count = 100; // تعداد دریافت در هر درخواست cURL
$username = "xescape";
$password = "2kkh7Gm36%#X91h";
$api_url = 'https://rest.payamak-panel.com/api/SendSMS/GetMessages';

// بررسی اینکه آیا فیلتری اعمال شده یا نه
$has_filter = (!empty($mobile_filter) || !empty($date_from_gregorian) || !empty($date_to_gregorian));

$matched_data = [];
$total_checked = 0;
// اگر فیلتر داشته باشیم، اجازه می‌دهیم تا 10,000 رکورد در بک‌گراند چک شود (100 درخواست)
$max_check_limit = $has_filter ? 10000 : 100; 
$current_index = $api_index;

$keep_fetching = true;
$has_more = false;

while ($keep_fetching && $total_checked < $max_check_limit && count($matched_data) < $count) {
    $curl = curl_init();
    $post_fields = [
        "username" => $username,
        "password" => $password,
        "location" => $location,
        "index"    => $current_index,
        "count"    => $count
    ];

    curl_setopt_array($curl, array(
      CURLOPT_URL => $api_url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 20, // تایم اوت برای درخواست‌های متوالی
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($post_fields),
      CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        wp_send_json_error('خطای ارتباط با سرور پیامک: ' . $err);
        break;
    }

    $result = json_decode($response, true);
    $data_list = [];

    // استخراج دیتا با ساختارهای مختلف
    if (isset($result['MyBase']) && isset($result['Data'])) {
        $data_list = $result['Data'];
    } elseif (isset($result[0]) && is_array($result[0])) {
        $data_list = $result;
    } elseif (isset($result['Data']) && is_array($result['Data'])) {
        $data_list = $result['Data'];
    }

    if (empty($data_list)) {
        // دیگر دیتایی در سرور فراپیامک وجود ندارد
        $has_more = false;
        break; 
    }

    foreach ($data_list as $msg) {
        $total_checked++;
        
        $mobile = $msg['Receiver'] ?? $msg['Sender'] ?? $msg['MobileNumber'] ?? '';
        $date   = $msg['SendDate'] ?? $msg['ReceiveDate'] ?? '';
        
        $is_match = true;
        
        // 1. بررسی فیلتر موبایل
        if (!empty($mobile_filter) && strpos($mobile, $mobile_filter) === false) {
            $is_match = false;
        }
        
        // 2. بررسی فیلتر تاریخ (تبدیل به Timestamp برای مقایسه دقیق ریاضیاتی)
        if ($is_match) {
            if (!empty($date)) {
                $api_timestamp = strtotime(date('Y-m-d', strtotime($date))); // حذف ساعت و دقیقه، فقط روز
                
                if (!empty($date_from_gregorian)) {
                    $from_timestamp = strtotime($date_from_gregorian);
                    if ($api_timestamp < $from_timestamp) {
                        $is_match = false;
                    }
                }
                
                if ($is_match && !empty($date_to_gregorian)) {
                    $to_timestamp = strtotime($date_to_gregorian);
                    if ($api_timestamp > $to_timestamp) {
                        $is_match = false;
                    }
                }
            }
        }

        if ($is_match) {
            // تعیین نوع پیامک
            $msg_type = 'نامشخص';
            if ($location == 1) {
                $msg_type = 'دریافتی';
            } elseif ($location == 2) {
                $msg_type = 'ارسالی';
            } else {
                // تلاش برای حدس زدن در حالت -1
                if (isset($msg['IsReceive'])) {
                    $msg_type = $msg['IsReceive'] ? 'دریافتی' : 'ارسالی';
                } else {
                    $msg_type = 'ارسالی/دریافتی';
                }
            }

            $matched_data[] = [
                'id'     => $msg['MsgID'] ?? $msg['ID'] ?? '',
                'mobile' => $mobile,
                'body'   => $msg['Body'] ?? '',
                'date'   => $date, // فرانت‌اند این تاریخ میلادی را دریافت کرده و به شمسی نمایش می‌دهد
                'type'   => $msg_type,
            ];
        }
    }

    // پیش‌روی در API برای حلقه یا صفحه بعدی
    $current_index += $count;
    
    // اگر دقیقا 100 رکورد برگشته بود، یعنی باز هم دیتا در سرور هست
    $has_more = (count($data_list) == $count); 

    // اگر فیلتری نداریم، فقط همان 100 تای اول را می‌خواهیم و نیازی به حلقه نیست
    if (!$has_filter) {
        break; 
    }
}

wp_send_json_success([
    'data'       => $matched_data,
    'next_index' => $current_index, // ارسال ایندکس برای استفاده در دکمه "صفحه بعدی"
    'has_more'   => $has_more,
    'checked'    => $total_checked
]);
?>
