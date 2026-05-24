<!-- Header --> 
<?php
/**
 * Template Name: Home Page
 */

get_header();

global $wpdb;
global $post;

// امروز
$today = date("Y-m-d");
list($todayStart, $todayEnd) = getStartAndEndTimestamps($today);

// فردا
$tomorrow = date("Y-m-d", strtotime("+1 day"));
list($tomorrowStart, $tomorrowEnd) = getStartAndEndTimestamps($tomorrow);

// پس فردا
$dayAfterTomorrow = date("Y-m-d", strtotime("+2 days"));
list($dayAfterTomorrowStart, $dayAfterTomorrowEnd) = getStartAndEndTimestamps(
    $dayAfterTomorrow
);

$user_id = get_current_user_id();

// $ez_admin_settings = get_option('ez_admin_settings');

foreach (get_terms(["taxonomy" => "product_cat"]) as $category) {
    $cities[] = ["id" => $category->term_id, "title" => $category->name];
}

foreach (get_terms("product_tag") as $tag) {
    $tags[] = ["id" => $tag->term_id, "title" => $tag->name];
}
/*===============================================================*/
//اسلایدر تبلیغاتی
$items_banner = [
//     [
//       "title" => "بلک فرایدی",
//       "md-src" =>
//           Theme_URL . "assets/images/banners/home-banners/back-friday-sm.avif",
//       "lg-src" =>
//           Theme_URL . "assets/images/banners/home-banners/back-friday-lg.avif",
//       "target-link" =>
//           "/discounts/",
//   ],
//     [
//       "title" => "روز اصفهان",
//       "md-src" =>
//           Theme_URL . "assets/images/banners/home-banners/isfahan-sm.avif",
//       "lg-src" =>
//           Theme_URL . "assets/images/banners/home-banners/isfahan-lg.avif",
//       "target-link" =>
//           "/isfahan-day/",
//   ],
//     [
//       "title" => "بنر تندیس زد",
//       "md-src" =>
//           Theme_URL . "assets/images/banners/home-banners/01-sm.avif",
//       "lg-src" =>
//           Theme_URL . "assets/images/banners/home-banners/01-lg.avif",
//       "target-link" =>
//           "/tandis-z/",
//   ],
//   [
//       "title" => "بنر هالووین",
//       "md-src" =>
//           Theme_URL . "assets/images/banners/home-banners/halloween-sm.avif",
//       "lg-src" =>
//           Theme_URL . "assets/images/banners/home-banners/halloween-lg.avif",
//       "target-link" =>
//           "/best-games/",
//   ],
    [
      "title" => "بنر سفیران",
      "md-src" =>
          Theme_URL . "assets/images/banners/home-banners/safiran-sm.avif",
      "lg-src" =>
          Theme_URL . "assets/images/banners/home-banners/safiran-1-lg.avif",
      "target-link" =>
          "/tehran-games/?utm_source=internal&utm_medium=banner&utm_campaign=safiran",
    ],
    [
      "title" => "بنر اینستاگرام",
      "md-src" =>
          Theme_URL . "assets/images/banners/home-banners/09-sm.avif",
      "lg-src" =>
          Theme_URL . "assets/images/banners/home-banners/09-lg.avif",
      "target-link" =>
          "https://www.instagram.com/escapezoom.ir?igsh=bjJmbGVyczBva3Ji",
    ],
     [
      "title" => "بنر سینماترس",
      "md-src" =>
          Theme_URL . "assets/images/banners/home-banners/05-sm.avif",
      "lg-src" =>
          Theme_URL . "assets/images/banners/home-banners/05-lg.avif",
      "target-link" =>
          "/city/سینما-ترس/",
  ],
    [
      "title" => "بنر پینتبال",
      "md-src" =>
          Theme_URL . "assets/images/banners/home-banners/06-sm.avif",
      "lg-src" =>
          Theme_URL . "assets/images/banners/home-banners/06-lg.avif",
      "target-link" =>
          "/city/پینت-بال/",
  ],
       [
      "title" => "بنر یوتیوب",
      "md-src" =>
          Theme_URL . "assets/images/banners/home-banners/07-sm.avif",
      "lg-src" =>
          Theme_URL . "assets/images/banners/home-banners/07-lg.avif",
      "target-link" =>
          "https://www.youtube.com/@escapezoom4009",
  ],
//   [
//       "title" => "بنر جدید اصفهان",
//       "md-src" =>
//           Theme_URL . "assets/images/banners/home-banners/isfahan-new-sm.avif",
//       "lg-src" =>
//           Theme_URL . "assets/images/banners/home-banners/isfahan-new-lg.avif",
//       "target-link" =>
//           "/isfahan-day/",
//   ],
//   [
//       "title" => "بنر بازی های تهران ",
//       "md-src" =>
//           Theme_URL . "assets/images/banners/home-banners/02-sm.avif",
//       "lg-src" =>
//           Theme_URL . "assets/images/banners/home-banners/02-lg.avif",
//       "target-link" =>
//           "/%d8%a8%d8%a7%d8%b2%db%8c-%d9%87%d8%a7%db%8c-%d8%aa%d9%87%d8%b1%d8%a7%d9%86/?utm_source=internal&utm_medium=banner&utm_campaign=tehran-games&utm_content=home-page",
//   ],
//   [
//       "title" => "بنر جنرال لیزرتگ",
//       "md-src" =>
//           Theme_URL . "assets/images/banners/home-banners/03-sm.avif",
//       "lg-src" =>
//           Theme_URL . "assets/images/banners/home-banners/03-lg.avif",
//       "target-link" =>
//           "/city/%D9%84%DB%8C%D8%B2%D8%B1%D8%AA%DA%AF/",
//   ]
];
if ($items_banner && !empty($items_banner)) :
?>
<div class="relative embla_fade w-full rounded-[14px] lg:rounded-[20px] overflow-hidden mt-7.5">
  <div class="embla__viewport">
    <div class="embla__container flex">
      <?php foreach ($items_banner as $item): ?>
        <div class="embla__slide flex-[0_0_100%]" data-title="<?= @$item['title'] ?>">
          <a class="block w-full" href="<?= (!in_array($item['title'], ['بنر کانال تلگرام', 'بنر اینستاگرام', 'بنر یوتیوب'])) ? home_url() . $item['target-link'] : $item['target-link'] ?>">
            <picture>
              <source media="(min-width: 1024px)" srcset="<?= $item['lg-src'] ?>" />
              <img class="w-full h-auto rounded-[14px] lg:rounded-[20px]" src="<?= $item['md-src'] ?>" alt="<?= @$item['title'] ?>" />
            </picture>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <button class="embla__button embla__button--prev absolute right-4 top-1/2 -translate-y-1/2 z-50 hidden md:flex items-center justify-center w-10 h-10 hover:scale-110 transition-transform" type="button" aria-label="Previous slide">
    <svg width="12" height="18" viewBox="0 0 12 18" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M3.17676 14.7646L8.58791 9.3535C8.78317 9.15823 8.78317 8.84165 8.58791 8.64639L3.17676 3.23524" stroke="#90A1B9" stroke-width="5" stroke-linecap="round" />
    </svg>
  </button>
  <button class="embla__button embla__button--next absolute left-4 top-1/2 -translate-y-1/2 z-50 hidden md:flex items-center justify-center w-10 h-10 hover:scale-110 transition-transform" type="button" aria-label="Next slide">
    <svg width="12" height="18" viewBox="0 0 12 18" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M8.82324 14.7646L3.41209 9.3535C3.21683 9.15823 3.21683 8.84165 3.41209 8.64639L8.82324 3.23524" stroke="#90A1B9" stroke-width="5" stroke-linecap="round" />
    </svg>
  </button>
  <div class="embla__dots absolute bottom-2 lg:bottom-3 left-0 right-0 mx-auto"></div>
</div>
<?php
endif;
/*===============================================================*/
// دسته بندی‌های ویژه
?>
<section class="my-8 lg:my-12.5 text-center">
  <div class="space-y-2 lg:space-y-4.5">
    <h1 class="font-black text-xl md:text-4xl">اسکیپ زوم، مرجع بازی های گروهی بزرگسالان</h1>
    <p class="lg:text-xl">اتاق فرار، لیزرتگ، سینما ترس، اتاق خشم، کافه بازی</p>
  </div>
  <div class="flex items-center justify-center gap-x-5 lg:gap-x-25 mt-10 lg:mt-12.5">
    <a href="<?= home_url('/type/اتاق-فرار-غرب-تهران/') ?>" class="w-19 hover:scale-105 transition-all">
      <img src="<?= Theme_ASSET_URL ?>images/cat-id/west-tehran.avif" class="w-11.5 lg:w-19 mx-auto" alt="" srcset="">
      <h2 class="text-sm lg:text-lg font-medium mt-4 lg:mt-5 text-nowrap">غرب تهران</h2>
    </a>
    <a href="<?= home_url('/type/اتاق-فرار-شرق-تهران/') ?>" class="w-19 hover:scale-105 transition-all">
      <img src="<?= Theme_ASSET_URL ?>images/cat-id/east-tehran.avif" class="w-11.5 lg:w-19 mx-auto" alt="" srcset="">
      <h2 class="text-sm lg:text-lg font-medium mt-4 lg:mt-5 text-nowrap">شرق تهران</h2>
    </a>
    <a href="<?= home_url('/بازی-های-کرج/') ?>" class="w-19 hover:scale-105 transition-all">
      <img src="<?= Theme_ASSET_URL ?>images/cat-id/karaj.avif" class="w-11.5 lg:w-19 mx-auto" alt="" srcset="">
      <h2 class="text-sm lg:text-lg font-medium mt-4 lg:mt-5 text-nowrap">کرج</h2>
    </a>
    <a href="<?= home_url('/بازی-های-مشهد/') ?>" class="w-19 hover:scale-105 transition-all">
      <img src="<?= Theme_ASSET_URL ?>images/cat-id/mashhad.avif" class="w-11.5 lg:w-19 mx-auto" alt="" srcset="">
      <h2 class="text-sm lg:text-lg font-medium mt-4 lg:mt-5 text-nowrap">مشهد</h2>
    </a>
    <a href="<?= home_url('/بازی-های-اصفهان/') ?>" class="w-19 hover:scale-105 transition-all">
      <img src="<?= Theme_ASSET_URL ?>images/cat-id/isfahan.avif" class="w-11.5 lg:w-19 mx-auto" alt="" srcset="">
      <h2 class="text-sm lg:text-lg font-medium mt-4 lg:mt-5 text-nowrap">اصفهان</h2>
    </a>
  </div>
</section>
<!--advance-search-->
<section class="max-w-full py-4 md:py-5 lg:py-9">
  <form action="<?= home_url("/game-finder/") ?>" method="get" id="game-finder-form">
    <div class="max-lg:relative max-lg:w-screen max-lg:right-1/2 max-lg:left-1/2 max-lg:-ml-[50vw] max-lg:-mr-[50vw] max-lg:bg-stone-100 max-lg:before:w-screen max-lg:before:h-1.5 max-lg:before:bg-[#CAD5E2] max-lg:before:absolute max-lg:before:top-0 max-lg:before:left-0 max-lg:before:right-0 lg:rounded-xl lg:w-full lg:bg-gradient-to-t lg:from-transparent lg:from-75% lg:to-slate-700/40 to-200% lg:shadow-9">
      <div
        class="max-lg:pt-5 max-lg:pb-6 max-lg:px-7 lg:px-14 lg:py-10 grid grid-cols-3 lg:grid-cols-12 lg:justify-between gap-x-4 gap-y-6 items-center lg:gap-x-5">
        <h2 class="text-xl font-black xl:text-2xl text-justify max-lg:col-span-3 lg:col-span-2 max-lg:text-center">
          سانــــــس یـــــــــــــاب
        </h2>
        <div class="flex items-center max-lg:gap-x-4 lg:grid lg:grid-cols-4 lg:gap-x-5 max-lg:col-span-3 lg:col-span-10">
          <div class="max-lg:relative max-lg:w-full">
            <div class="relative w-full max-w-xs">
              <div class="relative sans-dropdown-container">
                <button
                  type="button"
                  class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                  <span class="text-gray-700 text-nowrap">اتاق فرار</span>
                  <svg
                    class="w-4 h-4 m-0 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                <div
                  class="absolute z-50 hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg sans-options">
                  <div class="overflow-auto max-h-60">
                    <div class="py-1 city-options">
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100 text-nowrap">
                        <input
                          type="radio"
                          name="product_type"
                          value="اتاق فرار"
                          class="hidden option-sans-input"
                          checked="" />
                        اتاق فرار
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100 text-nowrap">
                        <input
                          type="radio"
                          name="product_type"
                          value="سینما ترس"
                          class="hidden option-sans-input" />
                        سینما ترس
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100 text-nowrap">
                        <input
                          type="radio"
                          name="product_type"
                          value="لیزرتگ"
                          class="hidden option-sans-input" />
                        لیزرتگ
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100 text-nowrap">
                        <input
                          type="radio"
                          name="product_type"
                          value="اتاق خشم"
                          class="hidden option-sans-input" />
                        اتاق خشم
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="max-lg:w-full">
            <div class="relative w-full max-w-xs">
              <div class="relative sans-dropdown-container">
                <button
                  type="button"
                  class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                  <span id="cities-box-title" class="relative text-gray-700">
                    شهر
                  </span>
                  <svg
                    class="w-4 h-4 m-0 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                <div
                  class="absolute z-50 hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg sans-options">
                  <div class="p-2">
                    <input
                      type="text"
                      name="city_search"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md city-search"
                      placeholder="جستجوی شهر..." />
                  </div>
                  <div class="overflow-auto max-h-60">
                    <div id="cities-box-list" class="py-1 city-options">
                      <?php
                      $cities = cities_type("اتاق فرار");
                      foreach ($cities as $city) {
                        echo '<label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100"><input type="radio" name="city_id" value="' . $city["city_id"] . '" class="hidden option-sans-input"/>' . $city["city_name"] . '</label>';
                      }
                      ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="max-lg:w-full">
            <div class="relative w-full max-w-xs">
              <div class="relative sans-dropdown-container">
                <button
                  type="button"
                  class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                  <span class="text-gray-700">
                    تعداد <span class="max-lg:hidden">نفرات</span>
                  </span>
                  <svg
                    class="w-4 h-4 m-0 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                <div
                  class="absolute z-50 hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg sans-options">
                  <div class="overflow-auto max-h-60">
                    <div class="py-1 city-options">
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="2"
                          class="hidden option-sans-input" />
                        + 2 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="3"
                          class="hidden option-sans-input" />
                        + 3 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="4"
                          class="hidden option-sans-input" />
                        + 4 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="5"
                          class="hidden option-sans-input" />
                        + 5 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="6"
                          class="hidden option-sans-input" />
                        + 6 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="7"
                          class="hidden option-sans-input" />
                        + 7 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="8"
                          class="hidden option-sans-input" />
                        + 8 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="9"
                          class="hidden option-sans-input" />
                        + 9 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="10"
                          class="hidden option-sans-input" />
                        + 10 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="11"
                          class="hidden option-sans-input" />
                        + 11 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="12"
                          class="hidden option-sans-input" />
                        + 12 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="13"
                          class="hidden option-sans-input" />
                        + 13 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="14"
                          class="hidden option-sans-input" />
                        + 14 نفر
                      </label>
                      <label
                        class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                        <input
                          type="radio"
                          name="count"
                          value="15"
                          class="hidden option-sans-input" />
                        + 15 نفر
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <button
            type="submit"
            class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-[#ccc] disabled:cursor-not-allowed disabled:shadow-none bg-primary-600 text-white shadow-14 hover:bg-primary-500 focus-visible:outline-primary-600 h-12 lg:px-5 p-4 lg:py-1 mr-auto rounded-lg max-lg:col-span-3 lg:order-2 max-lg:max-w-[56px] lg:w-full">
            <span class="truncate max-lg:hidden ">جستجو</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" class="mx-0 ">
              <path d="M9.47656 0.751953C11.7765 0.707681 14.0043 1.55548 15.6924 3.11816C17.3805 4.6811 18.3985 6.83804 18.5312 9.13477C18.6594 11.3543 17.9485 13.5359 16.5498 15.2568L18.9902 17.6963L18.9932 17.6992C19.1598 17.8718 19.2521 18.1029 19.25 18.3428C19.2479 18.5827 19.1521 18.8127 18.9824 18.9824C18.8127 19.1521 18.5827 19.2479 18.3428 19.25C18.1029 19.2521 17.8718 19.1598 17.6992 18.9932L15.2568 16.5508C13.5359 17.9492 11.3541 18.6594 9.13477 18.5312C6.83804 18.3985 4.6811 17.3805 3.11816 15.6924C1.55548 14.0043 0.707681 11.7765 0.751953 9.47656C0.796232 7.17643 1.72971 4.98318 3.35645 3.35645C4.98318 1.72971 7.17643 0.796232 9.47656 0.751953ZM9.64746 2.58008C7.77324 2.5802 5.97567 3.32511 4.65039 4.65039C3.32511 5.97567 2.5802 7.77324 2.58008 9.64746C2.58008 11.5218 3.325 13.3201 4.65039 14.6455C5.97567 15.9708 7.77326 16.7147 9.64746 16.7148C11.5218 16.7148 13.3201 15.9709 14.6455 14.6455C15.9709 13.3201 16.7148 11.5218 16.7148 9.64746C16.7147 7.77326 15.9708 5.97567 14.6455 4.65039C13.3201 3.325 11.5218 2.58008 9.64746 2.58008Z" fill="white" stroke="white" stroke-width="0.5" />
            </svg>
          </button>
        </div>

      </div>
    </div>
  </form>
</section>
<!-- tag-offer -->
<section class="mt-10">
  <h2 class="flex items-center gap-4">
    <svg xmlns="http://www.w3.org/2000/svg" class="mx-0" width="28" height="29" viewBox="0 0 28 29"
      fill="none">
      <g clip-path="url(#clip0_41979_1517)">
        <path
          d="M25.6666 14.5H26.8333M14 2.83335V1.66669M14 27.3334V26.1667M25 25L23.8333 23.8333M23.3333 5.16669L22.1666 6.33335M3.33333 25.1667L4.5 24M4.66663 5.16669L5.83329 6.33335M1.16663 14.5H2.33329"
          stroke="#0F172B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <path
          d="M13.2665 6.45046L10.928 11.1326L5.70047 11.8877C5.02997 11.9841 4.76297 12.8001 5.24747 13.2676L9.03048 16.9099L8.13798 22.0525C8.02248 22.7141 8.72448 23.217 9.32449 22.9053L14 20.4757L18.6755 22.9053C19.2755 23.217 19.9775 22.7141 19.862 22.0525L18.9695 16.9085L22.7525 13.2676C23.237 12.8001 22.97 11.9841 22.2995 11.8877L17.072 11.1326L14.7335 6.45046C14.6705 6.31639 14.5677 6.20247 14.4375 6.12252C14.3073 6.04256 14.1553 6 14 6C13.8447 6 13.6927 6.04256 13.5625 6.12252C13.4323 6.20247 13.3295 6.31639 13.2665 6.45046Z"
          stroke="#0F172B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
      </g>
      <defs>
        <clipPath id="clip0_41979_1517">
          <rect width="28" height="28" fill="white" transform="translate(0 0.5)" />
        </clipPath>
      </defs>
    </svg>
    <div class="text-base md:text-lg mt-1">
      <p>پرمخاطب‌ترین <b class="font-black">بازی‌ها</b>
      </p>
    </div>
  </h2>
  <div
    class="relative overflow-hidden embla_normal mt-5 lg:mt-12.5 max-lg:relative max-lg:w-screen max-lg:right-1/2 max-lg:left-1/2 max-lg:-ml-[50vw] max-lg:-mr-[50vw] max-lg:px-5">
    <div class="embla__viewport max-lg:relative">
      <div class="embla__container child:box-content flex child:ml-7 md:child:ml-12 xl:child:ml-15 last:child:ml-0 child:shrink-0 child:grow-0 child:w-[124px] md:child:w-[178px] child:py-2.5 child:relative max-lg:relative"
        style="transform: translate3d(0px, 0px, 0px);">
        <a href="<?= home_url('/type/اتاق-فرار-ترسناک/') ?>" class="embla__slide hover:scale-105 transition-all">
          <img src="<?= Theme_ASSET_URL ?>images/cat-id/01.avif" alt="">
        </a>
        <a href="<?= home_url('/type/معمایی/') ?>" class="embla__slide hover:scale-105 transition-all">
          <img src="<?= Theme_ASSET_URL ?>images/cat-id/02.avif" alt="">
        </a>
        <a href="<?= home_url('/type/هیجانی/') ?>" class="embla__slide hover:scale-105 transition-all">
          <img src="<?= Theme_ASSET_URL ?>images/cat-id/03.avif" alt="">
        </a>
        <a href="<?= home_url('/city/اتاق-فرار/') ?>" class="embla__slide hover:scale-105 transition-all">
          <img src="<?= Theme_ASSET_URL ?>images/cat-id/04.avif" alt="">
        </a>
        <a href="<?= home_url('/city/سینما-ترس/') ?>" class="embla__slide hover:scale-105 transition-all">
          <img src="<?= Theme_ASSET_URL ?>images/cat-id/05.avif" alt="">
        </a>
        <a href="<?= home_url('/city/لیزرتگ/') ?>" class="embla__slide hover:scale-105 transition-all">
          <img src="<?= Theme_ASSET_URL ?>images/cat-id/06.avif" alt="">
        </a>
      </div>
    </div>
  </div>
</section>
<?php
/*===============================================================*/
// اتاق فرارهای شهر
$params = [
  "city_id" => [15],
  "tag" => [124]
];
$args = [
  "source" => "home_cities_escaperoom",
  "params" => $params,
  "sort_type" => "hottest",
];
$cities_rooms = json_decode(
  ez_webservice(["type" => "sort_products_get", "data" => $args])
)->products;
?>
<section class="max-w-full py-4 md:py-5 lg:py-9 mt-2">
  <input type="hidden" id="cities-rooms" data-source="home_cities_escaperoom" data-params='{"sort_type":"hottest","city_id":[15],"tag":[124]}'>
  <div class="flex justify-between mb-6 md:mb-8">
    <div class="items-center gap-6 md:flex">
      <h2 class="flex items-center gap-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="29" viewBox="0 0 28 29" fill="none">
          <path d="M6.13245 22.3633L5.17223 20.6841L3.49986 21.6561C3.27686 21.7847 3.01191 21.8194 2.7633 21.7526C2.5147 21.6859 2.30281 21.5231 2.17424 21.3001C2.04567 21.077 2.01095 20.8121 2.07773 20.5635C2.14451 20.3149 2.3073 20.103 2.53031 19.9744L15.0128 12.7779C14.6353 11.6416 14.693 10.4056 15.1747 9.3094C15.6564 8.2132 16.5279 7.33485 17.6203 6.84454C18.7127 6.35424 19.9482 6.28688 21.0875 6.65553C22.2267 7.02419 23.1885 7.80259 23.7866 8.83993C24.3846 9.87726 24.5763 11.0997 24.3245 12.2703C24.0727 13.4409 23.3954 14.4764 22.4237 15.176C21.452 15.8757 20.2552 16.1898 19.0652 16.0575C17.8751 15.9251 16.7766 15.3557 15.9824 14.4596L10.1937 17.7891L11.1657 19.4614C11.2288 19.5719 11.2695 19.6938 11.2854 19.82C11.3013 19.9462 11.2921 20.0743 11.2584 20.197C11.2261 20.32 11.1699 20.4355 11.0928 20.5368C11.0158 20.638 10.9196 20.7231 10.8096 20.7871C10.6994 20.8513 10.5774 20.893 10.451 20.9098C10.3245 20.9267 10.1959 20.9183 10.0727 20.8852C9.94942 20.8521 9.83395 20.7949 9.73292 20.7169C9.63189 20.639 9.5473 20.5418 9.48402 20.431L8.57048 18.7643L6.88877 19.7339L7.86083 21.4063C7.92392 21.5167 7.96459 21.6386 7.98049 21.7648C7.9964 21.891 7.98723 22.0191 7.95352 22.1418C7.92123 22.2648 7.86497 22.3803 7.78796 22.4816C7.71095 22.5828 7.61471 22.6679 7.50475 22.7319C7.39188 22.8032 7.26547 22.8503 7.13348 22.8703C7.0015 22.8903 6.8668 22.8827 6.73787 22.8481C6.60895 22.8135 6.4886 22.7525 6.3844 22.6691C6.2802 22.5856 6.19442 22.4815 6.13245 22.3633ZM22.3959 12.0086C22.5447 11.4547 22.5259 10.8691 22.3419 10.3259C22.1578 9.78265 21.8168 9.3062 21.362 8.95677C20.9072 8.60734 20.359 8.40062 19.7867 8.36275C19.2144 8.32489 18.6438 8.45758 18.1469 8.74404C17.65 9.03051 17.2492 9.45789 16.9952 9.97213C16.7412 10.4864 16.6454 11.0644 16.72 11.6331C16.7945 12.2017 17.0359 12.7356 17.4138 13.167C17.7917 13.5985 18.2891 13.9082 18.843 14.0569C19.5858 14.2565 20.3774 14.1527 21.0437 13.7686C21.71 13.3845 22.1964 12.7514 22.3959 12.0086Z" fill="#0F172B" stroke="#0F172B" />
        </svg>
        <div class="text-base font-bold md:text-lg mt-1">
          <p>اتاق فرارهای <b id="cities-rooms-title">تهران</b>
          </p>
        </div>
      </h2>
    </div>
    <div class="flex items-center gap-6">
      <a href="<?= home_url("/city/اتاق-فرار-تهران") ?>" id="cities-rooms-link">
        <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs hover:text-primary-500 transition">
          <span id="cities-rooms-link-text">مشاهده همه</span> <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
            <path clip-rule="evenodd" d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z" fill="currentColor"></path>
          </svg>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
          </svg>
        </div>
      </a>
    </div>
  </div>
  <div class="grid grid-cols-3 lg:grid-cols-4 my-8 gap-x-4 lg:gap-x-11.5">
    <div class="lg:col-span-2">
      <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-[#E8EDF1] max-md:hidden">شهر مورد نظر</h3>
      <div class="relative dropdown-container">
        <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-[#E8EDF1] md:hidden">
          <span>انتخاب شهر</span>
          <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
            <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
          </svg>
        </button>
        <div class="overflow-x-auto options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide touch-pan-x">
          <button type="button" data-input="cities-rooms" data-params="city_id:[15]" class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled> تهران </button>
          <button type="button" data-input="cities-rooms" data-params="city_id:[162]" class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> کرج </button>
          <button type="button" data-input="cities-rooms" data-params="city_id:[121]" class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> مشهد </button>
          <button type="button" data-input="cities-rooms" data-params="city_id:[122]" class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> اصفهان </button>
          <button type="button" data-input="cities-rooms" data-params="city_id:[293]" class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> کرمانشاه </button>
          <button type="button" data-input="cities-rooms" data-params="city_id:[187]" class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> قم </button>
          <button type="button" data-input="cities-rooms" data-params="city_id:[270]" class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> قزوین </button>
          <button type="button" data-input="cities-rooms" data-params="city_id:[190]" class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> شیراز </button>
        </div>
      </div>
    </div>
    <div>
      <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-[#E8EDF1] max-md:hidden">سبک بازی</h3>
      <div class="relative dropdown-container">
        <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-[#E8EDF1] md:hidden">
          <span>سبک بازی</span>
          <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
            <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
          </svg>
        </button>
        <div class="overflow-x-auto options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide touch-pan-x">
          <button type="button" data-input="cities-rooms" data-params="tag:[124]" class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled> ترسناک </button>
          <button type="button" data-input="cities-rooms" data-params="tag:[178]" class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> هیجانی </button>
          <button type="button" data-input="cities-rooms" data-params="tag:[512]" class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> تعاملی </button>
          <button type="button" data-input="cities-rooms" data-params="tag:[125]" class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> غیرترسناک </button>
        </div>
      </div>
    </div>
    <div>
      <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-[#E8EDF1] max-md:hidden">براساس</h3>
      <div class="relative dropdown-container">
        <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-[#E8EDF1] md:hidden">
          <span>براساس</span>
          <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
            <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
          </svg>
        </button>
        <div class="overflow-x-auto options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide touch-pan-x">
          <button type="button" data-input="cities-rooms" data-params='sort_type:"hottest"' class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled> داغ ترین ها</button>
          <button type="button" data-input="cities-rooms" data-params='sort_type:"topsale"' class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> پرفروش ترین ها </button>
          <button type="button" data-input="cities-rooms" data-params='sort_type:"recent"' class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition"> جدیدترین ها </button>
        </div>
      </div>
    </div>
  </div>
  <div class="relative overflow-hidden embla_normal">
    <div class="embla__viewport">
      <div id="cities-rooms-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12  last:child:ml-0 child:shrink-0 child:grow-0 child:w-[156px] md:child:w-[190px] child:py-2.5 child:relative"> <?= $cities_rooms ?> </div>
    </div>
    <button class="embla__button embla__button--prev cities-rooms-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
        <g clip-path="url(#arrow_aa)">
          <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
          <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
          <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
        </g>
        <defs>
          <clipPath id="arrow_aa">
            <path fill="#fff" d="M0 0h30v113H0z"></path>
          </clipPath>
        </defs>
      </svg>
    </button>
    <button class="embla__button embla__button--next cities-rooms-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
        <g clip-path="url(#arrow_aa)">
          <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
          <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
          <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
        </g>
        <defs>
          <clipPath id="arrow_aa">
            <path fill="#fff" d="M0 0h30v113H0z"></path>
          </clipPath>
        </defs>
      </svg>
    </button>
  </div>
</section>
<!-- map banner -->
<!--<section class="map-section grid max-lg:grid-cols-5 grid-cols-3 lg:relative my-12.5 lg:my-20">-->
<!--  <div class="relative inline-block max-lg:col-span-5 col-span-3 lg:mt-12.5 lg:mr-25">-->
<!--    <img src="<?= Theme_ASSET_URL ?>images/cat-id/text.avif" alt="بازی‌های گروهی بر فراز نقشه ایران" class="max-w-[300px] lg:max-w-[600px] max-lg:mx-auto" />-->
<!--  </div>-->
<!--  <div class="max-lg:col-span-3 col-span-2 max-lg:mr-7 max-lg:max-w-[170px] max-lg:text-sm lg:text-2xl max-lg:leading-6 max-lg:mt-8 lg:mr-25 lg:-mt-10">-->
<!--    <p>با نقشه اسکیپ زوم نزدیک ترین سرگرمی به خودت رو پیدا کن</p>-->
<!--    <a href="<?= home_url('/maps/') ?>"-->
<!--      class="bg-[#294487] text-white shadow-8 w-[90px] h-[34px] rounded-lg lg:w-[135px] lg:h-[48px] lg:rounded-xl block text-center content-center hover:scale-105 transition-all max-lg:mt-4 lg:text-lg lg:mt-10">مشاهده-->
<!--      نقشه</a>-->
<!--  </div>-->
<!--  <div class="max-lg:col-span-2 lg:absolute lg:bottom-0 lg:left-20"><img-->
<!--      src="<?= Theme_ASSET_URL ?>images/cat-id/cto-home.avif" alt="" class="w-[250px] lg:w-[400px]"></div>-->
<!--</section>-->
<?php
/*===============================================================*/
// آخرین کامنت ها
get_template_part("template/layout/comments");
/*===============================================================*/
// کالکشن های محبوب
get_template_part("template/layout/collections");
/*===============================================================*/
// جدیدترین بازی‌ها
$args = [
  "source" => "cat_sansyab",
  "params" => [
    "sort_type" => "recent",
  ],
  "limit" => 50,
  "active_soon" => false,
];
$home_trends_rooms = json_decode(ez_webservice(["type" => 'sort_products_get', "data" => $args]))->products;
?>
<section class="max-w-full py-4 md:py-5 lg:py-9">
  <div class="mb-6 md:mb-8">
    <input type="hidden" id="trends-rooms" data-source="home_trends" data-params='{"schedule":-1}'>
    <div class="flex justify-between">
      <div class="items-center gap-6 md:flex">
        <h2 class="flex items-center gap-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="29" viewBox="0 0 28 29" fill="none">
            <g clip-path="url(#clip0_41980_1522)">
              <path d="M16.31 27.8927C16.1471 27.6825 16.0466 27.4308 16.02 27.1662C15.9934 26.9016 16.0417 26.6349 16.1595 26.3965C16.4675 25.798 16.5882 25.1137 16.5095 24.44C16.4452 23.9592 16.2864 23.4958 16.0422 23.0767C15.8025 22.6622 15.4803 22.3012 15.0955 22.0162C13.7871 21.0329 12.869 19.6182 12.5037 18.0227C9.51475 21.941 10.661 24.1652 12.0925 26.1252C12.2598 26.3552 12.3479 26.6333 12.3434 26.9177C12.339 27.202 12.2424 27.4773 12.068 27.702C11.8898 27.9291 11.6506 28.1008 11.3785 28.1972C11.1095 28.2914 10.8188 28.3054 10.542 28.2375C8.60825 27.765 6.78825 26.8655 5.5545 25.4322C4.85565 24.6357 4.31728 23.7117 3.969 22.711C3.61686 21.7015 3.46065 20.6342 3.50875 19.5662C3.50875 19.5662 3.276 15.256 8.4735 11.0332C8.4735 11.0332 14.616 5.82171 12.4845 1.96821C12.4058 1.77869 12.3824 1.57073 12.4171 1.36846C12.4518 1.16618 12.5431 0.977899 12.6805 0.825457C12.8142 0.67631 12.9903 0.571698 13.1852 0.525704C13.3802 0.47971 13.5845 0.494543 13.7708 0.568207L14.0262 0.669707C16.3399 2.1322 18.1482 4.27038 19.2063 6.79471C20.2213 9.26746 20.2143 12.1497 19.5283 14.717C20.097 14.206 20.5713 13.5952 20.93 12.9092L20.9808 12.7972C21.3273 11.9625 22.4175 12.2285 22.827 12.7745C22.9775 13.0142 26.838 18.6247 24.7642 23.3585C24.007 24.7934 22.8994 26.0136 21.5443 26.9057C20.4084 27.6609 19.1383 28.1914 17.8028 28.4685C17.5268 28.527 17.2398 28.5039 16.9768 28.402C16.7114 28.2985 16.4804 28.1227 16.31 27.8945V27.8927ZM13.2195 14.311C13.3827 14.225 13.5723 14.2035 13.7506 14.2509C13.9289 14.2982 14.0829 14.4108 14.182 14.5665C14.252 14.6715 14.2957 14.787 14.3132 14.913L14.392 15.5237C14.427 16.418 14.4165 17.3525 14.7648 18.2117C15.1252 19.0937 15.6853 19.8742 16.3958 20.4832C17.3111 21.0855 18.0196 21.9541 18.4258 22.9717C18.8108 23.9587 18.8633 25.0542 18.5728 26.0745C19.4619 25.7951 20.2882 25.3451 21.0053 24.7497L21.1855 24.6027C21.7735 24.118 22.2582 23.5125 22.6082 22.823C22.96 22.1352 23.1717 21.3792 23.2278 20.6022C23.3415 18.8085 22.7308 17.0077 21.7805 15.4012C21.3465 16.0312 20.748 16.5195 20.0568 16.8082C19.6245 16.992 19.166 17.1057 18.6988 17.1407C18.4312 17.1571 18.1645 17.0964 17.9305 16.9657C17.693 16.831 17.4983 16.632 17.3687 16.3917C17.262 16.1978 17.201 15.982 17.1904 15.7609C17.1798 15.5398 17.2198 15.3192 17.3075 15.116C18.0285 13.415 18.2525 11.5285 17.9463 9.69621C17.5842 7.59979 16.5801 5.66751 15.0727 4.16621C14.7997 8.02496 10.843 11.6177 10.031 12.3912C9.90457 12.5092 9.77382 12.6224 9.639 12.7307C5.3935 16.1695 5.684 19.302 5.684 19.4402C5.60191 20.6844 5.88083 21.9259 6.48725 23.0155C7.126 24.1442 8.04475 25.0752 9.14725 25.7105C7.875 22.914 7.875 19.5575 12.5702 14.745L13.2212 14.3075L13.2195 14.311Z" fill="#0F172B" />
            </g>
            <defs>
              <clipPath id="clip0_41980_1522">
                <rect width="28" height="28" fill="white" transform="translate(0 0.5)" />
              </clipPath>
            </defs>
          </svg>
          <div class="text-base font-bold md:text-lg mt-1">
            <p>جدیدترین <b class="font-black">بازی‌ها</b></p>
          </div>
        </h2>
      </div>
    </div>
  </div>
  <div class="relative overflow-hidden embla_normal horizontal dragFree slider-event" data-slider-event="trends-rooms">
    <div class="embla__viewport">
      <div id="trends-rooms-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12  last:child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-[156px] md:child:w-[190px] child:py-2.5"> <?= $home_trends_rooms ?> </div>
    </div>
    <button class="embla__button embla__button--prev trends-rooms-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
        <g clip-path="url(#arrow_aa)">
          <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
          <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
          <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
        </g>
        <defs>
          <clipPath id="arrow_aa">
            <path fill="#fff" d="M0 0h30v113H0z"></path>
          </clipPath>
        </defs>
      </svg>
    </button>
    <button class="embla__button embla__button--next trends-rooms-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
        <g clip-path="url(#arrow_aa)">
          <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
          <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
          <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
        </g>
        <defs>
          <clipPath id="arrow_aa">
            <path fill="#fff" d="M0 0h30v113H0z"></path>
          </clipPath>
        </defs>
      </svg>
    </button>
  </div>
</section>
<?php
/*===============================================================*/
// مجله خبری و تصویری
$args = [
  "posts_per_page" => 4,
  "tax_query" => [
    [
      "taxonomy" => "category", // نام دسته‌بندی استاندارد وردپرس
      "field" => "term_id", // نوع شناسه: می‌تواند 'term_id'، 'slug' یا 'name' باشد
      "terms" => 1, // آیدی دسته‌بندی که می‌خواهید پست‌ها را از آن بگیرید
    ],
  ],
];

$query = new WP_Query($args);
if ($query->have_posts()):
?>
  <section class="max-w-full py-4 md:py-5 lg:py-9">
    <div class="mb-6 md:mb-8">
      <div class="flex justify-between">
        <div class="items-center gap-6 md:flex">
          <h3 class="flex items-center gap-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
              <path d="M21 17.5V10.5C21 7.20067 21 5.54983 19.9745 4.5255C18.9502 3.5 17.2994 3.5 14 3.5H9.33337C6.03404 3.5 4.38321 3.5 3.35887 4.5255C2.33337 5.54983 2.33337 7.20067 2.33337 10.5V17.5C2.33337 20.7993 2.33337 22.4502 3.35887 23.4745C4.38321 24.5 6.03404 24.5 9.33337 24.5H23.3334M7.00004 9.33333H16.3334M7.00004 14H16.3334M7.00004 18.6667H11.6667" stroke="#09192D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M21 9.33325H22.1667C23.8163 9.33325 24.6412 9.33325 25.1533 9.84658C25.6667 10.3588 25.6667 11.1836 25.6667 12.8333V22.1666C25.6667 22.7854 25.4208 23.3789 24.9832 23.8165C24.5457 24.2541 23.9522 24.4999 23.3333 24.4999C22.7145 24.4999 22.121 24.2541 21.6834 23.8165C21.2458 23.3789 21 22.7854 21 22.1666V9.33325Z" stroke="#09192D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span class="text-base font-bold md:text-lg mt-1">
              <span class="text-md">مجله بازی</span>
            </span>
          </h3>
          <!--<div class="hidden md:block">-->
          <!--  <div class="relative hidden md:block">-->
          <!--    <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">-->
          <!--      <div class="flex gap-2">-->
          <!--        <button type="button" class="flex-shrink-0 px-3 text-xs font-semibold text-center transition blog-slider-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-primary-500 hover:text-primary-600" data-source="1" disabled> وبلاگ </button>-->
          <!--        <button type="button" class="flex-shrink-0 px-3 text-xs font-semibold text-center transition blog-slider-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl hover:text-primary-600" data-source="953"> مجله خبری </button>-->
          <!--      </div>-->
          <!--    </div>-->
          <!--  </div>-->
          <!--</div>-->
        </div>
        <div class="flex items-center gap-6">
          <div class="hidden md:block"></div>
          <a href="<?= home_url("/blog/") ?>">
            <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs hover:text-primary-500 transition">مشاهده همه <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                <path clip-rule="evenodd" d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z" fill="currentColor"></path>
              </svg>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
              </svg>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="flex justify-between max-lg:gap-5  max-lg:overflow-x-auto  [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
      <?php while ($query->have_posts()): $query->the_post(); ?>
        <div class="relative grow-0 shrink-0 w-[310px] max-lg:h-[174px] lg:h-[230px]">
          <a class="relative block overflow-hidden rounded-[16px] shadow-8 w-[310px] max-lg:h-[174px] lg:h-[230px]"
            href="<?= get_the_permalink() ?>">
            <img alt="Product" loading="lazy" width="281" height="328" decoding="async" data-nimg="1"
              class="h-full w-full object-cover"
              src="<?= get_the_post_thumbnail_url() ?>"
              style="color: transparent;">
            <div
              class="absolute right-0 top-0 flex h-full w-full flex-col justify-between bg-gradient-to-t from-[#09192D] to-transparent max-lg:p-3 lg:p-6 text-white/90">
              <div class="ez-post-category">
                <div class="text-white rounded-lg py-1 px-2 max-w-[55px] text-xs font-bold" style="background: rgba(255, 255, 255, 0.25);backdrop-filter: blur(2px);">سرگرمی</div>
              </div>
              <div>
                <h2 class="text-lg font-extrabold text-white lg:text-xl line-clamp-2"><?= get_the_title() ?></h2>
                <div
                  class="ez-post-info mt-4 flex items-center justify-between gap-5 text-xs lg:mt-6">
                  <div class="flex gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                      <g clip-path="url(#clip0_49360_27896)">
                        <path d="M7.38257 2.49707C6.34422 2.49707 5.32919 2.80498 4.46583 3.38185C3.60247 3.95873 2.92956 4.77867 2.5322 5.73798C2.13484 6.69729 2.03088 7.75289 2.23345 8.77129C2.43602 9.78969 2.93603 10.7252 3.67026 11.4594C4.40449 12.1936 5.33995 12.6936 6.35835 12.8962C7.37675 13.0988 8.43235 12.9948 9.39166 12.5974C10.351 12.2001 11.1709 11.5272 11.7478 10.6638C12.3247 9.80046 12.6326 8.78542 12.6326 7.74707C12.631 6.35517 12.0773 5.02074 11.0931 4.03652C10.1089 3.0523 8.77447 2.49866 7.38257 2.49707ZM7.38257 11.6846C6.60381 11.6846 5.84253 11.4536 5.19501 11.021C4.54749 10.5883 4.04282 9.97337 3.7448 9.25389C3.44678 8.5344 3.3688 7.7427 3.52073 6.9789C3.67266 6.2151 4.04767 5.51351 4.59834 4.96284C5.14901 4.41217 5.8506 4.03716 6.6144 3.88523C7.3782 3.7333 8.1699 3.81127 8.88939 4.10929C9.60887 4.40731 10.2238 4.91199 10.6565 5.55951C11.0891 6.20703 11.3201 6.96831 11.3201 7.74707C11.3189 8.79101 10.9037 9.79185 10.1655 10.53C9.42735 11.2682 8.42651 11.6834 7.38257 11.6846ZM9.37812 5.53277C9.4393 5.59374 9.48784 5.66619 9.52096 5.74595C9.55408 5.82572 9.57114 5.91125 9.57114 5.99762C9.57114 6.08399 9.55408 6.16951 9.52096 6.24928C9.48784 6.32905 9.4393 6.40149 9.37812 6.46246L7.84687 7.99371C7.72358 8.11699 7.55637 8.18625 7.38202 8.18625C7.20767 8.18625 7.04046 8.11699 6.91718 7.99371C6.7939 7.87043 6.72464 7.70322 6.72464 7.52887C6.72464 7.35452 6.7939 7.18731 6.91718 7.06402L8.44843 5.53277C8.5094 5.47159 8.58184 5.42305 8.66161 5.38993C8.74138 5.35681 8.8269 5.33976 8.91327 5.33976C8.99965 5.33976 9.08517 5.35681 9.16494 5.38993C9.2447 5.42305 9.31715 5.47159 9.37812 5.53277ZM5.41382 0.96582C5.41382 0.791772 5.48296 0.624852 5.60603 0.501781C5.7291 0.378711 5.89602 0.30957 6.07007 0.30957H8.69507C8.86912 0.30957 9.03604 0.378711 9.15911 0.501781C9.28218 0.624852 9.35132 0.791772 9.35132 0.96582C9.35132 1.13987 9.28218 1.30679 9.15911 1.42986C9.03604 1.55293 8.86912 1.62207 8.69507 1.62207H6.07007C5.89602 1.62207 5.7291 1.55293 5.60603 1.42986C5.48296 1.30679 5.41382 1.13987 5.41382 0.96582Z" fill="#A7A7A7" />
                      </g>
                      <defs>
                        <clipPath id="clip0_49360_27896">
                          <rect width="14" height="14" fill="white" transform="translate(0.382568 0.0908203)" />
                        </clipPath>
                      </defs>
                    </svg>

                    <?php
                    // Calculate estimated reading time
                    $content = get_post_field('post_content', $post->ID);
                    $content = strip_tags($content);
                    $word_count = str_word_count($content, 0, "آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهی");
                    $reading_time = max(1, ceil($word_count / 200)); // Assuming 200 words per minute
                    ?>
                    <p class="text-xs font-bold text-[#A7A7A7]"><?php echo $reading_time; ?> دقیقه</p>
                  </div>

                  <div class="flex gap-1">
                    <p class="text-xs font-bold text-[#A7A7A7]"><?= get_the_date('Y-m-d') ?></p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                      <g clip-path="url(#clip0_49360_27888)">
                        <path d="M1.48853 7.09074C1.48853 4.89099 1.48853 3.79082 2.17219 3.10774C2.85586 2.42466 3.95544 2.42407 6.15519 2.42407H8.48853C10.6883 2.42407 11.7884 2.42407 12.4715 3.10774C13.1546 3.79141 13.1552 4.89099 13.1552 7.09074V8.25741C13.1552 10.4572 13.1552 11.5573 12.4715 12.2404C11.7879 12.9235 10.6883 12.9241 8.48853 12.9241H6.15519C3.95544 12.9241 2.85528 12.9241 2.17219 12.2404C1.48911 11.5567 1.48853 10.4572 1.48853 8.25741V7.09074Z" stroke="#A7A7A7" stroke-width="1.5" />
                        <path d="M4.40527 2.42407V1.54907M10.2386 2.42407V1.54907M1.78027 5.34074H12.8636" stroke="#A7A7A7" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M10.8218 10.0075C10.8218 10.1622 10.7603 10.3106 10.6509 10.42C10.5415 10.5294 10.3932 10.5908 10.2384 10.5908C10.0837 10.5908 9.93536 10.5294 9.82596 10.42C9.71657 10.3106 9.65511 10.1622 9.65511 10.0075C9.65511 9.85278 9.71657 9.7044 9.82596 9.59501C9.93536 9.48561 10.0837 9.42415 10.2384 9.42415C10.3932 9.42415 10.5415 9.48561 10.6509 9.59501C10.7603 9.7044 10.8218 9.85278 10.8218 10.0075ZM10.8218 7.67415C10.8218 7.82886 10.7603 7.97724 10.6509 8.08663C10.5415 8.19603 10.3932 8.25749 10.2384 8.25749C10.0837 8.25749 9.93536 8.19603 9.82596 8.08663C9.71657 7.97724 9.65511 7.82886 9.65511 7.67415C9.65511 7.51944 9.71657 7.37107 9.82596 7.26167C9.93536 7.15228 10.0837 7.09082 10.2384 7.09082C10.3932 7.09082 10.5415 7.15228 10.6509 7.26167C10.7603 7.37107 10.8218 7.51944 10.8218 7.67415ZM7.90511 10.0075C7.90511 10.1622 7.84365 10.3106 7.73426 10.42C7.62486 10.5294 7.47649 10.5908 7.32178 10.5908C7.16707 10.5908 7.01869 10.5294 6.9093 10.42C6.7999 10.3106 6.73844 10.1622 6.73844 10.0075C6.73844 9.85278 6.7999 9.7044 6.9093 9.59501C7.01869 9.48561 7.16707 9.42415 7.32178 9.42415C7.47649 9.42415 7.62486 9.48561 7.73426 9.59501C7.84365 9.7044 7.90511 9.85278 7.90511 10.0075ZM7.90511 7.67415C7.90511 7.82886 7.84365 7.97724 7.73426 8.08663C7.62486 8.19603 7.47649 8.25749 7.32178 8.25749C7.16707 8.25749 7.01869 8.19603 6.9093 8.08663C6.7999 7.97724 6.73844 7.82886 6.73844 7.67415C6.73844 7.51944 6.7999 7.37107 6.9093 7.26167C7.01869 7.15228 7.16707 7.09082 7.32178 7.09082C7.47649 7.09082 7.62486 7.15228 7.73426 7.26167C7.84365 7.37107 7.90511 7.51944 7.90511 7.67415ZM4.98844 10.0075C4.98844 10.1622 4.92699 10.3106 4.81759 10.42C4.70819 10.5294 4.55982 10.5908 4.40511 10.5908C4.2504 10.5908 4.10203 10.5294 3.99263 10.42C3.88324 10.3106 3.82178 10.1622 3.82178 10.0075C3.82178 9.85278 3.88324 9.7044 3.99263 9.59501C4.10203 9.48561 4.2504 9.42415 4.40511 9.42415C4.55982 9.42415 4.70819 9.48561 4.81759 9.59501C4.92699 9.7044 4.98844 9.85278 4.98844 10.0075ZM4.98844 7.67415C4.98844 7.82886 4.92699 7.97724 4.81759 8.08663C4.70819 8.19603 4.55982 8.25749 4.40511 8.25749C4.2504 8.25749 4.10203 8.19603 3.99263 8.08663C3.88324 7.97724 3.82178 7.82886 3.82178 7.67415C3.82178 7.51944 3.88324 7.37107 3.99263 7.26167C4.10203 7.15228 4.2504 7.09082 4.40511 7.09082C4.55982 7.09082 4.70819 7.15228 4.81759 7.26167C4.92699 7.37107 4.98844 7.51944 4.98844 7.67415Z" fill="#A7A7A7" />
                      </g>
                      <defs>
                        <clipPath id="clip0_49360_27888">
                          <rect width="14" height="14" fill="white" transform="translate(0.321777 0.0908203)" />
                        </clipPath>
                      </defs>
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
<?php endif;
wp_reset_postdata();
?>
<?php
/*===============================================================*/
// محبوب ترین مجموعه ها
get_template_part("template/layout/brands");
?>
<?php get_footer(); ?>
<style>
    .loading-dots {
        display: inline-flex;
        align-items: center;
        gap: 2px;
    }
    
    .loading-dots span {
        display: inline-block;
        animation: loading-dots 1.4s infinite ease-in-out both;
        font-size: inherit;
    }
    
    .loading-dots span:nth-child(1) {
        animation-delay: -0.32s;
    }
    
    .loading-dots span:nth-child(2) {
        animation-delay: -0.16s;
    }
    
    .loading-dots span:nth-child(3) {
        animation-delay: 0s;
    }
    
    @keyframes loading-dots {
        0%, 80%, 100% {
            opacity: 0.3;
            transform: scale(0.8);
        }
        40% {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>
<script>
  jQuery(document).ready(function($) {
    let gameFinderForm = $('#game-finder-form');
    gameFinderForm.on('submit', function() {
      let formData = {};
      // Get all form inputs
      $(this).find('input').each(function() {
        let input = $(this);
        let name = input.attr('name');
        console.log(name);
        // For radio/checkbox inputs, only include if checked
        if ((input.attr('type') === 'radio' || input.attr('type') === 'checkbox') && !input.is(':checked')) {
          return;
        }
        formData[name] = input.val().trim();
      });

      // Get all select elements
      $(this).find('select').each(function() {
        let select = $(this);
        formData[select.attr('name')] = select.val();
      });
      zebline.event.track('game-finder', formData);
    });
    $('body').on('click', '.blog-item', function() {
      let title = $(this).find('h2').text();
      let href = $(this).find('a').attr('href');
      let currentPage = window.location.href;
      var currentPageTitle = '<?= esc_js(get_the_title()) ?>';
      let currentPageId = <?= get_the_ID() ?>;
      zebline.event.track("blog_click", {
        "title": title,
        "href": href,
        "current_page": currentPage,
        "current_page_title": currentPageTitle,
        "current_page_id": currentPageId
      });
    });
    let sliderBtnActive = function() {
      $('.blog-slider-btn').removeClass('text-primary-500')
      $('.blog-slider-btn').attr('disabled', false)
    }
    $('.blog-slider-btn').on('click', function() {
      sliderBtnActive();
      $(this).addClass('text-primary-500');
      $(this).attr('disabled', true)
      let dataSource = $(this).attr('data-source')
      $.ajax({
        url: "<?php echo admin_url('admin-ajax.php') ?>",
        type: 'POST',
        data: {
          'action': 'v2_ajax_handler',
          'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
          'callback': 'blog-cat-slider',
          'data-source': dataSource
        },
        beforeSend: function() {
          $('.blog-btn-slider').hide();
          $("#blog-slider").empty().append(`<div style="height:300px;width:100%;text-align: center; display: flex;align-items: center;justify-content: center;"> لطفا منتظر باشید... </div>`)
        },
        success: function(response) {
          if (response.data.length > 0) {
            $("#blog-slider").empty()
            // حلقه برای اضافه کردن هر عنصر به DOM
            $.each(response.data, function(index, data) {
              let slideHtml = `<div class="embla__slide relative grow-0 shrink-0 w-[264px] h-[300px] blog-item"><a class="relative block w-full overflow-hidden rounded-lg lg:rounded-3xl shadow-8  max-lg:[&_.ez-post-category]:hidden max-lg:[&_.ez-post-desc]:hidden max-lg:[&_.ez-post-info]:hidden w-[264px] h-[300px]"href="${data.url}"><img alt="Product" loading="lazy" width="281" height="328" decoding="async"data-nimg="1" class="object-cover w-full h-full" src="${data.image_url}" style="color: transparent;"><div class="absolute top-0 right-0 flex flex-col justify-between w-full h-full p-6 bg-slate-900/60 text-white/90 max-lg:justify-center"><div class="ez-post-category"><span class="inline px-2 py-1 text-xs font-medium border rounded-md border-white/30 bg-white/5">${data.cat_title}</span></div><div class="text-center"><h2 class="text-lg font-extrabold text-white lg:text-xl line-clamp-2">${data.title}</h2><p class="ez-post-desc mx-auto mt-3.5 max-w-[500px] text-2xs leading-6 lg:mt-5 line-clamp-4">${data.excerpt}</p><div class="flex items-center justify-center gap-5 mt-4 text-xs ez-post-info lg:mt-6"><span>0 دیدگاه</span><span class="h-3.5 border-l border-white/40"></span><span>نویسنده</span></div></div></div></a></div>`;
              // افزودن کد HTML به div مربوطه
              $("#blog-slider").append(slideHtml);
              $('.blog-btn-slider').show();
            });
          } else {
            $("#blog-slider").empty().append('<div style ="height:300px;width:100%;text-align: center; display: flex;align-items: center;justify-content: center;"> لطفا منتظر باشید... </div>')
          }
        }
      })
    })
    $('.city-btn-filter').on('click', function() {
      let id = '#' + $(this).attr('data-input') + '-link'
      let textId = id + '-text'
      let term_id = $(this).attr('data-params')
      let city_id = term_id.match(/\[(\d+)\]/)[1];
      let originalText = $(textId).text();
      
      // Show loading animation
      $(textId).html('<span class="loading-dots"><span>.</span><span>.</span><span>.</span></span>');
      
      $.ajax({
        type: 'POST',
        url: "<?php echo admin_url('admin-ajax.php') ?>", // در وردپرس ajaxurl به طور خودکار تعریف شده است
        data: {
          action: 'get_category_link',
          category_id: city_id // city_id به عنوان شناسه کتگوری ارسال می‌شود
        },
        success: function(response) {
          if (response) {
            let link = response;
            $(id).attr('href', link)
          }
          // Restore original text
          $(textId).text(originalText);
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error('AJAX Error: ' + textStatus);
          // Restore original text on error too
          $(textId).text(originalText);
        }
      });
    })
  })
</script>