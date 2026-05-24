<?php
function getTextDirection($text) {
    // بررسی اگر متن فارسی است
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
        return 'rtl'; // راست چین برای متن فارسی
    } else {
        return 'ltr'; // چپ چین برای متن انگلیسی
    }
}