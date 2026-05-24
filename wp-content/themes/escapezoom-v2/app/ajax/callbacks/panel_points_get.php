<?php

global $wpdb;

$user = wp_get_current_user();

$page = sanitize_text_field( $_POST['page'] ) ?: 1;

$limit = 10;

$prepare = $wpdb->prepare( "SELECT COUNT(*) FROM points WHERE user_id LIKE %d", (int) $user->ID );
$query   = (int) $wpdb->get_var( $prepare );

$total_pages = ceil( $query / $limit );
$offset      = ( $page - 1 ) * $limit;

$points = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM points WHERE user_id LIKE %d ORDER BY created_at DESC LIMIT %d, %d", $user->ID, $offset, $limit ) );

$items = [];
foreach ( $points as $point ) {
	$items[] = [
		'id'          => (int) $point->id,
		'description' => $point->description,
		'action'      => $point->action,
		'point'       => (int) $point->point,
		'created_at'  => (int) $point->created_at,
	];
}
?>

<div class="relative overflow-x-auto">

    <table class="w-full text-right text-sm max-lg:hidden">
        <thead class="border-b border-t border-slate-120 text-xs text-slate-350 max-lg:hidden">
            <tr>
                <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">بابت</th>
                <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0 text-center">نوع فعالیت
                </th>
                <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0 text-center">تاریخ ثبت
                </th>
                <th scope="col" class="text-nowrap px-4 py-6 text-left first:pr-0 last:pl-0">
                    امتیاز دریافت شده
                </th>
            </tr>
        </thead>
        <tbody class="max-lg:flex max-lg:flex-col">
			<?php foreach ( $points as $point ) { ?>
                <tr class="font-bold">
                    <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0">
						<?php echo esc_html( $point->description ) ?>
                    </td>
                    <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0 text-center">
						<?php echo esc_html( $point->action ) ?>
                    </td>
                    <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0 text-center">
						<?php echo esc_html( jdate( "Y.m.d H:i", $point->created_at ) ) ?>
                    </td>
                    <td class="border-b border-slate-120 px-4 py-6 text-left first:pr-0 last:pl-0">
						<?php echo esc_html( $point->point ) ?>
                        امتیاز
                    </td>
                </tr>
			<?php } ?>
        </tbody>
    </table>

    <div class="lg:hidden">
		<?php foreach ( $points as $index => $point ) { ?>
            <div class="border-b py-4 flex gap-4 items-center">
                <div class="flex items-center justify-between max-lg:flex-col max-lg:rounded-lg max-lg:border max-lg:border-[#ebe6db] max-lg:bg-[#f7f4ed] py-4 px-2 max-lg:shadow-13 lg:mt-3 lg:gap-3">
                    <span class="flex items-center gap-2.5 text-2xl leading-3">
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
						<?php echo esc_html( number_format( $point->point ) ) ?>
                    </span>
                </div>
                <div class="flex flex-col leading-3 gap-4 grow">
                    <span class="text-slate-350 text-xs">بابت</span>
                    <span><?php echo esc_html( $point->description ) ?></span>
                </div>
                <div class="relative">
                    <button type="button" class="more-button py-4 px-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="3" height="13" viewBox="0 0 3 13" fill="none">
                            <circle cx="1.5" cy="11.5" r="1.5" transform="rotate(-90 1.5 11.5)" fill="#889BAD"/>
                            <circle cx="1.5" cy="6.5" r="1.5" transform="rotate(-90 1.5 6.5)" fill="#889BAD"/>
                            <circle cx="1.5" cy="1.5" r="1.5" transform="rotate(-90 1.5 1.5)" fill="#889BAD"/>
                        </svg>
                    </button>
                    <div class="tooltip hidden bg-white rounded-xl absolute flex flex-col left-0 top-full w-fit text-nowrap p-4 shadow-13 rounded-xl z-10">
                        <div class="flex flex-col">
                            <span class="text-slate-350 text-xs">امتیاز دریافت شده</span>
                            <span><?php echo esc_html( number_format( $point->point ) ) ?></span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-slate-350 text-xs">تاریخ ثبت</span>
                            <span><?php echo esc_html( jdate( "Y.m.d", $point->created_at ) ) ?></span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-slate-350 text-xs">نوع فعالیت</span>
                            <span><?php echo esc_html( $point->action ) ?></span>
                        </div>
                    </div>
                </div>
            </div>
		<?php } ?>
    </div>

	<?php if ( $total_pages > 1 ) { ?>
        <div class="mb-9 flex w-full items-center justify-center gap-4">
            <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">
				<?php echo paginate_links( [
					'mid_size'  => 1,
					'base'      => get_pagenum_link( 1 ) . '%_%',
					'format'    => '?page=%#%',
					'current'   => max( 1, $page ),
					'total'     => $total_pages,
					'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
					'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
				] ); ?>
            </div>
        </div>
	<?php } ?>

</div>