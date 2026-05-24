<?php add_shortcode('single-product-days', function(){ ?>
<div class="relative w-full" dir="rtl">
    <div class="h-full w-full overflow-hidden px-0">
        <div class="flex h-full touch-pan-y -ml-1" style="transform: translate3d(0px, 0px, 0px);">
            <div class="relative min-w-0 shrink-0 grow-0 basis-13 pl-1">
                <button type="button" class="yesterday border-gray-120 flex h-15 w-full cursor-pointer flex-col items-center justify-center rounded-[10px] border text-lg leading-none shadow-13 transition-all duration-300">
                    <span class="mt-0.2 text-3xs">روز قبل</span>
                </button>
            </div>
            <?php $current_date = strtotime( date( 'Y-m-d 00:00:00' ) );
            $dates              = [];
            for ( $i = 1; $i <= 15; $i ++ ) {
                $dates[] = $current_date + ( 60 * 60 * 24 * $i );
            } ?>
            <div class="swiper select-day">
                <div class="swiper-wrapper pb-1">
                    <div class="swiper-slide">
                        <div class="relative min-w-0 shrink-0 grow-0 basis-13 pl-1">
                            <button type="button" data-reserve-timestamp="<?php echo esc_attr( $current_date ); ?>" class="active border-blue bg-blue text-white flex h-15 w-full cursor-pointer flex-col items-center justify-center rounded-[10px] border text-lg leading-none shadow-13 transition-all duration-300">
                                امروز
                            </button>
                        </div>
                    </div>
                    <?php foreach ( $dates as $date ) { ?>
                        <div class="swiper-slide">
                            <div class="relative min-w-0 shrink-0 grow-0 basis-13 pl-1">
                                <button type="button" data-reserve-timestamp="<?php echo esc_attr( $date ); ?>" class="border-gray-120 flex h-15 w-full cursor-pointer flex-col items-center justify-center rounded-[10px] border text-lg leading-none shadow-13 transition-all duration-300">
                                    <?php echo esc_html( jdate( 'd', $date ) ) ?>
                                    <div class="mt-0.2 text-3xs"><?php echo esc_html( jdate( 'l', $date ) ) ?></div>
                                </button>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="relative min-w-0 shrink-0 grow-0 basis-13 pl-1">
                <button type="button" class="tomorrow border-gray-120 flex h-15 w-full cursor-pointer flex-col items-center justify-center rounded-[10px] border text-lg leading-none shadow-13 transition-all duration-300">
                    <span class="mt-0.2 text-3xs">روز بعد</span>
                </button>
            </div>

        </div>

    </div>
</div>
<?php });