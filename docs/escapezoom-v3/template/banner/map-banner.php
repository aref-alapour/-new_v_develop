<!--<section class="max-w-full py-4 md:py-5 lg:py-9">-->
<!--    <div class="flex justify-between overflow-hidden rounded-xl bg-cover bg-center bg-no-repeat p-8 lg:px-16 lg:py-12 bg-[url('/assets/img/Image.svg')] shadow-8 max-md:justify-center"-->
<!--         style="background-image: url('http://escapezoom.ir/wp-content/uploads/2024/10/Tehran-Nights-forever.jpg');">-->
<!--        <div class="max-w-[14.5rem]">-->
<!--            <h2 class="mb-4 text-xl font-extrabold text-white md:text-3xl [&_b]:text-accent-400">-->
<!--                    <span>نزدیکترین <b style="color: rgb(2, 255, 142)">اتاق فرار</b> را بر روی نقشه پیدا کنید-->
<!--                    </span>-->
<!--            </h2>-->
<!--            <a class="flex gap-4 items-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none w-full bg-accent-400 text-slate-700 shadow-16 hover:bg-accent-500 focus-visible:outline-accent-500 h-16 min-w-16 px-9 py-2 rounded-2xl max-lg:rounded-lg max-lg:h-12 max-lg:min-w-12 max-lg:px-6 max-lg:py-1 justify-between shadow-none"-->
<!--               href="<?= home_url('/maps/') ?>"-->
<!--               style="background-color: rgb(2, 255, 142);">-->
<!--                <span class="truncate">مشاهده نقشه</span>-->
<!--                <span>-->
<!--                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" viewBox="0 0 24 24"-->
<!--                             stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">-->
<!--                            <path d="M4.25 12.274h15m-8.95 6.025-6.05-6.024L10.3 6.25"-->
<!--                                  vector-effect="non-scaling-stroke"></path>-->
<!--                        </svg>-->
<!--                    </span>-->
<!--            </a>-->
<!--        </div>-->
<!--        <div class="-ml-8 -mt-4.5 hidden text-2xs text-white/50 md:block">مجموع اتاق فرارهای ایران-->
<!--            <span class="mx-2.5 text-base font-semibold text-white/80">۱٬۳۶۶</span>-->
<!--        </div>-->
<!--    </div>-->
<!--</section>-->
<a href="<?= home_url('/maps/') ?>" onclick="zebline.event.track('map_banner_click', {
    'title': 'مشاهده نقشه',
    'href': '<?= home_url('/maps/') ?>',
    'current_page': window.location.href,
    'current_page_title': <?= get_the_title() ?>,
    'current_page_id': <?= get_the_ID() ?>,
});">
    <img src="<?= Theme_URL ?>assets/images/map-banner-lg.avif" class="max-lg:hidden" />
    <img src="<?= Theme_URL ?>assets/images/map-banner-sm.avif" class="lg:hidden" />
</a>