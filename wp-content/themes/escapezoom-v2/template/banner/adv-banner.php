<?php
global $params;
$slider_model = $params['slider_model'];
$items = $params['items'];
if ($items):
?>
    <div class="relative w-full overflow-hidden embla_fade rounded-[14px] lg:rounded-[20px] <?= ($slider_model == 'wide') ? 'mt-7.5 lg:mt-10' : '' ?>">
        <div class="embla__viewport relative min-h-[350px] md:min-h-[500px]">
            <div class="embla__container relative w-full min-h-[350px] md:min-h-[500px] md:h-[440px]">
                <?php foreach ($items as $item): ?>
                    <div class="embla__slide adv-banner absolute w-full min-h-[350px] md:min-h-[500px] md:h-[440px] select-none" style="opacity: 0;transition: opacity 1.5s ease;" data-title="<?= @$item['title'] ?>">
                        <a class="h-full block" href="<?= $item['target-link'] ?>">
                            <img class="lg:hidden h-full object-cover" src="<?= $item['md-src'] ?>" />
                            <img class="max-lg:hidden h-full object-cover" src="<?= $item['lg-src'] ?>" />
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="embla__button embla__button--prev absolute right-0 top-1/2 -translate-y-1/2 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px max-md:hidden" type="button">
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
        <button class="embla__button embla__button--next absolute left-0 top-1/2 -translate-y-1/2 z-50 cursor-pointer touch-manipulation appearance-none -ml-px max-md:hidden" type="button">
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
        <div class="embla__dots" style="bottom: -2px !important;"></div>
    </div>
<?php endif; ?>
<script>
    jQuery(document).ready(function($) {
        $('.adv-banner').on('click', function() {
            var title = $(this).data('title');
            var href = $(this).find('a').attr('href');
            var currentPage = window.location.href;
            var currentPageTitle = '<?= esc_js(get_the_title()) ?>';
            var currentPageId = <?= get_the_ID() ?>;
            zebline.event.track("adv_banner_click", {
                "title": title,
                "href": href,
                "current_page": currentPage,
                "current_page_title": currentPageTitle,
                "current_page_id": currentPageId,
            });
        });
    });
</script>