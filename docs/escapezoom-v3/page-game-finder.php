<?php get_header(); ?>
<?php
// امروز
$today = date('Y-m-d');
list($todayStart, $todayEnd) = getStartAndEndTimestamps($today);

// فردا
$tomorrow = date('Y-m-d', strtotime('+1 day'));
list($tomorrowStart, $tomorrowEnd) = getStartAndEndTimestamps($tomorrow);

// پس فردا
$dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));
list($dayAfterTomorrowStart, $dayAfterTomorrowEnd) = getStartAndEndTimestamps($dayAfterTomorrow);
?>
<?php if (isset($_GET['product_type']) || 1): ?>
    <?php
    // Set default values if not provided
    $product_type = isset($_GET['product_type']) ? $_GET['product_type'] : 'اتاق فرار';
    if ($_GET['city_id']) {
        $city_id[] = json_decode($_GET['city_id']);
        $city_id = json_encode($city_id);
    } else {
        // Default to Tehran (city_id = 15) for escape rooms
        $city_id = json_encode([15]);
    }
    $schedule = json_decode($_GET['schedule']);
    $count = json_decode($_GET['count']);
    ?>
    <script>
        jQuery(document).ready(function($) {
            let baseUrlWebService = 'https://' + location.hostname + '/web-service/web-service.php'
            if (location.hostname === 'localhost') {
                baseUrlWebService = 'http://' + location.hostname + '/escapezoom_wp/web-service/web-service.php'
            } else if (location.hostname === 'wo.escapezoom.local') {
                baseUrlWebService = 'http://' + location.hostname + '/web-service/web-service.php'
            }
            $('#product-container').empty()
            $.ajax({
                type: 'POST',
                url: baseUrlWebService,
                data: {
                    "type": "sort_products_get",
                    "data": {
                        "source": "cat_sansyab",
                        "params": {
                            "product_type": "<?= $product_type ?: 'اتاق فرار'  ?>",
                            "city_id": <?= $city_id ?: json_encode([15])  ?>,
                            "count": <?= ($count) ?: -1 ?>,
                            "schedule": <?= $schedule ?: -1  ?>
                        }
                    }
                },
                dataType: "json",
                beforeSend: function() {
                    $('#product-container').empty().append('<div class="text-center">لطفا منتظر بمانید<span class="loading-dots"></span></div>')
                },
                success: function(data) {
                    if ((data.products).length > 0) {
                        $('#product-container').empty().append('<section id="product_list_container" class="grid grid-cols-2 justify-between max-lg:gap-5.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 2xl:grid-cols-6 child:box-content gap-6"></section>')
                        $('#product-container #product_list_container').append(data.products)

                    } else {
                        $('#product-container').empty().append('<div class="rounded-xl px-16 py-12 border border-slate-100 text-center shadow-12">موردی یافت نشد.</div>')
                    }
                },
            });
        })
    </script>
<?php else: ?>
    <script>
        jQuery(document).ready(function($) {
            $('#product-container').empty().append('<div class="rounded-xl px-16 py-12 border border-slate-100 text-center shadow-12">موردی یافت نشد.</div>')
        })
    </script>
<?php endif; ?>
<section class="flex justify-between items-center max-lg:mt-10 lg:mt-8">
    <div class="flex items-center gap-x-4 text-slate-350 text-sm">
        <a href="<?= home_url() ?>" class="text-sm border-l border-slate-100 pl-4 font-bold">خانه</a>
        <span>سانس یاب</span>
    </div>
    <a href="javascript:void(0)" onclick="goBack()" class="text-sm font-bold flex items-center gap-x-2 text-slate-350 max-lg:bg-surface-sunken max-lg:rounded-lg max-lg:w-9 max-lg:h-9 max-lg:justify-center max-lg:text-center">
        <span class="mt-1 max-lg:hidden">بازگشت</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="13" viewBox="0 0 9 13" fill="none">
            <path d="M0.796549 7.67436L5.21581 12.393C5.54578 12.6862 5.98242 12.846 6.43367 12.8386C6.88491 12.8311 7.3155 12.6571 7.63463 12.3532C7.95375 12.0493 8.13648 11.6392 8.14427 11.2095C8.15207 10.7797 7.98433 10.3639 7.67642 10.0497L4.48746 6.5027L7.67642 3.36054C7.98432 3.0463 8.15206 2.63047 8.14427 2.20073C8.13647 1.77099 7.95375 1.36093 7.63463 1.057C7.3155 0.753095 6.88491 0.579082 6.43367 0.571659C5.98242 0.564235 5.54578 0.72398 5.21581 1.01721L0.796549 5.33103C0.470517 5.64191 0.287388 6.06332 0.287388 6.5027C0.287388 6.94207 0.470517 7.36348 0.796549 7.67436Z" fill="#0F172B" />
        </svg>
    </a>
</section>
<section class="flex justify-between gap-x-10 items-center mt-8">
    <h1 class="text-base font-extrabold lg:text-3xl mb-8">سانس یاب</h1>
    <div class="flex items-center gap-x-4 max-lg:hidden">
        <div class="text-sm border-l border-slate-100 pl-4 font-bold">سانس‌ برای</div>
        <div class="flex text-slate-350 gap-4">
            <label class="flex-shrink-0 whitespace-nowrap rounded-md cursor-pointer" for="time-lg-1">
                <input type="radio" name="schedule" value="[<?= $todayStart ?>,<?= $todayEnd ?>]" data-day="today"
                    class="hidden peer schedule-btn"
                    id="time-lg-1" checked />
                <span class="text-nowrap px-5 py-1.5 text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 rounded-md peer-checked:bg-primary-500 peer-checked:text-white">امروز</span>
            </label>
            <label class="flex-shrink-0 whitespace-nowrap rounded-md cursor-pointer" for="time-lg-2">
                <input type="radio" name="schedule" value="[<?= $tomorrowStart ?>,<?= $tomorrowEnd ?>]" data-day="tomorrow"
                    class="hidden peer schedule-btn"
                    id="time-lg-2" />
                <span class="text-nowrap px-5 py-1.5  text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2   rounded-md peer-checked:bg-primary-500 peer-checked:text-white">فردا</span>
            </label>
            <label class="flex-shrink-0 whitespace-nowrap rounded-md cursor-pointer" for="time-lg-3">
                <input type="radio" name="schedule"
                    value="[<?= $dayAfterTomorrowStart ?>,<?= $dayAfterTomorrowEnd ?>]" data-day="after-tomorrow" class="hidden peer schedule-btn"
                    id="time-lg-3" />
                <span class="text-nowrap px-5 py-1.5 text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2  rounded-md peer-checked:bg-primary-500 peer-checked:text-white">پس فردا</span>
            </label>
        </div>
    </div>
</section>

<form id="game-finder-form">
    <input type="hidden" name="schedule_start" data-limit="<?= ($schedule[0]) ?: $todayStart ?>" value="<?= ($schedule[0]) ?: $todayStart ?>">
    <input type="hidden" name="schedule_end" data-limit="<?= ($schedule[1]) ?: $todayEnd ?>" value="<?= ($schedule[1]) ?: $todayEnd ?>">
    <input type="hidden" name="sort_type_final" value="topsale">
    <section
        class="lg:flex lg:items-center lg:justify-between rounded-3xl border border-slate-110 px-4 lg:px-8 gap-x-10 max-lg:bg-stone-50">
        <div class="grid grid-cols-3 gap-x-1 lg:gap-x-6 lg:gap-y-5 grow py-8">
            <div class="w-full">
                <div class="mb-2 text-steel font-bold max-lg:hidden">شهر</div>
                <div class="relative w-full max-w-xs">
                    <div class="sans-dropdown-container relative">
                        <button type="button"
                            class="sans-dropdown-button w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between">
                            <span id="cities-box-title" class="text-ink-tab font-extrabold relative">
                                <?php
                                // Show selected city name or default to Tehran
                                $selected_city_id = isset($_GET['city_id']) ? json_decode($_GET['city_id']) : 15;
                                $cities = cities_type($product_type);
                                $selected_city_name = 'شهر';
                                foreach ($cities as $city) {
                                    if ($city['city_id'] == $selected_city_id) {
                                        $selected_city_name = $city['city_name'];
                                        break;
                                    }
                                }
                                echo $selected_city_name;
                                ?>
                            </span>
                            <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="sans-options absolute hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                            <div class="p-2">
                                <input type="text"
                                    class="city-search w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                    placeholder="جستجوی شهر...">
                            </div>
                            <div class="max-h-60 overflow-auto">
                                <div id="cities-box-list" class="city-options py-1">
                                    <?php
                                    $cities = cities_type($product_type);
                                    foreach ($cities as $city) {
                                        $checked = null;
                                        // Check for selected city from URL or default to Tehran (15)
                                        $selected_city_id = isset($_GET['city_id']) ? json_decode($_GET['city_id']) : 15;
                                        if ($city['city_id'] == $selected_city_id) {
                                            $checked = 'checked';
                                        }
                                        echo '<label class="option-sans block w-full hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4   py-2">
                                                    <input type="radio" name="city_id" value="' . $city['city_id'] . '" class="hidden option-sans-input" ' . $checked . '/>
                                                    ' . $city['city_name'] . '
                                                    </label>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full">
                <div class="mb-2 text-steel font-bold max-lg:hidden">نوع بازی</div>
                <div class="relative w-full max-w-xs">
                    <div class="sans-dropdown-container relative">
                        <button type="button"
                            class="sans-dropdown-button w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between">
                            <span class="text-ink-tab font-extrabold">
                                <span>اتاق فرار</span>
                            </span>
                            <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="sans-options absolute hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                            <div class="max-h-60 overflow-auto">
                                <div class="city-options py-1">
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="product_type" value="اتاق فرار"
                                            class="hidden option-sans-input" checked />
                                        اتاق فرار
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="product_type" value="سینما ترس"
                                            class="hidden option-sans-input" <?= ($product_type == "سینما ترس" ? 'checked' : '') ?> />
                                        سینما ترس
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="product_type" value="لیزرتگ"
                                            class="hidden option-sans-input" <?= ($product_type == "لیزرتگ") ? 'checked' : '' ?> />
                                        لیزرتگ
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="product_type" value="اتاق خشم"
                                            class="hidden option-sans-input" <?= ($product_type == "اتاق خشم") ? 'checked' : '' ?> />
                                        اتاق خشم
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full">
                <div class="mb-2 text-steel font-bold max-lg:hidden">تعداد نفرات</div>
                <div class="relative w-full max-w-xs">
                    <div class="sans-dropdown-container relative">
                        <button type="button"
                            class="sans-dropdown-button w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between">
                            <span class="text-ink-tab font-extrabold">
                                <span>تعداد نفرات</span>
                            </span>
                            <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="sans-options absolute hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                            <div class="max-h-60 overflow-auto">
                                <div class="py-1">
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="2"
                                            class="hidden option-sans-input" <?= ($count == 2) ? 'checked' : '' ?> />
                                        + 2 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="3"
                                            class="hidden option-sans-input" <?= ($count == 3) ? 'checked' : '' ?> />
                                        + 3 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="4"
                                            class="hidden option-sans-input" <?= ($count == 4) ? 'checked' : '' ?> />
                                        + 4 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="5"
                                            class="hidden option-sans-input" <?= ($count == 5) ? 'checked' : '' ?> />
                                        + 5 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="6"
                                            class="hidden option-sans-input" <?= ($count == 6) ? 'checked' : '' ?> />
                                        + 6 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="7"
                                            class="hidden option-sans-input" <?= ($count == 7) ? 'checked' : '' ?> />
                                        + 7 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="8"
                                            class="hidden option-sans-input" <?= ($count == 8) ? 'checked' : '' ?> />
                                        + 8 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="9"
                                            class="hidden option-sans-input" <?= ($count == 9) ? 'checked' : '' ?> />
                                        + 9 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="10"
                                            class="hidden option-sans-input" <?= ($count == 10) ? 'checked' : '' ?> />
                                        + 10 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="11"
                                            class="hidden option-sans-input" <?= ($count == 11) ? 'checked' : '' ?> />
                                        + 11 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="12"
                                            class="hidden option-sans-input" <?= ($count == 12) ? 'checked' : '' ?> />
                                        + 12 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="13"
                                            class="hidden option-sans-input" <?= ($count == 13) ? 'checked' : '' ?> />
                                        + 13 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="14"
                                            class="hidden option-sans-input" <?= ($count == 14) ? 'checked' : '' ?> />
                                        + 14 نفر
                                    </label>
                                    <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                        <input type="radio" name="count" value="15"
                                            class="hidden option-sans-input" <?= ($count == 15) ? 'checked' : '' ?> />
                                        + 15 نفر
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-x-4 lg:hidden">
            <div class="text-sm border-l border-slate-100 pl-4 font-bold">سانس‌ برای</div>
            <div class="flex text-slate-350 gap-4">
                <label class="flex-shrink-0 whitespace-nowrap rounded-md cursor-pointer" for="time-lg-1">
                    <input type="radio" name="schedule" value="[<?= $todayStart ?>,<?= $todayEnd ?>]" data-day="today"
                        class="hidden peer schedule-btn"
                        id="time-lg-1" checked />
                    <span class="text-nowrap px-5 py-1.5 text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 rounded-md peer-checked:bg-primary-500 peer-checked:text-white">امروز</span>
                </label>
                <label class="flex-shrink-0 whitespace-nowrap rounded-md cursor-pointer" for="time-lg-2">
                    <input type="radio" name="schedule" value="[<?= $tomorrowStart ?>,<?= $tomorrowEnd ?>]" data-day="tomorrow"
                        class="hidden peer schedule-btn"
                        id="time-lg-2" />
                    <span class="text-nowrap px-5 py-1.5  text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2   rounded-md peer-checked:bg-primary-500 peer-checked:text-white">فردا</span>
                </label>
                <label class="flex-shrink-0 whitespace-nowrap rounded-md cursor-pointer" for="time-lg-3">
                    <input type="radio" name="schedule"
                        value="[<?= $dayAfterTomorrowStart ?>,<?= $dayAfterTomorrowEnd ?>]" data-day="after-tomorrow" class="hidden peer schedule-btn"
                        id="time-lg-3" />
                    <span class="text-nowrap px-5 py-1.5 text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2  rounded-md peer-checked:bg-primary-500 peer-checked:text-white">پس فردا</span>
                </label>
            </div>
        </div>
        <div class="bg-slate-105 max-lg:w-full max-lg:h-0.5 lg:self-stretch lg:w-0.5 max-lg:my-5"></div>
        <div class="lg:flex max-lg:grid max-lg:grid-cols-2 items-center gap-x-5 shrink-0 lg:py-8">
            <div class="max-lg:w-fit">
                <div class="mb-2 text-steel font-bold">از ساعت</div>
                <div class="flex border rounded-8 h-12 content-center">
                    <button type="button" class="operator-clock text-2xl text-accent-950 px-4" data-params="plus" data-clock="min-clock">+</button>
                    <span id="min-clock" class="border-r border-l px-4 content-center w-12 text-center" data-min="<?= ($schedule[0] == $todayStart) ? intval(date('H')) + 1 : '8' ?>" data-max="24"><?= ($schedule[0] == $todayStart) ? intval(date('H')) + 1 : '8' ?></span>
                    <button type="button" class="operator-clock text-2xl px-4" data-params="minus" data-clock="min-clock">-</button>
                </div>
            </div>
            <div class="max-lg:w-fit max-lg:mr-auto">
                <div class="mb-2 text-steel font-bold">تا ساعت</div>
                <div class="flex border rounded-8 h-12 content-center">
                    <button type="button" class="operator-clock text-2xl text-accent-950 px-4" data-params="plus" data-clock="max-clock">+</button>
                    <span id="max-clock" class="border-r border-l px-4 content-center w-12 text-center" data-min="<?= ($schedule[0] == $todayStart) ? intval(date('H')) + 1 : '8' ?>" data-max="24">24</span>
                    <button type="button" class="operator-clock text-2xl px-4" data-params="minus" data-clock="max-clock">-</button>
                </div>
            </div>
            <button type="submit"
                class="max-lg:w-full max-lg:col-span-2 max-lg:h-12 max-lg:rounded-lg lg:w-42 h-d48 bg-accent-450 hover:bg-accent-700 shadow-13 shrink-0 rounded-8 self-end max-lg:my-5">
                <span class="text-white font-extrabold text-xl">نمایش</span>
            </button>
        </div>
    </section>
</form>
<!-- Sorting Section -->
<div class="flex justify-between items-end relative mt-10 mb-6">
    <!-- Sorting Buttons -->
    <div class="flex items-center gap-6">
        <div class="flex text-slate-350 gap-6 relative z-10">
            <label class="flex-shrink-0 whitespace-nowrap cursor-pointer" for="sort-topsale">
                <input type="radio" name="sort" value="topsale" class="hidden peer" id="sort-topsale" checked />
                <span class="text-nowrap text-sm text-gray-500 peer-checked:text-orange-500 peer-checked:border-b-2 peer-checked:border-orange-500 peer-checked:pb-2 font-bold">پرفروش</span>
            </label>
            <label class="flex-shrink-0 whitespace-nowrap cursor-pointer" for="sort-recent">
                <input type="radio" name="sort" value="recent" class="hidden peer" id="sort-recent" />
                <span class="text-nowrap text-sm text-gray-500 peer-checked:text-orange-500 peer-checked:border-b-2 peer-checked:border-orange-500 peer-checked:pb-2 font-bold">جدیدترین</span>
            </label>
            <label class="flex-shrink-0 whitespace-nowrap cursor-pointer" for="sort-popular">
                <input type="radio" name="sort" value="popular" class="hidden peer" id="sort-popular" />
                <span class="text-nowrap text-sm text-gray-500 peer-checked:text-orange-500 peer-checked:border-b-2 peer-checked:border-orange-500 peer-checked:pb-2 font-bold">محبوب ترین</span>
            </label>
        </div>
    </div>
    <div class="absolute bottom-[-3.5px] left-0 w-full h-px bg-gray-300"></div>
</div>
<div class="pb-12 pt-12">
    <div id="product-container"></div>
</div>
<script>
    let baseUrlWebService = 'https://' + location.hostname + '/web-service/web-service.php'
    if (location.hostname === 'localhost') {
        baseUrlWebService = 'http://' + location.hostname + '/escapezoom_wp/web-service/web-service.php'
    } else if (location.hostname === 'wo.escapezoom.local') {
        baseUrlWebService = 'http://' + location.hostname + '/web-service/web-service.php'
    }
    // price filter range
    function roundToNearestThousand(num) {
        return Math.round(num / 1000) * 1000;
    }
    const rangeInput = document.querySelectorAll(".range-input input"),
        priceInput = document.querySelectorAll(".price-input input"),
        range = document.querySelector(".slider .progress");
    let priceGap = 1000;
    rangeInput.forEach((input) => {
        input.addEventListener("input", (e) => {
            let minVal = parseInt(rangeInput[0].value),
                maxVal = parseInt(rangeInput[1].value);

            if (maxVal - minVal < priceGap) {
                if (e.target.className === "range-min") {
                    rangeInput[0].value = maxVal - priceGap;
                } else {
                    rangeInput[1].value = minVal + priceGap;
                }
            } else {
                priceInput[0].setAttribute('value', roundToNearestThousand(minVal).toLocaleString());
                priceInput[1].setAttribute('value', roundToNearestThousand(maxVal).toLocaleString());
                //priceInput[1].value = rangeInput[1].value;
                range.style.right = (minVal / rangeInput[0].max) * 100 + "%";
                range.style.left = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
            }
        });
    });
    // Function to handle back navigation
    function goBack() {
        // Check if there's a referrer and it's from the same domain
        if (document.referrer && document.referrer.includes(window.location.hostname)) {
            // If there's a valid referrer from the same domain, go back
            window.history.back();
        } else {
            // If no referrer or from external site, go to home page
            window.location.href = '<?= home_url() ?>';
        }
    }

    jQuery(document).ready(function($) {
        // Initialize default values when page loads
        function initializeDefaults() {
            // Set default product type to "اتاق فرار" if not already set
            if (!$('input[name="product_type"]:checked').length) {
                $('input[name="product_type"][value="اتاق فرار"]').prop('checked', true);
            }

            // Set default city to Tehran if not already set
            if (!$('input[name="city_id"]:checked').length) {
                $('input[name="city_id"][value="15"]').prop('checked', true);
                $('#cities-box-title').text('تهران');
            }

            // Update dropdown button text with selected values
            $('input.option-sans-input:checked').each(function() {
                const optionParam = $(this).parent().text().trim();
                $(this).closest('.sans-dropdown-container').find('.sans-dropdown-button span').text(optionParam);
            });
        }

        // Call initialization
        initializeDefaults();

        $('form#game-finder-form').on('submit', function(e) {
            e.preventDefault()
            let product_type = $('input[name="product_type"]:checked').val();
            let city_id = $('input[name="city_id"]:checked').val();
            if (!city_id) {
                city_id = -1
            } else {
                city_id = [city_id]
            }
            let count = $('input[name="count"]:checked').val();
            if (!count) {
                count = -1
            }
            let age = $('input[name="age"]:checked').val();
            if (!age) {
                age = -1
            }
            let duration = $('input[name="duration"]:checked').val();
            if (!duration) {
                duration = -1
            }
            let minPrice = parseInt(($('input[name="min_price"]').val() || '0').replace(/,/g, ""), 10);
            let maxPrice = parseInt(($('input[name="max_price"]').val() || '400000').replace(/,/g, ""), 10);
            let scheduleStart = $('input[name="schedule_start"]').val();
            let scheduleEnd = $('input[name="schedule_end"]').val();
            let sort_type = $('input[name="sort_type_final"]').val();
            $.ajax({
                type: 'POST',
                url: baseUrlWebService,
                data: {
                    "type": "sort_products_get",
                    "data": {
                        "source": "cat_sansyab",
                        "params": {
                            //   "sort_type":"recent", // sort_type:-1. it's -1 until the user presses sort buttons.
                            "sort_type": sort_type,
                            "page": 1,
                            "product_type": product_type,
                            "city_id": city_id,
                            "count": count,
                            "age": age,
                            "duration": duration,
                            "schedule": [
                                scheduleStart,
                                scheduleEnd
                            ],
                            "price": [
                                minPrice,
                                maxPrice
                            ],
                        }
                    },
                },
                dataType: "json",
                beforeSend: function() {
                    $('#product-container').empty().append('<div class="text-center">لطفا منتظر بمانید<span class="loading-dots"></span></div>')
                },
                success: function(data) {
                    if ((data.products).length > 0) {
                        $('#product-container').empty().append('<section id="product_list_container" class="grid grid-cols-2 justify-between max-lg:gap-5.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 2xl:grid-cols-6 child:box-content gap-6"></section>')
                        $('#product-container #product_list_container').append(data.products)
                    } else {
                        $('#product-container').empty().append('<div class="rounded-xl px-16 py-12 border border-slate-100 text-center shadow-12">موردی یافت نشد.</div>')
                    }
                },
            });
        })


        $('.schedule-btn').on('click', function() {
            let value = $(this).val().slice(1, -1).split(",")
            let day = $(this).attr('data-day')
            let startDay = 8;
            let endDay = 24;
            if (day === 'today') {
                let time = new Date();
                startDay = parseInt(String(time.getHours()).padStart(2, '0')) + 1;
            }
            let schedule_start = value[0];
            let schedule_end = value[1];
            $('input[name=schedule_start]').val(schedule_start).attr('data-limit', schedule_start)
            $('input[name=schedule_end]').val(schedule_end).attr('data-limit', schedule_end)
            $('#min-clock').text(startDay).attr('data-min', startDay).attr('data-max', endDay)
            $('#max-clock').text(endDay).attr('data-min', startDay).attr('data-max', endDay)
        })
        $('input[name=input-rate]').on('click', function() {
            let rate = $(this).val()
            $('#rating').val(rate)
        })
        $('input[name=sort]').on('click', function() {
            let sort = $(this).val()
            $('input[name=sort_type_final]').val(sort)
            // Auto-submit form when sort changes
            $('#game-finder-form').submit()
        })
        $('.operator-clock').on('click', function() {
            let clock = $(this).data('clock');
            let param = $(this).data('params');
            let currentTime = parseInt($('#' + clock).text());
            let minTime = parseInt($('#' + clock).attr('data-min'));
            let maxTime = parseInt($('#' + clock).attr('data-max'));

            if (param === 'plus') {
                if (currentTime < maxTime) {
                    currentTime++;
                }
            } else if (param === 'minus') {
                if (currentTime > minTime) {
                    currentTime--;
                }
            }

            $('#' + clock).text(currentTime);

            // Update schedule_start or schedule_end
            if (clock === 'min-clock') {
                let scheduleStart = $('input[name=schedule_start]');
                let limit = parseInt(scheduleStart.attr('data-limit'));
                // Calculate the start of the day and add the selected hour
                let dayStart = Math.floor(limit / 86400) * 86400; // Get start of day
                let newValue = dayStart + (currentTime * 3600);
                scheduleStart.val(newValue);
            } else if (clock === 'max-clock') {
                let scheduleStart = $('input[name=schedule_start]');
                let scheduleEnd = $('input[name=schedule_end]');
                let limit = parseInt(scheduleStart.attr('data-limit'));
                // Calculate the start of the day and add the selected hour
                let dayStart = Math.floor(limit / 86400) * 86400; // Get start of day
                let newValue = dayStart + (currentTime * 3600);
                scheduleEnd.val(newValue);
            }

            // Sync min and max values
            if (clock === 'min-clock') {
                $('#max-clock').attr('data-min', currentTime);
            } else if (clock === 'max-clock') {
                $('#min-clock').attr('data-max', currentTime);
            }
        });
    })
</script>

<?php get_footer(); ?>