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
            }
            $('#product-container').empty()
            $.ajax({
                type: 'POST',
                url: baseUrlWebService,
                data: {
                    "type": "sort_products_get",
                    "data": {
                        "source": "cat_sansyab",
                        "only_free_sanses": 1,
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

                        // Generate short link for initial load
                        let queryParts = [];

                        // schedule
                        let scheduleStart = <?= ($schedule[0]) ?: $todayStart ?>;
                        let scheduleEnd = <?= ($schedule[1]) ?: $todayEnd ?>;
                        if (scheduleStart && scheduleStart !== -1 && scheduleStart !== '-1' && scheduleEnd && scheduleEnd !== -1 && scheduleEnd !== '-1') {
                            let scheduleParam = '[' + scheduleStart + ',' + scheduleEnd + ']';
                            queryParts.push('schedule=' + encodeURIComponent(scheduleParam));
                        } else {
                            queryParts.push('schedule=-1');
                        }

                        // product_type
                        let productType = "<?= $product_type ?: 'اتاق فرار' ?>";
                        if (productType && productType !== -1 && productType !== '' && productType !== undefined) {
                            let productTypeParam = productType.toString().replace(/\s+/g, '+');
                            queryParts.push('product_type=' + productTypeParam);
                        }

                        // city_search
                        queryParts.push('city_search=');

                        // city_id
                        let cityId = <?= $city_id ?: json_encode([15]) ?>;
                        if (cityId && cityId !== -1) {
                            let cityValue = Array.isArray(cityId) ? cityId[0] : cityId;
                            queryParts.push('city_id=' + encodeURIComponent(cityValue));
                        }

                        // count
                        let count = <?= ($count) ?: -1 ?>;
                        if (count && count !== -1) queryParts.push('count=' + encodeURIComponent(count));

                        // sort_type (default to topsale)
                        queryParts.push('sort_type=' + encodeURIComponent('topsale'));

                        let queryString = queryParts.join('&');
                        generateShortLink(queryString.toString());
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
<h1 class="text-base font-extrabold lg:text-2xl mb-8">سانس یاب</h1>

<form id="game-finder-form" class="mt-6">
    <input type="hidden" name="schedule_start" data-limit="<?= ($schedule[0]) ?: $todayStart ?>" value="<?= ($schedule[0]) ?: $todayStart ?>">
    <input type="hidden" name="schedule_end" data-limit="<?= ($schedule[1]) ?: $todayEnd ?>" value="<?= ($schedule[1]) ?: $todayEnd ?>">
    <input type="hidden" name="sort_type_final" value="topsale">

    <section class="flex justify-between gap-x-10 items-start">
        <div class="flex items-center gap-x-4">
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
        <div class="flex justify-between gap-5">
            <div class="relative">
                <input
                    id="mobile-input-sansyab"
                    class="w-[236px] h-[54px] rounded-xl border border-[#E4EBF0] text-xs font-yekan-bold p-4 outline-none transition-colors duration-150"
                    placeholder="شماره پلیر را وارد کنید..."
                    inputmode="numeric"
                    pattern="[0-9+]*"
                    maxlength="14"
                    oninput="
                    // Remove non-numeric except + at start
                    let v = this.value;
                    // Only allow + at the start, then numbers
                    v = v.replace(/[^0-9+]/g, '');
                    if (v.startsWith('+')) {
                        v = '+' + v.slice(1).replace(/[^0-9]/g, '');
                    } else {
                        v = v.replace(/[^0-9]/g, '');
                    }
                    this.value = v;
                " />
                <p id="mobile-error-sansyab" class="text-red-500 text-xs absolute mt-1 hidden"></p>
            </div>

            <button
                id="send-sms-btn-sansyab"
                class="h-[54px] w-28 text-center content-center text-base font-yekan-heavy text-white bg-gray-400 rounded-xl cursor-not-allowed text-nowrap"
                disabled>
                ارسال پیامک
            </button>
        </div>
    </section>
    <section
        class="flex items-center justify-between mt-11 rounded-3xl border border-slate-110 px-8 gap-x-10">
        <div class="grid grid-cols-3 gap-x-6 gap-y-5 grow py-8">
            <div class="w-full">
                <div class="mb-2 text-[#62748E] font-bold">شهر</div>
                <div class="relative w-full max-w-xs">
                    <div class="sans-dropdown-container relative">
                        <button type="button"
                            class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                            <span id="cities-box-title" class="text-[#0F172B] font-extrabold relative">
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
                            <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
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
                <div class="mb-2 text-[#62748E] font-bold">نوع بازی</div>
                <div class="relative w-full max-w-xs">
                    <div class="sans-dropdown-container relative">
                        <button type="button"
                            class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                            <span class="text-[#0F172B] font-extrabold">
                                <span>اتاق فرار</span>
                            </span>
                            <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
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
                <div class="mb-2 text-[#62748E] font-bold">تعداد نفرات</div>
                <div class="relative w-full max-w-xs">
                    <div class="sans-dropdown-container relative">
                        <button type="button"
                            class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                            <span class="text-[#0F172B] font-extrabold">
                                <span>تعداد نفرات</span>
                            </span>
                            <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
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
        <div class="bg-[#E4EBF0] self-stretch w-0.5"></div>
        <div class="flex items-center gap-x-5 shrink-0 py-8">
            <div>
                <div class="mb-2 text-[#62748E] font-bold">از ساعت</div>
                <div class="flex border rounded-[8px] h-12 content-center">
                    <button type="button" class="operator-clock text-2xl text-[#049654] px-4" data-params="plus" data-clock="min-clock">+</button>
                    <span id="min-clock" class="border-r border-l px-4 content-center w-12 text-center" data-min="<?= ($schedule[0] == $todayStart) ? intval(date('H')) + 1 : '8' ?>" data-max="24"><?= ($schedule[0] == $todayStart) ? intval(date('H')) + 1 : '8' ?></span>
                    <button type="button" class="operator-clock text-2xl px-4" data-params="minus" data-clock="min-clock">-</button>
                </div>
            </div>
            <div>
                <div class="mb-2 text-[#62748E] font-bold">تا ساعت</div>
                <div class="flex border rounded-[8px] h-12 content-center">
                    <button type="button" class="operator-clock text-2xl text-[#049654] px-4" data-params="plus" data-clock="max-clock">+</button>
                    <span id="max-clock" class="border-r border-l px-4 content-center w-12 text-center" data-min="<?= ($schedule[0] == $todayStart) ? intval(date('H')) + 1 : '8' ?>" data-max="24">24</span>
                    <button type="button" class="operator-clock text-2xl px-4" data-params="minus" data-clock="max-clock">-</button>
                </div>
            </div>
            <button type="submit"
                class="w-42 h-[48px] bg-primary-500 hover:bg-primary-600 shadow-13 shrink-0 rounded-[8px] self-end">
                <span class="text-white font-extrabold text-xl">نمایش</span>
            </button>
        </div>
    </section>
</form>
<!-- Sorting and Short Link Section -->
<div class="flex justify-between items-end relative mt-10 mb-6">
    <!-- Sorting Buttons -->
    <div class="flex items-center gap-6">
        <div class="flex text-slate-350 gap-6 relative z-10">
            <label class="flex-shrink-0 whitespace-nowrap cursor-pointer" for="sort-topsale">
                <input type="radio" name="sort" value="topsale" class="hidden peer" id="sort-topsale" checked />
                <span class="text-nowrap text-sm font-bold text-gray-500 peer-checked:text-orange-500 peer-checked:border-b-2 peer-checked:border-orange-500 peer-checked:pb-2">پرفروش</span>
            </label>
            <label class="flex-shrink-0 whitespace-nowrap cursor-pointer" for="sort-recent">
                <input type="radio" name="sort" value="recent" class="hidden peer" id="sort-recent" />
                <span class="text-nowrap text-sm font-bold text-gray-500 peer-checked:text-orange-500 peer-checked:border-b-2 peer-checked:border-orange-500 peer-checked:pb-2">جدیدترین</span>
            </label>
            <label class="flex-shrink-0 whitespace-nowrap cursor-pointer" for="sort-popular">
                <input type="radio" name="sort" value="popular" class="hidden peer" id="sort-popular" />
                <span class="text-nowrap text-sm font-bold text-gray-500 peer-checked:text-orange-500 peer-checked:border-b-2 peer-checked:border-orange-500 peer-checked:pb-2">محبوب ترین</span>
            </label>
        </div>
    </div>
    <!-- Short Link Box and Copy Button -->
    <div class="flex items-center gap-3 absolute bottom-0 left-0 w-fit">
        <div id="short-link-display" class="hidden">
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border">
                <span class="text-sm text-gray-600">لینک کوتاه:</span>
                <span id="short-link-text" class="text-sm font-mono text-blue-600 bg-white px-2 py-1 rounded border"></span>
            </div>
        </div>
        <button id="copy-short-link-btn" class="hidden items-center gap-2 w-40 justify-center px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="14" height="14" x="8" y="8" rx="2" ry="2" />
                <path d="m4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
            </svg>
            <span id="copy-btn-text">کپی لینک کوتاه</span>
        </button>
    </div>
    <div class="absolute bottom-[-3.5px] left-0 w-full h-px bg-gray-300"></div>
</div>
<div class="pb-12 pt-12">
    <div class="flex justify-between items-center mb-4">
        <div id="short-link-display" class="hidden">
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border">
                <span class="text-sm text-gray-600">لینک کوتاه:</span>
                <span id="short-link-text" class="text-sm font-mono text-blue-600 bg-white px-2 py-1 rounded border"></span>
            </div>
        </div>
        <button id="copy-short-link-btn" class="hidden items-center gap-2 w-40 justify-center px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="14" height="14" x="8" y="8" rx="2" ry="2" />
                <path d="m4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
            </svg>
            <span id="copy-btn-text">کپی لینک کوتاه</span>
        </button>
    </div>
    <div id="product-container"></div>
</div>
<script>
    let baseUrlWebService = 'https://' + location.hostname + '/web-service/web-service.php'
    if (location.hostname === 'localhost') {
        baseUrlWebService = 'http://' + location.hostname + '/escapezoom_wp/web-service/web-service.php'
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
                        "only_free_sanses": $('input[name="schedule"]:checked').data('day') === 'today' ? 1 : 0,
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
                        let queryParts = [];

                        // schedule
                        if (scheduleStart && scheduleStart !== -1 && scheduleStart !== '-1' && scheduleEnd && scheduleEnd !== -1 && scheduleEnd !== '-1') {
                            let scheduleParam = '[' + scheduleStart + ',' + scheduleEnd + ']';
                            queryParts.push('schedule=' + encodeURIComponent(scheduleParam));
                        } else {
                            queryParts.push('schedule=-1');
                        }

                        // product_type با + بجای فاصله
                        if (product_type && product_type !== -1 && product_type !== '' && product_type !== undefined) {
                            let productTypeParam = product_type.toString().replace(/\s+/g, '+');
                            // بدون encodeURIComponent تا فارسی به همان شکل بماند
                            queryParts.push('product_type=' + productTypeParam);
                        }

                        // city_search همیشه خالی
                        queryParts.push('city_search=');

                        // city_id
                        if (city_id && city_id !== -1) {
                            let cityValue = Array.isArray(city_id) ? city_id[0] : city_id;
                            queryParts.push('city_id=' + encodeURIComponent(cityValue));
                        }

                        // count
                        if (count && count !== -1) queryParts.push('count=' + encodeURIComponent(count));
                        // age
                        if (age && age !== -1) queryParts.push('age=' + encodeURIComponent(age));
                        // duration
                        if (duration && duration !== -1) queryParts.push('duration=' + encodeURIComponent(duration));
                        // min_price
                        if (minPrice && minPrice !== 0) queryParts.push('min_price=' + encodeURIComponent(minPrice));
                        // max_price
                        if (maxPrice && maxPrice !== 400000) queryParts.push('max_price=' + encodeURIComponent(maxPrice));

                        // sort_type
                        if (sort_type && sort_type !== -1 && sort_type !== '') queryParts.push('sort_type=' + encodeURIComponent(sort_type));

                        let queryString = queryParts.join('&');

                        generateShortLink(queryString.toString());
                    } else {
                        $('#product-container').empty().append('<div class="rounded-xl px-16 py-12 border border-slate-100 text-center shadow-12">موردی یافت نشد.</div>')

                        // Hide copy button and link display if no products found
                        $('#copy-short-link-btn').addClass('hidden').removeClass('flex');
                        $('#short-link-display').addClass('hidden');
                        window.currentShortLink = null;
                    }
                },
            });
        })

        // Function to generate short link
        function generateShortLink(url) {

            let originalUrl = 'https://escapezoom.ir/game-finder' + '?' + url;
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'generate_sansyab_shortlink',
                    'original_url': originalUrl.toString()
                },
                success: function(response) {
                    if (response.success) {
                        window.currentShortLink = response.data.shortlink;
                        $('#short-link-text').text(response.data.shortlink);
                        $('#short-link-display').removeClass('hidden');
                        $('#copy-short-link-btn').removeClass('hidden').addClass('flex');
                    }
                },
                error: function() {
                    console.log('خطا در تولید لینک کوتاه');
                }
            });
        }

        // Copy short link functionality
        $('#copy-short-link-btn').on('click', function() {
            if (window.currentShortLink) {
                navigator.clipboard.writeText(window.currentShortLink).then(function() {
                    $('#copy-short-link-btn').removeClass('bg-primary-500 hover:bg-primary-600').addClass('bg-green-500');
                    $('#copy-btn-text').text('کپی شد');
                    setTimeout(() => {
                        $('#copy-short-link-btn').removeClass('bg-green-500').addClass('bg-primary-500 hover:bg-primary-600');
                        $('#copy-btn-text').text('کپی لینک کوتاه');
                    }, 2000);
                });
            }
        });

        $('.schedule-btn').on('click', function() {
            $('.schedule-btn').removeClass('active');
            $(this).addClass('active');

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

        // Mobile validation and SMS functionality for sansyab
        $('#mobile-input-sansyab').on('input', function() {
            var $input = $(this);
            var $btn = $('#send-sms-btn-sansyab');
            var $error = $('#mobile-error-sansyab');
            var val = $input.val().replace(/\s/g, '');

            // فرمت‌های معتبر: 09xxxxxxxxx, 9xxxxxxxxx, +989xxxxxxxxx
            var isValid = (
                /^09\d{9}$/.test(val) ||
                /^9\d{9}$/.test(val) ||
                /^\+989\d{9}$/.test(val)
            );

            if (isValid) {
                $btn.prop('disabled', false)
                    .removeClass('bg-gray-400 cursor-not-allowed')
                    .addClass('bg-[#02C96F] cursor-pointer');
                $input.removeClass('border-red-500');
                $error.addClass('hidden');
            } else {
                $btn.prop('disabled', true)
                    .removeClass('bg-[#02C96F] cursor-pointer')
                    .addClass('bg-gray-400 cursor-not-allowed');
                if (val.length > 0) {
                    $input.addClass('border-red-500');
                    $error.removeClass('hidden').text('شماره همراه نامعتبر است.');
                } else {
                    $input.removeClass('border-red-500');
                    $error.addClass('hidden');
                }
            }
        });

        // Validate on blur
        $('#mobile-input-sansyab').on('blur', function() {
            var $input = $(this);
            var $error = $('#mobile-error-sansyab');
            var val = $input.val().replace(/\s/g, '');

            var isValid = (
                /^09\d{9}$/.test(val) ||
                /^9\d{9}$/.test(val) ||
                /^\+989\d{9}$/.test(val)
            );

            if (!isValid && val.length > 0) {
                $input.addClass('border-red-500');
                $error.removeClass('hidden').text('شماره همراه نامعتبر است.');
            } else {
                $input.removeClass('border-red-500');
                $error.addClass('hidden');
            }
        });

        // Only allow numeric input and + at start
        $('#mobile-input-sansyab').on('keypress', function(e) {
            let char = String.fromCharCode(e.which);
            if (!/[0-9]/.test(char)) {
                if (char === '+' && this.selectionStart === 0 && this.value.indexOf('+') === -1) {
                    return true;
                }
                e.preventDefault();
                return false;
            }
        });

        // SMS send functionality
        $('#send-sms-btn-sansyab').on('click', function(e) {
            e.preventDefault();

            const $input = $('#mobile-input-sansyab');
            const $sendBtn = $(this);
            const $message = $('#mobile-error-sansyab');
            const mobile = $input.val().trim();
            const isValidMobile = /^09\d{9}$/.test(mobile);

            $input.removeClass('border-red-500');

            if (!isValidMobile) {
                $message.removeClass('hidden text-green-500').addClass('text-red-500').text('شماره همراه نامعتبر است.');
                $input.addClass('border-red-500');
            } else if (!window.currentShortLink) {
                $message.removeClass('hidden text-green-500').addClass('text-red-500').text('ابتدا فیلتری اعمال کنید تا لینک تولید شود.');
            } else {
                let smsText = `کاربر گرامی اسکیپ زوم، بازی های مورد نظر شما در لینک زیر قابل مشاهده هستند.\n${window.currentShortLink}`;

                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'team_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                        'callback': 'sms_template_send',
                        'text': smsText,
                        'phone': mobile.replace(/^(\+98|0098|98|0)?9/, '09'),
                    },
                    beforeSend: function() {
                        $sendBtn.text('منتظر بمانید...');
                    },
                    success: function(data) {
                        $message.removeClass('hidden text-red-500').addClass('text-green-500').text('پیامک با موفقیت ارسال شد.');
                        $sendBtn.text('ارسال پیامک');
                        $input.val('');
                        $sendBtn.prop('disabled', true)
                            .removeClass('bg-[#02C96F] cursor-pointer')
                            .addClass('bg-gray-400 cursor-not-allowed');
                    },
                    error: function() {
                        $message.removeClass('hidden text-green-500').addClass('text-red-500').text('خطا در ارسال پیامک. دوباره تلاش کنید.');
                        $sendBtn.text('ارسال پیامک');
                    }
                });
            }
        });
    })
</script>