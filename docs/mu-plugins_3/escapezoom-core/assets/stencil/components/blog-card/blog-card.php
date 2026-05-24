<?php

if (!function_exists('ez_render_blog_card')) {
    function ez_render_blog_card(array $post): string
    {
        $id         = $post['id'] ?? '';
        $href       = $post['href'] ?? '';
        $title      = $post['title'] ?? '';
        $excerpt    = $post['excerpt'] ?? '';
        $author     = $post['author'] ?? '';
        $views      = isset($post['views']) ? (int) $post['views'] : 0;
        $category   = $post['category'] ?? '';
        $date       = $post['date'] ?? '';
        $date_iso   = $post['date_iso'] ?? '';
        $image      = $post['image'] ?? '';
        $image_alt  = $post['image_alt'] ?? $title;

        ob_start();
        ?>
        <ez-blog-card href="<?= esc_url($href); ?>">
            <?php if ($image): ?>
                <img slot="image" src="<?= esc_url($image); ?>" alt="<?= esc_attr($image_alt); ?>"
                     loading="lazy" decoding="async"
                     class="h-full w-full object-cover" />
            <?php endif; ?>
            <?php if ($category): ?>
                <span slot="category"
                      class="absolute right-3 top-3 rounded-md border border-white/30 bg-slate-900/60 px-2 py-1 text-xs font-medium text-white">
                    <?= esc_html($category); ?>
                </span>
            <?php endif; ?>
            <div slot="title" class="flex flex-col gap-2">
                <h3 class="text-16 font-bold leading-tight line-clamp-2">
                    <a href="<?= esc_url($href); ?>" class="hover:text-primary-500 transition">
                        <?= esc_html($title); ?>
                    </a>
                </h3>
            </div>
            <div slot="meta" class="flex flex-wrap items-center gap-3 text-14 text-slate-350">
                <span class="flex items-center gap-1">
                    <?= number_format($views); ?>
                    <span>بازدید</span>
                </span>
                <?php if ($author): ?>
                    <span><?= esc_html($author); ?></span>
                <?php endif; ?>
                <?php if ($category): ?>
                    <span><?= esc_html($category); ?></span>
                <?php endif; ?>
                <?php if ($date): ?>
                    <time datetime="<?= esc_attr($date_iso ?: $date); ?>" dir="ltr"><?= esc_html($date); ?></time>
                <?php endif; ?>
            </div>
            <?php if ($excerpt): ?>
                <p slot="excerpt" class="text-12 text-slate-400 leading-6 line-clamp-2">
                    <?= esc_html($excerpt); ?>
                </p>
            <?php endif; ?>
        </ez-blog-card>
        <?php
        return ob_get_clean();
    }
}
