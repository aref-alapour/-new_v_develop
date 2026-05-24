<?php
// Cities Settings Page for WordPress Admin

// Register the settings page in the admin menu
add_action('admin_menu', function () {
    add_menu_page(
        'Cities IDs Settings',
        'ID شهرها',
        'manage_options',
        'cities-ids-settings',
        'cities_ids_settings_page',
        'dashicons-location-alt',
        60
    );
});

// Register the option to store cities data
add_action('admin_init', function () {
    register_setting('cities_ids_settings_group', 'cities_ids_settings');
});

// The settings page callback
function cities_ids_settings_page()
{
?>
    <div class="wrap">
        <h1 class="text-2xl font-bold mb-4">تنظیمات شهرها و IDها</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cities_ids_settings_group');
            $cities = get_option('cities_ids_settings', []);
            ?>
            <div id="cities-list" class="space-y-4">
                <?php if (!empty($cities) && is_array($cities)): ?>
                    <?php foreach ($cities as $city_index => $city): ?>
                        <div class="city-block bg-white p-4 rounded-lg shadow-md border border-gray-200" data-index="<?php echo esc_attr($city_index); ?>">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold">شهر</h3>
                                <button type="button" class="remove-city button bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">حذف شهر</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <input type="text" name="cities_ids_settings[<?php echo $city_index; ?>][name]" value="<?php echo esc_attr($city['name'] ?? ''); ?>" placeholder="نام شهر" class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <input type="text" name="cities_ids_settings[<?php echo $city_index; ?>][slug]" value="<?php echo esc_attr($city['slug'] ?? ''); ?>" placeholder="مثلا tehran" class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <input type="text" name="cities_ids_settings[<?php echo $city_index; ?>][city_id]" value="<?php echo esc_attr($city['city_id'] ?? ''); ?>" placeholder="مثلا 1" class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <label class="flex items-center">
                                    <input type="checkbox" name="cities_ids_settings[<?php echo $city_index; ?>][is_featured]" <?php checked(isset($city['is_featured']) && $city['is_featured']); ?> class="mr-2">
                                    شهر شاخص
                                </label>
                            </div>
                            <div class="city-children">
                                <h4 class="text-md font-medium mb-2">آیتم‌های شهر</h4>
                                <div class="children-list space-y-2">
                                    <?php if (!empty($city['children']) && is_array($city['children'])): ?>
                                        <?php foreach ($city['children'] as $child_index => $child): ?>
                                            <div class="child-block bg-gray-50 p-3 rounded border border-gray-200 flex items-center gap-4" data-child-index="<?php echo esc_attr($child_index); ?>">
                                                <span class="drag-handle cursor-move text-gray-500">☰</span>
                                                <input type="text" name="cities_ids_settings[<?php echo $city_index; ?>][children][<?php echo $child_index; ?>][label]" value="<?php echo esc_attr($child['label'] ?? ''); ?>" placeholder="مثلا room, cinema" class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <input type="text" name="cities_ids_settings[<?php echo $city_index; ?>][children][<?php echo $child_index; ?>][id]" value="<?php echo esc_attr($child['id'] ?? ''); ?>" placeholder="مثلا 123" class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <button type="button" class="remove-child button bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">حذف</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="add-child button bg-blue-500 text-white px-3 py-1 rounded mt-2 hover:bg-blue-600">افزودن آیتم جدید</button>
                            </div>
                            <hr class="my-4">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" class="add-city button bg-green-500 text-white px-4 py-2 rounded mt-4 hover:bg-green-600">افزودن شهر جدید</button>
            <?php submit_button('ذخیره تنظیمات', 'primary', 'submit', true, ['class' => 'bg-blue-600 text-white px-4 py-2 rounded mt-4 hover:bg-blue-700']); ?>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        (function($) {
            let cityIndex = <?php echo isset($cities) && is_array($cities) ? count($cities) : 0; ?>;

            // Make cities sortable
            $('#cities-list').sortable({
                handle: '.city-block h3',
                update: function(event, ui) {
                    // Update city indices
                    $('#cities-list .city-block').each(function(index) {
                        let $block = $(this);
                        let oldIndex = $block.data('index');
                        $block.data('index', index);
                        $block.find('input, button').each(function() {
                            let name = $(this).attr('name');
                            if (name) {
                                $(this).attr('name', name.replace(/cities_ids_settings\[\d+\]/, 'cities_ids_settings[' + index + ']'));
                            }
                        });
                        $block.find('.child-block').each(function(childIndex) {
                            let $child = $(this);
                            $child.find('input').each(function() {
                                let name = $(this).attr('name');
                                if (name) {
                                    $(this).attr('name', name.replace(/cities_ids_settings\[\d+\]\[children\]\[\d+\]/, 'cities_ids_settings[' + index + '][children][' + childIndex + ']'));
                                }
                            });
                        });
                    });
                }
            });

            // Make children sortable
            $('.children-list').sortable({
                handle: '.drag-handle',
                update: function(event, ui) {
                    let $cityBlock = $(this).closest('.city-block');
                    let cityIdx = $cityBlock.data('index');
                    $(this).find('.child-block').each(function(childIndex) {
                        let $child = $(this);
                        $child.data('child-index', childIndex);
                        $child.find('input').each(function() {
                            let name = $(this).attr('name');
                            if (name) {
                                $(this).attr('name', name.replace(/cities_ids_settings\[\d+\]\[children\]\[\d+\]/, 'cities_ids_settings[' + cityIdx + '][children][' + childIndex + ']'));
                            }
                        });
                    });
                }
            });

            // Add new city
            $('.add-city').on('click', function() {
                let html = `
            <div class="city-block bg-white p-4 rounded-lg shadow-md border border-gray-200" data-index="` + cityIndex + `">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold">شهر</h3>
                    <button type="button" class="remove-city button bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">حذف شهر</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <input type="text" name="cities_ids_settings[` + cityIndex + `][name]" placeholder="نام شهر" class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="cities_ids_settings[` + cityIndex + `][slug]" placeholder="مثلا tehran" class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="cities_ids_settings[` + cityIndex + `][city_id]" placeholder="مثلا 1" class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <label class="flex items-center">
                        <input type="checkbox" name="cities_ids_settings[` + cityIndex + `][is_featured]" class="mr-2">
                        شهر شاخص
                    </label>
                </div>
                <div class="city-children">
                    <h4 class="text-md font-medium mb-2">آیتم‌های شهر</h4>
                    <div class="children-list space-y-2"></div>
                    <button type="button" class="add-child button bg-blue-500 text-white px-3 py-1 rounded mt-2 hover:bg-blue-600">افزودن آیتم جدید</button>
                </div>
                <hr class="my-4">
            </div>
            `;
                $('#cities-list').append(html);
                $('.children-list').sortable({
                    handle: '.drag-handle',
                    update: function(event, ui) {
                        let $cityBlock = $(this).closest('.city-block');
                        let cityIdx = $cityBlock.data('index');
                        $(this).find('.child-block').each(function(childIndex) {
                            let $child = $(this);
                            $child.data('child-index', childIndex);
                            $child.find('input').each(function() {
                                let name = $(this).attr('name');
                                if (name) {
                                    $(this).attr('name', name.replace(/cities_ids_settings\[\d+\]\[children\]\[\d+\]/, 'cities_ids_settings[' + cityIdx + '][children][' + childIndex + ']'));
                                }
                            });
                        });
                    }
                });
                cityIndex++;
            });

            // Confirm and remove city
            $('#cities-list').on('click', '.remove-city', function() {
                if (confirm('آیا از حذف این شهر مطمئن هستید؟')) {
                    $(this).closest('.city-block').remove();
                }
            });

            // Add child to city
            $('#cities-list').on('click', '.add-child', function() {
                let $cityBlock = $(this).closest('.city-block');
                let cityIdx = $cityBlock.data('index');
                let $childrenList = $cityBlock.find('.children-list');
                let childIndex = $childrenList.find('.child-block').length;
                let html = `
            <div class="child-block bg-gray-50 p-3 rounded border border-gray-200 flex items-center gap-4" data-child-index="` + childIndex + `">
                <span class="drag-handle cursor-move text-gray-500">☰</span>
                <input type="text" name="cities_ids_settings[` + cityIdx + `][children][` + childIndex + `][label]" placeholder="مثلا room, cinema" class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" name="cities_ids_settings[` + cityIdx + `][children][` + childIndex + `][id]" placeholder="مثلا 123" class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button" class="remove-child button bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">حذف</button>
            </div>
            `;
                $childrenList.append(html);
            });

            // Confirm and remove child
            $('#cities-list').on('click', '.remove-child', function() {
                if (confirm('آیا از حذف این آیتم مطمئن هستید؟')) {
                    $(this).closest('.child-block').remove();
                }
            });
        })(jQuery);
    </script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<?php
}
?>