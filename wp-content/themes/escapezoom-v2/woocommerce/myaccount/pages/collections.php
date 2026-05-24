<?php
global $wpdb;

// Current User
$user = wp_get_current_user();

// Collection ID
$collection_ID = null;

// Page
$page = 1;

// Show Items Per Page
$items_per_page = 3;

$query = "SELECT * FROM collections WHERE user_id LIKE $user->ID";
if ($collection_ID) {
    $query .= " AND ID LIKE $collection_ID";
}

$prepare = $wpdb->prepare($query);

$collections = $wpdb->get_results($prepare);

$tabs = [];
foreach ($collections as $collection) {
    $tabs[$collection->ID] = $collection->title;
}
?>


<div class="lg:col-span-8 2xl:col-span-9">
    <section class="rounded-2xl border border-slate-120 px-8 shadow-12 max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none py-12 max-lg:border-0 max-lg:py-0">
        <div class="md:mb-8 mb-0 lg:mb-8 max-lg:border-b max-lg:border-slate-120 max-lg:pb-6">
            <div class="grid grid-cols-2 gap-4 lg:flex">
                <div class="items-center gap-6 order-1 md:flex">
                    <h2 class="flex items-center gap-4">
                        <span class="text-base font-bold md:text-lg">
                            <span class="text-xl">کالکشن‌های من</span>
                        </span>
                    </h2>
                    <div class="hidden md:block"></div>
                </div>

                <div class="space-x-6 grow order-3 lg:order-2 col-span-2 space-x-reverse overflow-hidden flex max-lg:h-12.5 max-lg:rounded-[10px] max-lg:border max-lg:border-[#E4EBF0]">
                    <button type="button" data-status="collection-1" class="grow lg:grow-0 max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500">
                        لیزرتگ
                    </button>
                    <button type="button" data-status="collection-2" class="grow lg:grow-0 text-[#889BAD]">
                        اتاق های خوفناک
                    </button>
                    <button type="button" data-status="collection-3" class="grow lg:grow-0 text-[#889BAD]">
                        سینما ترس
                    </button>
                </div>

                <div class="flex items-center justify-end order-2 lg:order-3 gap-6">

                    <div class="add-collection-modal hidden">
                        <div class="fixed bg-black/40 z-40 w-full h-full right-0 top-0"></div>
                        <div class="fixed bg-white w-[430px] max-w-[100%] h-auto right-[50%] translate-x-[50%] top-[50%] -translate-y-[50%] p-6 rounded-xl border z-50 flex flex-col gap-4">

                            <div class="border rounded-xl flex overflow-hidden">
                                <button type="button" class="category-selector grow p-2 bg-primary-500 text-white">
                                    لیزرتگ
                                </button>
                                <button type="button" class="category-selector grow p-2 text-[#889BAD]">
                                    اتاق های خوفناک
                                </button>
                                <button type="button" class="category-selector grow p-2 text-[#889BAD]">
                                    سینما ترس
                                </button>
                            </div>

                            <input class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl" placeholder="اسم کالکشن را وارد کنید" type="text">

                            <button type="button" class="bg-primaryColor p-4 text-white rounded-2xl shadow-primary-3 shadow-13">
                                ایجاد کالکشن
                            </button>
                        </div>
                    </div>

                    <a href="#" id="add-collection-button">
                        <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs">
                            ایجاد لیست جدید
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
        </div>
        <div class="relative">
            <div class="lg:mb-5 lg:grid lg:grid-cols-2 lg:items-center lg:gap-x-24">
                <div>
                    <div class="mt-8 flex justify-between lg:justify-start lg:gap-x-8">
                        <h4> مجموع بازی های اتاق های فرار خفناک من</h4>
                        <div class="flex items-center gap-2.5 text-xs flex-col-reverse max-lg:items-end lg:flex-row-reverse">
                            <button type="button" role="switch" aria-checked="false" data-state="unchecked" value="on" class="focus-visible:ring-ring p- peer inline-flex shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent shadow-none transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary-500 data-[state=unchecked]:bg-slate-120 h-5 w-9" id="switch_undefined">
                                <span data-state="unchecked" class="pointer-events-none block rounded-full bg-background shadow-lg ring-0 transition-transform h-4 w-4 data-[state=checked]:-translate-x-4 data-[state=unchecked]:translate-x-0"></span>
                            </button>
                            <label for="switch_undefined">نمایش در پروفایل</label>
                        </div>
                    </div>
                    <div class="inline-flex w-auto items-center justify-start text-2xs max-lg:border-t max-lg:pt-1 lg:mt-3">
                        <span class="text-secondary-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="18" viewBox="0 0 24 24" height="18">
                                <path fill="currentColor" fill-rule="evenodd" d="M15.85 2.5c.63 0 1.26.09 1.86.29 3.69 1.2 5.02 5.25 3.91 8.79a12.728 12.728 0 0 1-3.01 4.81 38.456 38.456 0 0 1-6.33 4.96l-.25.15-.26-.16a38.093 38.093 0 0 1-6.37-4.96 12.933 12.933 0 0 1-3.01-4.8c-1.13-3.54.2-7.59 3.93-8.81.29-.1.59-.17.89-.21h.12c.28-.04.56-.06.84-.06h.11c.63.02 1.24.13 1.83.33h.06c.04.02.07.04.09.06.22.07.43.15.63.26l.38.17c.092.05.195.125.284.19.056.04.107.077.146.1l.05.03c.085.05.175.102.25.16a6.263 6.263 0 0 1 3.85-1.3Zm2.66 7.2c.41-.01.76-.34.79-.76v-.12a3.3 3.3 0 0 0-2.11-3.16.8.8 0 0 0-1.01.5c-.14.42.08.88.5 1.03.64.24 1.07.87 1.07 1.57v.03a.86.86 0 0 0 .19.62c.14.17.35.27.57.29Z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                        <span class="w-full">
                            <span class="ml-2 mr-3 text-lg">0</span>
                            نفر پسندیدند
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-x-4 mb-4 lg:mb-0 rounded-[10px] border border-[#E8EDF1] px-6 py-3 shadow-13 relative">
                    <span>
                        <svg class="mx-0 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
                            <rect y="0.5" width="18" height="18" rx="4" fill="#FD7013"></rect>
                            <g filter="url(#filter0_d_9708_739)">
                                <path d="M4.62609 9.5C4.62619 9.33739 4.69083 9.18146 4.80582 9.06648C4.9208 8.95149 5.07673 8.88685 5.23934 8.88675L8.38603 8.88617L8.38661 5.73948C8.38661 5.65894 8.40247 5.5792 8.43329 5.5048C8.46411 5.43039 8.50928 5.36279 8.56623 5.30584C8.62317 5.2489 8.69078 5.20372 8.76518 5.1729C8.83959 5.14209 8.91933 5.12622 8.99987 5.12622C9.0804 5.12622 9.16014 5.14209 9.23455 5.1729C9.30895 5.20372 9.37656 5.2489 9.4335 5.30584C9.49045 5.36279 9.53562 5.43039 9.56644 5.5048C9.59726 5.5792 9.61312 5.65894 9.61312 5.73948L9.6137 8.88617L12.7604 8.88675C12.923 8.88675 13.079 8.95136 13.194 9.06636C13.309 9.18137 13.3736 9.33735 13.3736 9.5C13.3736 9.66265 13.309 9.81863 13.194 9.93364C13.079 10.0486 12.923 10.1133 12.7604 10.1133L9.6137 10.1138L9.61312 13.2605C9.61312 13.4232 9.54851 13.5792 9.4335 13.6942C9.31849 13.8092 9.16251 13.8738 8.99987 13.8738C8.83722 13.8738 8.68124 13.8092 8.56623 13.6942C8.45122 13.5792 8.38661 13.4232 8.38661 13.2605L8.38603 10.1138L5.23934 10.1133C5.07673 10.1132 4.9208 10.0485 4.80582 9.93352C4.69083 9.81854 4.62619 9.66261 4.62609 9.5Z" fill="white"></path>
                                <path d="M4.62609 9.5C4.62619 9.33739 4.69083 9.18146 4.80582 9.06648C4.9208 8.95149 5.07673 8.88685 5.23934 8.88675L8.38603 8.88617L8.38661 5.73948C8.38661 5.65894 8.40247 5.5792 8.43329 5.5048C8.46411 5.43039 8.50928 5.36279 8.56623 5.30584C8.62317 5.2489 8.69078 5.20372 8.76518 5.1729C8.83959 5.14209 8.91933 5.12622 8.99987 5.12622C9.0804 5.12622 9.16014 5.14209 9.23455 5.1729C9.30895 5.20372 9.37656 5.2489 9.4335 5.30584C9.49045 5.36279 9.53562 5.43039 9.56644 5.5048C9.59726 5.5792 9.61312 5.65894 9.61312 5.73948L9.6137 8.88617L12.7604 8.88675C12.923 8.88675 13.079 8.95136 13.194 9.06636C13.309 9.18137 13.3736 9.33735 13.3736 9.5C13.3736 9.66265 13.309 9.81863 13.194 9.93364C13.079 10.0486 12.923 10.1133 12.7604 10.1133L9.6137 10.1138L9.61312 13.2605C9.61312 13.4232 9.54851 13.5792 9.4335 13.6942C9.31849 13.8092 9.16251 13.8738 8.99987 13.8738C8.83722 13.8738 8.68124 13.8092 8.56623 13.6942C8.45122 13.5792 8.38661 13.4232 8.38661 13.2605L8.38603 10.1138L5.23934 10.1133C5.07673 10.1132 4.9208 10.0485 4.80582 9.93352C4.69083 9.81854 4.62619 9.66261 4.62609 9.5Z" stroke="white"></path>
                            </g>
                            <defs>
                                <filter id="filter0_d_9708_739" x="3.12598" y="4.62598" width="11.748" height="11.748" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"></feColorMatrix>
                                    <feOffset dy="1"></feOffset>
                                    <feGaussianBlur stdDeviation="0.5"></feGaussianBlur>
                                    <feComposite in2="hardAlpha" operator="out"></feComposite>
                                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"></feColorMatrix>
                                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_9708_739"></feBlend>
                                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_9708_739" result="shape"></feBlend>
                                </filter>
                            </defs>
                        </svg>
                    </span>
                    <input placeholder="افزودن بازی جدید به لیست" class="w-full focus:outline-0" type="text" id="search-field">
                    <div id="search-result" class="items-center gap-x-4 rounded-[10px] border border-[#E8EDF1] bg-white px-6 py-3 shadow-13 absolute top-[calc(100%+10px)] right-0 w-full hidden">
                        در حال جست و جو
                    </div>
                </div>
            </div>
            <div id="collection-1" class="collection grid border-slate-120 lg:grid-cols-2 gap-7 lg:pb-8 2xl:grid-cols-3 2xl:gap-5 3xl:gap-10">
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div id="collection-2" class="collection hidden border-slate-120 lg:grid-cols-2 gap-7 lg:pb-8 2xl:grid-cols-3 2xl:gap-5 3xl:gap-10">
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 2</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 2</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 2</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 2</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 2</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 2</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div id="collection-3" class="collection hidden border-slate-120 lg:grid-cols-2 gap-7 lg:pb-8 2xl:grid-cols-3 2xl:gap-5 3xl:gap-10">
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 3</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 3</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 3</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 3</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 3</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-span-1 border rounded-xl">
                    <div class="p-4 flex items-center gap-4">
                        <img src="../assets/images/thumbnail.png" class="w-[62px] rounded-xl">
                        <div>
                            <h3 class="text-2xl font-bold">ایستگاه شهر یخ 3</h3>
                            <div> سعادت آباد<b class="mx-3">.</b>تهران</div>
                        </div>
                    </div>
                    <div class="flex border-t">
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2 border-l">
                            حذف از لیست
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                                <rect x="0.192383" width="18" height="18" rx="4" fill="#232323" />
                                <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white" />
                            </svg>
                        </a>
                        <a href="#" class="grow flex justify-center items-center gap-4 p-2">
                            مشاهده اتاق
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                <rect width="18" height="18" rx="4" fill="#FD7013" />
                                <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white" />
                                <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-4 mt-8 justify-center">
            <a href="#" class="border-[#E8EDF1] h-12 rounded-xl border aspect-square flex items-center justify-center shadow-13 text-lg hover:text-white hover:shadow-none hover:bg-primaryColor duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25">
                    <path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
                </svg>
            </a>
            <a href="#" class="border-[#E8EDF1] h-12 rounded-xl border aspect-square flex items-center justify-center shadow-13 text-lg hover:text-white hover:shadow-none hover:bg-primaryColor duration-300">
                1
            </a>
            <a href="#" class="border-[#E8EDF1] h-12 rounded-xl border aspect-square flex items-center justify-center shadow-13 text-lg hover:text-white hover:shadow-none hover:bg-primaryColor duration-300">
                2
            </a>
            <a href="#" class="border-[#E8EDF1] h-12 rounded-xl aspect-square flex items-center justify-center text-lg text-white bg-primaryColor">
                3
            </a>
            <a href="#" class="border-[#E8EDF1] h-12 rounded-xl border aspect-square flex items-center justify-center shadow-13 text-lg hover:text-white hover:shadow-none hover:bg-primaryColor duration-300">
                4
            </a>
            <a href="#" class="border-[#E8EDF1] h-12 rounded-xl border aspect-square flex items-center justify-center shadow-13 text-lg hover:text-white hover:shadow-none hover:bg-primaryColor duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none">
                    <path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
                </svg>
            </a>
        </div>
    </section>
</div>

<script>
    jQuery(document).ready(function($) {
        let currentCollectionId = null;
        let currentCollectionData = null;

        // Add Collection Modal
        $('#add-collection-button').on('click', function(e) {
            e.preventDefault();
            $('.add-collection-modal').removeClass('hidden');
        });

        // Close Add Collection Modal
        $('.add-collection-modal .fixed.bg-black\\/40').on('click', function() {
            $('.add-collection-modal').addClass('hidden');
        });

        // Category Selector
        $('.category-selector').on('click', function() {
            $('.category-selector').removeClass('bg-primary-500 text-white').addClass('text-[#889BAD]');
            $(this).removeClass('text-[#889BAD]').addClass('bg-primary-500 text-white');
        });

        // Create Collection
        $('.add-collection-modal button:contains("ایجاد کالکشن")').on('click', function(e) {
            e.preventDefault();

            let $this = $(this);
            let name = $('.add-collection-modal input').val();
            let type = $('.category-selector.bg-primary-500').text().trim();

            if (name !== '') {
                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                        'callback': 'panel_collection_add',
                        'data': {
                            name: name,
                            type: type
                        }
                    },
                    beforeSend: function() {
                        $this.html('<div class="spinner" style="border-color: #FFFFFF; border-width: 3px; width: 33px; margin-inline: auto"></div>');
                    },
                    success: function(data) {
                        $this.html('ایجاد کالکشن');

                        if (data.success) {
                            // Send data to Zabaline
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

                            // Show success message
                            let successMessage = 'کالکشن با موفقیت ایجاد شد';
                            if (data.data && typeof data.data === 'object') {
                                successMessage = data.data.message || 'کالکشن با موفقیت ایجاد شد';
                            } else if (data.data && typeof data.data === 'string') {
                                successMessage = data.data;
                            }

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: successMessage,
                                    timer: 2000
                                });
                            } else if (typeof Toast !== 'undefined') {
                                Toast.fire({
                                    icon: 'success',
                                    title: successMessage
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

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: errorMessage
                                });
                            } else if (typeof Toast !== 'undefined') {
                                Toast.fire({
                                    icon: 'error',
                                    title: errorMessage
                                });
                            }
                        }
                    },
                    error: function() {
                        $this.html('ایجاد کالکشن');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطایی رخ داد. لطفا دوباره تلاش کنید.'
                            });
                        }
                    }
                });
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'نام کالکشن را وارد کنید'
                    });
                }
            }
        });
    });
</script>