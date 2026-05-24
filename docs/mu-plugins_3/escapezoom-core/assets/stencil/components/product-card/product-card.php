<?php

if (!function_exists('ez_product_card_category_label')) {
    function ez_product_card_category_label($product_type)
    {
        switch ($product_type) {
            case 'cafegame':
                return 'کافه بازی ';
            case 'cinema':
                return 'سینما ترس ';
            case 'rageroom':
                return 'اتاق خشم ';
            case 'lasertag':
                return 'لیزرتگ ';
            case 'bubblefootball':
                return 'فوتبال حبابی ';
            case 'paintball':
                return 'پینت بال ';
            case 'haunted_house':
                return 'هانتد هاوس ';
            default:
                return 'اتاق فرار';
        }
    }
}

if (!function_exists('ez_render_product_card')) {
    function ez_render_product_card(array $product, string $home_url)
    {
        $product_type     = $product['type'] ?? '';
        $product_cat_alt  = ez_product_card_category_label($product_type);
        $status           = $product['active'] ?? '';
        $href             = rtrim($home_url, '/') . '/room/' . ($product['url'] ?? '') . '/';
        $is_active        = !in_array($status, ['temp', 'deactivated', 'expired', 'soon'], true);
        $is_updated       = $status === 'updated';
        $location_text    = ($product['hood_name'] ?? '') . ' . ' . ($product['city_name'] ?? '');

        $event            = $product['event'] ?? null;
        $event_expire     = is_array($event) ? ($event['expire_date'] ?? null) : (is_object($event) ? ($event->expire_date ?? null) : null);
        $event_off        = is_array($event) ? ($event['off_percentage'] ?? null) : (is_object($event) ? ($event->off_percentage ?? null) : null);
        $has_event        = $event && $event_expire && $event_expire > time();

        $genres_text = '';
        if (!empty($product['genres']) && (is_array($product['genres']) || $product['genres'] instanceof Traversable)) {
            $titles = [];
            foreach ($product['genres'] as $genre) {
                if (is_object($genre) && isset($genre->title)) {
                    $titles[] = $genre->title;
                } elseif (is_array($genre) && isset($genre['title'])) {
                    $titles[] = $genre['title'];
                }
            }
            $genres_text = implode(' . ', $titles);
        }

        ob_start();
        ?>
        <ez-product-card product-id="<?= $product['product_id']; ?>" status="<?= $status; ?>" href="<?= $href; ?>">
            <?php if (in_array($status, ['temp', 'deactivated'], true)): ?>
                <a slot="media" href="<?= $href; ?>" class="relative after:bg-white/60 after:absolute after:top-0 after:left-0 after:bottom-0 after:right-0">
                    <img alt="<?= $product_cat_alt . ($product['title'] ?? ''); ?>"
                        loading="lazy" width="200" height="248" decoding="async"
                        class="h-[192px] lg:h-[236px] object-cover" src="<?= esc_url($product['image'] ?? ''); ?>"
                        style="color: transparent;">
                </a>
                <span slot="badge" class="bg-[#62748E] rounded-[9px] text-white w-[118px] h-[34px] absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 font-bold text-center text-shadow">غیرفعال</span>
            <?php elseif ($status === 'expired'): ?>
                <a slot="media" href="<?= $href; ?>" class="relative after:bg-white/60 after:absolute after:top-0 after:left-0 after:bottom-0 after:right-0">
                    <img alt="<?= $product_cat_alt . ($product['title'] ?? ''); ?>"
                        loading="lazy" width="200" height="248" decoding="async"
                        class="h-[192px] lg:h-[236px] object-cover" src="<?= esc_url($product['image'] ?? ''); ?>"
                        style="color: transparent;">
                </a>
                <span slot="badge" class="bg-[#62748E] rounded-[9px] text-white w-[118px] h-[34px] absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 font-bold text-center text-shadow">اکسپایر شده</span>
            <?php elseif ($status === 'soon'): ?>
                <a slot="media" href="<?= $href; ?>">
                    <img alt="<?= $product_cat_alt . ($product['title'] ?? ''); ?>"
                        loading="lazy" width="200" height="248" decoding="async"
                        class="h-[192px] lg:h-[236px] object-cover" src="<?= esc_url($product['image'] ?? ''); ?>"
                        style="color: transparent;">
                    <span class="bg-[#2B7FFF] text-white leading-none px-2.5 py-1 rounded-[40px] h-5 text-sm absolute top-4 right-4 font-bold">به زودی</span>
                </a>
            <?php else: ?>
                <a slot="media" href="<?= $href; ?>">
                    <img alt="<?= $product_cat_alt . ($product['title'] ?? ''); ?>"
                        loading="lazy" width="200" height="248" decoding="async"
                        class="h-[192px] lg:h-[236px] object-cover" src="<?= esc_url($product['image'] ?? ''); ?>"
                        style="color: transparent;">
                    <?php if ($is_updated): ?>
                        <span class="bg-[#F21543] text-white leading-none px-2.5 py-1 rounded-[40px] h-5 text-sm absolute top-4 right-4 font-bold">آپدیت شد</span>
                    <?php endif; ?>
                </a>
                <button type="button" slot="floating-action"
                    class="absolute bottom-2 right-2 flex h-7.5 w-7.5 items-center justify-center rounded-full bg-[#EFC101]/30 lg:hidden mobile-hover">
                    <span class="flex h-4.5 w-4.5 items-center justify-center rounded-full bg-white drop-shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="5" height="11" viewBox="0 0 5 11" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M2.5 0C2.78179 0 3.05204 0.111941 3.2513 0.311198C3.45055 0.510455 3.5625 0.780706 3.5625 1.0625C3.5625 1.34429 3.45055 1.61454 3.2513 1.8138C3.05204 2.01305 2.78179 2.125 2.5 2.125C2.21821 2.125 1.94796 2.01305 1.7487 1.8138C1.54944 1.61454 1.4375 1.34429 1.4375 1.0625C1.4375 0.780706 1.54944 0.510455 1.7487 0.311198C1.94796 0.111941 2.21821 0 2.5 0Z"
                                fill="#827748"></path>
                            <path d="M2.71211 9.77811V3.82812H1.86211M1.22461 9.77811H4.1996" stroke="#827748"
                                stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </button>
                <a slot="overlay-panel" href="<?= $href; ?>"
                    class="absolute left-0 top-0 flex h-full w-full flex-col justify-between bg-slate-900/80 px-1.5 md:px-4 py-2.5 md:py-5 text-2xs text-white transition-all max-lg:hidden lg:scale-90 lg:opacity-0 lg:hover:scale-100 lg:hover:opacity-100">
                    <?php if ($product_type === 'escaperoom'): ?>
                        <div class="mx-auto flex w-[90%] items-center justify-center gap-x-1 rounded bg-white/20 px-6 py-1.5 leading-none">
                            <div class="max-lg:hidden lg:flex lg:item-center lg:justify-between w-full">
                                <span class="text-[#FFFFFF]/70 text-2xs leading-none flex items-center">امروز</span>
                                <?php if (!empty($product['free_sanses'])): ?>
                                    <span class="flex items-center gap-x-1">
                                        <span class="text-xl leading-none font-bold"><?= $product['free_sanses']; ?></span>
                                        <span class="text-2xs leading-none">سانس</span>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xl leading-none">تکمیله</span>
                                <?php endif; ?>
                            </div>
                            <div class="lg:hidden py-1 text-nowrap text-3xs font-bold line-clamp-1" name="genres"><?= $genres_text; ?></div>
                        </div>
                    <?php endif; ?>
                    <span class="flex items-center justify-between py-1">
                        <span>
                            <span class="max-lg:hidden">مدت زمان سانس</span>
                            <span class="lg:hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                    <path d="M7.00039 1.89844C3.96039 1.89844 1.40039 4.45844 1.40039 7.49844C1.40039 10.5384 3.96039 13.0984 7.00039 13.0984C10.0404 13.0984 12.6004 10.5384 12.6004 7.49844C12.6004 4.45844 10.0404 1.89844 7.00039 1.89844ZM8.63372 9.59844L6.53372 7.73177V3.7651H7.46706V7.2651L9.33372 8.89844L8.63372 9.59844Z"
                                        fill="white"></path>
                                </svg>
                            </span>
                        </span>
                        <span><span class="ml-px text-base font-bold" dir="ltr"><?= $product['duration'] ?? ''; ?></span> دقیقه </span>
                    </span>
                    <span class="flex items-center justify-between border-b border-t border-b-white/50 border-t-white/50 py-3">
                        <?php if ($product_type === 'escaperoom'): ?>
                            <span>
                                <span class="max-lg:hidden">میزان سختی</span>
                                <span class="lg:hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M9.75078 4.97094V3.73594C9.75078 2.00694 8.32078 0.648438 6.50078 0.648438C4.68078 0.648438 3.25078 2.00694 3.25078 3.73594V4.97094C2.14578 4.97094 1.30078 5.77369 1.30078 6.82344V11.1459C1.30078 12.1957 2.14578 12.9984 3.25078 12.9984H9.75078C10.8558 12.9984 11.7008 12.1957 11.7008 11.1459V6.82344C11.7008 5.77369 10.8558 4.97094 9.75078 4.97094ZM4.55078 3.73594C4.55078 2.68619 5.39578 1.88344 6.50078 1.88344C7.60578 1.88344 8.45078 2.68619 8.45078 3.73594V4.97094H4.55078V3.73594ZM7.15078 9.91094C7.15078 10.2814 6.89078 10.5284 6.50078 10.5284C6.11078 10.5284 5.85078 10.2814 5.85078 9.91094V8.05844C5.85078 7.68794 6.11078 7.44094 6.50078 7.44094C6.89078 7.44094 7.15078 7.68794 7.15078 8.05844V9.91094Z"
                                            fill="white"></path>
                                    </svg>
                                </span>
                            </span>
                            <span>
                                <span class="ml-px text-base font-bold">
                                    <?= $product['level'] ?? ''; ?>
                                </span> از <span class="text-base font-bold">4</span>
                            </span>
                        <?php else: ?>
                            <span>
                                <span class="max-lg:hidden">مناسب سن</span>
                                <span class="lg:hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                        <path d="M13.2742 7.36244C12.7944 6.16677 11.9578 5.13089 10.8675 4.38244L12.8794 2.46244L12.2872 1.89844L1.11905 12.5344L1.71127 13.0984L3.85333 11.0624C4.8096 11.5928 5.89276 11.8806 6.99922 11.8984C8.36824 11.8494 9.69217 11.4194 10.8074 10.6616C11.9226 9.90379 12.7802 8.85138 13.2742 7.63444C13.3076 7.54655 13.3076 7.45032 13.2742 7.36244ZM6.99922 10.0984C6.42028 10.0982 5.8566 9.92158 5.39057 9.59444L6.15919 8.87044C6.4779 9.03674 6.84463 9.10014 7.20438 9.05113C7.56412 9.00213 7.89748 8.84335 8.15445 8.59863C8.41142 8.3539 8.57814 8.03643 8.6296 7.69382C8.68106 7.35122 8.61448 7.00196 8.43986 6.69844L9.20008 5.97444C9.49597 6.36148 9.67372 6.8189 9.71367 7.29614C9.75362 7.77338 9.65421 8.25185 9.42645 8.67864C9.19868 9.10544 8.85142 9.46394 8.42306 9.7145C7.9947 9.96507 7.50193 10.0979 6.99922 10.0984ZM2.18168 9.82244L4.28174 7.82244C4.27097 7.71476 4.26677 7.60658 4.26914 7.49844C4.27025 6.8092 4.55824 6.14849 5.06999 5.66113C5.58174 5.17376 6.2755 4.8995 6.99922 4.89844C7.11013 4.89914 7.22091 4.90582 7.33103 4.91844L8.91867 3.41044C8.30075 3.20839 7.65253 3.10302 6.99922 3.09844C5.6302 3.14747 4.30627 3.57746 3.19106 4.33527C2.07585 5.09308 1.21824 6.1455 0.724241 7.36244C0.690878 7.45032 0.690878 7.54655 0.724241 7.63444C1.04725 8.45129 1.54336 9.19608 2.18168 9.82244Z"
                                            fill="white"></path>
                                    </svg>
                                </span>
                            </span>
                            <span class="ml-px text-base font-bold"><?= $product['age'] ?? ''; ?>+</span>
                        <?php endif; ?>
                    </span>
                    <span class="flex items-center justify-between max-lg:py-1 lg:border-b lg:border-b-white/50 lg:pb-3">
                        <span>
                            <span class="max-lg:hidden">ظرفیت هر سانس</span>
                            <span class="lg:hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                    <circle cx="6.99961" cy="4.35313" r="3.15" fill="white"></circle>
                                    <ellipse cx="7.00039" cy="11.3562" rx="5.6" ry="2.45" fill="white"></ellipse>
                                </svg>
                            </span>
                        </span>
                        <span>
                            <span class="ml-px text-base font-bold">
                                <?= $product['number_min'] ?? ''; ?>
                            </span> تـا <span class="ml-px text-base font-bold">
                                <?= $product['number_max'] ?? ''; ?>
                            </span>
                            <span class="max-lg:hidden">نفر</span>
                        </span>
                    </span>
                    <span class="flex items-center justify-center mx-auto rounded-xl bg-[#5091FB]/40 px-2 py-0.5 relative w-[90%] h-[30px] lg:hidden">
                        <button type="button"
                            class="absolute right-[6px] top-[6px]">
                            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-white drop-shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="10" viewBox="0 0 13 10" fill="none">
                                    <path d="M11.5863 4.27438C11.751 4.50513 11.8333 4.62104 11.8333 4.79167C11.8333 4.96283 11.751 5.07821 11.5863 5.30896C10.8464 6.34679 8.95654 8.58333 6.41667 8.58333C3.87625 8.58333 1.98692 6.34625 1.247 5.30896C1.08233 5.07821 1 4.96229 1 4.79167C1 4.6205 1.08233 4.50513 1.247 4.27438C1.98692 3.23654 3.87679 1 6.41667 1C8.95708 1 10.8464 3.23708 11.5863 4.27438Z"
                                        stroke="#294276" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    </path>
                                    <path d="M8.04102 4.78906C8.04102 4.35809 7.86981 3.94476 7.56506 3.64001C7.26032 3.33527 6.84699 3.16406 6.41602 3.16406C5.98504 3.16406 5.57171 3.33527 5.26697 3.64001C4.96222 3.94476 4.79102 4.35809 4.79102 4.78906C4.79102 5.22004 4.96222 5.63336 5.26697 5.93811C5.57171 6.24286 5.98504 6.41406 6.41602 6.41406C6.84699 6.41406 7.26032 6.24286 7.56506 5.93811C7.86981 5.63336 8.04102 5.22004 8.04102 4.78906Z"
                                        stroke="#294276" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    </path>
                                </svg>
                            </span>
                        </button>
                        <span class="text-md font-bold leading-none pt-1">مشاهده</span>
                    </span>
                    <span class="max-lg:hidden text-2xs pt-2 text-center" name="genres"><?= $genres_text; ?></span>
                </a>
            <?php endif; ?>

            <div slot="meta" class="flex items-center justify-between my-3">
                <span class="text-base font-medium text-[#62748E] leading-none">
                    <?= $product_cat_alt; ?>
                </span>
                <?php if ($is_active): ?>
                    <span class="text-sm rounded-[4px] flex items-center justify-center leading-none pt-px bg-yellow-400 text-slate-900 w-[31px] h-[18.5px]" name="rate">
                        <?= $product_cat_alt === 'اتاق فرار' ? ($product['rate'] ?? '') : (($product['rate'] ?? 0) * 5); ?>
                    </span>
                <?php endif; ?>
            </div>

            <div slot="title" class="flex items-center justify-between">
                <h3 class="w-full truncate text-base font-bold lg:text-[22px]" title="<?= $product['title'] ?? ''; ?>" name="title">
                    <a href="<?= $href; ?>">
                        <?= $product['title'] ?? ''; ?>
                    </a>
                </h3>
            </div>

            <p slot="address" class="text-4xs text-[#565656] lg:text-2xs lg:[word-spacing:0.375rem] mt-3 flex items-center gap-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none" class="mx-0 ">
                    <path d="M5.99984 0.833374C3.0665 0.833374 0.666504 3.23337 0.666504 6.16671C0.666504 9.76671 5.33317 13.8334 5.53317 14.0334C5.6665 14.1 5.8665 14.1667 5.99984 14.1667C6.13317 14.1667 6.33317 14.1 6.4665 14.0334C6.6665 13.8334 11.3332 9.76671 11.3332 6.16671C11.3332 3.23337 8.93317 0.833374 5.99984 0.833374ZM5.99984 12.6334C4.59984 11.3 1.99984 8.43337 1.99984 6.16671C1.99984 3.96671 3.79984 2.16671 5.99984 2.16671C8.19984 2.16671 9.99984 3.96671 9.99984 6.16671C9.99984 8.36671 7.39984 11.3 5.99984 12.6334ZM5.99984 3.50004C4.53317 3.50004 3.33317 4.70004 3.33317 6.16671C3.33317 7.63337 4.53317 8.83337 5.99984 8.83337C7.4665 8.83337 8.6665 7.63337 8.6665 6.16671C8.6665 4.70004 7.4665 3.50004 5.99984 3.50004ZM5.99984 7.50004C5.2665 7.50004 4.6665 6.90004 4.6665 6.16671C4.6665 5.43337 5.2665 4.83337 5.99984 4.83337C6.73317 4.83337 7.33317 5.43337 7.33317 6.16671C7.33317 6.90004 6.73317 7.50004 5.99984 7.50004Z"
                        fill="#90A1B9" />
                </svg>
                <span class="text-2xs pt-1" name="address"> <?= $location_text; ?></span>
            </p>

            <?php if ($is_active): ?>
                <div slot="pricing" class="flex items-center justify-center gap-x-2 bg-[#ECECEE] px-2 rounded-[6px] mt-3">
                    <?php if ($has_event): ?>
                        <span class="bg-[#F21543] text-white rounded-[40px] w-8 h-4 flex items-center justify-center">
                            <span class="text-heavy text-md pt-1">
                                <?= $event_off; ?>
                            </span>
                            <span class="text-heavy text-md pt-1">%</span>
                        </span>
                    <?php endif; ?>
                    <div>
                        <span class="text-[#62748E] ml-1">از</span>
                        <span>
                            <span class="ml-px text-md font-bold" name="price">
                                <?= number_format($product['price'] ?? 0); ?>
                            </span>
                            <span class="text-[#62748E]">تومان</span>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </ez-product-card>
        <?php
        return ob_get_clean();
    }
}
