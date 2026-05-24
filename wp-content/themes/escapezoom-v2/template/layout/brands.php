<?php
$brands = get_terms([
  'taxonomy' => 'yith_product_brand',
  'hide_empty' => false,
  'number' => 15,
  'orderby' => 'meta_value_num',
  'meta_key' => 'brand_reputation',
  'meta_compare' => 'NUMERIC',
  'order' => 'DESC'
]);

foreach ($brands as $brand) {
  $brand_id = $brand->term_id;

  $image_url = Theme_URL . 'assets/images/brand-default-icon.png';

  $brand_img_id = get_term_meta($brand_id, 'thumbnail_id', true);
  if ($brand_img_id > 0)
    $image_url = wp_get_attachment_image_src($brand_img_id, 'full')[0];

  $brand_items[] = [
    'id'    => $brand_id,
    'title' => $brand->name,
    'image' => $image_url,
    'url'   => trim_home_url(get_term_link($brand)),
    'count' => 5,
  ];
}
?>
<?php if ($brand_items): ?>
  <section class="max-w-full py-4 md:py-5 lg:py-9">
    <div class="mb-6 md:mb-8">
      <div class="flex justify-between">
        <div class="items-center gap-6 md:flex">
          <h3 class="flex items-center gap-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
              <path d="M10.5 25.6666H24.5V5.83325C24.5 2.93759 23.8957 2.33325 21 2.33325H14C11.1043 2.33325 10.5 2.93759 10.5 5.83325V25.6666ZM10.5 25.6666V9.33325H7C4.10433 9.33325 3.5 9.93758 3.5 12.8333V25.6666H10.5ZM18.6667 6.99992H16.3333M18.6667 10.4999H16.3333M18.6667 13.9999H16.3333" stroke="#09192D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M14.5833 25.6665V20.9998C14.5833 19.8997 14.5833 19.3502 14.9251 19.0083C15.267 18.6665 15.8165 18.6665 16.9166 18.6665H18.0833C19.1835 18.6665 19.733 18.6665 20.0748 19.0083C20.4166 19.3502 20.4166 19.8997 20.4166 20.9998V25.6665" stroke="#09192D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span class="text-base font-bold md:text-lg flex items-center gap-2">
              <span class="text-md">محبوب ترین میزبان ها</span>
              <span class="help" data-help="برندهای مجموعه‌دار بازی که طراحی، ساخت، ارائه و اجرای بازی رو برعهده دارن."></span>
            </span>
          </h3>
          <div class="hidden md:block"></div>
        </div>
        <div class="flex items-center gap-6">
          <div class="hidden md:block"></div>
          <a href="<?= home_url('/brands/') ?>">
            <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs hover:text-primary-500 transition">مشاهده همه
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24"
                class="max-lg:hidden">
                <path clip-rule="evenodd"
                  d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                  stroke-linejoin="round"></path>
                <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z"
                  fill="currentColor"></path>
              </svg>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lg:hidden">
                <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
              </svg>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="embla_normal relative overflow-hidden">
      <div class="embla__viewport">
        <div id="trends-rooms-slider" class="embla__container flex gap-x-4 lg:gap-x-12.5 first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full child:box-content">
          <?php foreach ($brand_items as $bd): ?>
            <div class="embla__slide relative py-2.5 w-18 md:w-30 shrink-0 grow-0 brand-item">
              <a class="w-full md:w-30 py-2 flex flex-col" href="<?= home_url() . $bd['url'] ?>">
                <img src="<?= $bd['image'] ?>" alt="<?= $bd['title'] ?>" class="w-11.5 h-11.5 mx-auto md:h-30 md:w-30 object-cover rounded-md lg:rounded-3xl lg:shadow-11">
                <bdo dir="<?= htmlspecialchars(getTextDirection($bd['title'])); ?>" class="line-clamp-1 max-lg:text-5xs mt-4 text-center"><?= $bd['title'] ?></bdo>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="embla__button embla__button--prev trends-rooms-btn absolute right-0 top-1/2 translate-y-[-60px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
      <button class="embla__button embla__button--next trends-rooms-btn absolute left-0 top-1/2 translate-y-[-60px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
<?php endif; ?>

<script>
  jQuery(document).ready(function($) {
    $('.brand-item').on('click', function() {
      var title = $(this).find('bdo').text();
      var href = $(this).find('a').attr('href');
      var currentPage = window.location.href;
      var currentPageTitle = '<?= esc_js(get_the_title()) ?>';
      var currentPageId = <?= get_the_ID() ?>;
      zebline.event.track("brand_click", {
        "title": title,
        "href": href,
        "current_page": currentPage,
        "current_page_title": currentPageTitle,
        "current_page_id": currentPageId,
      });
    });
  });
</script>