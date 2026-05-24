<div class="zhkup zhkup-notice notice" style="padding: 0!important; background: initial!important;border: initial!important;box-shadow:initial!important;">
	<div class="w-full relative">
		<?php
        $class= 'w-full min-h-[50px] flex flex-col md:flex-row gap-[10px] items-center justify-between border border-[#C3C3C5] rounded py-0 px-0 bg-cover bg-white items-baseline md:items-center';

        $bg_color=!empty($args['bg_color'])? sprintf( 'background-color:%s;', $args['bg_color']):'';
        $bg_image=!empty($args['content']) && !empty($args['bg_image'])? sprintf( 'background-image:url(%s);background-position:center cetner;    background-size: cover;', $args['bg_image']):'';
        $style=sprintf('style="%s %s"',$bg_color,$bg_image);
        $href='';
		$main_element=!empty($args['link'])?'a':'div' ;
		 if($main_element == 'a'){
            $href= sprintf('href="%s" target="_blank"',$args['link']);
			$class.=' cursor-pointer';
		}

        $btn_bg_color=!empty($args['btn_bg_color'])? sprintf( 'background-color:%s;', $args['btn_bg_color']):'';
        $btn_color=!empty($args['btn_color'])? sprintf( 'color:%s;', $args['btn_color']):'';
		$btn_html = !empty($args['btn_text'])? sprintf('<span class="!text-base bg-white py-3 px-7 mx-2 rounded-md decoration-transparent text-[#152530] cursor-pointer inline-block whitespace-nowrap text-center w-full md:w-auto" %s style="%s %s"> %s </span>',$href,$btn_bg_color,$btn_color,$args['btn_text']):'';

		$title_color=!empty($args['title_color'])? sprintf( 'color:%s;', $args['title_color']):'';
        $content_color=!empty($args['content_color'])? sprintf( 'color:%s;', $args['content_color']):'';
        $title= !empty($args['title'])? sprintf('<p class="font-bold text-[18px] !m-0 !p-0 " style="%s">%s</p>',$title_color,$args['title']):'';
		$content= !empty($args['content'])? sprintf('<div class="flex flex-col gap-2 justify-start w-full">%s <p class="text-[16px] font-light !m-0 !p-0" style="%s"> %s </p></div>',$title,$content_color,$args['content']):'';

		$img = empty($args['content']) ? sprintf('<img src="%s" class="min-h-[70px] w-auto object-cover" />',$args['bg_image']):'';
        if (empty($img)){
            $class.=' !py-4 !px-[30px]';
        }
		echo sprintf('<%s class="%s" %s %s> %s %s %s</%s>',$main_element,$class,$style,$href ,$img ,$content ,$btn_html,$main_element);

		 if (!empty($args['dismissable'])):
            $query_var = add_query_arg( [
                                            'dismiss_nonce' => wp_create_nonce( 'zhupclient_dismiss_me' ),
                                            'dismiss_id'    => !empty($args['id'])?$args['id']:'system',
                                        ] );
            ?>
			<a class="font-semibold flex items-center justify-center gap-[10px] text-white transition duration-300 focus:outline-none focus:outline-0 text-xs hover:bg-secondary/80 h-[23px] w-[23px] rounded-lg bg-[#FFF1ED] p-0 absolute right-2 left-auto  rtl:!left-2 rtl:!right-auto top-2" type="button" href="<?php echo $query_var ?>"><div class="flex items-center justify-center"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" color="#FF6437" height="14" width="14" xmlns="http://www.w3.org/2000/svg" style="color: rgb(255, 100, 55);"><path d="m289.94 256 95-95A24 24 0 0 0 351 127l-95 95-95-95a24 24 0 0 0-34 34l95 95-95 95a24 24 0 1 0 34 34l95-95 95 95a24 24 0 0 0 34-34z"></path></svg></div></a>
        <?php endif; ?>
	</div>
</div>
