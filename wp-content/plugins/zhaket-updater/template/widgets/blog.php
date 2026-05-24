<link rel="stylesheet" href="<?php echo ZHAKET_UPDATER_PLUGIN_ASSET_URL ?>/admin/swiper-bundle.min.css" />
<script src="<?php echo ZHAKET_UPDATER_PLUGIN_ASSET_URL ?>/admin/swiper-bundle.min.js"></script>

<div style="min-height:360px;" class="zhkup">
	<div class="zhaket-swiper-btn mr-auto flex items-center justify-end absolute top-28 z-10 w-full">
		<a class="zhaket-next bg-white flex items-center justify-center rounded-3xl p-3 cursor-pointer transition-all delay-300 border-[5px] border-[#e4e4e426] mr-1 outline-none select-none">
			<svg width="5" height="8" viewBox="0 0 5 8" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M1 7L4 4L1 1" stroke="#939393" stroke-linecap="round" stroke-linejoin="round" />
			</svg>
		</a>
		<a class="zhaket-prev bg-white flex items-center justify-center rounded-3xl p-3 cursor-pointer transition-all delay-300 border-[5px] border-[#e4e4e426] mr-auto ml-1 outline-none select-none">
			<svg width="5" height="8" viewBox="0 0 5 8" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M4 7L1 4L4 1" stroke="#939393" stroke-linecap="round" stroke-linejoin="round" />
			</svg>

		</a>
	</div>
	<div class="swiper py-4 px-1 mb-5">
		<div class="swiper-wrapper">
			<?php foreach ($posts as $post): ?>
			<figure class="swiper-slide flex flex-shrink-0 justify-center items-center relative w-[250px] h-[250px] text-right mr-0">
				<a class="post-thumbnail-link w-full block max-w-[250px] max-h-[250px]" href="<?php echo $post['url']??'' ?>" target="_blank">
					<img class="rounded-2xl max-w-[250px] h-auto max-w-[250] min-w-[250] rounded-xl wp-post-image" width="250" height="250"
						 src="<?php echo $post['img']?? ''; ?>" alt="<?php echo $post['name']??'' ?>" decoding="async">
				</a>
				<div class="post-title-filter absolute bottom-3 left-4 right-4 p-4 rounded-lg flex flex-col">
                    <span class="post-title mt-0 mb-1 min-h-12 ">
                      <a href="<?php echo $post['url']??'' ?>" target="_blank" class="post-title-link hover:text-[#ff9800] transition-all delay-300 text-sm font-light text-[#333333] decoration-transparent">
                        <?php echo $post['name']??'' ?>
                      </a>
                    </span>
					<div class="justify-start flex items-center gap-2"><svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" color="#878F9B" style="color:#878F9B" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16.5 12"></polyline></svg><span class="transition duration-300 text-xs leading-7 pt-1 text-[#878F9B]"><?php echo $post['read_time']??'' ?><!-- --> دقیقه زمان مطالعه</span></div>
				</div>
			</figure>
			<?php endforeach; ?>
		</div>

	</div>

	<a class="bg-white border border-[#E5E8EB] rounded-md px-9 py-4 mb-5 mr-4 btn-blog"
	   href="https://www.zhaket.com/blog" target="_blank"><?php esc_html_e('view blog','zhaket-updater'); ?></a>
</div>


<script>

    const swiperRelated = new Swiper(".swiper", {
        direction: "horizontal", loop: true, slidesPerView: "auto", spaceBetween: 20, navigation: {
            nextEl: ".zhaket-next", prevEl: ".zhaket-prev",
        },

        scrollbar: {
            el: ".swiper-scrollbar",
        },
    });
</script>