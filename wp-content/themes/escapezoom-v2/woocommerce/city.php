<?php
get_header();

$current_archive_obj = get_queried_object();
$term_type = $current_archive_obj->taxonomy;

if ( $current_archive_obj->parent == 0 )
    $product_type = get_term( $current_archive_obj )->name;
else
    $product_type = get_term( $current_archive_obj->parent )->name;

if ( $term_type == 'product_tag' ) { ?>

    <div>product_tag</div>

<?php
} else if ( $term_type == 'product_cat' ) { ?>

    <div>product_cat</div>

<?php
} else if ( $term_type == 'yith_product_brand' ) { ?>

    <div>yith_product_brand</div>

<?php
}

get_footer() ?>