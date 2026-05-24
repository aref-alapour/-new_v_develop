<?php get_header(); ?>
<div class="mt-12">
<?php
$post_id = get_the_ID();
if ( get_post_meta($post_id, 'assign_as_city_page', true) ) : // این صفحه به عنوان یک شهر ایجاده شده است؟

    include_once Theme_PATH . 'template/product-archive/city.php';

else :
    if ( have_posts() ) :
        while ( have_posts() ) : the_post();
            the_content();
        endwhile;
    endif;
endif;
?>
</div>

<?php get_footer(); ?>