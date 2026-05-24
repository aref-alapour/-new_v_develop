<?php

get_header();

global $wpdb, $wp;

$page_num = get_query_var( 'paged' ) !== 0 ? get_query_var( 'paged' ) : 1;

$terms_per_page = 24;

$args = [
    'taxonomy'      => 'yith_product_brand',
    'hide_empty'    => false,
    'number'        => $terms_per_page,
    'offset'        => $terms_per_page * ( $page_num - 1 )
];

if( !isset( $_GET['order'] ) || $_GET['order'] !== 'new' ){
    $args['orderby'] = 'meta_value_num';
    $args['meta_key'] = 'brand_reputation';
    $args['meta_compare'] = 'NUMERIC';
    $args['order'] = 'DESC';
}

$brands = get_terms( $args );

unset($args['offset']);
$args['number'] = 0;

$count = get_terms( $args );

$total_pages = ceil( count( $count ) / $terms_per_page ); ?>

    <section class="mb-12 mt-8 max-lg:mb-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center">
                <li class="group">
                    <div class="flex items-center">
                        <a class="text-2xs font-medium text-slate-310 hover:text-primary-600" href="/">صفحه اصلی</a>
                    </div>
                </li>
                <li class="group">
                    <div class="flex items-center">
                        <div class="mx-5 h-2 w-px bg-slate-110"></div>
                        <a class="text-2xs font-medium text-slate-310 cursor-text" href="">برند ها</a>
                    </div>
                </li>
            </ol>
        </nav>
    </section>

    <section class="flex justify-between items-center w-full max-lg:flex-col max-lg:items-start">
        <div class="flex items-center gap-2">
            <h1 class="text-2xl">
                برند ها
            </h1>
            <span class="help" data-help="برندهای مجموعه‌دار بازی که طراحی، ساخت، ارائه و اجرای بازی رو برعهده دارن."></span>
        </div>
        <div class="flex lg:gap-3 max-lg:w-full max-lg:border rounded-2xl lg:rounded-none max-lg:overflow-hidden">
            <a href="<?php echo home_url( 'brands' );?>" class="border rounded-xl grow max-lg:text-center max-lg:border-none max-lg:rounded-none px-3 py-1.5 <?php echo ( !isset( $_GET['order'] ) || $_GET['order'] !== 'new' ) ? "bg-primaryColor text-white" : ""; ?>">محبوب ترین ها</a>
            <a href="<?php echo home_url( 'brands' );?>?order=new" class="border rounded-xl grow max-lg:text-center max-lg:border-none max-lg:rounded-none px-3 py-1.5 <?php echo ( isset( $_GET['order'] ) && $_GET['order'] !== 'new' ) ? "bg-primaryColor text-white" : ""; ?>">جدید ترین ها</a>
        </div>
    </section>
    
    <hr class="my-6">

    <section class="max-w-full py-4 md:py-5 lg:py-9">
        <div class="relative w-full grid grid-cols-4 sm:grid-cols-5 lg:grid-cols-6 xl:grid-cols-8 gap-10">
            <?php foreach ( $brands as $brand ) : ?>
                <div class="flex flex-col gap-3 max-lg:gap-2">
                    <a href="<?php echo get_term_link( $brand ); ?>">
                        <?php $image_id = get_term_meta( $brand->term_id, 'thumbnail_id', true );
                        if($image_id > 0) {
                            echo wp_get_attachment_image( $image_id, 'large', false, [
                                'class' => 'rounded-xl shadow-13 aspect-square'
                            ]);
                        } else { ?>
                            <img src="<?php bloginfo('template_url');?>/assets/images/brand-default-icon.svg" class="aspect-square">
                        <?php } ?>
                    </a>
                    <a href="<?php echo get_term_link( $brand ); ?>" class="flex justify-between">
                        <span><?php echo esc_html( $brand->name );?></span>
                        <span class="flex items-center gap-2 max-lg:hidden">
                            <?php echo $brand->count; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13" fill="none">
                                <path d="M3.55248 5.52134C3.55248 4.71176 3.42316 3.46277 3.92084 2.73396C5.02103 1.12537 7.35366 1.33882 8.1766 2.90895C8.58023 3.68007 8.42838 4.75791 8.447 5.52134M3.55248 5.52134C2.28182 5.52134 2.02221 6.23477 1.82823 6.79533C1.64894 7.42511 1.46574 8.92985 1.74593 10.5788C1.95559 11.6288 2.77363 12.0903 3.47704 12.149C4.15009 12.2047 6.99118 12.1836 7.81314 12.1836C9.08771 12.1836 9.88322 11.9086 10.2575 10.6481C10.4367 9.66828 10.4857 7.91547 10.1869 6.79533C9.79113 5.67518 8.9917 5.52134 8.447 5.52134M3.55248 5.52134C4.89857 5.46846 7.67696 5.47904 8.447 5.52134" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 7.71875V9.75" stroke="black" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <?php if ( $total_pages > 1 ) : ?>
        <div class="mb-9 flex w-full items-center justify-center gap-4">
            <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center">
                <?php
                echo paginate_links( array(
                    'mid_size'  => 1,
                    'base'      => remove_query_arg( 'order', get_pagenum_link(1)) . '%_%',
                    'format'    => 'page/%#%',
                    'current'   => max( 1, get_query_var('paged') ),
                    'total'     => $total_pages,
                    'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                    'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                ) ); ?>
            </div>
        </div>
    <?php endif; ?>

<?php get_footer();