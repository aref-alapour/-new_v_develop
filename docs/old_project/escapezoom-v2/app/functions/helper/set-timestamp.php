<?php
// تنظیم منطقه زمانی (در صورت نیاز)
date_default_timezone_set('Asia/Tehran');

// تابع برای به دست آوردن timestamp شروع و پایان روز
function getStartAndEndTimestamps($date) {
    $start = strtotime($date . ' 00:00:00'); // شروع روز
    $end = strtotime($date . ' 23:59:59');   // پایان روز
    return [$start, $end];
}