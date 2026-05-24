<?php
global $wpdb;

$limit = 15;

switch ( sanitize_text_field( $_POST["period"] ) ) {
	case "1-month":
		$created_at = "1 MONTH";
		break;
	case "3-month":
		$created_at = "3 MONTH";
		break;
	case "1-year":
		$created_at = "3 YEAR";
		break;
	default:
		$created_at = "1 WEEK";
		break;
}

$query = $wpdb->prepare( "SELECT user_id, SUM(point) AS total_points
FROM points 
WHERE created_at >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL $created_at))
GROUP BY user_id 
ORDER BY total_points DESC
LIMIT $limit" );

$items = $wpdb->get_results( $query );

foreach ( $items as $index => $item ) {
	$user = get_user_by( 'id', $item->user_id );
	$num  = $index + 1;
	?>
	
	<div class="flex items-center gap-[20px] lg:gap-[30px]">
        <p class="text-lg font-black <?php echo $num > 3 ? 'text-[#889BAD]' : '' ?>"><?php echo $num; ?></p>                
        <div class="flex items-center justify-between lg:gap-x-8 bg-white rounded-[14px] py-[14px] px-[16px] lg:px-[34px] w-full">
            
            <div class="relative shrink-0">
                <?php echo get_avatar( $user->ID, 34, '', $user->display_name, [
                    'class' => 'w-9 h-9 lg:w-[48px] lg:h-[48px] shrink-0',
                ] ) ?>
                
				<?php if ( $num == 1 ) { ?>
                    <img alt="cup" src="<?php echo bloginfo( 'template_url' ) ?>/assets/images/cup.svg" class="w-[15px] h-[15px] absolute -bottom-1 right-0"/>
                <?php } ?>
            </div>
            
            <div class="max-lg:flex max-lg:ml-auto max-lg:flex-wrap lg:grid lg:grid-cols-6 lg:items-center max-lg:mx-2 max-lg:border-l max-lg:pl-2 lg:grow">
                <a href="<?php echo site_url( 'profile/' . $user->ID ) ?>" class="text-lg lg:text-2xl font-bold leading-[18px] lg:col-span-2 line-clamp-1"><?php echo $user->display_name; ?></a>
                <div class="col-span-2 justify-center max-lg:mr-auto">
                    <?php user_badge_by_level( $user->ID, 'bg-[#FD701338] text-[#FD7013] rounded-[24px] w-fit h-5.5 flex items-center justify-center text-xs font-black' ); ?>
                </div>
                <div class="space-x-4 max-lg:w-full leading-[18px]">
                    <span class="text-[#889BAD]">امتیازکل</span>
                    <span class="text-[#BF9A00] font-heavy-yekanbakh"><?php echo number_format( get_user_points( $user->ID ) ); ?></span>
                </div>
            </div>
            <div class="flex flex-col lg:flex-row items-center justify-end gap-[6px] pr-[4px] lg:gap-[20px] lg:w-[20%] lg:shrink-0">
                
                <?php if ( $num == 1 ) { ?>
                    <img src="<?php echo bloginfo( 'template_url' ) ?>/assets/images/person1.svg" class="w-[30px] h-[30px] lg:w-[48px] lg:h-[48px]"/>
				<?php } elseif ( $num == 2 ) { ?>
                    <img src="<?php echo bloginfo( 'template_url' ) ?>/assets/images/person2.svg" class="w-[30px] h-[30px] lg:w-[48px] lg:h-[48px]"/>
				<?php } elseif ( $num == 3 ) { ?>
                    <img src="<?php echo bloginfo( 'template_url' ) ?>/assets/images/person3.svg" class="w-[30px] h-[30px] lg:w-[48px] lg:h-[48px]"/>
				<?php } ?>
				
                <div class="flex bg-[#EFC101] gap-[6px] px-[6px] h-4 lg:h-6 content-center lg:py-[4px] rounded-[4px]">
                    <span class="text-[12px] font-bold hidden lg:flex leading-normal">امتیاز کسب شده</span>
                    <span class="text-[12px] font-black leading-normal font-heavy-yekanbakh"><?php echo number_format( $item->total_points ); ?></span>
                </div>
                
            </div>
        </div>
    </div>
    
    <div class="hidden items-center gap-[20px] lg:gap-[30px]">
        <p class="text-lg font-black <?php echo $num > 3 ? 'text-[#889BAD]' : '' ?>"><?php echo $num; ?></p>
        <div class="flex justify-between items-center bg-white rounded-[14px] py-[14px] px-[16px] lg:px-[34px] w-full">

            <div class="flex items-center gap-[18px] lg:gap-[30px]">

                <div class="relative">
                    <a href="<?= home_url('/profile/').$user->ID ?>">
                        <?php echo get_avatar( $user->ID, 34, '', $user->display_name, [
                            'class' => 'w-[34px] h-[34px] lg:w-[48px] lg:h-[48px] max-lg:hidden',
                        ] ) ?>
                    </a>
					<?php if ( $num == 1 ) { ?>
                        <img src="<?php echo bloginfo( 'template_url' ) ?>/assets/images/cup.svg" class="w-[15px] h-[15px] absolute bottom-[-5px] right-0"/>
					<?php } ?>
                </div>

                <a href="<?= home_url('/profile/').$user->ID ?>" class="hidden lg:flex text-2xl font-bold">
					<?php echo $user->display_name; ?>
                </a>

                <div class="flex lg:hidden flex-col">
                    <p class="text-sm font-bold">
						<?php echo $user->display_name; ?>
                    </p>
                    <div class="flex lg:hidden gap-[4px]">
                        <p class="text-[#889BAD] text-xs font-bold">امتیازکل</p>
                        <p class="text-[#BF9A00] text-xs font-bold">
							<?php echo number_format( get_user_points( $user->ID ) ); ?>
                        </p>
                    </div>
                </div>

				<?php user_badge_by_level( $user->ID, 'bg-[#FD701338] text-[#FD7013] rounded-[24px] p-[4px] text-xs font-black text-nowrap' ); ?>

            </div>

            <div class="hidden lg:flex gap-[4px]">
                <p class="text-[#889BAD]">امتیازکل</p>
                <p class="text-[#BF9A00]"><?php echo number_format( get_user_points( $user->ID ) ); ?></p>
            </div>

            <hr class="flex md:hidden h-[60px] w-[1px] mx-[8px] bg-[#E4EBF0]"/>

            <div class="flex flex-col lg:flex-row items-center gap-[6px] lg:gap-[20px]">

				<?php if ( $num == 1 ) { ?>
                    <img src="<?php echo bloginfo( 'template_url' ) ?>/assets/images/person1.svg" class="w-[30px] h-[30px] lg:w-[48px] lg:h-[48px]"/>
				<?php } elseif ( $num == 2 ) { ?>
                    <img src="<?php echo bloginfo( 'template_url' ) ?>/assets/images/person2.svg" class="w-[30px] h-[30px] lg:w-[48px] lg:h-[48px]"/>
				<?php } elseif ( $num == 3 ) { ?>
                    <img src="<?php echo bloginfo( 'template_url' ) ?>/assets/images/person3.svg" class="w-[30px] h-[30px] lg:w-[48px] lg:h-[48px]"/>
				<?php } ?>
                <div class="flex bg-[#EFC101] gap-[6px] px-[6px] lg:py-[4px] rounded-[4px]">
                    <p class="text-[12px] font-bold hidden lg:flex">امتیاز کسب شده</p>
                    <p class="text-[12px] font-black"><?php echo number_format( $item->total_points ); ?></p>
                </div>

            </div>
        </div>
    </div>
<?php } ?>
