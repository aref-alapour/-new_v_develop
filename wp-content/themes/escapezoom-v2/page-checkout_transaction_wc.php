<style>
    body {
        direction: rtl;
        margin: 60px auto;
        max-width: 1200px;
    }

    table {
        margin: 20px 0;
        font-family: Arial, sans-serif;
    }

    th {
        background-color: #f0f0f0;
        padding: 10px;
        text-align: center;
    }

    td {
        padding: 8px;
        text-align: center;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    h2,
    h3 {
        color: #333;
        margin: 20px 0 10px 0;
        text-align: center;
    }
</style>
<?php

if (!(current_user_can('administrator') || current_user_can('accounting'))) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}

$medoo = medoo();
global $wpdb; // فقط برای prepare استفاده می‌شود، اجرای اصلی با medoo انجام می‌شود


// پردازش اصلاحیه شماره 2 برای تراکنش‌های بی‌تأثیر
if (isset($_GET['apply_second_correction']) && $_GET['apply_second_correction'] == 'yes') {
    echo "<h2>ایجاد اصلاحیه شماره 2 برای تراکنش‌های بی‌تأثیر</h2>";

    // ابتدا تراکنش‌های بی‌تأثیر که اصلاحیه داشته‌اند را دوباره پیدا کنیم (فقط تا سقف مشخص در هر درخواست)
    $duplicate_query_for_second_correction = "
        SELECT wt1.*
        FROM wallet_transactions wt1
        INNER JOIN (
            SELECT 
                SUBSTRING_INDEX(description, 'سفارش: ', -1) as order_number,
                MIN(ID) as first_id
            FROM wallet_transactions 
            WHERE description LIKE 'فروش تیکت بازی% - سفارش: %'
            AND SUBSTRING_INDEX(description, 'سفارش: ', -1) != ''
            AND SUBSTRING_INDEX(description, 'سفارش: ', -1) IS NOT NULL
            AND TRIM(SUBSTRING_INDEX(description, 'سفارش: ', -1)) != ''
            AND created_at >= 1758084349
            GROUP BY SUBSTRING_INDEX(description, 'سفارش: ', -1)
            HAVING COUNT(*) > 1
        ) duplicates ON SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1) = duplicates.order_number
        WHERE wt1.ID != duplicates.first_id
        AND wt1.description LIKE 'فروش تیکت بازی% - سفارش: %'
        AND SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1) != ''
        AND SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1) IS NOT NULL
        AND TRIM(SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1)) != ''
        AND wt1.created_at >= 1758084349
        ORDER BY SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1), wt1.ID
    ";

    $duplicate_transactions_for_second_correction_stmt = $medoo->query($duplicate_query_for_second_correction);
    $duplicate_transactions_for_second_correction = $duplicate_transactions_for_second_correction_stmt ? $duplicate_transactions_for_second_correction_stmt->fetchAll(PDO::FETCH_OBJ) : [];
    $ineffective_for_second_correction = [];

    if (!empty($duplicate_transactions_for_second_correction)) {
        foreach ($duplicate_transactions_for_second_correction as $transaction) {
            // بررسی اینکه آیا این تراکنش بی‌تأثیر است
            $previous_transaction_sql = $wpdb->prepare(
                "SELECT balance FROM wallet_transactions 
                 WHERE user_id = %d AND ID < %d 
                 ORDER BY ID DESC LIMIT 1",
                $transaction->user_id,
                $transaction->ID
            );
            $previous_transaction_stmt = $medoo->query($previous_transaction_sql);
            $previous_transaction = $previous_transaction_stmt ? $previous_transaction_stmt->fetchColumn() : null;

            $previous_balance = $previous_transaction ? floatval($previous_transaction) : 0;
            $current_balance = floatval($transaction->balance);
            $transaction_amount = floatval($transaction->amount);
            $expected_balance = $previous_balance + $transaction_amount;

            // اگر balance واقعی با مورد انتظار برابر نباشد، یعنی تراکنش بی‌تأثیر بوده
            if (abs($current_balance - $expected_balance) >= 0.01) {
                $ineffective_for_second_correction[] = $transaction;
            }
        }
    }


    $second_correction_count = 0;
    $second_correction_errors = [];

    foreach ($ineffective_for_second_correction as $transaction) {
        try {

            // محاسبه آخرین balance برای user_id
            $last_balance_sql = $wpdb->prepare(
                "SELECT balance FROM wallet_transactions 
                 WHERE user_id = %d 
                 ORDER BY ID DESC 
                 LIMIT 1",
                $transaction->user_id
            );
            $last_balance_stmt = $medoo->query($last_balance_sql);
            $last_balance = $last_balance_stmt ? $last_balance_stmt->fetchColumn() : null;
            $last_balance = $last_balance ? floatval($last_balance) : 0;

            // محاسبه تفاوت balance که باید اصلاح شود
            $previous_transaction_sql = $wpdb->prepare(
                "SELECT balance FROM wallet_transactions 
                 WHERE user_id = %d AND ID < %d 
                 ORDER BY ID DESC LIMIT 1",
                $transaction->user_id,
                $transaction->ID
            );
            $previous_transaction_stmt = $medoo->query($previous_transaction_sql);
            $previous_transaction = $previous_transaction_stmt ? $previous_transaction_stmt->fetchColumn() : null;
            $previous_balance = $previous_transaction ? floatval($previous_transaction) : 0;
            $transaction_amount = floatval($transaction->amount);
            $expected_balance = $previous_balance + $transaction_amount;
            $current_balance = floatval($transaction->balance);
            $balance_difference = $current_balance - $expected_balance;

            // تعیین مبلغ اصلاحیه شماره 2 (عکس تفاوت balance)
            $second_correction_amount = -$balance_difference;

            // محاسبه balance جدید
            $new_balance = $last_balance + $second_correction_amount;

            // درج ردیف اصلاحیه
            $correction_description = 'اصلاحیه ' . $transaction->description;
            $second_correction_data = [
                'user_id' => $transaction->user_id,
                'type' => 'transaction',
                'amount' => $second_correction_amount,
                'balance' => $new_balance,
                'description' => $correction_description,
                'created_at' => time(),
                'status' => 'completed'
            ];

            $result = $medoo->insert('wallet_transactions', $second_correction_data);

            if ($result !== false) {
                $second_correction_count++;
                $amount_display = $second_correction_amount > 0 ? '+' . number_format($second_correction_amount) : number_format($second_correction_amount);
                echo "<p style='color: green;'>✓ اصلاحیه برای تراکنش ID {$transaction->ID} (کاربر {$transaction->user_id}) ایجاد شد - مبلغ اصلاحیه: {$amount_display} تومان</p>";
            } else {
                $second_correction_errors[] = "خطا در درج اصلاحیه برای تراکنش ID {$transaction->ID}";
            }
        } catch (Exception $e) {
            $second_correction_errors[] = "خطا در پردازش تراکنش ID {$transaction->ID}: " . $e->getMessage();
        }
    }

    echo "<h4 style='color: blue;'>خلاصه عملیات اصلاحیه:</h4>";
    echo "<p>تعداد اصلاحیه‌های ایجاد شده: <strong>{$second_correction_count}</strong></p>";

    if (!empty($second_correction_errors)) {
        echo "<h4 style='color: red;'>خطاهای اصلاحیه شماره 2:</h4>";
        foreach ($second_correction_errors as $error) {
            echo "<p style='color: red;'>• {$error}</p>";
        }
    }

    // دکمه بازگشت
    echo "<div style='margin: 20px 0; text-align: center;'>";
    echo "<a href='" . strtok($_SERVER["REQUEST_URI"], '?') . "' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>بازگشت به صفحه اصلی</a>";
    echo "</div>";

    return; // خروج از اسکریپت تا بقیه کد اجرا نشود
}

// پردازش حذف تراکنش‌های بی‌تأثیر
if (isset($_GET['delete_ineffective']) && $_GET['delete_ineffective'] == 'yes') {
    echo "<h2>حذف تراکنش‌های بی‌تأثیر</h2>";

    // ابتدا تراکنش‌های بی‌تأثیر را دوباره پیدا کنیم (فقط تا سقف مشخص در هر درخواست)
    $duplicate_query_for_delete = "
        SELECT wt1.*
        FROM wallet_transactions wt1
        INNER JOIN (
            SELECT 
                SUBSTRING_INDEX(description, 'سفارش: ', -1) as order_number,
                MIN(ID) as first_id
            FROM wallet_transactions 
            WHERE description LIKE 'فروش تیکت بازی% - سفارش: %'
            AND SUBSTRING_INDEX(description, 'سفارش: ', -1) != ''
            AND SUBSTRING_INDEX(description, 'سفارش: ', -1) IS NOT NULL
            AND TRIM(SUBSTRING_INDEX(description, 'سفارش: ', -1)) != ''
            AND created_at >= 1758084349
            GROUP BY SUBSTRING_INDEX(description, 'سفارش: ', -1)
            HAVING COUNT(*) > 1
        ) duplicates ON SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1) = duplicates.order_number
        WHERE wt1.ID != duplicates.first_id
        AND wt1.description LIKE 'فروش تیکت بازی% - سفارش: %'
        AND SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1) != ''
        AND SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1) IS NOT NULL
        AND TRIM(SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1)) != ''
        AND wt1.created_at >= 1758084349
        ORDER BY SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1), wt1.ID
    ";

    $duplicate_transactions_for_delete_stmt = $medoo->query($duplicate_query_for_delete);
    $duplicate_transactions_for_delete = $duplicate_transactions_for_delete_stmt ? $duplicate_transactions_for_delete_stmt->fetchAll(PDO::FETCH_OBJ) : [];
    $ineffective_for_delete = [];

    if (!empty($duplicate_transactions_for_delete)) {
        foreach ($duplicate_transactions_for_delete as $transaction) {
            // بررسی اینکه آیا این تراکنش بی‌تأثیر است
            $previous_transaction_sql = $wpdb->prepare(
                "SELECT balance FROM wallet_transactions 
                 WHERE user_id = %d AND ID < %d 
                 ORDER BY ID DESC LIMIT 1",
                $transaction->user_id,
                $transaction->ID
            );
            $previous_transaction_stmt = $medoo->query($previous_transaction_sql);
            $previous_transaction = $previous_transaction_stmt ? $previous_transaction_stmt->fetchColumn() : null;

            $previous_balance = $previous_transaction ? floatval($previous_transaction) : 0;
            $current_balance = floatval($transaction->balance);
            $transaction_amount = floatval($transaction->amount);
            $expected_balance = $previous_balance + $transaction_amount;

            // اگر balance واقعی با مورد انتظار برابر نباشد، یعنی تراکنش بی‌تأثیر بوده
            if (abs($current_balance - $expected_balance) >= 0.01) {
                $ineffective_for_delete[] = $transaction;
            }
        }
    }

    $deleted_count = 0;
    $delete_errors = [];

    foreach ($ineffective_for_delete as $transaction) {
        try {
            $result = $medoo->delete('wallet_transactions', ['ID' => $transaction->ID]);

            if ($result !== false) {
                $deleted_count++;
                echo "<p style='color: green;'>✓ تراکنش ID {$transaction->ID} (کاربر {$transaction->user_id}) حذف شد</p>";
            } else {
                $delete_errors[] = "خطا در حذف تراکنش ID {$transaction->ID}";
            }
        } catch (Exception $e) {
            $delete_errors[] = "خطا در حذف تراکنش ID {$transaction->ID}: " . $e->getMessage();
        }
    }

    echo "<h4 style='color: blue;'>خلاصه عملیات حذف:</h4>";
    echo "<p>تعداد تراکنش‌های حذف شده: <strong>{$deleted_count}</strong></p>";

    if (!empty($delete_errors)) {
        echo "<h4 style='color: red;'>خطاهای حذف:</h4>";
        foreach ($delete_errors as $error) {
            echo "<p style='color: red;'>• {$error}</p>";
        }
    }

    // دکمه بازگشت
    echo "<div style='margin: 20px 0; text-align: center;'>";
    echo "<a href='" . strtok($_SERVER["REQUEST_URI"], '?') . "' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>بازگشت به صفحه اصلی</a>";
    echo "</div>";

    return; // خروج از اسکریپت تا بقیه کد اجرا نشود
}

// پیدا کردن ردیف‌های تکراری در جدول wallet_transactions بر اساس شماره سفارش در description
echo "<h2>پیدا کردن ردیف‌های تکراری در wallet_transactions</h2>";

// کوئری برای پیدا کردن ردیف‌های تکراری بر اساس شماره سفارش (فقط تا سقف مشخص در هر درخواست)
$duplicate_query = "
    SELECT wt1.*
    FROM wallet_transactions wt1
    INNER JOIN (
        SELECT 
            SUBSTRING_INDEX(description, 'سفارش: ', -1) as order_number,
            MIN(ID) as first_id
        FROM wallet_transactions 
        WHERE description LIKE 'فروش تیکت بازی% - سفارش: %'
        AND SUBSTRING_INDEX(description, 'سفارش: ', -1) != ''
        AND SUBSTRING_INDEX(description, 'سفارش: ', -1) IS NOT NULL
        AND TRIM(SUBSTRING_INDEX(description, 'سفارش: ', -1)) != ''
        AND created_at >= 1758084349
        GROUP BY SUBSTRING_INDEX(description, 'سفارش: ', -1)
        HAVING COUNT(*) > 1
    ) duplicates ON SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1) = duplicates.order_number
    WHERE wt1.ID != duplicates.first_id
    AND wt1.description LIKE 'فروش تیکت بازی% - سفارش: %'
    AND SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1) != ''
    AND SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1) IS NOT NULL
    AND TRIM(SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1)) != ''
    AND wt1.created_at >= 1758084349
    ORDER BY SUBSTRING_INDEX(wt1.description, 'سفارش: ', -1), wt1.ID
";

$duplicate_transactions_stmt = $medoo->query($duplicate_query);
$duplicate_transactions = $duplicate_transactions_stmt ? $duplicate_transactions_stmt->fetchAll(PDO::FETCH_OBJ) : [];

// جدا کردن تراکنش‌های مؤثر و بی‌تأثیر
$effective_transactions = [];
$ineffective_transactions = [];

if (!empty($duplicate_transactions)) {
    foreach ($duplicate_transactions as $transaction) {
        // بررسی اینکه آیا این تراکنش باعث تغییر balance شده است
        // با مقایسه balance قبل و بعد از این تراکنش

        // پیدا کردن تراکنش قبلی همین کاربر
        $previous_transaction_sql = $wpdb->prepare(
            "SELECT balance FROM wallet_transactions 
             WHERE user_id = %d AND ID < %d 
             ORDER BY ID DESC LIMIT 1",
            $transaction->user_id,
            $transaction->ID
        );
        $previous_transaction_stmt = $medoo->query($previous_transaction_sql);
        $previous_transaction = $previous_transaction_stmt ? $previous_transaction_stmt->fetchColumn() : null;

        $previous_balance = $previous_transaction ? floatval($previous_transaction) : 0;
        $current_balance = floatval($transaction->balance);
        $transaction_amount = floatval($transaction->amount);

        // محاسبه balance مورد انتظار
        $expected_balance = $previous_balance + $transaction_amount;

        // اگر balance واقعی با مورد انتظار برابر باشد، یعنی تراکنش مؤثر بوده
        if (abs($current_balance - $expected_balance) < 0.01) { // با در نظر گیری خطای اعشار
            $effective_transactions[] = $transaction;
        } else {
            $ineffective_transactions[] = $transaction;
        }
    }
}

// فیلتر کردن تراکنش‌های مؤثر بر اساس تعداد تکرار و تعداد اصلاحیه (بهینه‌سازی شده)
if (!empty($effective_transactions)) {
    $filtered_transactions = [];
    $already_corrected = 0;

    // گروه‌بندی تراکنش‌ها بر اساس شماره سفارش
    $transactions_by_order = [];
    $order_descriptions = []; // برای نگهداری description هر سفارش

    foreach ($effective_transactions as $transaction) {
        preg_match('/سفارش:\s*(\d+)/', $transaction->description, $matches);
        $order_number = isset($matches[1]) ? $matches[1] : '';
        if ($order_number) {
            if (!isset($transactions_by_order[$order_number])) {
                $transactions_by_order[$order_number] = [];
                $order_descriptions[$order_number] = $transaction->description;
            }
            $transactions_by_order[$order_number][] = $transaction;
        }
    }

    // بهینه‌سازی: یک کوئری برای گرفتن همه اصلاحیه‌ها به صورت یکجا
    if (!empty($order_descriptions)) {
        // ساخت لیست description های اصلاحیه
        $correction_descriptions = [];
        foreach ($order_descriptions as $order_number => $description) {
            $correction_descriptions[] = 'اصلاحیه ' . $description;
        }

        // Escape کردن description ها برای استفاده در IN clause
        $escaped_descriptions = [];
        foreach ($correction_descriptions as $desc) {
            $escaped_descriptions[] = $medoo->quote($desc);
        }

        // یک کوئری برای شمارش همه اصلاحیه‌ها
        $correction_count_sql = "SELECT description, COUNT(*) as correction_count 
             FROM wallet_transactions 
             WHERE description IN (" . implode(',', $escaped_descriptions) . ")
             GROUP BY description";

        $correction_count_stmt = $medoo->query($correction_count_sql);
        $correction_counts = [];
        if ($correction_count_stmt) {
            while ($row = $correction_count_stmt->fetch(PDO::FETCH_OBJ)) {
                $correction_counts[$row->description] = intval($row->correction_count);
            }
        }
    }

    // Debug mode
    $debug_mode = isset($_GET['debug']) && $_GET['debug'] == 'yes';

    // برای هر سفارش، بررسی تعداد تکرار و تعداد اصلاحیه
    foreach ($transactions_by_order as $order_number => $transactions) {
        $duplicate_count = count($transactions); // تعداد تکرار

        // برای شمارش اصلاحیه‌ها، description اولین تراکنش را می‌گیریم
        $sample_description = $transactions[0]->description;
        $correction_description = 'اصلاحیه ' . $sample_description;

        // استفاده از نتیجه کوئری بهینه‌سازی شده
        $correction_count = isset($correction_counts[$correction_description]) ? $correction_counts[$correction_description] : 0;

        if ($debug_mode) {
            echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border: 1px solid #ffc107;'>";
            echo "<h4>🔍 Debug - سفارش: {$order_number}</h4>";
            echo "<p>تعداد تکرار: {$duplicate_count}</p>";
            echo "<p>تعداد اصلاحیه موجود: {$correction_count}</p>";
            echo "<p>Description اصلاحیه: {$correction_description}</p>";
        }

        // محاسبه تعداد اصلاحیه‌های لازم
        $needed_corrections = $duplicate_count - intval($correction_count);

        if ($debug_mode) {
            echo "<p><strong>تعداد اصلاحیه لازم: {$needed_corrections}</strong></p>";
            echo "</div>";
        }

        if ($needed_corrections > 0) {
            // اضافه کردن به تعداد لازم
            for ($i = 0; $i < $needed_corrections && $i < count($transactions); $i++) {
                $filtered_transactions[] = $transactions[$i];
            }
        } else {
            $already_corrected += $duplicate_count;
        }
    }

    $effective_transactions = $filtered_transactions;

    if ($already_corrected > 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<p style='color: #155724; margin: 0;'>ℹ️ تعداد {$already_corrected} تراکنش تکراری که قبلاً اصلاح شده‌اند از لیست حذف شدند.</p>";
        echo "</div>";
    }
}



// برای سازگاری با کد قدیمی، duplicate_transactions را به effective_transactions تغییر می‌دهیم
$duplicate_transactions = $effective_transactions;

// نمایش تراکنش‌های مؤثر (که باید اصلاح شوند)
if (!empty($duplicate_transactions)) {
    // اگر اصلاحیه در حال اجرا است، جداول را نمایش نده
    if (!isset($_GET['apply_correction']) || $_GET['apply_correction'] != 'yes') {
        echo "<h3 style='color: #d63384;'>تراکنش‌های تکراری مؤثر (نیاز به اصلاحیه)</h3>";
        echo "<p>تعداد تراکنش‌های تکراری مؤثر: " . count($duplicate_transactions) . "</p>";

        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #ffe6e6;'>
                <th>ID</th>
                <th>User ID</th>
                <th>مبلغ</th>
                <th>Balance قبلی</th>
                <th>Balance فعلی</th>
                <th>توضیحات</th>
                <th>شماره سفارش</th>
                <th>تاریخ ایجاد</th>
              </tr>";

        foreach ($duplicate_transactions as $transaction) {
            // استخراج شماره سفارش از انتهای description
            preg_match('/سفارش:\s*(\d+)/', $transaction->description, $matches);
            $order_number = isset($matches[1]) ? $matches[1] : 'نامشخص';

            // محاسبه balance قبلی
            $previous_transaction_sql = $wpdb->prepare(
                "SELECT balance FROM wallet_transactions 
                 WHERE user_id = %d AND ID < %d 
                 ORDER BY ID DESC LIMIT 1",
                $transaction->user_id,
                $transaction->ID
            );
            $previous_transaction_stmt = $medoo->query($previous_transaction_sql);
            $previous_transaction = $previous_transaction_stmt ? $previous_transaction_stmt->fetchColumn() : null;
            $previous_balance = $previous_transaction ? floatval($previous_transaction) : 0;

            echo "<tr>";
            echo "<td>{$transaction->ID}</td>";
            echo "<td>{$transaction->user_id}</td>";
            echo "<td>" . number_format($transaction->amount) . "</td>";
            echo "<td>" . number_format($previous_balance) . "</td>";
            echo "<td>" . number_format($transaction->balance) . "</td>";
            echo "<td>{$transaction->description}</td>";
            echo "<td>{$order_number}</td>";
            echo "<td>" . date('Y-m-d H:i:s', $transaction->created_at) . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // دکمه اصلاحیه بلافاصله زیر جدول تراکنش‌های مؤثر
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h4 style='color: #856404;'>⚠️ هشدار - اعمال اصلاحیه برای تراکنش‌های مؤثر</h4>";
        echo "<p>برای اعمال اصلاحیه‌ها برای <strong>" . count($duplicate_transactions) . " تراکنش مؤثر</strong> و درج ردیف‌های جدید در دیتابیس، روی لینک زیر کلیک کنید:</p>";
        echo "<p style='color: #856404;'><small>تراکنش‌های بی‌تأثیر اصلاح نخواهند شد و باید جداگانه حذف شوند.</small></p>";
        echo "<a href='?apply_correction=yes' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>اعمال اصلاحیه برای " . count($duplicate_transactions) . " تراکنش مؤثر</a>";
        echo "<p style='color: #856404; margin-top: 10px;'><small>توجه: این عملیات غیرقابل برگشت است!</small></p>";
        echo "</div>";
    }
}

// نمایش تراکنش‌های بی‌تأثیر (که باید حذف شوند)
if (!empty($ineffective_transactions)) {
    if (!isset($_GET['apply_correction']) || $_GET['apply_correction'] != 'yes') {
        echo "<h3 style='color: #fd7e14;'>تراکنش‌های تکراری بی‌تأثیر (قابل حذف)</h3>";
        echo "<p>تعداد تراکنش‌های تکراری بی‌تأثیر: " . count($ineffective_transactions) . "</p>";
        echo "<p style='color: #fd7e14;'><small>این تراکنش‌ها روی موجودی کیف پول تأثیری نگذاشته‌اند و می‌توانند حذف شوند.</small></p>";

        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #fff3cd;'>
                <th>ID</th>
                <th>User ID</th>
                <th>مبلغ</th>
                <th>Balance قبلی</th>
                <th>Balance فعلی</th>
                <th>تفاوت</th>
                <th>توضیحات</th>
                <th>شماره سفارش</th>
                <th>تاریخ ایجاد</th>
              </tr>";

        foreach ($ineffective_transactions as $transaction) {
            // استخراج شماره سفارش از انتهای description
            preg_match('/سفارش:\s*(\d+)/', $transaction->description, $matches);
            $order_number = isset($matches[1]) ? $matches[1] : 'نامشخص';

            // محاسبه balance قبلی
            $previous_transaction_sql = $wpdb->prepare(
                "SELECT balance FROM wallet_transactions 
                 WHERE user_id = %d AND ID < %d 
                 ORDER BY ID DESC LIMIT 1",
                $transaction->user_id,
                $transaction->ID
            );
            $previous_transaction_stmt = $medoo->query($previous_transaction_sql);
            $previous_transaction = $previous_transaction_stmt ? $previous_transaction_stmt->fetchColumn() : null;
            $previous_balance = $previous_transaction ? floatval($previous_transaction) : 0;
            $current_balance = floatval($transaction->balance);
            $transaction_amount = floatval($transaction->amount);
            $expected_balance = $previous_balance + $transaction_amount;
            $difference = $current_balance - $expected_balance;

            echo "<tr>";
            echo "<td>{$transaction->ID}</td>";
            echo "<td>{$transaction->user_id}</td>";
            echo "<td>" . number_format($transaction->amount) . "</td>";
            echo "<td>" . number_format($previous_balance) . "</td>";
            echo "<td>" . number_format($transaction->balance) . "</td>";
            echo "<td style='color: red;'>" . number_format($difference) . "</td>";
            echo "<td>{$transaction->description}</td>";
            echo "<td>{$order_number}</td>";
            echo "<td>" . date('Y-m-d H:i:s', $transaction->created_at) . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // دکمه حذف تراکنش‌های بی‌تأثیر
        echo "<div style='margin: 20px 0; text-align: center; background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
        echo "<h4 style='color: #856404;'>حذف تراکنش‌های بی‌تأثیر</h4>";
        echo "<p style='color: #856404;'>این تراکنش‌ها هیچ تأثیری روی موجودی کاربران نداشته‌اند و می‌توانند بدون نگرانی حذف شوند.</p>";
        echo "<a href='?delete_ineffective=yes' onclick='return confirm(\"آیا مطمئن هستید که می‌خواهید " . count($ineffective_transactions) . " تراکنش بی‌تأثیر را حذف کنید؟\")' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>حذف " . count($ineffective_transactions) . " تراکنش بی‌تأثیر</a>";
        echo "<p style='color: #856404; margin-top: 10px;'><small>توجه: این عملیات غیرقابل برگشت است!</small></p>";
        echo "</div>";
    }
}


// بازگرداندن جدول تراکنش‌های مؤثر برای ادامه کد
if (!empty($duplicate_transactions)) {
    if (!isset($_GET['apply_correction']) || $_GET['apply_correction'] != 'yes') {
        if (!isset($_GET['apply_second_correction']) || $_GET['apply_second_correction'] != 'yes') {
            // نمایش آمار گروه‌بندی شده برای تراکنش‌های مؤثر
            echo "<h3>آمار گروه‌بندی شده بر اساس شماره سفارش (تراکنش‌های مؤثر):</h3>";
            $grouped_stats_stmt = $medoo->query("
            SELECT 
                SUBSTRING_INDEX(description, 'سفارش: ', -1) as order_number,
                COUNT(*) as count,
                GROUP_CONCAT(ID ORDER BY ID) as transaction_ids
            FROM wallet_transactions 
            WHERE description LIKE 'فروش تیکت بازی% - سفارش: %'
            AND SUBSTRING_INDEX(description, 'سفارش: ', -1) != ''
            AND SUBSTRING_INDEX(description, 'سفارش: ', -1) IS NOT NULL
            AND TRIM(SUBSTRING_INDEX(description, 'سفارش: ', -1)) != ''
            AND created_at >= 1758084349
            GROUP BY SUBSTRING_INDEX(description, 'سفارش: ', -1)
            HAVING COUNT(*) > 1
            ORDER BY count DESC, order_number
        ");
            $grouped_stats = $grouped_stats_stmt ? $grouped_stats_stmt->fetchAll(PDO::FETCH_OBJ) : [];

            if (!empty($grouped_stats)) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr>
                    <th>شماره سفارش</th>
                    <th>تعداد تکرار</th>
                    <th>ID های تراکنش</th>
                  </tr>";

                foreach ($grouped_stats as $stat) {
                    echo "<tr>";
                    echo "<td>{$stat->order_number}</td>";
                    echo "<td>{$stat->count}</td>";
                    echo "<td>{$stat->transaction_ids}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    }

    // درج ردیف‌های اصلاحیه فقط برای تراکنش‌های مؤثر
    if (isset($_GET['apply_correction']) && $_GET['apply_correction'] == 'yes') {
        echo "<h3 style='color: red;'>در حال اعمال اصلاحیه‌ها برای تراکنش‌های مؤثر...</h3>";
        echo "<p style='color: #666;'>تعداد تراکنش‌های مؤثر برای اصلاح: " . count($duplicate_transactions) . "</p>";

        $correction_count = 0;
        $errors = [];

        foreach ($duplicate_transactions as $transaction) {
            try {
                // محاسبه آخرین balance برای user_id
                $last_balance_sql = $wpdb->prepare(
                    "SELECT balance FROM wallet_transactions 
                     WHERE user_id = %d 
                     ORDER BY ID DESC 
                     LIMIT 1",
                    $transaction->user_id
                );
                $last_balance_stmt = $medoo->query($last_balance_sql);
                $last_balance = $last_balance_stmt ? $last_balance_stmt->fetchColumn() : null;
                $last_balance = $last_balance ? floatval($last_balance) : 0;

                // تعیین مبلغ اصلاحیه (عکس علامت مبلغ اصلی)
                $original_amount = floatval($transaction->amount);
                $correction_amount = -$original_amount;

                // محاسبه balance جدید
                $new_balance = $last_balance + $correction_amount;

                // درج ردیف اصلاحیه (فرمت ساده بدون ID)
                $correction_description = 'اصلاحیه ' . $transaction->description;
                $correction_data = [
                    'user_id' => $transaction->user_id,
                    'type' => 'transaction',
                    'amount' => $correction_amount,
                    'balance' => $new_balance,
                    'description' => $correction_description,
                    'created_at' => time(),
                    'status' => 'completed'
                ];

                $result = $medoo->insert('wallet_transactions', $correction_data);

                if ($result !== false) {
                    $correction_count++;
                    $amount_display = $correction_amount > 0 ? '+' . number_format($correction_amount) : number_format($correction_amount);
                    echo "<p style='color: green;'>✓ اصلاحیه برای تراکنش ID {$transaction->ID} (کاربر {$transaction->user_id}) ایجاد شد - مبلغ اصلاحیه: {$amount_display} تومان</p>";
                } else {
                    $errors[] = "خطا در درج اصلاحیه برای تراکنش ID {$transaction->ID}";
                }
            } catch (Exception $e) {
                $errors[] = "خطا در پردازش تراکنش ID {$transaction->ID}: " . $e->getMessage();
            }
        }

        echo "<h4 style='color: blue;'>خلاصه عملیات اصلاحیه:</h4>";
        echo "<p>تعداد اصلاحیه‌های ایجاد شده برای تراکنش‌های مؤثر: <strong>{$correction_count}</strong></p>";
        if (!empty($ineffective_transactions)) {
            echo "<p style='color: #fd7e14;'>تعداد تراکنش‌های بی‌تأثیر که اصلاح نشدند: <strong>" . count($ineffective_transactions) . "</strong></p>";
            echo "<p style='color: #fd7e14;'><small>برای حذف تراکنش‌های بی‌تأثیر، به صفحه اصلی بازگردید و از دکمه حذف استفاده کنید.</small></p>";
        }

        if (!empty($errors)) {
            echo "<h4 style='color: red;'>خطاها:</h4>";
            foreach ($errors as $error) {
                echo "<p style='color: red;'>• {$error}</p>";
            }
        }

        // دکمه بازگشت
        echo "<div style='margin: 20px 0; text-align: center;'>";
        echo "<a href='" . strtok($_SERVER["REQUEST_URI"], '?') . "' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>نمایش تراکنش‌های تکراری</a>";
        echo "</div>";
    }
} else {
    echo "<p>هیچ ردیف تکراری پیدا نشد.</p>";
}

echo 'created by engineer Aref Alapour';











