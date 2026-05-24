<?php

namespace EscapeZoom\Core\Modules\Common;

use EscapeZoom\Core\Modules\Common\Services\ProductSnapshotService;
use WC_Product;
use WP_Post;

/**
 * Keeps wp_products_snapshot in sync on product create/update/delete and related taxonomy changes.
 *
 * All syncs for a request are deferred to {@see flushPendingSnapshotSyncs()} on shutdown so ACF / custom
 * metaboxes that save after WooCommerce still see the final meta in the DB.
 */
final class ProductSnapshotSync
{
    /** @var array<int, true> */
    private static array $pendingSyncIds = [];

    /** @var array<int, true> product IDs being deleted this request — skip sync */
    private static array $deletedProductIds = [];

    private static bool $shutdownHookRegistered = false;

    /** @var list<string> */
    private const TAXONOMIES = [
        'product_cat',
        'product_tag',
        'product_genre',
        'product_brand',
        'ez_game_identity',
    ];

    /**
     * Post meta keys that affect ProductSnapshotService::buildSnapshotRow (sans, pricing hooks, etc.).
     *
     * @var list<string>
     */
    private const POST_META_KEYS_TOUCHING_SNAPSHOT = [
        'product_state',
        'room_loc',
        'sanses',
        'sans_manager',
        'manager_id',
        'pish_pardakht_per_person',
        'comments_count_new',
        'ez_weighted_rating_overall',
        '_thumbnail_id',
    ];

    public static function register(): void
    {
        if (class_exists(\WooCommerce::class)) {
            add_action('woocommerce_after_product_object_save', [self::class, 'onWcProductObjectSave'], 99, 2);
        }

        add_action('save_post_product', [self::class, 'onSavePostProduct'], 99_999, 3);

        add_action('transition_post_status', [self::class, 'onTransitionPostStatus'], 10, 3);
        add_action('before_delete_post', [self::class, 'onBeforeDeletePost'], 10, 2);
        add_action('set_object_terms', [self::class, 'onSetObjectTerms'], 99, 6);

        add_action('acf/save_post', [self::class, 'onAcfSavePost'], 999);

        add_action('added_post_meta', [self::class, 'onPostMetaAddUpdate'], 10, 4);
        add_action('updated_post_meta', [self::class, 'onPostMetaAddUpdate'], 10, 4);
        add_action('deleted_post_meta', [self::class, 'onPostMetaDeleted'], 10, 4);
    }

    public static function flushPendingSnapshotSyncs(): void
    {
        $ids = array_keys(self::$pendingSyncIds);
        self::$pendingSyncIds = [];

        foreach ($ids as $postId) {
            $postId = (int) $postId;
            if ($postId <= 0 || isset(self::$deletedProductIds[$postId])) {
                continue;
            }
            if (get_post_type($postId) !== 'product') {
                continue;
            }
            (new ProductSnapshotService())->syncProduct($postId);
        }

        self::$deletedProductIds = [];
    }

    public static function onWcProductObjectSave($product, $dataStore): void
    {
        unset($dataStore);
        if (! $product instanceof WC_Product) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        $postId = $product->get_id();
        if ($postId <= 0 || wp_is_post_revision($postId)) {
            return;
        }
        self::queueSnapshotSync($postId);
    }

    public static function onSavePostProduct(int $postId, WP_Post $post, bool $update): void
    {
        unset($post, $update);
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($postId)) {
            return;
        }

        if (class_exists(\WooCommerce::class) && self::isWooCommerceAdminProductFormSave()) {
            return;
        }

        self::queueSnapshotSync($postId);
    }

    public static function onTransitionPostStatus(string $newStatus, string $oldStatus, WP_Post $post): void
    {
        unset($newStatus, $oldStatus);
        if ($post->post_type !== 'product') {
            return;
        }
        self::queueSnapshotSync((int) $post->ID);
    }

    /**
     * @param int|WP_Post $postId
     * @param WP_Post|null $post
     */
    public static function onBeforeDeletePost($postId, $post): void
    {
        if (! $post instanceof WP_Post) {
            return;
        }
        if ($post->post_type !== 'product') {
            return;
        }
        $id = (int) $postId;
        self::$deletedProductIds[$id] = true;
        unset(self::$pendingSyncIds[$id]);
        (new ProductSnapshotService())->deleteSnapshot($id);
    }

    /**
     * @param int $objectId
     * @param list<int|string>|list<string> $terms
     * @param list<int> $ttIds
     * @param string $taxonomy
     * @param bool $append
     * @param list<int>|false $oldTtIds
     */
    public static function onSetObjectTerms($objectId, $terms, $ttIds, $taxonomy, $append, $oldTtIds): void
    {
        unset($terms, $ttIds, $append, $oldTtIds);
        if (! in_array($taxonomy, self::TAXONOMIES, true)) {
            return;
        }
        if ((int) $objectId <= 0 || get_post_type((int) $objectId) !== 'product') {
            return;
        }
        self::queueSnapshotSync((int) $objectId);
    }

    /**
     * After ACF saves (priority 999 runs after ACF core save_post handlers on the same post).
     */
    public static function onAcfSavePost(mixed $postId): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (! is_numeric($postId)) {
            return;
        }
        $id = (int) $postId;
        if ($id <= 0 || get_post_type($id) !== 'product') {
            return;
        }
        self::queueSnapshotSync($id);
    }

    /**
     * @param mixed $metaId
     */
    public static function onPostMetaAddUpdate($metaId, int $objectId, string $metaKey, mixed $metaValue): void
    {
        unset($metaId, $metaValue);
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if ($objectId <= 0 || get_post_type($objectId) !== 'product') {
            return;
        }
        if (! self::metaKeyTouchesSnapshot($metaKey)) {
            return;
        }
        self::queueSnapshotSync($objectId);
    }

    /**
     * @param list<int>|mixed $metaIds
     */
    public static function onPostMetaDeleted($metaIds, int $objectId, string $metaKey, mixed $metaValue): void
    {
        unset($metaIds, $metaValue);
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if ($objectId <= 0 || get_post_type($objectId) !== 'product') {
            return;
        }
        if (! self::metaKeyTouchesSnapshot($metaKey)) {
            return;
        }
        self::queueSnapshotSync($objectId);
    }

    private static function queueSnapshotSync(int $postId): void
    {
        if ($postId <= 0 || get_post_type($postId) !== 'product') {
            return;
        }
        if (isset(self::$deletedProductIds[$postId])) {
            return;
        }
        self::$pendingSyncIds[$postId] = true;
        if (! self::$shutdownHookRegistered) {
            self::$shutdownHookRegistered = true;
            add_action('shutdown', [self::class, 'flushPendingSnapshotSyncs'], 999_999);
        }
    }

    private static function metaKeyTouchesSnapshot(string $metaKey): bool
    {
        if (in_array($metaKey, self::POST_META_KEYS_TOUCHING_SNAPSHOT, true)) {
            return true;
        }

        $extra = apply_filters('ez_product_snapshot_meta_keys', []);
        if (is_array($extra)) {
            foreach ($extra as $key) {
                if ($metaKey === (string) $key) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Full product edit in wp-admin runs save_post before all meta is written; WC then fires
     * woocommerce_after_product_object_save. Skip the early save_post pass when that form is submitting.
     */
    private static function isWooCommerceAdminProductFormSave(): bool
    {
        if (! is_admin() || ! isset($_POST['woocommerce_meta_nonce'])) {
            return false;
        }

        return (string) wp_unslash($_POST['woocommerce_meta_nonce']) !== '';
    }
}
