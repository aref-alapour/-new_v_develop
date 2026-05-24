<?php

global $wpdb;

$author = $_POST['author'];

$posts_count = $wpdb->get_var( 
    $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_author=%d AND post_status=%s AND post_type=%s", [
        $author,
        'publish',
        'post'
    ] ) 
);

$posts_per_page = $_POST['posts_per_page'];

$all_posts = [];
$args = [
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'author__in'     => [ $author ],
    'posts_per_page' => $posts_per_page,
    'offset'         => $posts_per_page * ( (int) $_POST['page'] - 1 )
];

$query = new WP_Query( $args );

if( $query->have_posts() ){
    while( $query->have_posts() ){
        $query->the_post();
        $all_posts[] = get_the_ID();
    }
    wp_reset_postdata();
}

$total_pages = ceil( $posts_count / ($posts_per_page) );

$data = [
    'all_posts'     => $all_posts,
    'total_pages'   => $_POST['total_pages']
];

?>
            
<div class="md:mb-8 mb-8 mt-8 lg:mb-0 mt-20 [&amp;>div]:items-start">
    <div class="flex justify-between">
        <div class="items-center gap-6 md:flex">
            <h2 class="flex items-center gap-4">
                <span class="text-base font-bold md:text-lg">
                    <span class="text-">جدیدترین پست های منتشر
                        شده
                    </span>
                </span>
            </h2>
        </div>
    </div>
</div>

<div class="mb-11.5 w-full border-t border-slate-100 max-lg:hidden"></div>

<div class="grid auto-cols-max grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-11 2xl:grid-cols-4">
    <?php foreach( $data['all_posts'] as $post ): ?>
        <div class="w-full max-sm:border-b max-sm:border-slate-100 max-sm:pb-8">
            <a href="<?php echo get_the_permalink( $post ); ?>">
                <div class="h-44 w-full overflow-hidden rounded-md lg:h-54 lg:rounded-xlh lg:shadow-23">
                    <?php echo get_the_post_thumbnail( $post, 'medium_large', [
                        'class' => 'h-full w-full object-cover'
                    ] ); ?>
                </div>
                <div class="mt-8">
                    <h2 class="truncate text-base"><?php echo get_the_title( $post ); ?></h2>
                    <div class="mt-3 flex items-center gap-5 text-xs text-slate-350">
                        <span>
                            <?php $views = (int) get_post_meta( $post, 'views', true );
                            echo esc_html( $views ); ?>
                            بازدید
                        </span>
                        <span>
                        <?php $categories = get_the_category();
                        if ( ! empty( $categories ) ) {
                        	echo esc_html( $categories[0]->name );	
                        } ?>
                        </span>
                        <time datetime="<?php echo get_post_timestamp( $post ); ?>" dir="ltr">
                            <?php echo jdate( "Y . m . d", get_post_timestamp( $post ));?>
                        </time>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="mb-9 flex w-full items-center justify-center gap-4">
    <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">
        <?php echo paginate_links( array(
            'mid_size'  => 1,
            'base'      => $_POST['base_url'] . '%_%',
            'format'    => '?page=%#%',
            'current'   => max( 1, (int) $_POST['page'] ),
            'total'     => $total_pages,
            'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
            'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
        ) ); ?>
    </div>
</div>

<script>
    jQuery(document).ready( function( $ ) {
        $(".pagination a.page-numbers").each( (index, item) => {
            let page = $(item).attr('href').split('?page=')[1]
            
            if ( page == undefined ) {
                page = 1
            }
            
            $(item).on('click', function(e) {
                e.preventDefault()
                
                $.ajax({
                    type    : 'POST',
                    url     : "<?php echo admin_url('admin-ajax.php') ?>",
                    data    : {
                        'action'        : 'v2_ajax_handler',
                        'nonce'         : "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                        'callback'      : 'get_author_posts',
                        'author'        : <?php echo $author; ?>,
                        'page'          : page,
                        'posts_per_page': <?php echo $posts_per_page; ?>,
                        'total_pages'   : <?php echo $total_pages; ?>
                    },
                    success: function(data) {
                        $(".all-posts").html(data)
                    },
                });
            })
        })
    } )
</script>
            