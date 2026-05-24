<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Admin;

use EscapeZoom\Core\Modules\Games\Models\Brand;
use EscapeZoom\Core\Modules\Games\Models\City;
use EscapeZoom\Core\Modules\Games\Models\Area;
use EscapeZoom\Core\Modules\Games\Models\GameType;
use EscapeZoom\Core\Modules\Games\Models\EzUser;
use EscapeZoom\Core\Modules\Games\Models\Genre;
use EscapeZoom\Core\Modules\Games\Models\Mood;
use EscapeZoom\Core\Modules\Games\Models\Theme;

/**
 * AJAX endpoint: create entity (brand, city, area, game_type, ez_user, genre, mood, theme) and return id + label.
 * Uses wp_ez_cities, wp_ez_areas, and taxonomy tables. Used by "افزودن همین‌جا" in the relationships metabox.
 */
final class EzAddEntityAjax
{
    public const ACTION = 'ez_add_entity';
    public const NONCE_KEY = 'ez_add_entity_nonce';

    public static function register(): void
    {
        add_action('wp_ajax_' . self::ACTION, [self::class, 'handle']);
    }

    public static function handle(): void
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), self::NONCE_KEY)) {
            wp_send_json_error(['message' => 'Invalid nonce'], 403);
        }
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }

        $entity = isset($_POST['entity']) ? sanitize_text_field($_POST['entity']) : '';
        $allowed = ['brand', 'city', 'area', 'game_type', 'ez_user', 'genre', 'mood', 'theme'];
        if (!in_array($entity, $allowed, true)) {
            wp_send_json_error(['message' => 'Invalid entity'], 400);
        }

        $result = self::createEntity($entity);
        if (isset($result['error'])) {
            wp_send_json_error($result, 400);
        }
        wp_send_json_success($result);
    }

    private static function createEntity(string $entity): array
    {
        switch ($entity) {
            case 'brand':
                return self::createBrand();
            case 'city':
                return self::createCity();
            case 'area':
                return self::createArea();
            case 'game_type':
                return self::createGameType();
            case 'ez_user':
                return self::createEzUser();
            case 'genre':
                return self::createGenre();
            case 'mood':
                return self::createMood();
            case 'theme':
                return self::createTheme();
            default:
                return ['error' => 'Unknown entity'];
        }
    }

    private static function uniqueSlug(string $base, string $tableName, string $column = 'slug'): string
    {
        $slug = sanitize_title($base);
        if ($slug === '') {
            $slug = 'item';
        }
        $conn = \Illuminate\Database\Capsule\Manager::connection('default');
        $existing = $conn->table($tableName)->where($column, $slug)->exists();
        if (!$existing) {
            return $slug;
        }
        $i = 1;
        while (true) {
            $candidate = $slug . '-' . $i;
            if (!$conn->table($tableName)->where($column, $candidate)->exists()) {
                return $candidate;
            }
            $i++;
        }
    }

    private static function createBrand(): array
    {
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        if ($title === '') {
            return ['error' => 'عنوان برند الزامی است'];
        }
        $slug = self::uniqueSlug($title, 'ez_brands');
        $brand = Brand::create([
            'title' => $title,
            'slug'  => $slug,
        ]);
        return ['id' => (int) $brand->id, 'label' => $brand->title];
    }

    private static function createCity(): array
    {
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        if ($name === '') {
            return ['error' => 'نام شهر الزامی است'];
        }

        $slug = self::uniqueSlug($name, 'ez_cities');

        $city = City::create([
            'name'      => $name,
            'slug'      => $slug,
            'is_active' => true,
        ]);

        return ['id' => (int) $city->id, 'label' => $city->name];
    }

    private static function createArea(): array
    {
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $city_id = isset($_POST['parent_id']) ? absint($_POST['parent_id']) : 0;

        if ($name === '') {
            return ['error' => 'نام منطقه الزامی است'];
        }
        if ($city_id <= 0) {
            return ['error' => 'انتخاب شهر الزامی است'];
        }

        $slug = self::uniqueSlug($name, 'ez_areas');

        $area = Area::create([
            'city_id'   => $city_id,
            'name'      => $name,
            'slug'      => $slug,
            'is_active' => true,
        ]);

        return ['id' => (int) $area->id, 'label' => $area->name];
    }

    private static function createGameType(): array
    {
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        if ($title === '') {
            return ['error' => 'عنوان نوع بازی الزامی است'];
        }
        $slug = self::uniqueSlug($title, 'ez_game_types');
        $gt = GameType::create([
            'title' => $title,
            'slug'  => $slug,
        ]);
        return ['id' => (int) $gt->id, 'label' => $gt->title];
    }

    /**
     * Create user: WP (نقش مشتری) + ez_users (نقش اسکیپ‌زوم: owner یا manager).
     */
    private static function createEzUser(): array
    {
        $display_name = isset($_POST['display_name']) ? sanitize_text_field(wp_unslash($_POST['display_name'])) : '';
        if ($display_name === '') {
            return ['error' => 'نام نمایشی الزامی است'];
        }
        $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $phone = $phone !== '' ? $phone : null;
        $internal_role = isset($_POST['internal_role']) ? sanitize_text_field($_POST['internal_role']) : '';
        if (!in_array($internal_role, ['owner', 'manager'], true)) {
            $internal_role = 'owner';
        }

        $wp_user_id = null;
        $user_login = 'ez_' . $internal_role . '_' . time() . '_' . wp_rand(100, 999);
        $user_email = 'ez+' . $user_login . '@' . wp_parse_url(home_url(), PHP_URL_HOST);
        if (!$user_email || $user_email === 'ez+@') {
            $user_email = 'ez+' . uniqid('', true) . '@noreply.local';
        }
        $wp_id = wp_insert_user([
            'user_login'   => $user_login,
            'user_pass'   => wp_generate_password(24, true),
            'user_email'  => $user_email,
            'display_name' => $display_name,
            'role'        => 'customer',
        ]);
        if (!is_wp_error($wp_id)) {
            $wp_user_id = (int) $wp_id;
        }

        $ez_data = [
            'display_name' => $display_name,
            'phone'       => $phone,
            'status'      => 'active',
            'internal_role' => $internal_role,
        ];
        if ($wp_user_id !== null) {
            $ez_data['wp_user_id'] = $wp_user_id;
        }
        $user = EzUser::create($ez_data);
        return ['id' => (int) $user->id, 'label' => $user->display_name ?? $display_name];
    }

    private static function createGenre(): array
    {
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        if ($name === '') {
            return ['error' => 'نام ژانر الزامی است'];
        }
        $slug = self::uniqueSlug($name, 'ez_genres');
        $g = Genre::create([
            'name'       => $name,
            'slug'      => $slug,
            'is_active' => true,
        ]);
        return ['id' => (int) $g->id, 'label' => $g->name];
    }

    private static function createMood(): array
    {
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        if ($name === '') {
            return ['error' => 'نام مود الزامی است'];
        }
        $slug = self::uniqueSlug($name, 'ez_moods');
        $m = Mood::create([
            'name'       => $name,
            'slug'      => $slug,
            'is_active' => true,
        ]);
        return ['id' => (int) $m->id, 'label' => $m->name];
    }

    private static function createTheme(): array
    {
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        if ($name === '') {
            return ['error' => 'نام تم الزامی است'];
        }
        $slug = self::uniqueSlug($name, 'ez_themes');
        $t = Theme::create([
            'name'       => $name,
            'slug'       => $slug,
            'is_active'  => true,
        ]);

        return ['id' => (int) $t->id, 'label' => $t->name];
    }
}
