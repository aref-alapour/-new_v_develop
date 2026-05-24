<section id="gamesSection" class="mt-7">
    <div class="w-full px-[34px] py-4 rounded-t-2.5xl bg-[#E4EBF0] grid gap-4">
        <div class="grid text-sm font-yekan-bold text-grayy text-center"
             style="grid-template-columns: 1fr 1.2fr 1.2fr 1fr 1.1fr 1fr 1fr 1fr 1fr 1fr">
            <p>نوع بازی</p>
            <p>اسم بازی</p>
            <p>مجموعه</p>
            <p>شهر</p>
            <p>شماره تماس</p>
            <p>شماره مالک</p>
            <p>لینک بازی</p>
            <p>لینک کوتاه</p>
            <p>تقویم رزرو</p>
            <?php
            if (array_intersect(['administrator', 'supervisor', 'poshtiban'], wp_get_current_user()->roles)) : ?>
                <p>عملیات</p>
            <?php
            endif; ?>
        </div>
    </div>

    <?php
    $args_query = array(
        'post_type'         => 'product',
        'post_status'       => 'any',
        'posts_per_page'    => -1,
    );

    $the_query = new WP_Query($args_query);
    if ($the_query->have_posts()) :
        while ($the_query->have_posts()) : $the_query->the_post();
            global $product;

            $product_id = $product->get_id();

            $product_brands = get_the_terms($product_id, 'yith_product_brand');

            $terms = get_the_terms($product_id, 'product_cat');
            if ($terms) {
                if (count($terms) > 1) {
                    foreach ($terms as $term)
                        if ($term->parent == 0)
                            $product_type = $term->name;
                        else
                            $city_name  = $term->name;
                } else {
                    $product_type   = get_term($terms[0]->parent)->name;
                    $city_name      = $terms[0]->name;
                }
            }
            $owner_user_id = get_post_meta( $product_id, 'user_ebtal', true ); ?>

            <div class="res_row grid text-sm font-yekan-bold text-grayy text-center px-[34px] items-center" ${backgroundStyle}
                 style="grid-template-columns: 1fr 1.2fr 1.2fr 1fr 1.1fr 1fr 1fr 1fr 1fr 1fr">
                <p class="text-base font-yekan-bold text-navyBlue"><?php echo $product_type ?></p>
                <p class="text-base font-yekan-bold text-blueEscape cursor-pointer">
                    <a target="_blank" href="<?php the_permalink() ?>"><?php the_title(); ?></a>
                </p>
                <p class="text-base font-yekan-bold text-navyBlue"><?php echo $product_brands[0]->name; ?></p>
                <p class="text-base font-yekan-bold text-navyBlue"><?php echo $city_name ?></p>
                <p class="text-base font-yekan-bold text-navyBlue"><?php the_field('room_phone'); ?></p>
                <p class="text-base font-yekan-bold text-navyBlue"><a target="_blank" href="<?php echo home_url("team/transactions?user_id=$owner_user_id"); ?>"><?php echo get_userdata( $owner_user_id )->user_login;; ?></a></p>
                <div class="flex justify-center">
                    <a target="_blank" href="<?php the_permalink() ?>" class="w-6 h-6 bg-[#2B7FFF] rounded flex items-center justify-center">
                        <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                        </svg>
                    </a>
                </div>
                <div class="flex justify-center">
                    <?php
                    $shortlink = get_post_meta($product_id, '_ez_shortlink', true);
                    if (!$shortlink) {
                        $shortlink = 'eszm.ir?r=' . $product_id;
                    }
                    ?>
                    <button onclick="openShortlinkModal('<?php echo esc_js($shortlink); ?>')" class="w-6 h-6 bg-[#10B981] rounded flex items-center justify-center cursor-pointer hover:bg-[#059669] transition-colors" title="مشاهده لینک کوتاه">
                        <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <div class="flex justify-center">
                    <a target="_blank" href="<?= home_url('/r/') . $product_id ?>" class="w-6 h-6 bg-green-500 rounded flex items-center justify-center">
                        <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </a>
                </div>

                <?php
                if (array_intersect(['administrator', 'supervisor', 'poshtiban'], wp_get_current_user()->roles)) : ?>
                    <button onclick="openEditModal(<?php echo get_the_ID(); ?>, '<?php echo $product_brands[0]->term_id ?? 0; ?>')" class="w-6 h-6 bg-[#FD7013] rounded flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="20" viewBox="0 0 17 20" fill="none">
                            <path d="M4.33333 6.66376H3.64667C2.39917 6.66376 1.775 6.66376 1.38833 6.2971C1 5.93293 1 5.34376 1 4.16626C1 2.98876 1 2.3996 1.3875 2.0346C1.775 1.66876 2.39917 1.66876 3.64667 1.66876H13.3525C14.6008 1.66876 15.225 1.66876 15.6125 2.0346C16 2.40043 16 2.98793 16 4.16543C16 5.34293 16 5.9321 15.6125 6.29793C15.225 6.66376 14.6008 6.66376 13.3525 6.66376H12.25" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M13.5252 18.3263C13.4835 16.8979 13.6002 16.7096 13.7093 16.3929C13.8193 16.0746 14.4985 14.9471 14.7702 14.1279C15.6468 11.4796 14.976 11.0321 13.9252 10.2279C12.7218 9.30543 10.6835 8.83627 9.41265 8.93793V5.5296C9.41265 4.81043 8.72432 4.1846 7.95265 4.1846C7.18099 4.1846 6.49765 4.81043 6.49765 5.5296V11.9896L4.85515 10.5913C4.41182 10.1438 3.70265 10.1846 3.18849 10.5238C3.02859 10.6295 2.90042 10.7767 2.81765 10.9496C2.58432 11.4404 2.65099 11.9954 3.01932 12.4496L3.95265 13.6538M3.95265 13.6538C4.17599 13.9238 4.40182 14.2388 4.68515 14.5971M3.95265 13.6538L4.68515 14.5971M6.44015 18.3313V17.5429C6.50099 16.5738 5.62099 15.7963 4.68515 14.5971M4.68515 14.5971C4.61765 14.5104 4.74849 14.6779 4.68515 14.5971ZM4.68515 14.5971L5.60765 15.7254" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                <?php
                endif; ?>

            </div>
        <?php
        endwhile;
        wp_reset_postdata();

    endif; ?>


</section>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" style="display: none;">
    <div class="bg-white rounded-2xl p-4 shadow-2xl max-w-md w-full mx-4 relative">
        <div class="flex gap-4">
            <button id="editGameBtn" class="flex-1 bg-[#2B7FFF] text-white px-6 py-3 rounded-lg font-yekan-bold text-sm hover:bg-[#1e5bb8] transition-colors">
                ویرایش بازی
            </button>
            <button id="editBrandBtn" class="flex-1 bg-[#FD7013] text-white px-6 py-3 rounded-lg font-yekan-bold text-sm hover:bg-[#e55a0a] transition-colors">
                ویرایش برند
            </button>
        </div>
        <button onclick="closeEditModal()" class="absolute top-px left-px text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
</div>

<!-- Shortlink Modal -->
<div id="shortlinkModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" style="display: none;">
    <div class="bg-white rounded-2xl p-6 shadow-2xl max-w-md w-full mx-4 relative">
        <h3 class="text-lg font-yekan-bold text-navyBlue mb-4">لینک کوتاه</h3>
        <div class="mb-4">
            <p class="text-sm font-yekan-bold text-grayy mb-2">لینک:</p>
            <div id="shortlinkContent" class="flex items-center gap-2">
                <a id="shortlinkLink" href="" target="_blank" rel="noopener noreferrer" class="text-sm font-yekan-bold text-[#2B7FFF] hover:underline break-all"></a>
            </div>
        </div>
        <div class="flex gap-3">
            <button id="copyShortlinkBtn" class="flex-1 bg-[#10B981] text-white px-6 py-3 rounded-lg font-yekan-bold text-sm hover:bg-[#059669] transition-colors flex items-center justify-center gap-2">
                <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
                کپی لینک
            </button>
            <button onclick="closeShortlinkModal()" class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-yekan-bold text-sm hover:bg-gray-400 transition-colors">
                بستن
            </button>
        </div>
        <button onclick="closeShortlinkModal()" class="absolute top-2 left-2 text-gray-500 hover:text-gray-700">
            <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
</div>

<script>
    let currentProductId = null;
    let currentBrandId = null;

    function openEditModal(productId, brandId) {
        currentProductId = productId;
        currentBrandId = brandId;
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        currentProductId = null;
        currentBrandId = null;
    }

    // Event listeners for the buttons
    document.getElementById('editGameBtn').addEventListener('click', function() {
        if (currentProductId) {
            window.open(`https://escapezoom.ir/wp-admin/post.php?post=${currentProductId}&action=edit`, '_blank');
            closeEditModal();
        }
    });

    document.getElementById('editBrandBtn').addEventListener('click', function() {
        if (currentBrandId && currentBrandId !== '0') {
            window.open(`https://escapezoom.ir/wp-admin/term.php?taxonomy=yith_product_brand&tag_ID=${currentBrandId}&post_type=product`, '_blank');
            closeEditModal();
        } else {
            alert('برند برای این بازی تعریف نشده است');
        }
    });

    // Close modal when clicking outside
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

    // Shortlink Modal Functions
    function openShortlinkModal(shortlink) {
        const fullUrl = shortlink.indexOf('http') === 0 ? shortlink : 'https://' + shortlink;
        const linkElement = document.getElementById('shortlinkLink');
        linkElement.href = fullUrl;
        linkElement.textContent = shortlink;
        const modal = document.getElementById('shortlinkModal');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
    }

    function closeShortlinkModal() {
        const modal = document.getElementById('shortlinkModal');
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    // Copy shortlink functionality
    document.getElementById('copyShortlinkBtn').addEventListener('click', function() {
        const shortlink = document.getElementById('shortlinkLink').textContent;
        const fullUrl = shortlink.indexOf('http') === 0 ? shortlink : 'https://' + shortlink;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(fullUrl).then(function() {
                const btn = document.getElementById('copyShortlinkBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> کپی شد!';
                btn.classList.remove('bg-[#10B981]', 'hover:bg-[#059669]');
                btn.classList.add('bg-[#00a32a]');
                
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.classList.remove('bg-[#00a32a]');
                    btn.classList.add('bg-[#10B981]', 'hover:bg-[#059669]');
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                // Fallback
                const $temp = document.createElement('input');
                document.body.appendChild($temp);
                $temp.value = fullUrl;
                $temp.select();
                document.execCommand('copy');
                document.body.removeChild($temp);
            });
        } else {
            // Fallback for older browsers
            const $temp = document.createElement('input');
            document.body.appendChild($temp);
            $temp.value = fullUrl;
            $temp.select();
            document.execCommand('copy');
            document.body.removeChild($temp);
        }
    });

    // Close shortlink modal when clicking outside
    document.getElementById('shortlinkModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeShortlinkModal();
        }
    });
</script>