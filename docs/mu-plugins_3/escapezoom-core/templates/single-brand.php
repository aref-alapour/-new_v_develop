<?php
/**
 * Template: Single Brand Page.
 * Layout aligned with legacy taxonomy-yith_product_brand: breadcrumb, header (title + game types + address + logo), games, intro, team.
 *
 * Brand data is loaded into $ez_current_brand global by BrandBootstrap::maybeLoadBrandTemplate().
 *
 * @see EscapeZoom\Core\Modules\Brands\BrandBootstrap
 */

if (!defined('ABSPATH')) {
    exit;
}

global $ez_current_brand;

if (empty($ez_current_brand)) {
    get_header();
    echo '<main class="min-h-screen p-4"><p>' . esc_html__('برند یافت نشد.', 'escapezoom-core') . '</p></main>';
    get_footer();
    return;
}

$brand = $ez_current_brand;
$brandId = (int) $brand->id;
$brandName = esc_html($brand->title ?? '');
$rawLogo = $brand->thumbnail_url ?? $brand->logo ?? '';
$brandLogo = $rawLogo !== '' ? (function_exists('ez_brand_thumbnail_display_url') ? ez_brand_thumbnail_display_url((string) $rawLogo) : esc_url($rawLogo)) : '';
$brandDescription = wp_kses_post($brand->description ?? '');
$brandAddress = esc_html($brand->address ?? '');
$brandPhone = isset($brand->phone) ? esc_html($brand->phone) : '';
$brandInstagram = isset($brand->instagram) && $brand->instagram !== '' ? esc_url($brand->instagram) : '';
$brandWebsite = isset($brand->website) && $brand->website !== '' ? esc_url($brand->website) : '';
$brandEstablishedYear = isset($brand->established_year) ? (int) $brand->established_year : 0;

// Game types: JSON array → labels joined (like old template)
$gameTypesLabels = [];
if (!empty($brand->game_types)) {
    $gt = json_decode((string) $brand->game_types, true);
    if (is_array($gt)) {
        foreach ($gt as $v) {
            $label = is_string($v) ? trim($v) : (isset($v['title']) ? trim((string) $v['title']) : (isset($v['عنوان_تایپ']) ? trim((string) $v['عنوان_تایپ']) : ''));
            if ($label !== '') {
                $gameTypesLabels[] = $label;
            }
        }
    }
}

// Games count (raw brand has no games_count)
$gamesList = function_exists('get_games_by_brand') ? get_games_by_brand($brandId, 500) : [];
$gamesCount = count($gamesList);

// Teams: JSON array of objects (name, position, avatar) or strings
$teamMembers = [];
if (!empty($brand->teams)) {
    $t = json_decode((string) $brand->teams, true);
    if (is_array($t)) {
        foreach ($t as $m) {
            if (is_string($m)) {
                $teamMembers[] = ['name' => $m, 'position' => '', 'avatar' => ''];
            } elseif (is_array($m)) {
                $teamMembers[] = [
                    'name' => isset($m['name']) ? trim((string) $m['name']) : '',
                    'position' => isset($m['position']) ? trim((string) $m['position']) : '',
                    'avatar' => isset($m['avatar']) ? trim((string) $m['avatar']) : (isset($m['image']) ? trim((string) $m['image']) : ''),
                ];
            }
        }
    }
}

$gamesEndpoint = esc_url(rest_url('escapezoom/v1/brands/' . $brandId . '/games-html?limit=100'));
$brandsListUrl = home_url('/brands/');

get_header();
?>

<main class="min-h-screen bg-slate-50">
    <!-- Breadcrumb (like old taxonomy template) -->
    <section class="my-8 md:my-12">
        <nav class="container mx-auto px-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center flex-wrap gap-1 text-sm">
                <li>
                    <a class="text-slate-500 hover:text-primary" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('صفحه اصلی', 'escapezoom-core'); ?></a>
                </li>
                <li class="flex items-center gap-1">
                    <span class="text-slate-300 mx-1">/</span>
                    <a class="text-slate-500 hover:text-primary" href="<?php echo esc_url($brandsListUrl); ?>"><?php esc_html_e('برندها', 'escapezoom-core'); ?></a>
                </li>
                <li class="flex items-center gap-1">
                    <span class="text-slate-300 mx-1">/</span>
                    <span class="text-slate-700"><?php echo $brandName; ?></span>
                </li>
            </ol>
        </nav>
    </section>

    <!-- Header: title + game types + address (left), logo (right) - like old template -->
    <section class="container mx-auto px-4 flex flex-col md:flex-row md:items-center md:justify-between gap-6 lg:gap-8">
        <div class="flex-1">
            <h1 class="font-bold text-2xl lg:text-4xl text-slate-800"><?php echo $brandName; ?></h1>
            <?php if ($gameTypesLabels !== []): ?>
                <div class="text-primary text-base lg:text-lg font-semibold my-3 lg:my-4">
                    <?php
                    $sep = '<span class="inline-block w-1 h-1 rounded-full bg-slate-400 mx-1.5 align-middle"></span>';
                    echo implode($sep, array_map('esc_html', $gameTypesLabels));
                    ?>
                </div>
            <?php endif; ?>
            <?php if ($brandAddress !== ''): ?>
                <div class="flex items-center gap-2 text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 16 18" fill="none" class="flex-shrink-0">
                        <path d="M9.445 17.18C11.6238 15.2625 15.5 11.345 15.5 7.75C15.5 5.76088 14.7098 3.85322 13.3033 2.4467C11.8968 1.04018 9.98912 0.25 8 0.25C6.01088 0.25 4.10322 1.04018 2.6967 2.4467C1.29018 3.85322 0.5 5.76088 0.5 7.75C0.5 11.345 4.375 15.2625 6.555 17.18C6.95264 17.5349 7.467 17.7311 8 17.7311C8.533 17.7311 9.04736 17.5349 9.445 17.18ZM5.5 7.75C5.5 7.08696 5.76339 6.45107 6.23223 5.98223C6.70107 5.51339 7.33696 5.25 8 5.25C8.66304 5.25 9.29893 5.51339 9.76777 5.98223C10.2366 6.45107 10.5 7.08696 10.5 7.75C10.5 8.41304 10.2366 9.04893 9.76777 9.51777C9.29893 9.98661 8.66304 10.25 8 10.25C7.33696 10.25 6.70107 9.98661 6.23223 9.51777C5.76339 9.04893 5.5 8.41304 5.5 7.75Z" fill="currentColor"/>
                    </svg>
                    <span><?php echo $brandAddress; ?></span>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-shrink-0">
            <?php if ($brandLogo !== ''): ?>
                <img src="<?php echo $brandLogo; ?>" alt="<?php echo esc_attr($brandName); ?>" class="w-24 h-24 lg:w-40 lg:h-40 rounded-2xl shadow-lg object-cover">
            <?php else: ?>
                <div class="w-24 h-24 lg:w-40 lg:h-40 rounded-2xl bg-slate-200 flex items-center justify-center shadow-lg">
                    <span class="text-2xl lg:text-4xl font-bold text-slate-500"><?php echo esc_html(mb_substr($brandName, 0, 1)); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="border-b border-slate-200 my-8 md:my-10"></div>

    <!-- رزرو بازی ها (games section - like old template) -->
    <?php if ($gamesCount > 0): ?>
    <section class="container mx-auto px-4 py-4 md:py-8">
        <h2 class="text-lg font-bold text-slate-800 mb-4 md:mb-6"><?php esc_html_e('رزرو بازی ها', 'escapezoom-core'); ?></h2>
        <div
            id="brand-games-grid"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6"
            hx-get="<?php echo $gamesEndpoint; ?>"
            hx-trigger="load"
            hx-swap="innerHTML"
        >
            <ez-skeleton class="col-span-2 md:col-span-3 lg:col-span-4">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <?php for ($i = 0; $i < 8; $i++): ?>
                    <div class="bg-white rounded-xl shadow overflow-hidden animate-pulse">
                        <div class="aspect-video bg-slate-200"></div>
                        <div class="p-4 space-y-3">
                            <div class="h-4 bg-slate-200 rounded w-3/4"></div>
                            <div class="h-3 bg-slate-200 rounded w-1/2"></div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </ez-skeleton>
        </div>
    </section>
    <div class="border-b border-slate-200 my-8 md:my-10"></div>
    <?php endif; ?>

    <!-- معرفی (intro - like old template) -->
    <?php if ($brandDescription !== ''): ?>
    <section class="container mx-auto px-4 py-6">
        <h2 class="font-bold text-2xl lg:text-3xl text-slate-800 mb-4 md:mb-6"><?php esc_html_e('معرفی', 'escapezoom-core'); ?></h2>
        <div class="prose prose-slate max-w-none text-justify"><?php echo $brandDescription; ?></div>
    </section>
    <div class="border-b border-slate-200 my-8 md:my-10"></div>
    <?php endif; ?>

    <!-- اعضاء (team - like old template) -->
    <?php if ($teamMembers !== []): ?>
    <section class="container mx-auto px-4 py-6 md:py-10">
        <h2 class="font-bold text-2xl lg:text-3xl text-slate-800 mb-4 md:mb-6"><?php esc_html_e('اعضاء', 'escapezoom-core'); ?></h2>
        <div class="flex gap-6 md:gap-8 overflow-x-auto pb-2 no-scrollbar">
            <?php foreach ($teamMembers as $member): ?>
            <div class="flex flex-col items-center text-center shrink-0 w-28 md:w-40">
                <?php
                $avatarUrl = '';
                if (!empty($member['avatar'])) {
                    $avatarUrl = function_exists('ez_brand_thumbnail_display_url') ? ez_brand_thumbnail_display_url($member['avatar']) : esc_url($member['avatar']);
                }
                if ($avatarUrl !== ''):
                ?>
                    <img src="<?php echo esc_url($avatarUrl); ?>" alt="" class="aspect-square w-20 h-20 md:w-28 md:h-28 rounded-full object-cover mb-3">
                <?php else: ?>
                    <div class="w-20 h-20 md:w-28 md:h-28 rounded-full bg-slate-200 flex items-center justify-center mb-3 text-slate-500 font-bold text-xl"><?php echo esc_html(mb_substr($member['name'] ?: '?', 0, 1)); ?></div>
                <?php endif; ?>
                <span class="text-slate-800 font-medium"><?php echo esc_html($member['name'] ?: '—'); ?></span>
                <?php if ($member['position'] !== ''): ?>
                    <span class="text-slate-500 text-sm"><?php echo esc_html($member['position']); ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($brandPhone !== '' || $brandInstagram !== '' || $brandWebsite !== '' || $brandEstablishedYear > 0): ?>
    <section class="container mx-auto px-4 py-6 border-t border-slate-200">
        <div class="flex flex-wrap gap-3">
            <?php if ($brandPhone !== ''): ?>
                <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $brandPhone)); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm"><?php echo $brandPhone; ?></a>
            <?php endif; ?>
            <?php if ($brandInstagram !== ''): ?>
                <a href="<?php echo $brandInstagram; ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg text-sm hover:opacity-90"><?php esc_html_e('اینستاگرام', 'escapezoom-core'); ?></a>
            <?php endif; ?>
            <?php if ($brandWebsite !== ''): ?>
                <a href="<?php echo $brandWebsite; ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 text-white rounded-lg text-sm hover:bg-slate-800"><?php esc_html_e('وب‌سایت', 'escapezoom-core'); ?></a>
            <?php endif; ?>
            <?php if ($brandEstablishedYear > 0): ?>
                <span class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 text-sm"><?php esc_html_e('تأسیس:', 'escapezoom-core'); ?> <?php echo (int) $brandEstablishedYear; ?></span>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php
get_footer();
