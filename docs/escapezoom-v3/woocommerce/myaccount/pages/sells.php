<div class="lg:col-span-8 2xl:col-span-9">
    <section class="border-edge lg:h-full lg:rounded-3xl lg:border lg:shadow-13 lg:p-8">
        <div class="lg:flex lg:items-center lg:justify-between">
            <h2 class="text-22 font-bold lg:text-lg">فروش های من</h2>
            <div class="flex items-center max-lg:my-7 max-lg:justify-between lg:gap-x-11">
                <button type="button" class="change-date border-b border-b-2 border-b-primary-500 pb-1 text-md font-bold" data-id="filter-by-date">
                    بازه زمانی
                </button>
                <button type="button" class="change-date text-md font-bold text-gray-ui" data-id="one-week">
                    یک هفته
                </button>
                <button type="button" class="change-date text-md font-bold text-gray-ui" data-id="one-month">
                    یک ماه
                </button>
                <button type="button" class="change-date text-md font-bold text-gray-ui" data-id="three-month">
                    سه ماه
                </button>
            </div>
            <div class="flex items-center gap-2 max-lg:grid grid-cols-2">
                <span class="max-lg:hidden">از</span>
                <input type="text" id="start-date" class="persian-date-picker grow col-span-1 min-h-8 rounded-md border border-rail bg-gray-20 px-2.5 max-lg:p-2.5 text-right text-sm shadow-13"/>
                <span class="max-lg:hidden">تا</span>
                <input type="text" id="end-date" class="persian-date-picker grow col-span-1 min-h-8 rounded-md border border-rail bg-gray-20 px-2.5 max-lg:p-2.5 text-right text-sm shadow-13"/>
                <button type="button" id="filter-by-date" class="rounded-md col-span-2 min-h-8 border border-primary-700 bg-primary-500 px-2.5 max-lg:p-2.5 text-center text-sm text-white shadow-13">
                    مشاهده
                </button>
            </div>
        </div>

        <div id="summary" class="mt-5"></div>

        <div id="data-table">
            <div class="text-22 font-bold lg:text-lg text-center lg:my-19 text-gray-500">
                لطفا بازه زمانی را مشخص کنید.
            </div>
        </div>

    </section>
</div>

<script>
    jQuery(document).ready(function ($) {
        let Today = new Date()
        let Yesterday = new Date(Today.getTime() - (60 * 60 * 24 * 1000))
        let Tomorrow = new Date(Today.getTime() + (60 * 60 * 24 * 1000));
        let OneWeekAgo = new Date(Today.getTime() - (7 * 60 * 60 * 24 * 1000))
        let OneMonthAgo = new Date(Today.getTime() - (30 * 60 * 60 * 24 * 1000))
        let ThreeMonthAgo = new Date(Today.getTime() - (3 * 30 * 60 * 60 * 24 * 1000))

        let type

        function jalali_to_gregorian(jy, jm, jd) {
            let sal_a, gy, gm, gd, days;
            jy += 1595;
            days = -355668 + (365 * jy) + (~~(jy / 33) * 8) + ~~(((jy % 33) + 3) / 4) + jd + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
            gy = 400 * ~~(days / 146097);
            days %= 146097;
            if (days > 36524) {
                gy += 100 * ~~(--days / 36524);
                days %= 36524;
                if (days >= 365) days++;
            }
            gy += 4 * ~~(days / 1461);
            days %= 1461;
            if (days > 365) {
                gy += ~~((days - 1) / 365);
                days = (days - 1) % 365;
            }
            gd = days + 1;
            sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            for (gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
            return [gy, gm, gd];
        }

        const ConvertDate = (el, time = "00:00:00") => {
            const ConvertNumbers = value => value.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))

            let value = el.val()
            value = ConvertNumbers(value)

            let arrayDate = value.split('/'),
                year = parseInt(arrayDate[0]),
                month = parseInt(arrayDate[1]),
                day = parseInt(arrayDate[2])

            let enDate = jalali_to_gregorian(year, month, day),
                enYear = String(enDate[0]).padStart(4, '0'),
                enMonth = String(enDate[1]).padStart(2, '0'),
                enDay = String(enDate[2]).padStart(2, '0')

            let date = new Date(`${enYear}-${enMonth}-${enDay}T${time}`)

            return date.getTime()
        }

        const BuildSummary = (start, end) => {
            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' );?>",
                type: "POST",
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                    'callback': 'panel_sells_get_summary',
                    'start': start,
                    'end': end
                }, beforeSend: function () {
                    $("#summary").html('<div class="skeleton w-full h-15 rounded-xl my-6"></div>')
                }, success: function (response) {
                    $("#summary").html(response)
                }
            })
        }

        const BuildDataTable = (start, end, page = 1) => {
            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' );?>",
                type: "POST",
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                    'callback': 'panel_sells_get_tables',
                    'start': start,
                    'end': end,
                    'page': page
                }, beforeSend: function () {
                    let out = ''
                    for (let i = 0; i < 10; i++) {
                        out += '<div class="skeleton w-full h-15 rounded-xl my-3"></div>'
                    }

                    $("#data-table").html(out)
                }, success: function (response) {
                    $("#data-table").html(response)
                }
            })
        }

        $('.change-date').on('click', function () {
            let id = $(this).data('id')

            $(".change-date")
                .removeClass('border-b border-b-2 border-b-primary-500 pb-1')
                .addClass('text-gray-ui')

            $(this)
                .addClass('border-b border-b-2 border-b-primary-500 pb-1')
                .removeClass('text-gray-ui')

            type = id

            switch (type) {
                case "one-week":
                    BuildSummary(Tomorrow.getTime(), OneWeekAgo.getTime())
                    BuildDataTable(Tomorrow.getTime(), OneWeekAgo.getTime())
                    break;
                case "one-month":
                    BuildSummary(Tomorrow.getTime(), OneMonthAgo.getTime())
                    BuildDataTable(Tomorrow.getTime(), OneMonthAgo.getTime())
                    break;
                case "three-month":
                    BuildSummary(Tomorrow.getTime(), ThreeMonthAgo.getTime())
                    BuildDataTable(Tomorrow.getTime(), ThreeMonthAgo.getTime())
                    break;
            }
        })

        $("#filter-by-date").on('click', function () {
            let startDate = ConvertDate($("#start-date")),
                endDate = ConvertDate($("#end-date"), "23:59:59")

            type = "filter-by-date"

            $(".change-date")
                .removeClass('border-b border-b-2 border-b-primary-500 pb-1')
                .addClass('text-gray-ui')

            $(".change-date[data-id='filter-by-date']")
                .addClass('border-b border-b-2 border-b-primary-500 pb-1')
                .removeClass('text-gray-ui')

            BuildSummary(endDate, startDate)
            BuildDataTable(endDate, startDate)
        })

        $("body")
            .on('click', ".show-more", function () {
                $(this).find('span').text($(this).find('span').text() === 'مشاهده جزئیات بیشتر' ? 'مشاهده جزئیات کمتر' : 'مشاهده جزئیات بیشتر')
                $(this).find('svg').toggleClass('rotate-180')
                $(this).next().slideToggle(150)
            })
            .on('click', '.pagination a', function (e) {
            e.preventDefault()

            let page = $(this).attr('href').split('?page=')[1] ? $(this)
                .attr('href')
                .split('?page=')[1] : 1

            switch (type) {
                case "one-week":
                    BuildDataTable(Today.getTime(), OneWeekAgo.getTime(), page)
                    break;
                case "one-month":
                    BuildDataTable(Today.getTime(), OneMonthAgo.getTime(), page)
                    break;
                case "three-month":
                    BuildDataTable(Today.getTime(), ThreeMonthAgo.getTime(), page)
                    break;
                case "filter-by-date":
                    let startDate = ConvertDate($("#start-date")),
                        endDate = ConvertDate($("#end-date"))
                    BuildDataTable(endDate, startDate, page)
                    break;
            }
        })
    })
</script>