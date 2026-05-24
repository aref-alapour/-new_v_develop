<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );

$user = wp_get_current_user();

$ID       = $user->ID;
$name     = $user->display_name;
$email    = $user->user_email;
$username = $user->user_login;

?>

<div class="grid-cols-12 gap-7.5 lg:grid mt-8">
    <div class="col-span-3">

        <!-- Mobile -->
        <div class="mb-9 lg:hidden">
            <div class="mb-5 flex gap-2.5">
                <a href="<?php echo site_url( '/profile/' . $ID ); ?>" class="flex relative items-center gap-3 grow max-lg:rounded-lg max-lg:border max-lg:border-slate-110 max-lg:p-4 max-lg:shadow-13 lg:flex-wrap lg:gap-6.5">
                    <div class="h-12 w-12 overflow-hidden rounded-lg">
						<?php echo get_avatar( $ID, 48, '', $name ); ?>
                    </div>
                    <h3 class="text-base lg:mt-1.5 lg:w-full flex flex-col">
						<?php echo esc_html( $name ); ?>
                        <span class="text-slate-150 leading-3" style="font-size: 12px">مشاهده پروفایل</span>
                    </h3>
                </a>
                <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'points' ) ); ?>" class="flex items-center justify-between max-lg:w-3/12 max-lg:flex-col max-lg:rounded-lg max-lg:border max-lg:border-gray-100 max-lg:bg-warn-surface-2 max-lg:p-4 max-lg:shadow-13 lg:mt-3 lg:gap-3">
                    <span class="max-lg:font-medium max-lg:leading-4">امتیاز من</span>
                    <span class="flex items-center gap-2.5 text-2xl leading-3 max-lg:flex-row-reverse">
						<?php echo get_user_points( $ID, true ) ?: 0; ?>
                        <span class="text-yellow-800">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" fill="none" viewBox="0 0 20 20">
                                <path fill="#FFD200" fill-rule="evenodd" d="M9.667.666c5.338 0 9.666 4.328 9.666 9.667 0 5.339-4.328 8.767-9.666 8.767C4.328 19.1 0 15.672 0 10.333A9.667 9.667 0 0 1 9.667.666Z" clip-rule="evenodd"></path>
                                <path fill="#FFA200" fill-rule="evenodd" d="M9.668 2.477a7.855 7.855 0 1 1 0 15.71 7.855 7.855 0 0 1 0-15.71Z" clip-rule="evenodd"></path>
                                <path fill="#fff" fill-rule="evenodd" d="m10.561 13.181.003-.001a2.816 2.816 0 1 0-1.439.016l.041.01.004 2.167-1.633-.87C5.346 13.244 4.59 10.377 5.85 8.186a4.576 4.576 0 1 1 7.936 4.56s-.782 1.114-1.62 1.806c-.81.67-1.675.919-1.675.919l-.011-2.268.082-.022Z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                    </span>
                </a>
            </div>

			<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) {
				if ( wc_is_current_account_menu_item( $endpoint ) ) { ?>
                    <button type="button" aria-haspopup="dialog" aria-expanded="false" aria-controls="radix-:R15jqqfj6:" data-state="closed" class="w-full expand-menu">
                        <div class="border border-slate-120 w-full rounded-lg px-6 py-5.5 shadow-13">
                            <div class="flex w-full items-center justify-between text-xs text-slate-700 mb-0">
                                <div class="flex w-full items-center justify-between text-xs <?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
                                    <div class="flex items-center gap-3 text-primary-500">
										<?php echo ez_account_nav_icon_markup( $endpoint ); ?>
										<?php echo esc_html( $label ); ?>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="10" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="m-0">
                                    <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
				<?php }
			} ?>

            <section class="rounded-xl border border-slate-120 shadow-12 sticky mt-2 p-6 hidden submenu">
                <div class="flex flex-col gap-6">
					<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
						<?php if ( $endpoint == 'customer-logout' ) { ?>
                            <hr>
						<?php } ?>
                        <a class="group flex w-full items-center justify-between text-xs transition-colors duration-200 <?php echo wc_get_account_menu_item_classes( $endpoint ); ?> <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'text-primary-500' : 'text-slate-700 hover:text-primary-500'; ?>" href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo $endpoint == 'customer-logout' ? 'onclick="zebline.user.logout();"' : ''; ?>>
                            <div class="flex items-center gap-3">
								<?php echo ez_account_nav_icon_markup( $endpoint ); ?>
								<?php echo esc_html( $label ); ?>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="10" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="m-0">
                                <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                            </svg>
                        </a>
					<?php endforeach; ?>
                </div>
            </section>
        </div>
        <!-- Mobile -->

        <section class="rounded-2xl border border-slate-120 shadow-12 sticky top-2 z-10 px-9 py-10 max-lg:hidden">

            <div class="max-lg:hidden">

                <div class="flex items-center gap-3 max-lg:w-7/12 max-lg:rounded-lg max-lg:border max-lg:border-slate-110 max-lg:p-4 max-lg:shadow-13 lg:flex-wrap lg:gap-6.5">

                    <div class="h-12 w-12 overflow-hidden rounded-lg">
						<?php echo get_avatar( $ID, 48, '', $name ); ?>
                    </div>

                    <a href="<?php echo site_url( '/profile/' . $ID ); ?>" class="flex gap-4 items-center relative text-md font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-white text-gray-900 border border-gray-100 hover:bg-button-gradient focus-visible:bg-button-gradient h-12 min-w-12 px-6 py-1 rounded-lg w-[calc(100%-4.625rem)] justify-center max-lg:hidden">
                        <span class="truncate">مشاهده پروفایل</span>
                    </a>

                </div>

                <div class="flex justify-between items-center lg:mt-4 lg:w-full">
                    <h3 class="text-base"><?php echo esc_html( $name ); ?></h3>
					<?php user_badge_by_level( $ID, 'rounded-full' ); ?>
                </div>
                <div class="flex items-center justify-between max-lg:w-5/12 max-lg:flex-col max-lg:rounded-lg max-lg:border max-lg:border-gray-100 max-lg:bg-warn-surface-2 max-lg:p-4 max-lg:shadow-13 lg:mt-3 lg:gap-3">
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'points' ) ); ?>" class="max-lg:font-medium max-lg:leading-4 <?php echo wc_is_current_account_menu_item( 'points' ) ? 'text-primary-500' : 'text-slate-700'; ?>">
                        امتیاز من
                    </a>
                    <span class="flex items-center gap-2.5 text-2xl leading-3 max-lg:flex-row-reverse font-bold">
						<?php echo get_user_points( $ID, true ); ?>
                        <span class="text-yellow-800">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                                <rect x="0.5" y="0.5" width="24" height="24" rx="12" fill="#FEAE1A" fill-opacity="0.6"/>
                                <rect x="2.68164" y="2.68164" width="19.6364" height="19.6364" rx="9.81818" fill="#FEAE1A"/>
                                <g filter="url(#filter0_d_5820_196)">
                                    <path d="M17.4395 19.5C16.4085 17.6268 15.378 15.753 14.3405 13.868C14.7661 13.4201 15.0786 12.9103 15.2489 12.3103C15.8149 10.3144 14.6328 8.21252 12.7112 7.80235C10.7414 7.38188 8.88525 8.77282 8.63967 10.8536C8.35059 13.3038 10.4589 15.2923 12.7734 14.7487C12.888 14.7217 12.9703 14.6987 13.0438 14.8356C13.4232 15.5396 13.8114 16.2378 14.1959 16.9389C14.2104 16.9654 14.2165 16.9978 14.2347 17.0522C14.1697 17.0562 14.1197 17.063 14.0696 17.0621C13.2052 17.0513 12.3379 17.0841 11.4767 17.0194C9.10511 16.8413 7.05157 14.8817 6.60298 12.4187C6.02621 9.25217 7.92678 6.27011 10.9397 5.61561C13.9966 4.95129 17.0409 7.22635 17.4451 10.4807C17.4774 10.7393 17.497 11.0018 17.4975 11.2623C17.5012 13.9362 17.4998 16.6097 17.4994 19.2836C17.4994 19.3533 17.4914 19.4235 17.4872 19.4931C17.4713 19.4956 17.4554 19.4975 17.4395 19.5Z" fill="white"/>
                                    <path d="M12.3276 8.55547C11.7878 9.11234 11.2887 9.62603 10.791 10.1412C10.6301 10.3075 10.4626 10.468 10.3162 10.647C10.181 10.8124 10.0692 10.9993 9.90828 11.2333C9.78573 11.2118 9.58318 11.1754 9.3825 11.1401C9.42694 9.56814 10.8172 8.35431 12.3276 8.55547Z" fill="white"/>
                                </g>
                                <defs>
                                    <filter id="filter0_d_5820_196" x="4.5" y="4.5" width="15" height="18" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                                        <feOffset dy="1"/>
                                        <feGaussianBlur stdDeviation="1"/>
                                        <feComposite in2="hardAlpha" operator="out"/>
                                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5820_196"/>
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5820_196" result="shape"/>
                                    </filter>
                                </defs>
                            </svg>
                        </span>
                    </span>
                </div>
            </div>

            <div class="line my-6 max-lg:hidden"></div>

            <div class="flex flex-col gap-6">

				<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
					<?php if ( $endpoint == 'customer-logout' ) { ?>
                        <hr>
					<?php } ?>

                    <a class="group flex w-full items-center justify-between text-xs transition-colors duration-200 <?php echo wc_get_account_menu_item_classes( $endpoint ); ?> <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'text-primary-500' : 'text-slate-700 hover:text-primary-500'; ?>" href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo $endpoint == 'customer-logout' ? 'onclick="zebline.user.logout();"' : ''; ?>>
                        <div class="flex items-center gap-3">
							<?php echo ez_account_nav_icon_markup( $endpoint ); ?>
							<?php echo esc_html( $label ); ?>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="10" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="m-0">
                            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </a>
				<?php endforeach; ?>

        </section>

    </div>

	<?php do_action( 'woocommerce_after_account_navigation' ); ?>
