<?php

define("TIME_TO_EXPIRE", 0);
define("CRISIS_TIME", 24);
define("TIME_TO_DISABLE_REQUEST", 2);
define("URGENT_TIME", 4);

require_once Theme_PATH . "template/team/ajax/init.php";
require_once Theme_PATH . "template/team/functions/init.php";

add_action('init', function () {
    add_rewrite_tag('%team_page%', '([^&]+)');
    add_rewrite_rule('^team/([^/]+)/?', 'index.php?team_page=$matches[1]', 'top');
}, 10);

add_filter('query_vars', function ($vars) {
    $vars[] = 'team_page';
    return $vars;
});

add_action('template_redirect', function () {

    $team_page  = get_query_var('team_page');
    $uri_path   = parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH);
    $base_path  = parse_url(home_url(), PHP_URL_PATH);

    if ($base_path && strpos($uri_path, $base_path) === 0)
        $uri_path = substr($uri_path, strlen($base_path));

    $requested_path = trim($uri_path, '/');

    if (! $team_page && $requested_path !== 'team') // فقط درخواست های مربوط به روت team پردازش بشه
        return;

    $current_user   = wp_get_current_user();
    $allowed_roles  = ['administrator', 'supervisor', 'poshtiban', 'accounting', 'sales', 'marketing', 'team_admin']; // رول های مجازی که میتوانند به پنل team دسترسی داشته باشند.

    // بررسی وجود حداقل یکی از رول های مجاز
    $has_access = array_intersect($allowed_roles, $current_user->roles);
    if (empty($has_access))
        page_404();

    if ($team_page) {
        $menu_items = get_accessible_team_menu_items();

        if (isset($menu_items[$team_page])) {
            include get_template_directory() . '/template/team/layout.php';
            exit();
        }
    } else {
        include get_template_directory() . '/template/team/layout.php';
        exit();
    }

    page_404();
});

function get_team_menu_items(): array
{
    return [

        'orders'                => ['label' => 'سفارشات',           'roles' => ['administrator', 'team_admin'],                                                                'icon' => 'orders-icon'],
        'orders2'               => ['label' => 'سفارشات',           'roles' => ['administrator', 'supervisor', 'poshtiban', 'accounting', 'sales', 'team_admin'],              'icon' => 'orders-icon'],
        'sans_management'       => ['label' => 'مدیریت سانس',       'roles' => ['administrator', 'supervisor', 'poshtiban', 'team_admin'],                                     'icon' => 'manageSans-icon'],
        'game_finder'           => ['label' => 'سانس یاب',          'roles' => ['administrator', 'supervisor', 'poshtiban', 'team_admin'],                                     'icon' => 'gameFinder-icon'],
        'games_info'            => ['label' => 'اطلاعات بازی ها',    'roles' => ['administrator', 'supervisor', 'poshtiban', 'accounting', 'sales', 'marketing', 'team_admin'], 'icon' => 'gameInformation-icon'],
        'sms'                   => ['label' => 'پیامک',             'roles' => ['administrator', 'supervisor', 'poshtiban', 'accounting', 'team_admin'],                       'icon' => 'sms-icon'],
        'withdrawals'           => ['label' => 'تسویه حساب ها',     'roles' => ['administrator', 'supervisor', 'accounting', 'team_admin'],                                    'icon' => 'settlement-icon'],
        'transactions'          => ['label' => 'تراکنش ها',         'roles' => ['administrator', 'supervisor', 'accounting', 'team_admin'],                                    'icon' => 'wallet-icon'],
        'comments'              => ['label' => 'کامنت ها',          'roles' => ['administrator', 'supervisor', 'poshtiban', 'team_admin'],                                     'icon' => 'comments-icon'],
        'cancellation_requests' => ['label' => 'درخواست ها',        'roles' => ['administrator', 'supervisor', 'poshtiban', 'team_admin'],                                     'icon' => 'requests-icon'],
        'cancellation_history'  => ['label' => 'تاریخچه لغو',       'roles' => ['administrator', 'supervisor', 'poshtiban', 'accounting', 'team_admin'],                       'icon' => 'crm-icon-cansel-history'],
        'users'                 => ['label' => 'مدیریت کاربران',    'roles' => ['administrator', 'supervisor', 'poshtiban', 'accounting', 'team_admin'],                       'icon' => 'manage-users-icon'],
        'sales_report'          => ['label' => 'گزارش فروش',        'roles' => ['administrator', 'supervisor', 'sales', 'team_admin'],                                         'icon' => 'crm-icon-sales-reports'],
        'marketing_report'      => ['label' => 'گزارش مارکتینگ',    'roles' => ['administrator', 'accounting', 'marketing', 'team_admin'],                                     'icon' => 'crm-icon-marketing-reports'],
    ];
}

function get_accessible_team_menu_items(): array
{
    $all_items  = get_team_menu_items();
    $user       = wp_get_current_user();

    return array_filter($all_items, function ($item) use ($user) {
        if (empty($item['roles']))
            return true;

        foreach ($item['roles'] as $role)
            if (in_array($role, $user->roles))
                return true;

        return false;
    });
}

function page_404()
{
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
    include get_404_template();
    exit;
}

function get_default_team_page_for_user($user = null): ?string
{

    if (! $user)
        $user = wp_get_current_user();

    if (empty($user->roles))
        return null;

    $map = [
        'administrator' => 'orders2',
        'supervisor'    => 'sans_management',
        'poshtiban'     => 'orders',
        'accounting'    => 'withdrawals',
        'sales'         => 'games_info',
        'marketing'     => 'games_info',
    ];
    $menu_items = get_accessible_team_menu_items();

    foreach ($map as $role => $slug)
        if (in_array($role, $user->roles, true))
            if (isset($menu_items[$slug]))
                return $slug;

    return array_key_first($menu_items) ?: null;
}

// تابع ترجمه نقش کاربر به فارسی
function translate_user_role_to_persian($role)
{
    $role_translations = array(
        'administrator' => 'مدیر',
        'editor'        => 'ویرایشگر',
        'author'        => 'نویسنده',
        'contributor'   => 'مشارکت‌کننده',
        'accounting'    => 'حسابدار',
        'shopist'       => 'فروشنده',
        'contentist'    => 'محتواگذار',
        'poshtiban'     => 'پشتیبان',
        'subscriber'    => 'مشترک',
        'support'       => 'پشتیبان',
        'manager'       => 'مدیر',
        'staff'         => 'کارمند',
        'employee'      => 'کارمند'
    );

    return isset($role_translations[$role]) ? $role_translations[$role] : $role;
}
