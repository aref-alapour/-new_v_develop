<?php
global $wpdb;

$user        = get_current_user_id();
$collections = $wpdb->get_results($wpdb->prepare("SELECT * FROM collections WHERE user_id LIKE $user"));

$tabs = [];
foreach ($collections as $collection) {
    $tabs[] = [
        'id'    => $collection->ID,
        'title' => $collection->title,
    ];
}

$types = [
    'escaperoom' => 'اتاق فرار',
    'cinema'     => 'سینما ترس',
    'lasertag'   => 'لیزرتگ',
    //	'rageroom'   => 'اتاق خشم',
];

?>
<section class="rounded-2xl border border-slate-120 px-8 shadow-12 max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none py-12 max-lg:border-0 max-lg:py-0">

    <!-- Title -->
    <div class="md:mb-8 mb-0 lg:mb-8">

        <h2 class="flex items-center gap-4 lg:hidden mb-5">
            <span class="text-base font-bold md:text-lg">
                <span class="text-xl">کالکشن‌های من</span>
                <span class="help" data-help="لیست بازی‌هایی که توسط برخی کاربران ساخته میشه و جنبه پیشنهاد به سایر کاربران رو داره."></span>
            </span>
        </h2>

        <div class="flex lg:hidden gap-5">
            <select class="select-box h-auto" id="collections-dropdown">
                <?php if (isset($tabs) && count($tabs) > 0) { ?>
                    <?php foreach ($tabs as $index => $tab) { ?>
                        <option value="<?php echo $tab['id']; ?>">
                            <?php echo esc_html($tab['title']); ?>
                        </option>
                    <?php } ?>
                <?php } else { ?>
                    <option value="0">کالکشنی وجود ندارد</option>
                <?php } ?>
            </select>
            <?php if (isset($tabs) && count($tabs) > 0) : ?>
                <button type="button" class="delete-collection-button text-red-500 hover:text-red-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 18 18" fill="none">
                        <rect width="18" height="18" rx="4" fill="#EF4444" />
                        <path d="M6 7h6v6H6z" fill="white" />
                        <path d="M7 5V4h4v1h2v1H5V5h2z" fill="white" />
                    </svg>
                </button>
            <?php endif; ?>
            <button type="button" class="add-collection-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" fill="none">
                    <rect width="50" height="50" rx="10" fill="#1ED982" />
                    <g filter="url(#filter0_d_21512_10694)">
                        <path d="M16.0904 25C16.0906 24.6687 16.2223 24.3511 16.4566 24.1169C16.6908 23.8827 17.0084 23.751 17.3397 23.7508L23.7496 23.7496L23.7508 17.3397C23.7508 17.1756 23.7831 17.0132 23.8459 16.8616C23.9086 16.7101 24.0007 16.5723 24.1167 16.4563C24.2327 16.3403 24.3704 16.2483 24.5219 16.1855C24.6735 16.1228 24.8359 16.0905 25 16.0905C25.164 16.0905 25.3265 16.1228 25.478 16.1855C25.6296 16.2483 25.7673 16.3403 25.8833 16.4563C25.9993 16.5723 26.0913 16.7101 26.1541 16.8616C26.2169 17.0132 26.2492 17.1756 26.2492 17.3397L26.2504 23.7496L32.6603 23.7508C32.9916 23.7508 33.3094 23.8824 33.5436 24.1167C33.7779 24.3509 33.9095 24.6687 33.9095 25C33.9095 25.3313 33.7779 25.6491 33.5436 25.8833C33.3094 26.1176 32.9916 26.2492 32.6603 26.2492L26.2504 26.2504L26.2492 32.6603C26.2492 32.9916 26.1176 33.3094 25.8833 33.5437C25.649 33.7779 25.3313 33.9095 25 33.9095C24.6687 33.9095 24.3509 33.7779 24.1167 33.5437C23.8824 33.3094 23.7508 32.9916 23.7508 32.6603L23.7496 26.2504L17.3397 26.2492C17.0084 26.249 16.6908 26.1173 16.4566 25.8831C16.2223 25.6489 16.0906 25.3313 16.0904 25Z" fill="white" />
                        <path d="M16.0904 25C16.0906 24.6687 16.2223 24.3511 16.4566 24.1169C16.6908 23.8827 17.0084 23.751 17.3397 23.7508L23.7496 23.7496L23.7508 17.3397C23.7508 17.1756 23.7831 17.0132 23.8459 16.8616C23.9086 16.7101 24.0007 16.5723 24.1167 16.4563C24.2327 16.3403 24.3704 16.2483 24.5219 16.1855C24.6735 16.1228 24.8359 16.0905 25 16.0905C25.164 16.0905 25.3265 16.1228 25.478 16.1855C25.6296 16.2483 25.7673 16.3403 25.8833 16.4563C25.9993 16.5723 26.0913 16.7101 26.1541 16.8616C26.2169 17.0132 26.2492 17.1756 26.2492 17.3397L26.2504 23.7496L32.6603 23.7508C32.9916 23.7508 33.3094 23.8824 33.5436 24.1167C33.7779 24.3509 33.9095 24.6687 33.9095 25C33.9095 25.3313 33.7779 25.6491 33.5436 25.8833C33.3094 26.1176 32.9916 26.2492 32.6603 26.2492L26.2504 26.2504L26.2492 32.6603C26.2492 32.9916 26.1176 33.3094 25.8833 33.5437C25.649 33.7779 25.3313 33.9095 25 33.9095C24.6687 33.9095 24.3509 33.7779 24.1167 33.5437C23.8824 33.3094 23.7508 32.9916 23.7508 32.6603L23.7496 26.2504L17.3397 26.2492C17.0084 26.249 16.6908 26.1173 16.4566 25.8831C16.2223 25.6489 16.0906 25.3313 16.0904 25Z" stroke="white" />
                    </g>
                    <defs>
                        <filter id="filter0_d_21512_10694" x="14.5903" y="15.5908" width="20.8191" height="20.8184" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="1" />
                            <feGaussianBlur stdDeviation="0.5" />
                            <feComposite in2="hardAlpha" operator="out" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_21512_10694" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_21512_10694" result="shape" />
                        </filter>
                    </defs>
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:flex">

            <div class="max-lg:hidden items-center gap-6 order-1 md:flex shrink-0">
                <h2 class="flex items-center gap-4">
                    <span class="text-base font-bold md:text-lg">
                        <span class="text-xl">کالکشن‌های من</span>
                    </span>
                    <span class="help" data-help="لیست بازی‌هایی که توسط برخی کاربران ساخته میشه و جنبه پیشنهاد به سایر کاربران رو داره."></span>
                </h2>
                <div class="hidden md:block"></div>
            </div>

            <?php if (isset($tabs) && count($tabs) > 0) { ?>
                <div class="max-lg:hidden scrollable space-x-6 grow order-3 lg:order-2 col-span-2 space-x-reverse overflow-hidden flex max-lg:h-12.5 max-lg:rounded-[10px] max-lg:border max-lg:border-[#E4EBF0]">
                    <?php foreach ($tabs as $index => $tab) { ?>
                        <button type="button"
                            data-tab="<?php echo $tab['id']; ?>"
                            class="category-selector grow lg:grow-0 py-3 leading-3 text-nowrap <?php echo $index == 0 ? 'max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500' : 'text-[#889BAD]'; ?>">
                            <?php echo esc_html($tab['title']); ?>
                        </button>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="flex items-center justify-end order-2 lg:order-3 gap-6 shrink-0">

                <!-- Add Collection Modal -->
                <div class="add-collection-modal z-50 hidden">
                    <div class="fixed bg-black/40 z-40 w-full h-full right-0 top-0"></div>
                    <div class="fixed bg-white w-[430px] max-w-[100%] h-auto right-[50%] translate-x-[50%] top-[50%] -translate-y-[50%] p-6 rounded-xl border z-50 flex flex-col gap-4">

                        <div class="border rounded-xl flex overflow-hidden">
                            <?php foreach ($types as $type => $label) { ?>
                                <button type="button"
                                    data-type="<?php echo $type; ?>"
                                    class="type-selector <?php echo $type == 'escaperoom' ? 'selected grow p-2 bg-primary-500 text-white' : 'grow p-2 text-[#889BAD]'; ?>">
                                    <?php echo esc_html($label); ?>
                                </button>
                            <?php } ?>
                        </div>

                        <input id="add-collection-name" class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl" placeholder="اسم کالکشن را وارد کنید" type="text">
                        <button type="button" id="add-collection-submit" class="bg-primaryColor p-4 text-white rounded-2xl shadow-primary-3 shadow-13">
                            ایجاد کالکشن
                        </button>

                    </div>
                </div>

                <!-- Delete Collection Modal -->
                <div class="delete-collection-modal z-50 hidden">
                    <div class="fixed bg-black/40 z-40 w-full h-full right-0 top-0"></div>
                    <div class="fixed bg-white w-[430px] max-w-[100%] h-auto right-[50%] translate-x-[50%] top-[50%] -translate-y-[50%] p-6 rounded-xl border z-50 flex flex-col gap-4">
                        <h3 class="text-lg font-bold text-center">حذف کالکشن</h3>
                        <p class="text-center text-gray-600">آیا از حذف این کالکشن مطمئن هستید؟ این عمل قابل بازگشت نیست.</p>
                        <div class="flex gap-4">
                            <button type="button" id="confirm-delete-collection" class="bg-red-500 text-white p-4 rounded-2xl flex-1">
                                حذف کالکشن
                            </button>
                            <button type="button" id="cancel-delete-collection" class="bg-gray-300 text-gray-700 p-4 rounded-2xl flex-1">
                                انصراف
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($tabs) && count($tabs) > 0) : ?>
                    <button type="button" class="delete-collection-button text-red-500 hover:text-red-700 transition-colors max-lg:hidden">
                        <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs">
                            <span class="max-lg:hidden">حذف کالکشن</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <rect width="18" height="18" rx="4" fill="#EF4444" />
                                <path d="M6 7h6v6H6z" fill="white" />
                                <path d="M7 5V4h4v1h2v1H5V5h2z" fill="white" />
                            </svg>
                        </div>
                    </button>
                <?php endif; ?>

                <a href="#" class="max-lg:hidden add-collection-button">
                    <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs">
                        <span class="max-lg:hidden">ایجاد لیست جدید</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <rect width="18" height="18" rx="4" fill="#1ED982" />
                            <g filter="url(#filter0_d_5322_2235)">
                                <path d="M4.62707 9C4.62717 8.83739 4.69181 8.68146 4.80679 8.56648C4.92178 8.45149 5.07771 8.38685 5.24032 8.38675L8.38701 8.38617L8.38759 5.23948C8.38759 5.15894 8.40345 5.0792 8.43427 5.0048C8.46509 4.93039 8.51026 4.86279 8.56721 4.80584C8.62415 4.7489 8.69176 4.70372 8.76616 4.6729C8.84056 4.64209 8.92031 4.62622 9.00084 4.62622C9.08138 4.62622 9.16112 4.64209 9.23552 4.6729C9.30993 4.70372 9.37753 4.7489 9.43448 4.80584C9.49142 4.86279 9.5366 4.93039 9.56741 5.0048C9.59823 5.0792 9.6141 5.15894 9.6141 5.23948L9.61467 8.38617L12.7614 8.38675C12.924 8.38675 13.08 8.45136 13.195 8.56636C13.31 8.68137 13.3746 8.83735 13.3746 9C13.3746 9.16265 13.31 9.31863 13.195 9.43364C13.08 9.54864 12.924 9.61325 12.7614 9.61325L9.61467 9.61383L9.6141 12.7605C9.6141 12.9232 9.54949 13.0792 9.43448 13.1942C9.31947 13.3092 9.16349 13.3738 9.00084 13.3738C8.8382 13.3738 8.68221 13.3092 8.56721 13.1942C8.4522 13.0792 8.38759 12.9232 8.38759 12.7605L8.38701 9.61383L5.24032 9.61325C5.07771 9.61315 4.92178 9.54851 4.80679 9.43352C4.69181 9.31854 4.62717 9.16261 4.62707 9Z" fill="white" />
                                <path d="M4.62707 9C4.62717 8.83739 4.69181 8.68146 4.80679 8.56648C4.92178 8.45149 5.07771 8.38685 5.24032 8.38675L8.38701 8.38617L8.38759 5.23948C8.38759 5.15894 8.40345 5.0792 8.43427 5.0048C8.46509 4.93039 8.51026 4.86279 8.56721 4.80584C8.62415 4.7489 8.69176 4.70372 8.76616 4.6729C8.84056 4.64209 8.92031 4.62622 9.00084 4.62622C9.08138 4.62622 9.16112 4.64209 9.23552 4.6729C9.30993 4.70372 9.37753 4.7489 9.43448 4.80584C9.49142 4.86279 9.5366 4.93039 9.56741 5.0048C9.59823 5.0792 9.6141 5.15894 9.6141 5.23948L9.61467 8.38617L12.7614 8.38675C12.924 8.38675 13.08 8.45136 13.195 8.56636C13.31 8.68137 13.3746 8.83735 13.3746 9C13.3746 9.16265 13.31 9.31863 13.195 9.43364C13.08 9.54864 12.924 9.61325 12.7614 9.61325L9.61467 9.61383L9.6141 12.7605C9.6141 12.9232 9.54949 13.0792 9.43448 13.1942C9.31947 13.3092 9.16349 13.3738 9.00084 13.3738C8.8382 13.3738 8.68221 13.3092 8.56721 13.1942C8.4522 13.0792 8.38759 12.9232 8.38759 12.7605L8.38701 9.61383L5.24032 9.61325C5.07771 9.61315 4.92178 9.54851 4.80679 9.43352C4.69181 9.31854 4.62717 9.16261 4.62707 9Z" stroke="white" />
                            </g>
                            <defs>
                                <filter id="filter0_d_5322_2235" x="3.12695" y="4.12598" width="11.748" height="11.748" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="1" />
                                    <feGaussianBlur stdDeviation="0.5" />
                                    <feComposite in2="hardAlpha" operator="out" />
                                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5322_2235" />
                                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5322_2235" result="shape" />
                                </filter>
                            </defs>
                        </svg>
                    </div>
                </a>

            </div>

        </div>

        <hr class="mt-7 max-lg:hidden">

    </div>
    <!-- Title -->

    <?php if (isset($tabs) && count($tabs) > 0) { ?>
        <div class="relative" id="collections-list"></div>
    <?php } else { ?>
        <div class="flex flex-col h-[300px] justify-center items-center w-fit mx-auto gap-3">
            <span class="text-lg text-slate-350">شما تاکنون کالکشن ایجاد نکردید ه اید!</span>
            <button type="button" class="add-collection-button bg-primaryColor p-2 text-white rounded-xl shadow-primary-3 shadow-13 w-full">
                ایجاد کالکشن
            </button>
        </div>
    <?php } ?>

</section>

<script>
    jQuery(document).ready(function($) {
        /**
         * Initialize Toast
         */
        let Toast = null;
        if (typeof Swal !== 'undefined') {
            Toast = Swal.mixin({
                toast: true,
                position: 'bottom-start',
                showConfirmButton: false,
                timer: 3000,
            });
        }

        /**
         * Ajax
         */
        const GetCollection = (collection) => {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                    'callback': 'panel_collection_get',
                    'collection': collection
                },
                beforeSend: function() {
                    $("#collections-list").html(function() {
                        let out = '<div class="w-full h-12 rounded-xl skeleton lg:mb-5 max-lg:mt-5 max-lg:mb-5"></div>'
                        out += '<div class="collection grid border-slate-120 lg:grid-cols-2 gap-7 lg:pb-8 2xl:grid-cols-3 2xl:gap-5 3xl:gap-10">'
                        for (let i = 0; i < 6; i++) {
                            out += '<div class="w-full h-44 rounded-xl skeleton"></div>'
                        }
                        out += '</div>'
                        return out
                    })
                },
                success: function(data) {
                    $("#collections-list").html(data)
                },
            })
        }

        // Send initial page load event to Zabaline
        if (typeof zebline !== 'undefined' && zebline.event) {
            let initialZeblineData = {
                "current_page": window.location.href,
                "action": "page_load",
                "collections_count": <?php echo isset($tabs) ? count($tabs) : 0; ?>,
                "first_collection_id": '<?php echo isset($tabs[0]) ? $tabs[0]['id'] : 0; ?>'
            };
            zebline.event.track("collection_action", initialZeblineData);
        }

        <?php if (isset($tabs[0]) && !empty($tabs[0]['id'])): ?>
            GetCollection('<?php echo $tabs[0]['id']; ?>')
        <?php endif; ?>

        $("[data-tab]").on('click', function() {
            $("[data-tab]")
                .removeClass('max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500')
                .addClass('text-[#889BAD]')

            $(this)
                .removeClass('text-[#889BAD]')
                .addClass('max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500');

            // Send data to Zabaline for tab switch
            if (typeof zebline !== 'undefined' && zebline.event) {
                let zeblineData = {
                    "collection_id": $(this).data('tab'),
                    "collection_title": $(this).text().trim(),
                    "current_page": window.location.href,
                    "action": "view"
                };
                zebline.event.track("collection_action", zeblineData);
            }

            GetCollection($(this).data('tab'))
        })


        /**
         * Switch Type In Modal Form
         */
        $(".type-selector").on('click', function() {
            $(".type-selector").removeClass('selected bg-primary-500 text-white').addClass('text-[#889BAD]')
            $(this).addClass('selected bg-primary-500 text-white').removeClass('text-[#889BAD]')

            // Send data to Zabaline for type selection
            if (typeof zebline !== 'undefined' && zebline.event) {
                let zeblineData = {
                    "collection_type": $(this).data('type'),
                    "collection_type_label": $(this).text().trim(),
                    "current_page": window.location.href,
                    "action": "type_select"
                };
                zebline.event.track("collection_action", zeblineData);
            }
        })

        /**
         * Show Modal
         */
        $(".add-collection-button").on('click', function() {
            // Send data to Zabaline for modal open
            if (typeof zebline !== 'undefined' && zebline.event) {
                let zeblineData = {
                    "current_page": window.location.href,
                    "action": "modal_open",
                    "modal_type": "add_collection"
                };
                zebline.event.track("collection_action", zeblineData);
            }

            $(".add-collection-modal").removeClass('hidden')
        })

        /**
         * Hide Modal
         */
        $(".add-collection-modal").find('> div:first-child').on('click', function() {
            // Send data to Zabaline for modal close
            if (typeof zebline !== 'undefined' && zebline.event) {
                let zeblineData = {
                    "current_page": window.location.href,
                    "action": "modal_close",
                    "modal_type": "add_collection"
                };
                zebline.event.track("collection_action", zeblineData);
            }

            $(".add-collection-modal").addClass('hidden')
        })

        $("#collections-dropdown").on('change', function() {
            let id = parseInt($(this).val())
            if (id !== 0) {
                // Send data to Zabaline for dropdown change
                if (typeof zebline !== 'undefined' && zebline.event) {
                    let selectedOption = $(this).find('option:selected');
                    let zeblineData = {
                        "collection_id": id,
                        "collection_title": selectedOption.text().trim(),
                        "current_page": window.location.href,
                        "action": "view",
                        "source": "dropdown"
                    };
                    zebline.event.track("collection_action", zeblineData);
                }

                $(`[data-tab=${id}]`).click()
            }
        })

        /**
         * Add Collection
         */
        $("#add-collection-submit").on('click', function(e) {
            e.preventDefault()
            let $this = $(this)
            let name = $("#add-collection-name").val()
            let type = $(".type-selector.selected").data('type')

            if (name !== '') {
                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                        'callback': 'panel_collection_add',
                        'data': {
                            name,
                            type
                        }
                    },
                    beforeSend: function() {
                        $this.prop('disabled', true)
                            .addClass('opacity-50 cursor-not-allowed')
                            .html('<div class="spinner" style="border-color: #FFFFFF; border-width: 3px; width: 33px; margin-inline: auto"></div>')
                    },
                    success: function(data) {
                        if (data.success) {
                            // Send data to Zabaline
                            if (typeof zebline !== 'undefined' && zebline.event) {
                                let zeblineData = {
                                    "collection_id": data.data.collection_id,
                                    "collection_title": data.data.collection_title,
                                    "collection_type": data.data.collection_type,
                                    "user_id": data.data.user_id,
                                    "timestamp": data.data.timestamp,
                                    "current_page": window.location.href,
                                    "action": "create"
                                };
                                zebline.event.track("collection_action", zeblineData);
                            }

                            // Show success message
                            let successMessage = 'کالکشن با موفقیت ایجاد شد';
                            if (data.data && typeof data.data === 'object') {
                                successMessage = data.data.message || 'کالکشن با موفقیت ایجاد شد';
                            } else if (data.data && typeof data.data === 'string') {
                                successMessage = data.data;
                            }

                            if (typeof Toast !== 'undefined' && Toast) {
                                Toast.fire({
                                    icon: 'success',
                                    title: successMessage
                                });
                            } else if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: successMessage,
                                    timer: 2000
                                });
                            }

                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            let errorMessage = 'خطایی رخ داد';
                            if (data.data && typeof data.data === 'object') {
                                errorMessage = data.data.message || 'خطایی رخ داد';
                            } else if (data.data && typeof data.data === 'string') {
                                errorMessage = data.data;
                            }

                            if (typeof Toast !== 'undefined' && Toast) {
                                Toast.fire({
                                    icon: 'error',
                                    title: errorMessage
                                });
                            } else if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: errorMessage
                                });
                            }
                        }
                    },
                    error: function() {
                        $this.html('ایجاد کالکشن');
                        if (typeof Toast !== 'undefined' && Toast) {
                            Toast.fire({
                                icon: 'error',
                                title: 'خطایی رخ داد. لطفا دوباره تلاش کنید.'
                            });
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطایی رخ داد. لطفا دوباره تلاش کنید.'
                            });
                        }
                    }
                })
            } else {
                if (typeof Toast !== 'undefined' && Toast) {
                    Toast.fire({
                        icon: 'error',
                        title: 'نام کالکشن نمیتواند خالی باشد.',
                    })
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'نام کالکشن را وارد کنید'
                    });
                }
            }
        })

        /**
         * Delete Collection Modal
         */
        let currentCollectionId = null;
        let currentCollectionData = null;

        $('.delete-collection-button').on('click', function(e) {
            e.preventDefault();

            // Get current active collection data
            let activeTab = $('button[data-tab].max-lg\\:bg-primary-500, button[data-tab]:not(.text-\\[\\#889BAD\\])').first();
            if (activeTab.length === 0) {
                activeTab = $('button[data-tab]').first();
            }

            if (activeTab.length > 0) {
                let tabId = activeTab.data('tab');
                let collectionTitle = activeTab.text().trim();

                currentCollectionId = tabId;
                currentCollectionData = {
                    id: currentCollectionId,
                    title: collectionTitle,
                    type: 'unknown' // We'll get this from the server response
                };

                $('.delete-collection-modal').removeClass('hidden');
            }
        });

        // Close Delete Collection Modal
        $('.delete-collection-modal .fixed.bg-black\\/40, #cancel-delete-collection').on('click', function() {
            $('.delete-collection-modal').addClass('hidden');
            currentCollectionId = null;
            currentCollectionData = null;
        });

        // Confirm Delete Collection
        $('#confirm-delete-collection').on('click', function(e) {
            e.preventDefault();

            if (!currentCollectionId) {
                return;
            }

            let $this = $(this);

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                    'callback': 'panel_collection_delete',
                    'collection_id': currentCollectionId
                },
                beforeSend: function() {
                    $this.html('<div class="spinner" style="border-color: #FFFFFF; border-width: 3px; width: 20px; margin-inline: auto"></div>');
                },
                success: function(data) {
                    $this.html('حذف کالکشن');

                    if (data.success) {
                        // Send data to Zabaline
                        if (typeof zebline !== 'undefined' && zebline.event) {
                            let zeblineData = {
                                "collection_id": data.data.collection_id,
                                "collection_title": data.data.collection_title,
                                "collection_type": data.data.collection_type,
                                "user_id": data.data.user_id,
                                "timestamp": data.data.timestamp,
                                "items_count": data.data.items_count,
                                "current_page": window.location.href,
                                "action": "delete"
                            };
                            zebline.event.track("collection_action", zeblineData);
                        }

                        // Show success message
                        let successMessage = 'کالکشن با موفقیت حذف شد';
                        if (data.data && typeof data.data === 'object') {
                            successMessage = data.data.message || 'کالکشن با موفقیت حذف شد';
                        } else if (data.data && typeof data.data === 'string') {
                            successMessage = data.data;
                        }

                        if (typeof Toast !== 'undefined' && Toast) {
                            Toast.fire({
                                icon: 'success',
                                title: successMessage
                            });
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: successMessage,
                                timer: 2000
                            });
                        }

                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        let errorMessage = 'خطایی رخ داد';
                        if (data.data && typeof data.data === 'object') {
                            errorMessage = data.data.message || 'خطایی رخ داد';
                        } else if (data.data && typeof data.data === 'string') {
                            errorMessage = data.data;
                        }

                        if (typeof Toast !== 'undefined' && Toast) {
                            Toast.fire({
                                icon: 'error',
                                title: errorMessage
                            });
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: errorMessage
                            });
                        }
                    }

                    $('.delete-collection-modal').addClass('hidden');
                },
                error: function() {
                    $this.html('حذف کالکشن');
                    $('.delete-collection-modal').addClass('hidden');

                    if (typeof Toast !== 'undefined' && Toast) {
                        Toast.fire({
                            icon: 'error',
                            title: 'خطایی رخ داد. لطفا دوباره تلاش کنید.'
                        });
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطایی رخ داد. لطفا دوباره تلاش کنید.'
                        });
                    }
                }
            });
        });
    })
</script>