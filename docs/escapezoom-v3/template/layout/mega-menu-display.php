<?php

/**
 * Mega Menu Display Functions
 * فانکشن‌های نمایش مگامنو در فرانت‌اند
 */

/**
 * نمایش مگامنو در فرانت‌اند
 * 
 * @param string $location لوکیشن منو (header, footer, sidebar, ...)
 * @param array $args آرگومان‌های اختیاری برای سفارشی‌سازی نمایش
 * @return string HTML مگامنو
 */
function display_mega_menu($location = 'header', $args = [])
{
    $defaults = [
        'container_class' => 'mega-menu-container',
        'menu_class' => 'mega-menu',
        'item_class' => 'mega-menu-item',
        'submenu_class' => 'mega-menu-submenu',
        'has_children_class' => 'has-children',
        'show_icons' => true,
        'echo' => true
    ];

    $args = wp_parse_args($args, $defaults);

    // Get menu items for specific location from separate option
    $menu_items = get_option('ez_mega_menu_' . $location, []);

    if (empty($menu_items)) {
        return '';
    }

    ob_start();
?>
    <nav class="<?php echo esc_attr($args['container_class']); ?>">
        <ul class="<?php echo esc_attr($args['menu_class']); ?>">
            <?php foreach ($menu_items as $item): ?>
                <?php
                // Check item visibility - این فایل برای دسکتاپ است
                $item_visibility = $item['item_visibility'] ?? 'both';
                if ($item_visibility === 'none') continue;
                if ($item_visibility === 'mobile') continue; // فقط mobile را نمایش نمی‌دهیم

                $has_children = !empty($item['children']);
                $item_classes = [$args['item_class']];
                if ($has_children) {
                    $item_classes[] = $args['has_children_class'];
                }

                // Add visibility classes
                $item_classes[] = 'visibility-' . $item_visibility;

                // Icon data
                $icon_type = $item['icon_type'] ?? 'image';
                $icon_value = $item['icon_value'] ?? '';
                $icon_visibility = $item['icon_visibility'] ?? 'both';

                // Check if we should show icon - این فایل برای دسکتاپ است
                $show_icon = $args['show_icons'] && !empty($icon_value);
                if ($icon_visibility === 'none') $show_icon = false;
                if ($icon_visibility === 'mobile') $show_icon = false; // فقط mobile را نمایش نمی‌دهیم
                ?>
                <li class="<?php echo esc_attr(implode(' ', $item_classes)); ?>" data-visibility="<?php echo esc_attr($item_visibility); ?>">
                    <a href="<?php echo esc_url($item['url']); ?>" class="mega-menu-link">
                        <?php if ($show_icon): ?>
                            <?php if ($icon_type === 'svg'): ?>
                                <span class="menu-icon svg-icon" style="width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center;"><?php echo $icon_value; ?></span>
                            <?php else: ?>
                                <img src="<?php echo esc_url($icon_value); ?>" alt="<?php echo esc_attr($item['title']); ?>" class="menu-icon" style="width: 18px; height: 18px; object-fit: contain;">
                            <?php endif; ?>
                        <?php endif; ?>
                        <span class="menu-text"><?php echo esc_html($item['title']); ?></span>
                        <?php if ($has_children): ?>
                            <span class="menu-arrow" style="width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="4" viewBox="0 0 8 4" fill="none">
                                    <path d="M1 0.5L3.29289 2.79289C3.68342 3.18342 4.31658 3.18342 4.70711 2.79289L7 0.5" stroke="#09192D" stroke-linecap="round" />
                                </svg>
                            </span>
                        <?php endif; ?>
                    </a>

                    <?php if ($has_children): ?>
                        <ul class="<?php echo esc_attr($args['submenu_class']); ?>">
                            <?php foreach ($item['children'] as $child): ?>
                                <?php
                                // Check child visibility - این فایل برای دسکتاپ است
                                $child_visibility = $child['item_visibility'] ?? 'both';
                                if ($child_visibility === 'none') continue;
                                if ($child_visibility === 'mobile') continue; // فقط mobile را نمایش نمی‌دهیم
                                ?>
                                <li class="mega-menu-child-item visibility-<?php echo esc_attr($child_visibility); ?>" data-visibility="<?php echo esc_attr($child_visibility); ?>">
                                    <a href="<?php echo esc_url($child['url']); ?>" class="mega-menu-child-link">
                                        <?php echo esc_html($child['title']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php

    $output = ob_get_clean();

    if ($args['echo']) {
        echo $output;
    }

    return $output;
}

/**
 * نمایش مگامنو به صورت horizontal dropdown
 * 
 * @param string $location لوکیشن منو
 * @param array $args آرگومان‌های اضافی
 */
function display_mega_menu_horizontal($location = 'header', $args = [])
{
    $defaults = [
        'container_class' => 'mega-menu-horizontal',
        'menu_class' => 'mega-menu-list-horizontal',
    ];

    $args = wp_parse_args($args, $defaults);

    return display_mega_menu($location, $args);
}

/**
 * نمایش مگامنو به صورت vertical sidebar
 * 
 * @param string $location لوکیشن منو
 * @param array $args آرگومان‌های اضافی
 */
function display_mega_menu_vertical($location = 'header', $args = [])
{
    $defaults = [
        'container_class' => 'mega-menu-vertical',
        'menu_class' => 'mega-menu-list-vertical',
    ];

    $args = wp_parse_args($args, $defaults);

    return display_mega_menu($location, $args);
}

/**
 * دریافت آیتم‌های مگامنو به صورت آرایه
 * 
 * @param string $location لوکیشن منو
 * @return array آیتم‌های منو
 */
function get_mega_menu_items($location = 'header')
{
    return get_option('ez_mega_menu_' . $location, []);
}

/**
 * دریافت یک آیتم خاص از مگامنو با ID
 * 
 * @param string $item_id شناسه آیتم
 * @return array|null آیتم منو یا null
 */
function get_mega_menu_item_by_id($item_id)
{
    $menu_items = get_mega_menu_items();

    foreach ($menu_items as $item) {
        if ($item['id'] === $item_id) {
            return $item;
        }
    }

    return null;
}

/**
 * چک کردن اینکه آیا مگامنو خالی است یا نه
 * 
 * @param string $location لوکیشن منو
 * @return bool
 */
function mega_menu_is_empty($location = 'header')
{
    $menu_items = get_mega_menu_items($location);
    return empty($menu_items);
}

/**
 * دریافت تعداد آیتم‌های مگامنو
 * 
 * @param string $location لوکیشن منو
 * @return int
 */
function get_mega_menu_items_count($location = 'header')
{
    $menu_items = get_mega_menu_items($location);
    return count($menu_items);
}

// Shortcode removed - use PHP functions directly in theme files

/**
 * ویجت مگامنو
 */
class Mega_Menu_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'mega_menu_widget',
            'مگامنو',
            ['description' => 'نمایش مگامنو در سایدبار']
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $location = isset($instance['location']) ? $instance['location'] : 'header';

        $menu_args = [
            'show_icons' => isset($instance['show_icons']) ? (bool) $instance['show_icons'] : true,
            'container_class' => 'mega-menu-widget',
        ];

        display_mega_menu_vertical($location, $menu_args);

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $location = isset($instance['location']) ? $instance['location'] : 'header';
        $show_icons = isset($instance['show_icons']) ? (bool) $instance['show_icons'] : true;

        // Get available locations
        if (function_exists('get_mega_menu_locations')) {
            $locations = get_mega_menu_locations();
        } else {
            $locations = ['header' => 'منوی هدر', 'footer' => 'منوی فوتر'];
        }
    ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">عنوان:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('location')); ?>">لوکیشن منو:</label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('location')); ?>"
                name="<?php echo esc_attr($this->get_field_name('location')); ?>">
                <?php foreach ($locations as $loc_key => $loc_name): ?>
                    <option value="<?php echo esc_attr($loc_key); ?>" <?php selected($location, $loc_key); ?>>
                        <?php echo esc_html($loc_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <input class="checkbox" type="checkbox"
                id="<?php echo esc_attr($this->get_field_id('show_icons')); ?>"
                name="<?php echo esc_attr($this->get_field_name('show_icons')); ?>"
                <?php checked($show_icons); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_icons')); ?>">نمایش آیکون‌ها</label>
        </p>
<?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['location'] = (!empty($new_instance['location'])) ? sanitize_key($new_instance['location']) : 'header';
        $instance['show_icons'] = isset($new_instance['show_icons']) ? (bool) $new_instance['show_icons'] : false;

        return $instance;
    }
}

// ثبت ویجت
add_action('widgets_init', function () {
    register_widget('Mega_Menu_Widget');
});

