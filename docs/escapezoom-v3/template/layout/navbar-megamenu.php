<?php
global $post;

$is_single_blog = is_single() && $post->post_type == 'post';
$is_blog_archive = ((is_category() || is_tag() || is_archive()) && $post->post_type == 'post') || is_page('blog');
$is_blog_page = $is_single_blog || $is_blog_archive;
$menu_location = $is_blog_page ? 'blog' : 'header';
$header_menu_items = get_option('ez_mega_menu_' . $menu_location, []);

if ($is_blog_page && empty($header_menu_items)) {
    $header_menu_items = get_option('ez_mega_menu_header', []);
}

if (empty($header_menu_items)) {
    get_template_part('template/layout/navbar', null, $args);
    return;
}

$is_mobile = wp_is_mobile();
?>

<div class="flex items-center gap-3 z-50 relative">
    <?php foreach ($header_menu_items as $menu_item):
        // بررسی visibility
        $item_visibility = $menu_item['item_visibility'] ?? 'both';
        if ($item_visibility === 'none') continue;
        if ($item_visibility === 'mobile' && !$is_mobile) continue;
        if ($item_visibility === 'desktop' && $is_mobile) continue;

        // بررسی وجود فرزندان
        $has_children = !empty($menu_item['children']);

        // اطلاعات آیکون
        $icon_type = $menu_item['icon_type'] ?? 'image';
        $icon_value = $menu_item['icon_value'] ?? '';
        $icon_visibility = $menu_item['icon_visibility'] ?? 'both';

        // آیا باید آیکون نمایش داده بشه؟
        $show_icon = !empty($icon_value);
        if ($icon_visibility === 'none') $show_icon = false;
        if ($icon_visibility === 'mobile' && !$is_mobile) $show_icon = false;
        if ($icon_visibility === 'desktop' && $is_mobile) $show_icon = false;
    ?>

        <nav aria-label="Main" data-orientation="horizontal" dir="rtl" class="<?php echo $has_children ? 'z-50' : 'z-10'; ?> flex max-w-max flex-1 items-center justify-center">
            <div>
                <ul data-orientation="horizontal" class="group flex flex-1 list-none items-center justify-center gap-3" dir="rtl">
                    <li class="<?php echo $has_children ? 'group' : ''; ?>">
                        <?php if ($has_children): ?>
                            <!-- Menu Item with Children (Dropdown) -->
                            <button data-state="closed" aria-expanded="false"
                                class="group justify-center bg-background text-sm font-medium transition-colors hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none disabled:pointer-events-none disabled:opacity-50 data-[active]:bg-accent/50 data-[state=open]:bg-accent/50 group shadow-wrapper inline-flex h-11.5 w-full items-center rounded-lg px-6 py-5.5 before:rounded-lg hover:bg-button-gradient hover:shadow-none"
                                data-radix-collection-item="">

                                <?php if ($show_icon): ?>
                                    <?php if ($icon_type === 'svg'): ?>
                                        <span class="menu-icon-svg" style="width: 18px;">
                                            <?php echo $icon_value; ?>
                                        </span>
                                    <?php else: ?>
                                        <img src="<?php echo esc_url($icon_value); ?>" alt="" width="18" class="menu-icon-image w-4.5">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Default Icon -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="18" viewBox="0 0 24 24">
                                        <path clip-rule="evenodd" d="M16.334 2.75H7.665c-3.02 0-4.915 2.14-4.915 5.166v8.168c0 3.027 1.885 5.166 4.915 5.166h8.668c3.031 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path clip-rule="evenodd" d="M10.692 12a1.852 1.852 0 11-3.705 0 1.852 1.852 0 013.705 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M10.692 12h6.318v1.852M14.181 13.852V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                <?php endif; ?>

                                <span class="mx-2.5 text-nowrap text-2xs"><?php echo esc_html($menu_item['title']); ?></span>

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" viewBox="0 0 24 24" class="relative top-px ml-1 h-3 w-3 transition duration-300 group-data-[state=open]:rotate-180" aria-hidden="true">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m19 8.5-7 7-7-7" vector-effect="non-scaling-stroke"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="hidden right-0 group-hover:grid grid-cols-3 min-w-megamenu items-center justify-between ez-megamenu-panel-bg absolute shadow-13 shadow rounded-xl">
                                <div class="col-span-3 p-9 grid grid-cols-6">
                                    <div class="px-3 col-span-5">
                                        <a href="<?php echo esc_url($menu_item['url']); ?>">
                                            <h5 class="font-bold"><?php echo esc_html($menu_item['title']); ?></h5>
                                        </a>
                                        <div class="relative mb-4 mt-3 w-full border-t border-slate-100 after:absolute after:-top-px after:right-0 after:z-20 after:block after:h-px after:w-8 after:bg-primary-500 after:content-['']"></div>
                                        <div class="flex flex-col gap-2">
                                            <ul class="grid grid-cols-6 text-xs grow">
                                                <?php foreach ($menu_item['children'] as $child):
                                                    // بررسی visibility فرزند
                                                    $child_visibility = $child['item_visibility'] ?? 'both';
                                                    if ($child_visibility === 'none') continue;
                                                    if ($child_visibility === 'mobile' && !$is_mobile) continue;
                                                    if ($child_visibility === 'desktop' && $is_mobile) continue;
                                                ?>
                                                    <li class="leading-8">
                                                        <a class="cursor-pointer hover:text-primary-500" href="<?php echo esc_url($child['url']); ?>">
                                                            <?php echo esc_html($child['title']); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Simple Menu Item (No Children) -->
                            <a href="<?php echo esc_url($menu_item['url']); ?>"
                                class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-white text-gray-900 border border-gray-100 hover:bg-button-gradient focus-visible:bg-button-gradient min-w-16 py-2 shadow-wrapper h-11.5 rounded-lg px-6 before:rounded-lg hover:shadow-none">
                                <span class="truncate">
                                    <?php if ($show_icon): ?>
                                        <?php if ($icon_type === 'svg'): ?>
                                            <span class="menu-icon-svg inline-block" style="width: 18px; vertical-align: middle; margin-left: 8px;">
                                                <?php echo $icon_value; ?>
                                            </span>
                                        <?php else: ?>
                                            <img src="<?php echo esc_url($icon_value); ?>" alt="" width="18" class="menu-icon-image inline-block" style="vertical-align: middle; margin-left: 8px;">
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <span class="mx-2.5 text-2xs"><?php echo esc_html($menu_item['title']); ?></span>
                                </span>
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
            <div class="absolute right-0 top-full flex justify-center"></div>
        </nav>

    <?php endforeach; ?>
</div>