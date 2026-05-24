<div class="zhkup zhkup-notice" >
    <div class="w-full relative">
        <?php

        $class= 'w-full min-h-[95px] flex flex-col gap-[10px] items-center justify-between border border-[#C3C3C5] rounded bg-cover bg-white';

        $bg_color=!empty($args['bg_color'])? sprintf( 'background-color:%s;', $args['bg_color']):'';
        $bg_image=!empty($args['content']) && !empty($args['bg_image'])? sprintf( 'background-image:url(%s);', $args['bg_image']):'';
        $style=sprintf('style="%s %s"',$bg_color,$bg_image);
        $href='';
        $main_element=!empty($args['link'])?'a':'div' ;
        if($main_element == 'a'){
            $href= sprintf('href="%s" target="_blank"',$args['link']);
            $class.=' cursor-pointer';
        }

        $btn_bg_color=!empty($args['btn_bg_color'])? sprintf( 'background-color:%s;', $args['btn_bg_color']):'';
        $btn_color=!empty($args['btn_color'])? sprintf( 'color:%s;', $args['btn_color']):'';
        $btn_html = !empty($args['btn_text'])? sprintf('<span class="text-base bg-white py-3 px-7 mb-4 rounded-md decoration-transparent text-[#152530] cursor-pointer inline-block whitespace-nowrap	" %s style="%s %s"> %s </span>',$href,$btn_bg_color,$btn_color,$args['btn_text']):'';

        $title_color=!empty($args['title_color'])? sprintf( 'color:%s;', $args['title_color']):'';
        $content_color=!empty($args['content_color'])? sprintf( 'color:%s;', $args['content_color']):'';
        $title= !empty($args['title'])? sprintf('<p class="font-bold text-[18px] my-4  " style="%s">%s</p>',$title_color,$args['title']):'';
        $content= !empty($args['content'])? sprintf('<div class="flex flex-col px-7 ">%s <p  class="text-[16px] font-light" style="%s"> %s </p></div>',$title,$content_color,$args['content']):'';

        $img = empty($args['content']) ? sprintf('<img src="%s" />',$args['bg_image']):'';
        if (empty($img)){
            $class.=' !p-4';
        }
        echo sprintf('<%s class="%s" %s %s> %s %s %s</%s>',$main_element,$class,$style,$href ,$img ,$content ,$btn_html,$main_element);
		?>
    </div>
</div>
