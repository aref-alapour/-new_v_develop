<?php

/**
 * API Shortener Module
 * Automatically calls API when content is published
 * 
 * @package EscapeZoom
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EZ_API_Shortener
{

    private $api_url;
    private $token;

    public function __construct()
    {
        // API Configuration
        $this->api_url = 'https://eszm.ir/shortener_core/api.php';
        /** توکن از wp-config (EZ_ESZM_SHORTENER_TOKEN)؛ روی پرود فقط env/ثابت خارج از مخزن */
        if ( defined( 'EZ_ESZM_SHORTENER_TOKEN' ) && EZ_ESZM_SHORTENER_TOKEN !== '' ) {
            $this->token = EZ_ESZM_SHORTENER_TOKEN;
        } else {
            $this->token = 'AUQvoz46mbdiMu5fD7YpLB7JrH7SivHw64JRvPQPeCOTvHJx7APTBaxDryF80Jda';
        }

        // Hook into WordPress publishing actions
        $this->init_hooks();
    }

    /**
     * تأیید گواهی TLS برای درخواست‌های کوتاه‌کننده (پیش‌فرض true).
     * برای شبکهٔ داخلی/dev: add_filter( 'ez_api_shortener_sslverify', '__return_false' );
     */
    private function get_sslverify()
    {
        return (bool) apply_filters( 'ez_api_shortener_sslverify', true );
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks()
    {
        // Products (WooCommerce)
        add_action('woocommerce_new_product', array($this, 'handle_product_publish'), 10, 1);
        add_action('woocommerce_update_product', array($this, 'handle_product_publish'), 10, 1);

        // Posts
        add_action('publish_post', array($this, 'handle_post_publish'), 10, 2);
        add_action('publish_page', array($this, 'handle_page_publish'), 10, 2);

        // Categories and Tags
        add_action('created_category', array($this, 'handle_category_create'), 10, 2);
        add_action('edited_category', array($this, 'handle_category_update'), 10, 2);
        add_action('created_post_tag', array($this, 'handle_tag_create'), 10, 2);
        add_action('edited_post_tag', array($this, 'handle_tag_update'), 10, 2);

        // Product Categories and Tags and Brands
        add_action('created_product_cat', array($this, 'handle_product_category_create'), 10, 2);
        add_action('edited_product_cat', array($this, 'handle_product_category_update'), 10, 2);
        add_action('created_product_tag', array($this, 'handle_product_tag_create'), 10, 2);
        add_action('edited_product_tag', array($this, 'handle_product_tag_update'), 10, 2);
        add_action('created_product_brand', array($this, 'handle_wc_product_brand_create'), 10, 2);
        add_action('edited_product_brand', array($this, 'handle_wc_product_brand_update'), 10, 2);

        // Custom post types (if any)
        add_action('publish', array($this, 'handle_custom_post_publish'), 10, 2);

        // Add shortlink meta boxes
        add_action('add_meta_boxes', array($this, 'add_shortlink_meta_boxes'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Add shortlink fields to taxonomy edit pages
        add_action('category_edit_form_fields', array($this, 'add_taxonomy_shortlink_field'), 10, 2);
        add_action('post_tag_edit_form_fields', array($this, 'add_taxonomy_shortlink_field'), 10, 2);
        add_action('product_cat_edit_form_fields', array($this, 'add_taxonomy_shortlink_field'), 10, 2);
        add_action('product_tag_edit_form_fields', array($this, 'add_taxonomy_shortlink_field'), 10, 2);
        add_action('product_brand_edit_form_fields', array($this, 'add_taxonomy_shortlink_field'), 10, 2);
    }

    /**
     * Handle product publishing
     */
    public function handle_product_publish($product_id)
    {
        $product = wc_get_product($product_id);
        if (!$product) return;

        // Generate and save shortlink
        $shortlink = $this->generate_shortlink($product_id, 'product');
        update_post_meta($product_id, '_ez_shortlink', $shortlink);

        $this->call_api(array(
            'original_url' => get_permalink($product_id),
            'type' => 'product',
            'item_id' => $product_id
        ));
    }

    /**
     * Handle post publishing
     */
    public function handle_post_publish($post_id, $post)
    {
        // Skip revisions and autosaves
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        // Generate and save shortlink
        $shortlink = $this->generate_shortlink($post_id, 'blog');
        update_post_meta($post_id, '_ez_shortlink', $shortlink);

        $this->call_api(array(
            'original_url' => get_permalink($post_id),
            'type' => 'blog',
            'item_id' => $post_id
        ));
    }

    /**
     * Handle page publishing
     */
    public function handle_page_publish($post_id, $post)
    {
        // Skip revisions and autosaves
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        // Generate and save shortlink
        $shortlink = $this->generate_shortlink($post_id, 'page');
        update_post_meta($post_id, '_ez_shortlink', $shortlink);

        $this->call_api(array(
            'original_url' => get_permalink($post_id),
            'type' => 'page',
            'item_id' => $post_id
        ));
    }

    /**
     * Handle category creation
     */
    public function handle_category_create($term_id, $tt_id)
    {
        // Generate and save shortlink
        $shortlink = $this->generate_taxonomy_shortlink($term_id, 'category');
        update_term_meta($term_id, '_ez_shortlink', $shortlink);

        $this->call_api(array(
            'original_url' => get_term_link($term_id, 'category'),
            'type' => 'blog_cat',
            'item_id' => $term_id
        ));
    }

    /**
     * Handle category update
     */
    public function handle_category_update($term_id, $tt_id)
    {
        // Generate and save shortlink
        $shortlink = $this->generate_taxonomy_shortlink($term_id, 'category');
        update_term_meta($term_id, '_ez_shortlink', $shortlink);

        $this->call_api(array(
            'original_url' => get_term_link($term_id, 'category'),
            'type' => 'blog_cat',
            'item_id' => $term_id
        ));
    }

    /**
     * Handle tag creation
     */
    public function handle_tag_create($term_id, $tt_id)
    {
        // Generate and save shortlink
        $shortlink = $this->generate_taxonomy_shortlink($term_id, 'post_tag');
        update_term_meta($term_id, '_ez_shortlink', $shortlink);

        $this->call_api(array(
            'original_url' => get_term_link($term_id, 'post_tag'),
            'type' => 'blog_tag',
            'item_id' => $term_id
        ));
    }

    /**
     * Handle tag update
     */
    public function handle_tag_update($term_id, $tt_id)
    {
        $this->call_api(array(
            'original_url' => get_term_link($term_id, 'post_tag'),
            'type' => 'blog_tag',
            'item_id' => $term_id
        ));
    }

    /**
     * Handle product category creation
     */
    public function handle_product_category_create($term_id, $tt_id)
    {
        $this->call_api(array(
            'original_url' => get_term_link($term_id, 'product_cat'),
            'type' => 'product_cat',
            'item_id' => $term_id
        ));
    }

    /**
     * Handle product category update
     */
    public function handle_product_category_update($term_id, $tt_id)
    {
        $this->call_api(array(
            'original_url' => get_term_link($term_id, 'product_cat'),
            'type' => 'product_cat',
            'item_id' => $term_id
        ));
    }

    /**
     * Handle product tag creation
     */
    public function handle_product_tag_create($term_id, $tt_id)
    {
        $this->call_api(array(
            'original_url' => get_term_link($term_id, 'product_tag'),
            'type' => 'product_tag',
            'item_id' => $term_id
        ));
    }

    /**
     * Handle product tag update
     */
    public function handle_product_tag_update($term_id, $tt_id)
    {
        $this->call_api(array(
            'original_url' => get_term_link($term_id, 'product_tag'),
            'type' => 'product_tag',
            'item_id' => $term_id
        ));
    }

    /**
     * WooCommerce native product_brand.
     */
    public function handle_wc_product_brand_create($term_id, $tt_id)
    {
        unset($tt_id);
        if (!taxonomy_exists('product_brand')) {
            return;
        }
        $link = get_term_link((int) $term_id, 'product_brand');
        if (is_wp_error($link)) {
            return;
        }
        $this->call_api(array(
            'original_url' => $link,
            'type' => 'brand',
            'item_id' => $term_id
        ));
    }

    /**
     * @param mixed $tt_id
     */
    public function handle_wc_product_brand_update($term_id, $tt_id)
    {
        $this->handle_wc_product_brand_create($term_id, $tt_id);
    }

    /**
     * Handle custom post type publishing
     */
    public function handle_custom_post_publish($post_id, $post)
    {
        // Skip revisions and autosaves
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        // Skip standard post types that are handled separately
        if (in_array($post->post_type, array('post', 'page', 'product'))) {
            return;
        }

        // Determine type based on post type
        $type = $this->get_custom_post_type_mapping($post->post_type);

        if ($type) {
            $this->call_api(array(
                'original_url' => get_permalink($post_id),
                'type' => $type,
                'item_id' => $post_id
            ));
        }
    }

    /**
     * Get custom post type mapping
     */
    private function get_custom_post_type_mapping($post_type)
    {
        $mappings = array(
            'event' => 'event',
            'team' => 'team',
            'testimonial' => 'testimonial',
            'faq' => 'faq',
            'gallery' => 'gallery',
            // Add more mappings as needed
        );

        return isset($mappings[$post_type]) ? $mappings[$post_type] : $post_type;
    }

    /**
     * POST با چند بار تلاش برای قطع شبکهٔ کوتاه‌کنندهٔ خارجی (مثلاً timeout / connection refused).
     *
     * @param array $args آرگومان‌های مشابه wp_remote_post (timeout در body قابل بازنویسی است).
     * @return array|\WP_Error
     */
    private function wp_remote_shortener_post(array $args)
    {
        $defaults = array(
            'timeout' => 22,
            'sslverify' => $this->get_sslverify(),
        );
        $args = array_merge( $defaults, $args );
        $attempts = 3;
        $last = null;
        for ($i = 1; $i <= $attempts; $i++) {
            $last = wp_remote_post($this->api_url, $args);
            if (! is_wp_error($last)) {
                return $last;
            }
            if ($i < $attempts) {
                sleep(min($i, 2));
            }
        }
        return $last;
    }

    /**
     * Make API call
     */
    private function call_api($data)
    {
        $args = array(
            'body' => $data,
            'headers' => array(
                'X-AUTH-TOKEN' => $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'timeout' => 30,
        );

        $response = $this->wp_remote_shortener_post($args);

        if (is_wp_error($response)) {
            // Log error
            error_log('EZ API Shortener Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $http_code = wp_remote_retrieve_response_code($response);

        // Log response for debugging
        if ($http_code !== 200) {
            error_log('EZ API Shortener HTTP Error: ' . $http_code . ' - ' . $body);
        }

        // Try to parse response and save shortlink
        $response_data = json_decode($body, true);
        if ($response_data && isset($response_data['shortlink']) && isset($data['item_id'])) {
            // Check if it's a taxonomy term or post
            if (isset($data['type']) && in_array($data['type'], array('blog_cat', 'blog_tag', 'product_cat', 'product_tag', 'brand'))) {
                update_term_meta($data['item_id'], '_ez_shortlink', $response_data['shortlink']);
            } else {
                update_post_meta($data['item_id'], '_ez_shortlink', $response_data['shortlink']);
            }
        }

        return $body;
    }

    /**
     * Add shortlink meta boxes to post types
     */
    public function add_shortlink_meta_boxes()
    {
        // Add to posts
        add_meta_box(
            'ez_shortlink_box',
            'لینک کوتاه',
            array($this, 'render_shortlink_meta_box'),
            'post',
            'side',
            'high'
        );

        // Add to pages
        add_meta_box(
            'ez_shortlink_box',
            'لینک کوتاه',
            array($this, 'render_shortlink_meta_box'),
            'page',
            'side',
            'high'
        );

        // Add to products
        add_meta_box(
            'ez_shortlink_box',
            'لینک کوتاه',
            array($this, 'render_shortlink_meta_box'),
            'product',
            'side',
            'high'
        );
    }

    /**
     * Render shortlink meta box
     */
    public function render_shortlink_meta_box($post)
    {
        $shortlink = $this->get_shortlink($post->ID, $post->post_type);
?>
        <div class="ez-shortlink-container">
            <?php if ($shortlink): ?>
                <div class="ez-shortlink-field">
                    <input type="text" id="ez_shortlink_<?php echo $post->ID; ?>"
                        value="<?php echo esc_attr($shortlink); ?>"
                        readonly class="widefat" />
                </div>
                <div class="ez-shortlink-actions">
                    <button type="button" class="button button-secondary ez-copy-shortlink"
                        data-target="ez_shortlink_<?php echo $post->ID; ?>">
                        کپی لینک
                    </button>
                </div>
            <?php else: ?>
                <p class="description">لینک کوتاه هنوز ایجاد نشده است. پس از انتشار محتوا، لینک کوتاه نمایش داده خواهد شد.</p>
            <?php endif; ?>
        </div>
    <?php
    }

    /**
     * Get shortlink from database or API
     */
    private function get_shortlink($item_id, $type)
    {
        // Try to get from database first
        $shortlink = get_post_meta($item_id, '_ez_shortlink', true);

        if (!$shortlink) {
            // Generate shortlink based on type
            $shortlink = $this->generate_shortlink($item_id, $type);
            if ($shortlink) {
                update_post_meta($item_id, '_ez_shortlink', $shortlink);
            }
        }

        return $shortlink;
    }

    /**
     * Generate shortlink based on content type
     */
    private function generate_shortlink($item_id, $type)
    {
        $base_url = 'eszm.ir';

        switch ($type) {
            case 'product':
                return $base_url . '?r=' . $item_id;
            case 'post':
                return $base_url . '?b=' . $item_id;
            case 'page':
                return $base_url . '?p=' . $item_id;
        }
    }

    /**
     * Get shortlink from API
     */
    private function get_shortlink_from_api($item_id, $type)
    {
        $args = array(
            'body' => array(
                'action' => 'get_shortlink',
                'item_id' => $item_id,
                'type' => $type
            ),
            'headers' => array(
                'X-AUTH-TOKEN' => $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'timeout' => 30,
        );

        $response = $this->wp_remote_shortener_post($args);

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($data && isset($data['shortlink'])) {
                return $data['shortlink'];
            }
        }

        return false;
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook)
    {
        global $post_type;

        // Only load on post edit pages
        if (
            in_array($hook, array('post.php', 'post-new.php', 'term.php', 'edit-tags.php')) &&
            (in_array($post_type, array('post', 'page', 'product')) ||
                isset($_GET['taxonomy']) && in_array($_GET['taxonomy'], array('category', 'post_tag', 'product_cat', 'product_tag', 'product_brand'), true))
        ) {

            wp_add_inline_style('wp-admin', '
                .ez-shortlink-container { margin: 10px 0; }
                .ez-shortlink-field { margin-bottom: 10px; }
                .ez-shortlink-field input { font-family: monospace; font-size: 12px; }
                .ez-shortlink-actions { text-align: center; }
                .ez-copy-shortlink { width: 100%; }
                .ez-taxonomy-shortlink-container { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; align-items: center; }
                .ez-taxonomy-shortlink-container input { font-family: monospace; font-size: 12px; }
                .ez-taxonomy-shortlink-container button { white-space: nowrap; }
            ');
        }
    }

    /**
     * Manual API call for existing content
     */
    public function sync_existing_content($type = 'all', $limit = 100, $offset = 0)
    {
        switch ($type) {
            case 'products':
                $this->sync_products($limit, $offset);
                break;
            case 'posts':
                $this->sync_posts($limit, $offset);
                break;
            case 'pages':
                $this->sync_pages($limit, $offset);
                break;
            case 'categories':
                $this->sync_categories($limit, $offset);
                break;
            case 'tags':
                $this->sync_tags($limit, $offset);
                break;
            case 'brands':
                $this->sync_brands($limit, $offset);
                break;
            case 'all':
            default:
                $this->sync_products($limit, $offset);
                $this->sync_posts($limit, $offset);
                $this->sync_pages($limit, $offset);
                $this->sync_categories($limit, $offset);
                $this->sync_tags($limit, $offset);
                $this->sync_brands($limit, $offset);
                break;
        }
    }

    /**
     * Sync existing products
     */
    private function sync_products($limit, $offset = 0)
    {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $products = new WP_Query($args);

        if ($products->have_posts()) {
            while ($products->have_posts()) {
                $products->the_post();
                $this->handle_product_publish(get_the_ID());
            }
            wp_reset_postdata();
        }
    }

    /**
     * Sync existing posts
     */
    private function sync_posts($limit, $offset = 0)
    {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $posts = new WP_Query($args);

        if ($posts->have_posts()) {
            while ($posts->have_posts()) {
                $posts->the_post();
                $this->handle_post_publish(get_the_ID(), get_post());
            }
            wp_reset_postdata();
        }
    }

    /**
     * Sync existing pages
     */
    private function sync_pages($limit, $offset = 0)
    {
        $args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $pages = new WP_Query($args);

        if ($pages->have_posts()) {
            while ($pages->have_posts()) {
                $pages->the_post();
                $this->handle_page_publish(get_the_ID(), get_post());
            }
            wp_reset_postdata();
        }
    }

    /**
     * Sync existing categories
     */
    private function sync_categories($limit, $offset = 0)
    {
        $categories = get_terms(array(
            'taxonomy' => 'category',
            'hide_empty' => false,
            'number' => $limit,
            'offset' => $offset
        ));

        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $this->handle_category_create($category->term_id, $category->term_taxonomy_id);
            }
        }

        // Product categories
        $product_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'number' => $limit,
            'offset' => $offset
        ));

        if (!is_wp_error($product_categories)) {
            foreach ($product_categories as $category) {
                $this->handle_product_category_create($category->term_id, $category->term_taxonomy_id);
            }
        }
    }

    /**
     * Sync existing tags
     */
    private function sync_tags($limit, $offset = 0)
    {
        $tags = get_terms(array(
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
            'number' => $limit,
            'offset' => $offset
        ));

        if (!is_wp_error($tags)) {
            foreach ($tags as $tag) {
                $this->handle_tag_create($tag->term_id, $tag->term_taxonomy_id);
            }
        }

        // Product tags
        $product_tags = get_terms(array(
            'taxonomy' => 'product_tag',
            'hide_empty' => false,
            'number' => $limit,
            'offset' => $offset
        ));

        if (!is_wp_error($product_tags)) {
            foreach ($product_tags as $tag) {
                $this->handle_product_tag_create($tag->term_id, $tag->term_taxonomy_id);
            }
        }
    }

    /**
     * Sync existing brands
     */
    private function sync_brands($limit, $offset = 0)
    {
        if (!taxonomy_exists('product_brand')) {
            return;
        }
        $wc_brands = get_terms(array(
            'taxonomy' => 'product_brand',
            'hide_empty' => false,
            'number' => $limit,
            'offset' => $offset
        ));
        if (!is_wp_error($wc_brands)) {
            foreach ($wc_brands as $brand) {
                $this->handle_wc_product_brand_create($brand->term_id, $brand->term_taxonomy_id);
            }
        }
    }

    /**
     * Add shortlink field to taxonomy edit pages
     */
    public function add_taxonomy_shortlink_field($term, $taxonomy)
    {
        $shortlink = $this->get_taxonomy_shortlink($term->term_id, $taxonomy);
    ?>
        <tr class="form-field">
            <th scope="row">
                <label for="ez_taxonomy_shortlink">لینک کوتاه</label>
            </th>
            <td>
                <?php if ($shortlink): ?>
                    <div class="ez-taxonomy-shortlink-container">
                        <input type="text" id="ez_taxonomy_shortlink_<?php echo $term->term_id; ?>"
                            value="<?php echo esc_attr($shortlink); ?>"
                            readonly class="regular-text" />
                        <button type="button" class="button button-secondary ez-copy-shortlink"
                            data-target="ez_taxonomy_shortlink_<?php echo $term->term_id; ?>">
                            کپی لینک
                        </button>
                    </div>
                <?php else: ?>
                    <p class="description">لینک کوتاه هنوز ایجاد نشده است. پس از ذخیره دسته‌بندی/برچسب، لینک کوتاه نمایش داده خواهد شد.</p>
                <?php endif; ?>
            </td>
        </tr>
    <?php
    }

    /**
     * Get taxonomy shortlink
     */
    private function get_taxonomy_shortlink($term_id, $taxonomy)
    {
        // Try to get from term meta first
        $shortlink = get_term_meta($term_id, '_ez_shortlink', true);

        if (!$shortlink) {
            // Generate shortlink based on taxonomy type
            $shortlink = $this->generate_taxonomy_shortlink($term_id, $taxonomy);
            if ($shortlink) {
                update_term_meta($term_id, '_ez_shortlink', $shortlink);
            }
        }

        return $shortlink;
    }

    /**
     * Generate taxonomy shortlink based on taxonomy type
     */
    private function generate_taxonomy_shortlink($term_id, $taxonomy)
    {
        $base_url = 'eszm.ir';

        switch ($taxonomy) {
            case 'product_cat':
                return $base_url . '?pc=' . $term_id;
            case 'product_tag':
                return $base_url . '?pt=' . $term_id;
            case 'category':
                return $base_url . '?bc=' . $term_id;
            case 'post_tag':
                return $base_url . '?bt=' . $term_id;
            case 'product_brand':
                return $base_url . '?br=' . $term_id;
        }
    }

    /**
     * Create shortlink for sans (sansyab) type
     */
    public function create_sans_shortlink($original_url, $item_id)
    {
        // Call API to create shortlink for sans type
        $response = $this->call_api_for_sans($original_url, $item_id);

        if ($response && isset($response['short_url'])) {
            return $response['short_url'];
        }

        return false;
    }

    /**
     * Create shortlink for custom type
     * مشابه create_sans_shortlink اما برای type='custom'
     */
    public function create_custom_shortlink($original_url, $item_id = null)
    {
        // برای custom type، item_id اختیاری است و نیازی به ارسال نیست
        // استفاده از متد مشابه call_api_for_sans
        $response = $this->call_api_for_custom($original_url);

        if ($response && isset($response['short_url'])) {
            return $response['short_url'];
        }

        return false;
    }

    /**
     * Call API specifically for custom type
     * مشابه call_api_for_sans اما بدون item_id
     */
    private function call_api_for_custom($original_url)
    {
        // Debug: Log URL before wp_remote_post
        error_log('EZ API Custom: Before wp_remote_post - ' . $original_url);

        // استفاده از همان ساختار call_api برای سازگاری
        // WordPress به صورت خودکار array را به query string تبدیل می‌کند
        $args = array(
            'body' => array(
                'original_url' => $original_url,
                'type' => 'custom'
                // برای custom type، item_id ارسال نمی‌شود (اختیاری است)
            ),
            'headers' => array(
                'X-AUTH-TOKEN' => $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'timeout' => 30,
            'redirection' => 5
        );

        // Debug: Log what will be sent
        error_log('EZ API Custom: wp_remote_post body - ' . print_r($args['body'], true));
        error_log('EZ API Custom: API URL - ' . $this->api_url);

        $response = $this->wp_remote_shortener_post($args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('EZ API Custom WP Error: ' . $error_message);
            error_log('EZ API Custom WP Error Code: ' . $response->get_error_code());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $http_code = wp_remote_retrieve_response_code($response);
        
        error_log('EZ API Custom: HTTP Code - ' . $http_code);
        error_log('EZ API Custom: Response Body - ' . $body);
        
        // بررسی کد HTTP - API ممکن است 200 یا 201 برگرداند
        if ($http_code !== 200 && $http_code !== 201) {
            error_log('EZ API Custom: HTTP Error Code - ' . $http_code);
            // حتی در صورت خطا، سعی می‌کنیم پاسخ را پارس کنیم
            if (!empty($body)) {
                $error_data = json_decode($body, true);
                if ($error_data && isset($error_data['error'])) {
                    error_log('EZ API Custom Error Message: ' . $error_data['error']);
                }
            }
            return false;
        }
        
        $data = json_decode($body, true);

        if (!$data) {
            error_log('EZ API Custom: Failed to decode JSON - ' . $body);
            error_log('EZ API Custom: JSON Error - ' . json_last_error_msg());
            return false;
        }

        if (isset($data['short_url'])) {
            error_log('EZ API Custom: Success - ' . $data['short_url']);
            return $data;
        } elseif (isset($data['error'])) {
            error_log('EZ API Custom Error: ' . $data['error']);
            return false;
        } else {
            error_log('EZ API Custom: Unexpected response format - ' . $body);
            error_log('EZ API Custom: Decoded data - ' . print_r($data, true));
            // بررسی اینکه آیا فیلدهای دیگری در پاسخ وجود دارد
            if (isset($data['message'])) {
                error_log('EZ API Custom: Message - ' . $data['message']);
            }
            return false;
        }
    }

    /**
     * Call API specifically for sans type
     */
    private function call_api_for_sans($original_url, $item_id)
    {
        // Debug: Log URL before wp_remote_post
        error_log('Before wp_remote_post: ' . $original_url);

        $args = array(
            'body' => array(
                'original_url' => $original_url,
                'type' => 'sans',
                'item_id' => $item_id
            ),
            'headers' => array(
                'X-AUTH-TOKEN' => $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'timeout' => 30,
        );

        // Debug: Log what will be sent
        error_log('wp_remote_post body: ' . print_r($args['body'], true));

        $response = $this->wp_remote_shortener_post($args);

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($data && isset($data['short_url'])) {
                return $data;
            }
        }

        return false;
    }
}

// Initialize the module
new EZ_API_Shortener();

// Add admin menu for manual sync
add_action('admin_menu', function () {
    add_submenu_page(
        'tools.php',
        'API Shortener Sync',
        'API Shortener',
        'manage_options',
        'api-shortener-sync',
        'ez_api_shortener_admin_page'
    );
});

function ez_api_shortener_admin_page()
{
    if (isset($_POST['sync_action']) && wp_verify_nonce($_POST['_wpnonce'], 'ez_api_sync')) {
        $type = sanitize_text_field($_POST['sync_type']);
        $limit = intval($_POST['sync_limit']);
        $offset = intval($_POST['sync_offset']);

        $api_shortener = new EZ_API_Shortener();
        $api_shortener->sync_existing_content($type, $limit, $offset);

        echo '<div class="notice notice-success"><p>Sync completed successfully!</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>API Shortener Sync</h1>
        <p>Manually sync existing content with the API shortener service.</p>

        <form method="post" action="">
            <?php wp_nonce_field('ez_api_sync'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Content Type</th>
                    <td>
                        <select name="sync_type">
                            <option value="all">All Content</option>
                            <option value="products">Products Only</option>
                            <option value="posts">Posts Only</option>
                            <option value="pages">Pages Only</option>
                            <option value="categories">Categories Only</option>
                            <option value="tags">Tags Only</option>
                            <option value="brands">Brands Only</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Limit</th>
                    <td>
                        <input type="number" name="sync_limit" value="100" min="1" max="1000" />
                        <p class="description">Maximum number of items to sync (1-1000)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Start From</th>
                    <td>
                        <input type="number" name="sync_offset" value="0" min="0" />
                        <p class="description">Start from this row number (0 = first item)</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="sync_action" class="button-primary" value="Start Sync" />
            </p>
        </form>
    </div>
<?php
}
