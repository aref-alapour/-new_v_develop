<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>جست و جو بر روی نقشه - اسکیپ زوم</title>
    <?php wp_head(); ?>
    <style>
        .leaflet-top.leaflet-right > .leaflet-control.leaflet-bar.geoapify-leaflet-control > form.geoapify-form > input.geoapify-address-input {
            width: 100%;
            height: 40px;
        }
        @media screen and (max-width: 768px) {
            .leaflet-top.leaflet-right{
                top: 90px;
                width: 100%;
                left: 0;
                right: 0;
            }
            .leaflet-top.leaflet-right > .leaflet-control.leaflet-bar.geoapify-leaflet-control {
                float: unset;
                max-width: 100%;
                margin-left: 20px;
                margin-right: 20px;
            }
            .leaflet-top.leaflet-right > .leaflet-control.leaflet-bar.geoapify-leaflet-control > form.geoapify-form {
                width: 100%;
                height: 40px;
            }
        }
    </style>
</head>

<body>
<?php if (wp_is_mobile()): ?>
    <div class="flex">
        <nav class="px-4 fixed top-0 w-screen z-[600]">
            <div class="">
                <div class="mobile-navbar my-5 flex items-center justify-between rounded-lg bg-white px-5.5 py-3.5 text-slate-800 shadow-13 ring-1 ring-inset ring-gray-100 transition-all">

                     

                        <a href="<?= home_url() ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="113" fill="none" viewBox="0 0 113 29" class="w-40 2xl:w-44">
                                <path class="fill-primary-500" fill-rule="evenodd" d="M110.388 23.144c-.991 0-1.771-.832-1.771-1.81V7.091c0-.98.781-1.811 1.771-1.811.99 0 1.77.832 1.77 1.811v14.243c0 .979-.78 1.81-1.77 1.81Zm-5.235 0H90.997c-.035 0-.069 0-.097-.002h-.005a6.754 6.754 0 0 1-4.632-2.029c-3.211 2.644-9.819 3.029-12.737-.207a6.753 6.753 0 0 1-5.029 2.238h-.034a6.753 6.753 0 0 1-5.029-2.238 6.757 6.757 0 0 1-5.03 2.238H46.643c-.962 0-1.77-.771-1.77-1.741 0-.97.808-1.742 1.77-1.742h11.761a3.289 3.289 0 0 0 3.288-3.288V13.94l.001-.049v-.019l.001-.014.002-.035.001-.014.002-.03.002-.011.001-.012a.29.29 0 0 1 .004-.031l.002-.015.004-.033.002-.012.005-.028a1.72 1.72 0 0 1 1.468-1.396l.047-.005.018-.002.026-.002.027-.001.037-.002h.006c.014-.001.028-.002.037-.001l.02-.001h.058l.042.001.032.001.008.001a.434.434 0 0 1 .054.003l.013.001.022.002.011.001a.236.236 0 0 0 .021.002l.014.002a1.72 1.72 0 0 1 1.468 1.397l.007.041.006.042.004.028.001.012a.31.31 0 0 1 .003.034l.002.025.001.01v.01l.001.016.001.01v.009l.001.026v2.472a3.289 3.289 0 0 0 3.288 3.288h.034a3.289 3.289 0 0 0 3.288-3.288v-2.491l.001-.01.001-.014.002-.035v-.014l.003-.03.001-.011.002-.012.003-.031.002-.015.005-.033.002-.012.005-.028a1.72 1.72 0 0 1 1.468-1.396l.047-.005.018-.002.025-.002.028-.001.036-.002h.007a.233.233 0 0 1 .036-.001l.02-.001h.059l.042.001.032.001.007.001a.459.459 0 0 1 .054.003l.014.001.022.002.011.001a.206.206 0 0 0 .021.002 1.72 1.72 0 0 1 1.482 1.399l.007.041.006.042.003.028.002.012a.31.31 0 0 1 .003.034l.002.025.001.01v.01l.001.016v.01l.001.009v.026l.001.014v2.458c0 4.429 6.896 3.85 9.115 1.631a5.362 5.362 0 0 0-3.774-9.155h-.021l-.023.001a5.342 5.342 0 0 0-3.767 1.57.563.563 0 0 1-.146.106 1.764 1.764 0 0 1-2.483-2.483.581.581 0 0 1 .105-.147L81.668.503a1.788 1.788 0 0 1 2.524 0 1.79 1.79 0 0 1 0 2.525l-1.471 1.471-.007.006-.863.864a8.938 8.938 0 0 1 6.643 13.01 3.276 3.276 0 0 0 2.177 1.255 1.79 1.79 0 0 1 .326-.031h14.156c.974 0 1.77.798 1.77 1.771 0 .974-.796 1.77-1.77 1.77Zm-63.937-13.13a1.952 1.952 0 1 1 0-3.904 1.952 1.952 0 0 1 0 3.904Zm-15.893 4.912-1.054-.188.094-.527a4.242 4.242 0 0 1 4.175-3.491h.535l-3.656 3.679-.094.527Zm7.077 3.871.006-.005a5.444 5.444 0 0 0-3.868-9.275 5.444 5.444 0 1 0 1.47 10.688l.078-.022 2.095 3.628h-3.643c-4.888 0-8.852-3.963-8.852-8.85a8.852 8.852 0 0 1 17.702 0v12.79l-5.103-8.839.115-.115ZM9.162 23.811a8.821 8.821 0 0 1-5.443-1.871v5c0 .938-.766 1.704-1.704 1.704A1.707 1.707 0 0 1 .312 26.94V14.961a8.852 8.852 0 0 1 17.702 0c0 4.887-3.965 8.85-8.852 8.85Zm0-14.294a5.444 5.444 0 0 0-3.868 9.275l.03.03.002.002a5.444 5.444 0 1 0 3.836-9.307Zm32.054 1.348c.938 0 1.703.766 1.703 1.703v14.371c0 .937-.765 1.703-1.703 1.703a1.706 1.706 0 0 1-1.703-1.703V12.568c0-.938.765-1.703 1.703-1.703Z" clip-rule="evenodd"></path>
                                <path fill-rule="evenodd" d="M47.352 24.8c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm4.204 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm4.204 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm10.511 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.921-2.052 2.052-2.052Zm4.203 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Z" clip-rule="evenodd" class="fill-slate-800"></path>
                            </svg>
                        </a>

                        <a href="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ) ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="27" viewBox="0 0 24 27" fill="none">
                                <circle cx="11.9992" cy="7.19844" r="6.3" fill="#09192D"/>
                                <ellipse cx="12.0008" cy="21.1969" rx="11.2" ry="4.9" fill="#09192D"/>
                            </svg>
                        </a>

                    </div>
            </div>
        </nav>

        <div class="px-4 fixed bottom-5 w-screen z-[600]">
            <div class="bg-white px-8 py-2 rounded-lg w-full shadow-13 border flex">
                <button type="button" class="w-full flex items-center justify-between gap-8" id="open-rooms">
                    لیست بازی ها
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"
                         class="m-0">
                        <rect y="1" width="12" height="3" rx="1.5" fill="black"/>
                        <rect x="14" width="5" height="5" rx="2.5" fill="black"/>
                        <rect y="10" width="12" height="3" rx="1.5" fill="black"/>
                        <rect x="14" y="9" width="5" height="5" rx="2.5" fill="black"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="modals">
            <div class="overlay fixed w-screen h-screen z-[500] backdrop-blur-sm invisible opacity-0 transition-all duration-300"></div>
            <div id="rooms"
                 class="max-h-[calc(100vh-235px)] w-[calc(100vw-2rem)] fixed bottom-[80px] shadow-13 z-[600] scale-75 opacity-0 pointer-events-none overflow-y-auto overflow-x-hidden rounded-xl no-scrollbar mx-4 transition-all duration-300">
                <div class="bg-white p-4">
                    <div id="results" class="grid grid-cols-1 gap-4"></div>
                </div>
            </div>
           <div id="filters"
                 class="max-h-[calc(100vh-235px)] w-[calc(100vw-2rem)] fixed bottom-[80px] shadow-13 z-[600] scale-75 opacity-0 pointer-events-none overflow-y-auto overflow-x-hidden rounded-xl no-scrollbar mx-4 transition-all duration-300">
                <div class="bg-white p-4">
                    <form action="#" method="post">
                        <div class="flex flex-col gap-4 justify-between">
                            <select name="" class="select-box rounded-xl pl-12 w-auto">
                                <option>نوع سرگرمی</option>
                                <option value="">اتاق فرار</option>
                                <option value="">لیزرتگ</option>
                                <option value="">سینما ترس</option>
                            </select>
                            <select name="" class="select-box rounded-xl pl-12 w-auto">
                                <option>رده سنی</option>
                            </select>
                            <select name="" class="select-box rounded-xl pl-12 w-auto">
                                <option>تعداد نفرات</option>
                            </select>
                            <button type="submit"
                                    class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-[#ccc] disabled:cursor-not-allowed disabled:shadow-none bg-primaryColor text-white shadow-13 h-16 min-w-16 px-9 py-2 rounded-xl w-full">
                                <span class="truncate">اعمال فیلتر</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="map" class="grow h-screen w-screen"></div>

    </div>
<?php else: ?>
    <div class="flex">
        <div class="panel w-1/2 max-h-screen overflow-y-auto no-scrollbar">
            <div class="p-8 flex flex-col gap-8">
                <div class="flex justify-between items-center">
                    <a class="flex items-center gap-4.5" href="/">
                        <svg xmlns="http://www.w3.org/2000/svg" width="113" fill="none" viewBox="0 0 113 29"
                             class="w-40 2xl:w-44 m-0">
                            <path class="fill-primary-500" fill-rule="evenodd"
                                  d="M110.388 23.144c-.991 0-1.771-.832-1.771-1.81V7.091c0-.98.781-1.811 1.771-1.811.99 0 1.77.832 1.77 1.811v14.243c0 .979-.78 1.81-1.77 1.81Zm-5.235 0H90.997c-.035 0-.069 0-.097-.002h-.005a6.754 6.754 0 0 1-4.632-2.029c-3.211 2.644-9.819 3.029-12.737-.207a6.753 6.753 0 0 1-5.029 2.238h-.034a6.753 6.753 0 0 1-5.029-2.238 6.757 6.757 0 0 1-5.03 2.238H46.643c-.962 0-1.77-.771-1.77-1.741 0-.97.808-1.742 1.77-1.742h11.761a3.289 3.289 0 0 0 3.288-3.288V13.94l.001-.049v-.019l.001-.014.002-.035.001-.014.002-.03.002-.011.001-.012a.29.29 0 0 1 .004-.031l.002-.015.004-.033.002-.012.005-.028a1.72 1.72 0 0 1 1.468-1.396l.047-.005.018-.002.026-.002.027-.001.037-.002h.006c.014-.001.028-.002.037-.001l.02-.001h.058l.042.001.032.001.008.001a.434.434 0 0 1 .054.003l.013.001.022.002.011.001a.236.236 0 0 0 .021.002l.014.002a1.72 1.72 0 0 1 1.468 1.397l.007.041.006.042.004.028.001.012a.31.31 0 0 1 .003.034l.002.025.001.01v.01l.001.016.001.01v.009l.001.026v2.472a3.289 3.289 0 0 0 3.288 3.288h.034a3.289 3.289 0 0 0 3.288-3.288v-2.491l.001-.01.001-.014.002-.035v-.014l.003-.03.001-.011.002-.012.003-.031.002-.015.005-.033.002-.012.005-.028a1.72 1.72 0 0 1 1.468-1.396l.047-.005.018-.002.025-.002.028-.001.036-.002h.007a.233.233 0 0 1 .036-.001l.02-.001h.059l.042.001.032.001.007.001a.459.459 0 0 1 .054.003l.014.001.022.002.011.001a.206.206 0 0 0 .021.002 1.72 1.72 0 0 1 1.482 1.399l.007.041.006.042.003.028.002.012a.31.31 0 0 1 .003.034l.002.025.001.01v.01l.001.016v.01l.001.009v.026l.001.014v2.458c0 4.429 6.896 3.85 9.115 1.631a5.362 5.362 0 0 0-3.774-9.155h-.021l-.023.001a5.342 5.342 0 0 0-3.767 1.57.563.563 0 0 1-.146.106 1.764 1.764 0 0 1-2.483-2.483.581.581 0 0 1 .105-.147L81.668.503a1.788 1.788 0 0 1 2.524 0 1.79 1.79 0 0 1 0 2.525l-1.471 1.471-.007.006-.863.864a8.938 8.938 0 0 1 6.643 13.01 3.276 3.276 0 0 0 2.177 1.255 1.79 1.79 0 0 1 .326-.031h14.156c.974 0 1.77.798 1.77 1.771 0 .974-.796 1.77-1.77 1.77Zm-63.937-13.13a1.952 1.952 0 1 1 0-3.904 1.952 1.952 0 0 1 0 3.904Zm-15.893 4.912-1.054-.188.094-.527a4.242 4.242 0 0 1 4.175-3.491h.535l-3.656 3.679-.094.527Zm7.077 3.871.006-.005a5.444 5.444 0 0 0-3.868-9.275 5.444 5.444 0 1 0 1.47 10.688l.078-.022 2.095 3.628h-3.643c-4.888 0-8.852-3.963-8.852-8.85a8.852 8.852 0 0 1 17.702 0v12.79l-5.103-8.839.115-.115ZM9.162 23.811a8.821 8.821 0 0 1-5.443-1.871v5c0 .938-.766 1.704-1.704 1.704A1.707 1.707 0 0 1 .312 26.94V14.961a8.852 8.852 0 0 1 17.702 0c0 4.887-3.965 8.85-8.852 8.85Zm0-14.294a5.444 5.444 0 0 0-3.868 9.275l.03.03.002.002a5.444 5.444 0 1 0 3.836-9.307Zm32.054 1.348c.938 0 1.703.766 1.703 1.703v14.371c0 .937-.765 1.703-1.703 1.703a1.706 1.706 0 0 1-1.703-1.703V12.568c0-.938.765-1.703 1.703-1.703Z"
                                  clip-rule="evenodd"></path>
                            <path fill-rule="evenodd"
                                  d="M47.352 24.8c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm4.204 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm4.204 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm10.511 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.921-2.052 2.052-2.052Zm4.203 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Z"
                                  clip-rule="evenodd" class="fill-slate-800"></path>
                        </svg>
                    </a>
                    <div class="flex items-center flex-col-reverse justify-between">
                        <div class="text-gray-600">پشتیبانی شبانه روزی</div>
                        <div class="flex items-center">
                            <a href="tel:02191307900" class="mx-3 text-lg leading-4" dir="ltr">02191307900</a>
                            <svg width="16" height="16" class="-mt-1" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 16 16" fill="none">
                                <path d="M10.4001 9.68149C8.80348 11.3615 4.73094 7.32546 6.33356 5.63878C7.31219 4.60877 6.20689 3.43209 5.59491 2.56608C4.44628 0.942732 1.92568 3.18409 2.00168 4.6101C2.24367 9.10748 7.1082 14.4369 11.8187 13.9715C13.292 13.8262 14.9853 11.1648 13.2947 10.1922C12.45 9.70549 11.2894 8.74548 10.4001 9.68082M9.33347 2.00008C10.5711 2.00008 11.7581 2.49175 12.6332 3.36692C13.5083 4.2421 14 5.4291 14 6.66679M9.33347 4.66677C9.86389 4.66677 10.3726 4.87748 10.7476 5.25256C11.1227 5.62764 11.3334 6.13635 11.3334 6.66679"
                                      stroke="#09192D" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div id="results" class="grid grid-cols-2 gap-4"></div>
            </div>
        </div>
        <div class="modals">
            <div class="overlay fixed w-screen h-screen z-[500] backdrop-blur-sm invisible opacity-0 transition-all duration-300"></div>
            <div id="rooms"
                 class="max-h-[calc(100vh-235px)] w-[calc(100vw-2rem)] fixed bottom-[80px] shadow-13 z-[600] scale-75 opacity-0 pointer-events-none overflow-y-auto overflow-x-hidden rounded-xl no-scrollbar mx-4 transition-all duration-300">
                <div class="bg-white p-4">
                    <div id="results" class="grid grid-cols-1 gap-4"></div>
                </div>
            </div>
            <div id="filters"
                 class="max-h-[calc(100vh-235px)] w-[calc(100vw-2rem)] fixed bottom-[80px] shadow-13 z-[600] scale-75 opacity-0 pointer-events-none overflow-y-auto overflow-x-hidden rounded-xl no-scrollbar mx-4 transition-all duration-300">
                <div class="bg-white p-4">
                    <form action="#" method="post">
                        <div class="flex flex-col gap-4 justify-between">
                            <select name="" class="select-box rounded-xl pl-12 w-auto">
                                <option>نوع سرگرمی</option>
                                <option value="">اتاق فرار</option>
                                <option value="">لیزرتگ</option>
                                <option value="">سینما ترس</option>
                            </select>
                            <select name="" class="select-box rounded-xl pl-12 w-auto">
                                <option>رده سنی</option>
                            </select>
                            <select name="" class="select-box rounded-xl pl-12 w-auto">
                                <option>تعداد نفرات</option>
                            </select>
                            <button type="submit"
                                    class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-[#ccc] disabled:cursor-not-allowed disabled:shadow-none bg-primaryColor text-white shadow-13 h-16 min-w-16 px-9 py-2 rounded-xl w-full">
                                <span class="truncate">اعمال فیلتر</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="map" class="grow h-screen"></div>
    </div>
<?php endif; ?>
<?php wp_footer(); ?>
<script>
    jQuery(document).ready(function ($) {
        let [lat, lon] = [35.7219, 51.3347]
        var marker = null;
        let mapZoom = 2;
        let map = L.map('map').setView({lon, lat}, mapZoom);
        let primaryIcon = L.icon({
            iconUrl: "<?= Theme_URL ?>assets/images/escapezoom-marker-icon.png",
            iconSize: [28, 34]
        });
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            minZoom: 11,
            center: [lat, lon],
        }).addTo(map);
        let loadProduct = function () {
            // get view Bound information
            let northLine = map.getBounds().getNorth(); // North Line
            let westLine = map.getBounds().getWest(); // West Line
            let southLine = map.getBounds().getSouth(); // South Line
            let eastLine = map.getBounds().getEast(); // East Line
            $.ajax({
                type: 'POST',
                url: '"/wp-admin/admin-ajax.php?action=v2_ajax_handler"',
                data: {
                    "type": "sort_products_get",
                    "data": {
                        "source": "map_search",
                        "params": {
                            "bounds": {
                                "sw": {
                                    "lat": southLine,
                                    "lng": westLine
                                },
                                "ne": {
                                    "lat": northLine,
                                    "lng": eastLine
                                }
                            }
                        }
                    }
                },
                dataType: "json",
                success: function (data) {
                    if (data.products) {
                        $('#results').empty()
                        $('.leaflet-pane.leaflet-overlay-pane').empty()
                        $('.leaflet-pane.leaflet-shadow-pane').empty()
                        $('.leaflet-pane.leaflet-marker-pane').empty()
                        $('.leaflet-pane.leaflet-popup-pane').empty()
                        data.products.forEach(element => {
                            const titles = element.genres.map(genre => genre.title).join(', ');
                            const geoString = element.geo;
                            const geoArray = geoString.split(',').map(Number);
                            let resultItem = `
                                    <a href="<?= home_url('/room/') ?>${element.url}" class="flex p-3 gap-3 border shadow-13 rounded-xl">
                                        <div>
                                            <img src="${element.image}" alt="${element.url}" class="rounded-xl w-[70px]">
                                        </div>
                                        <div class="flex flex-col grow justify-between">
                                            <div class="flex justify-between">
                                                <h3 class="text-xl line-clamp-1">${element.title}</h3>
                                                <span class="text-gray-600">از ${(element.price).toLocaleString()} تومان</span>
                                            </div>
                                            <div class="flex justify-between items-center gap-x-6">
                                                <div class="flex flex-col leading-3 w-fit grow">
                                                    <span class="mb-3 after:border-b after:grow flex items-center gap-2 text-gray-600">ژانــر
                                                        بــازی</span>
                                                    <p class="line-clamp-1"><span>${titles}</span></p>
                                                </div>
                                                <div class="bg-[#EFC101] leading-5 px-2 rounded" dir="ltr">
                                                    <span class="text-lg">${element.rate}</span>/5
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                `;
                            $('#results').append(resultItem);
                            // show and pin markers
                            marker = L.marker({lat: geoArray[0], lon: geoArray[1]}, {icon: primaryIcon})
                                .bindPopup(`<div class="flex gap-4 p-1">
                                                <div>
                                                    <a href="<?= home_url('/room/') ?>${element.url}">
                                                    <img src="${element.image}" alt="${element.title}" class="rounded-xl w-[70px]">
                                                    </a>
                                                </div>
                                                <div class="flex flex-col grow justify-between items-start">
                                                <h3 class="text-xl"><a href="<?= home_url('/room/') ?>${element.url}">${element.title}</a></h3>
                                                <div>${titles}</div>
                                                <div class="flex justify-between w-full">
                                                <span class="border border-accent-550 text-accent-550 px-3 py-1 rounded-lg">${element.free_sanses} سانس موجود</span>
                                                <a href="<?= home_url('/room/') ?>${element.url}" class="bg-accent-550 !text-white px-3 py-1 rounded-lg flex items-center justify-content"> مشاهده</a>
                                                </div>
                                                </div>
                                            </div>`, {
                                    maxWidth: 320,
                                })
                                .addTo(map);
                        });
                    } else {
                        $('#results').empty().append('سرگرمی یافت نشد')
                    }
                },
            });
        }
        L.Control.ExpandButton = L.Control.extend({
            options: {position: 'topleft'},
            onAdd: map => {
                let container = L.DomUtil.create('div', 'leaflet-bar leaflet-control max-md:hidden lg:flex lg:items-center lg:justify-center p-0')
                let button = L.DomUtil.create('a', 'leaflet-control-button p-1', container)
                let icon = L.DomUtil.create('img', '', button)
                icon.src = '<?= Theme_URL ?>assets/images/icon-expand.png'

                let expanded = false
                L.DomEvent.disableClickPropagation(button)
                L.DomEvent.on(button, 'click', function () {
                    let panel = document.querySelector('.panel')

                    if (!expanded) {
                        panel.style.width = 0
                        panel.style.flexGrow = 0
                    } else {
                        panel.removeAttribute('style')
                    }

                    expanded = !expanded

                    map.invalidateSize()
                })

                container.title = "Title"

                return container
            },
        })

        let myAPIKey = "24703b4d6e484a2a9a3a581c635667d5";
        var mapURL = L.Browser.retina
            ? `https://maps.geoapify.com/v1/tile/{mapStyle}/{z}/{x}/{y}.png?apiKey={apiKey}`
            : `https://maps.geoapify.com/v1/tile/{mapStyle}/{z}/{x}/{y}@2x.png?apiKey={apiKey}`;

        // Add map tiles layer. Set 20 as the maximal zoom and provide map data attribution.
        L.tileLayer(mapURL, {
            attribution: 'طراحی و اجرا: آرتین فناوران برخط',
            apiKey: myAPIKey,
            mapStyle: "osm-bright-smooth", // More map styles on https://apidocs.geoapify.com/docs/maps/map-tiles/
            maxZoom: 20
        }).addTo(map);

        const addressSearchControl = L.control.addressSearch(myAPIKey, {
            position: 'topright',
            placeholder: "جست و جو : تهران سعادت آباد",
            lang: 'fa',
            resultCallback: (address) => {

                if (!address) {
                    return;
                }

                marker = L.marker([address.lat, address.lon]).addTo(map);
                if (address.bbox && address.bbox.lat1 !== address.bbox.lat2 && address.bbox.lon1 !== address.bbox.lon2) {
                    map.fitBounds([[address.bbox.lat1, address.bbox.lon1], [address.bbox.lat2, address.bbox.lon2]], { padding: [100, 100] })
                } else {
                    map.setView([address.lat, address.lon], 15);
                }
            },
            suggestionsCallback: (suggestions) => {
;
            }
        });
        map.addControl(addressSearchControl);
        var controls = [
            new L.Control.ExpandButton()
        ]
        controls.forEach(control => control.addTo(map));

        let opened = false
        let rooms = document.getElementById("rooms")
        let overlay = document.querySelector('.overlay')

        $('#open-rooms').on('click', function () {
            if (filters.classList.contains('scale-100')) {
                opened = false
            }

            if (!opened) {
                rooms.classList.remove('scale-75', 'opacity-0', 'pointer-events-none')
                rooms.classList.add('scale-100', 'opacity-100', 'pointer-events-auto')
                overlay.classList.remove('invisible', 'opacity-0')
            } else {
                rooms.classList.add('scale-75', 'opacity-0', 'pointer-events-none')
                rooms.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto')
                overlay.classList.add('invisible', 'opacity-0')
            }

            filters.classList.add('scale-75', 'opacity-0', 'pointer-events-none')
            filters.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto')
            opened = !opened
        })
        overlay.addEventListener('click', () => {
            opened = false
            rooms.classList.add('scale-75', 'opacity-0', 'pointer-events-none')
            rooms.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto')
            overlay.classList.add('invisible', 'opacity-0')
        })
        map.on('zoomend', function() {
            loadProduct()
        });
        map.on('moveend', function() {
            loadProduct()
        });
        loadProduct()
    });
</script>
</body>
</html>