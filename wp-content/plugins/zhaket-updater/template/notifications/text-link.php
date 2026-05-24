<div class="zhkup zhkup-notice">

<div class="w-full h-[95px] flex gap-[10px] items-center border border-[#C3C3C5] py-0 px-8" style="background-color: <?php echo esc_attr($additional_data['bgcolor'] ?? ''); ?>;">
    <p style="color: <?php echo esc_attr($additional_data['text_color'] ?? ''); ?>;" class="text-2xl"><?php echo wp_kses_post($message); ?></p>
    <a style="background-color: <?php echo esc_attr($additional_data['button_bgcolor'] ?? ''); ?>; color: <?php echo esc_attr($additional_data['button_text_color'] ?? ''); ?>;" class=" text-base bg-white py-4 px-7 rounded-md decoration-transparent text-[#152530] mr-auto" href="<?php echo esc_url($link_url); ?>"><?php echo esc_html($button_text); ?></a>
</div>
           </div>
