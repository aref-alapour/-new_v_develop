<?php
$comments_per_page = 10;
$args = array(
    'post_type' => 'product',
    'status' => 'approve',
    'number' => $comments_per_page,
    'orderby' => 'comment_date',
    'order' => 'DESC',
    'parent' => 0,
);
$comments_query = new WP_Comment_Query;
$comments = $comments_query->query($args);

$comment_items = [];

if ($comments) {
    foreach ($comments as $comment) {
        $comment_id = $comment->comment_ID;

        $replies_args = array(
            'parent' => $comment_id,
            'status' => 'approve',
            'type' => 'comment',
        );

        $author_title = $comment->comment_author;

        if (ctype_digit($comment->comment_author))
            $author_title = str_replace(substr($comment->comment_author, 3, 5), "×××××", $comment->comment_author);

        $comment_rating = get_comment_meta($comment_id, 'comment_rating', true);

        $comment_items[] = [
            'id' => (int)$comment_id,
            'author' => $author_title,
            'author_id' => $comment->user_id,
            'author_image' => get_user_meta($comment->user_id, 'user_avatar', true) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
            'author_level' => '',
            'product_title' => get_the_title($comment->comment_post_ID),
            'product_url' => trim_home_url(get_permalink($comment->comment_post_ID)),
            'content' => $comment->comment_content,
            'date' => strtotime($comment->comment_date),
            'reply' => isset(get_comments($replies_args)[0]) ? get_comments($replies_args)[0]->comment_content : null,
            'votes_count' => ((int)get_comment_meta($comment_id, 'cld_like_count', true) - (int)get_comment_meta($comment_id, 'cld_dislike_count', true)),
            //'rating_items' => $comment_rating ? array_map(fn($value) => $value / 20, get_comment_meta($comment_id, 'comment_rating', true)) : 0,
        ];
    }
} ?>
<section class="py-4 md:py-5 lg:py-9 overflow-hidden">
    <div class="flex items-center gap-3.5 pb-5 lg:hidden">
        <h3 class="text-nowrap text-base text-slate-700">آخرین دیدگاه کاربران</h3>
        <div class="h-px w-full bg-slate-100 max-md:h-2 max-md:bg-slate-50"></div>
    </div>
    <div class="max-md:flex max-md:items-center max-md:justify-between max-md:gap-x-8 max-md:h-[267px]">
        <div class="embla_comments_main_lg relative overflow-hidden  max-md:h-[267px] max-md:hidden">
            <div class="embla__viewport  max-md:h-[267px] relative py-2.5 grow md:before:h-full md:before:w-24 md:before:bg-gradient-to-l md:before:from-white md:before:to-white/0 md:before:absolute md:before:z-10 md:after:h-full md:after:w-24 md:after:bg-gradient-to-l md:after:to-white md:after:from-white/0 md:after:absolute md:after:left-0 md:after:top-0 md:after:z-10 max-md:before:h-30 max-md:before:w-full max-md:before:bg-gradient-to-b max-md:before:from-white max-md:before:to-white/0 max-md:before:absolute max-md:before:top-0 max-md:before:z-10 max-md:after:h-30 max-md:after:w-full max-md:after:bg-gradient-to-b max-md:after:to-white max-md:after:from-white/0 max-md:after:absolute max-md:after:bottom-0 max-md:after:z-10">
                <div class="embla__container flex max-md:flex-col child:relative child:before:absolute max-md:gap-y-20 md:child:mx-3 child:md:pr-6 child:md:before:w-px child:md:before:absolute child:md:before:bg-gradient-to-t child:md:before:from-white child:md:before:via-slate-110 child:md:before:to-white child:md:before:right-0 child:md:before:h-full">
                    <?php foreach ($comment_items as $comment): ?>
                        <div class="embla__slide shrink-0 grow-0 md:w-[320px] h-[240px]">
                            <div class="flex max-md:gap-x-5 md:justify-between items-start w-full">
                                <div class="w-14 h-14 md:w-11 md:h-11 rounded-xl overflow-hidden shrink-0 md:order-1">
                                    <a href="<?= home_url() . $comment['product_url'] ?>">
                                        <img alt="بازیکن سایت اسکیپ‌زوم - <?= $comment['product_title'] ?>" loading="lazy" width="58" height="58" decoding="async" data-nimg="1" class="h-full w-full object-cover" src="<?= $comment['author_image'] ?>" style="color: transparent;">
                                    </a>
                                </div>
                                <div class="grow">
                                    <div class="space-x-8 space-x-reverse select-none">
                                        <bdo dir="rtl" class="text-[#889BAD] text-md">
                                            <?= $comment['author'] ?>
                                        </bdo>
                                        <?php user_badge_by_level($comment['author_id'], 'px-1.5 py-0.5 bg-primary-100/20 text-primary-500 rounded-2xl text-4xs inline-flex') ?>
                                    </div>
                                    <h2 class="leading-normal">
                                        <a href="<?= home_url() . $comment['product_url'] ?>">
                                            <?= $comment['product_title'] ?>
                                        </a>
                                    </h2>
                                </div>
                            </div>
                            <p class="text-[#889BAD] text-sm font-medium-yekanbakh my-6 leading-[1.7] line-clamp-4 select-none">
                                <?= $comment['content'] ?>
                            </p>
                            <div class="flex items-center justify-between select-none">
                                <div class="text-[#889BAD] text-sm font-medium-yekanbakh flex items-center gap-x-2">
                                    <span class="font-medium-yekanbakh">(<?= $comment['votes_count'] ?>)</span>
                                    <span>
                                        <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="19" height="19"
                                            viewBox="0 0 19 19" fill="none">
                                            <path d="M11.8743 5.34454L11.2885 5.24717C11.2744 5.33222 11.2789 5.41933 11.3019 5.50245C11.3248 5.58557 11.3655 5.66269 11.4213 5.72846C11.4771 5.79424 11.5465 5.84708 11.6247 5.88331C11.7029 5.91955 11.7881 5.93831 11.8743 5.93829V5.34454ZM3.16602 5.34454V4.75079C3.00854 4.75079 2.85752 4.81335 2.74617 4.9247C2.63482 5.03605 2.57227 5.18707 2.57227 5.34454H3.16602ZM4.74935 13.855H13.7427V12.6675H4.74935V13.855ZM14.6927 4.75079H11.8743V5.93829H14.6927V4.75079ZM12.4602 5.44192L13.0983 1.61421L11.9266 1.41867L11.2885 5.24717L12.4602 5.44192ZM11.7318 0.000791609H11.5624V1.18829H11.7311L11.7318 0.000791609ZM9.09164 1.32287L7.1006 4.30983L8.0886 4.9685L10.0796 1.98154L9.09164 1.32287ZM6.27727 4.75079H3.16602V5.93829H6.27727V4.75079ZM2.57227 5.34454V11.6779H3.75977V5.34454H2.57227ZM15.8778 12.1054L16.8278 7.35537L15.6641 7.12183L14.7141 11.8718L15.8778 12.1054ZM7.1006 4.30983C7.01027 4.44543 6.88786 4.55584 6.74423 4.63276C6.6006 4.70969 6.4402 4.74996 6.27727 4.75V5.9375C7.0056 5.9375 7.68485 5.57412 8.0886 4.9685L7.1006 4.30983ZM13.0983 1.61421C13.1313 1.41579 13.1206 1.21178 13.0672 1.01788C13.0137 0.823979 12.9186 0.644047 12.7886 0.490588C12.6586 0.33713 12.4967 0.213824 12.3142 0.129242C12.1317 0.0446588 11.933 0.000826733 11.7318 0.000791609L11.7311 1.18829C11.7598 1.18836 11.7889 1.19467 11.8149 1.20678C11.8409 1.2189 11.864 1.23652 11.8825 1.25843C11.901 1.28035 11.9146 1.30603 11.9222 1.3337C11.9298 1.36137 11.9313 1.39036 11.9266 1.41867L13.0983 1.61421ZM14.6927 5.9375C15.3181 5.9375 15.7852 6.50908 15.6633 7.12104L16.8278 7.35458C16.8908 7.03878 16.883 6.71215 16.8048 6.39974C16.7267 6.08734 16.5803 5.79615 16.3761 5.54717C16.1718 5.29819 15.9149 5.09762 15.6238 4.95991C15.3327 4.8222 15.0147 4.75077 14.6927 4.75079V5.9375ZM13.7427 13.855C14.246 13.8551 14.7337 13.68 15.123 13.361C15.5122 13.042 15.779 12.5981 15.8778 12.1046L14.7141 11.871C14.6692 12.0956 14.5478 12.2976 14.3707 12.4426C14.1936 12.5877 13.9716 12.6677 13.7427 12.6675V13.855ZM11.5624 0.000791609C11.0737 0.000827114 10.5926 0.120725 10.1617 0.351357C9.73079 0.58199 9.36351 0.91543 9.09243 1.32208L10.0796 1.98154C10.2424 1.73744 10.4629 1.53651 10.7215 1.39813C10.9802 1.25974 11.2691 1.18818 11.5624 1.18829V0.000791609ZM4.74935 12.6675C4.2031 12.6675 3.75977 12.2241 3.75977 11.6779H2.57227C2.57227 12.2553 2.80164 12.809 3.20992 13.2173C3.6182 13.6256 4.17195 13.855 4.74935 13.855V12.6675Z"
                                                fill="#889BAD" />
                                            <path d="M6.33203 5.34375V13.2604" stroke="#889BAD" />
                                        </svg>
                                    </span>
                                    <span class="font-medium-yekanbakh">رأی مثبت</span>
                                </div>
                                <span class="text-[#889BAD] text-sm font-medium-yekanbakh">
                                    <?= jdate('Y/m/d', $comment['date']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="embla_comments_main_md md:hidden overflow-hidden w-full h-[267px]">
            <div class="embla__viewport  max-md:h-[267px] relative py-2.5 grow md:before:h-full md:before:w-24 md:before:bg-gradient-to-l md:before:from-white md:before:to-white/0 md:before:absolute md:before:z-10 md:after:h-full md:after:w-24 md:after:bg-gradient-to-l md:after:to-white md:after:from-white/0 md:after:absolute md:after:left-0 md:after:top-0 md:after:z-10 max-md:before:h-12 max-md:before:w-full max-md:before:bg-gradient-to-b max-md:before:from-white max-md:before:to-white/0 max-md:before:absolute max-md:before:top-0 max-md:before:z-10 max-md:after:h-12 max-md:after:w-full max-md:after:bg-gradient-to-b max-md:after:to-white max-md:after:from-white/0 max-md:after:absolute max-md:after:bottom-0 max-md:after:z-10">
                <div class="embla_comments_main_md__container w-full flex flex-col h-[267px]">
                    <?php foreach ($comment_items as $comment): ?>
                        <div class="embla__slide shrink-0 grow-0 md:w-[320px] h-[267px] content-center">
                            <div class="flex max-md:gap-x-5 md:justify-between items-start w-full">
                                <div class="w-14 h-14 md:w-11 md:h-11 rounded-xl overflow-hidden shrink-0 md:order-1">
                                    <a href="<?= home_url() . $comment['product_url'] ?>">
                                        <img alt="بازیکن سایت اسکیپ‌زوم - <?= $comment['product_title'] ?>" loading="lazy" width="58" height="58" decoding="async" data-nimg="1" class="h-full w-full object-cover" src="<?= $comment['author_image'] ?>" style="color: transparent;">
                                    </a>
                                </div>
                                <div class="grow">
                                    <div class="space-x-8 space-x-reverse select-none">
                                        <bdo dir="rtl" class="text-[#889BAD] text-md">
                                            <?= $comment['author'] ?>
                                        </bdo>
                                        <?php user_badge_by_level($comment['author_id'], 'px-1.5 py-0.5 bg-primary-100/20 text-primary-500 rounded-2xl text-4xs inline-flex') ?>
                                    </div>
                                    <h2 class="leading-normal">
                                        <a href="<?= home_url() . $comment['product_url'] ?>">
                                            <?= $comment['product_title'] ?>
                                        </a>
                                    </h2>
                                </div>
                            </div>
                            <p class="text-[#889BAD] text-sm font-medium-yekanbakh my-6 leading-[1.7] line-clamp-4 select-none">
                                <?= $comment['content'] ?>
                            </p>
                            <div class="flex items-center justify-between select-none">
                                <div class="text-[#889BAD] text-sm font-medium-yekanbakh flex items-center gap-x-2">
                                    <span class="font-medium-yekanbakh">(<?= $comment['votes_count'] ?>)</span>
                                    <span>
                                        <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="19" height="19"
                                            viewBox="0 0 19 19" fill="none">
                                            <path d="M11.8743 5.34454L11.2885 5.24717C11.2744 5.33222 11.2789 5.41933 11.3019 5.50245C11.3248 5.58557 11.3655 5.66269 11.4213 5.72846C11.4771 5.79424 11.5465 5.84708 11.6247 5.88331C11.7029 5.91955 11.7881 5.93831 11.8743 5.93829V5.34454ZM3.16602 5.34454V4.75079C3.00854 4.75079 2.85752 4.81335 2.74617 4.9247C2.63482 5.03605 2.57227 5.18707 2.57227 5.34454H3.16602ZM4.74935 13.855H13.7427V12.6675H4.74935V13.855ZM14.6927 4.75079H11.8743V5.93829H14.6927V4.75079ZM12.4602 5.44192L13.0983 1.61421L11.9266 1.41867L11.2885 5.24717L12.4602 5.44192ZM11.7318 0.000791609H11.5624V1.18829H11.7311L11.7318 0.000791609ZM9.09164 1.32287L7.1006 4.30983L8.0886 4.9685L10.0796 1.98154L9.09164 1.32287ZM6.27727 4.75079H3.16602V5.93829H6.27727V4.75079ZM2.57227 5.34454V11.6779H3.75977V5.34454H2.57227ZM15.8778 12.1054L16.8278 7.35537L15.6641 7.12183L14.7141 11.8718L15.8778 12.1054ZM7.1006 4.30983C7.01027 4.44543 6.88786 4.55584 6.74423 4.63276C6.6006 4.70969 6.4402 4.74996 6.27727 4.75V5.9375C7.0056 5.9375 7.68485 5.57412 8.0886 4.9685L7.1006 4.30983ZM13.0983 1.61421C13.1313 1.41579 13.1206 1.21178 13.0672 1.01788C13.0137 0.823979 12.9186 0.644047 12.7886 0.490588C12.6586 0.33713 12.4967 0.213824 12.3142 0.129242C12.1317 0.0446588 11.933 0.000826733 11.7318 0.000791609L11.7311 1.18829C11.7598 1.18836 11.7889 1.19467 11.8149 1.20678C11.8409 1.2189 11.864 1.23652 11.8825 1.25843C11.901 1.28035 11.9146 1.30603 11.9222 1.3337C11.9298 1.36137 11.9313 1.39036 11.9266 1.41867L13.0983 1.61421ZM14.6927 5.9375C15.3181 5.9375 15.7852 6.50908 15.6633 7.12104L16.8278 7.35458C16.8908 7.03878 16.883 6.71215 16.8048 6.39974C16.7267 6.08734 16.5803 5.79615 16.3761 5.54717C16.1718 5.29819 15.9149 5.09762 15.6238 4.95991C15.3327 4.8222 15.0147 4.75077 14.6927 4.75079V5.9375ZM13.7427 13.855C14.246 13.8551 14.7337 13.68 15.123 13.361C15.5122 13.042 15.779 12.5981 15.8778 12.1046L14.7141 11.871C14.6692 12.0956 14.5478 12.2976 14.3707 12.4426C14.1936 12.5877 13.9716 12.6677 13.7427 12.6675V13.855ZM11.5624 0.000791609C11.0737 0.000827114 10.5926 0.120725 10.1617 0.351357C9.73079 0.58199 9.36351 0.91543 9.09243 1.32208L10.0796 1.98154C10.2424 1.73744 10.4629 1.53651 10.7215 1.39813C10.9802 1.25974 11.2691 1.18818 11.5624 1.18829V0.000791609ZM4.74935 12.6675C4.2031 12.6675 3.75977 12.2241 3.75977 11.6779H2.57227C2.57227 12.2553 2.80164 12.809 3.20992 13.2173C3.6182 13.6256 4.17195 13.855 4.74935 13.855V12.6675Z"
                                                fill="#889BAD" />
                                            <path d="M6.33203 5.34375V13.2604" stroke="#889BAD" />
                                        </svg>
                                    </span>
                                    <span class="font-medium-yekanbakh">رأی مثبت</span>
                                </div>
                                <span class="text-[#889BAD] text-sm font-medium-yekanbakh">
                                    <?= jdate('Y/m/d', $comment['date']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="embla_comments_thumbs_md overflow-hidden w-11 h-[267px] shrink-0 md:hidden">
            <div class="embla__viewport  max-md:h-[267px] relative py-2.5 grow md:before:h-full md:before:w-24 md:before:bg-gradient-to-l md:before:from-white md:before:to-white/0 md:before:absolute md:before:z-10 md:after:h-full md:after:w-24 md:after:bg-gradient-to-l md:after:to-white md:after:from-white/0 md:after:absolute md:after:left-0 md:after:top-0 md:after:z-10 max-md:before:h-12 max-md:before:w-full max-md:before:bg-gradient-to-b max-md:before:from-white max-md:before:to-white/0 max-md:before:absolute max-md:before:top-0 max-md:before:z-10 max-md:after:h-12 max-md:after:w-full max-md:after:bg-gradient-to-b max-md:after:to-white max-md:after:from-white/0 max-md:after:absolute max-md:after:bottom-0 max-md:after:z-10">
                <div class="embla_comments_thumbs_md__container flex flex-col h-[267px]">
                    <?php foreach ($comment_items as $comment): ?>
                        <div class="embla-thumbs__slide shrink-0 grow-0 w-11 h-11 rounded-xl overflow-hidden fill-primary-500 bg-[#EEE8E8] flex items-center justify-center child:mx-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21"
                                fill="colorCurrent">
                                <circle cx="10" cy="5.79688" r="4.5" fill="colorCurrent" />
                                <ellipse cx="10" cy="15.7969" rx="8" ry="3.5" fill="colorCurrent" />
                            </svg>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>


    </div>
</section>