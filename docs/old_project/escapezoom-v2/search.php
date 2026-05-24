<?php get_header(); ?>
    <div class="max-lg:w-full mt-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center">
                <li class="group">
                    <div class="flex items-center">
                        <a class="text-2xs font-medium text-slate-310 hover:text-primary-600" href="<?= home_url() ?>">صفحه
                            نخست</a>
                    </div>
                </li>
                <li class="group">
                    <div class="flex items-center">
                        <div class="mx-5 h-2 w-px bg-slate-110"></div>
                        <span class="text-2xs font-medium text-slate-310">نتایج جستجو</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
<?php if (have_posts()) : ?>
    <?php
    global $wp_query;
    $post_count = $wp_query->post_count;
    ?>
    <div class="mt-5 flex items-center justify-between w-full">
        <h2 class="text-base lg:text-3xl">نتایج جستجو</h2>
        <p><?= $post_count ?> مورد یافت شد:</p>
    </div>
    <section class="grid grid-cols-2 justify-between max-lg:gap-5.5 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 2xl:grid-cols-8 child:box-content gap-6">
    <?php while ( have_posts() ) : the_post(); ?>


    <?php endwhile; ?>
    </section>
<?php else: ?>
    <div class="rounded-xl px-16 py-12 border border-slate-100 text-center shadow-12">موردی یافت نشد.</div>
<?php endif; ?>
<?php get_footer(); ?>