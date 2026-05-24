<?php
get_header();

$current_archive_obj = get_queried_object();
$term_type  = $current_archive_obj->taxonomy;
$term_id    = $current_archive_obj->term_id;

if ( $term_type == 'product_tag' ) {

    if (str_contains($current_archive_obj->name, '|||||'))
        include_once Theme_PATH . 'template/product-archive/genre.php';
    else
        include_once Theme_PATH . 'template/product-archive/hood.php';

} else if ( $term_type == 'product_cat' ) {

    if ( $current_archive_obj->parent == 0 )
        include_once Theme_PATH . 'template/product-archive/type.php';
    else
        include_once Theme_PATH . 'template/product-archive/type_city.php';
}

get_footer() ?>