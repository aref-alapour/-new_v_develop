<?php
global $wpdb;

// Get Current User
$user = wp_get_current_user();

// Get Collection ID
$collection_ID = sanitize_text_field( $_POST["collection"] );

// Limit Collections
$limit = 1;

$query       = "SELECT * FROM collections WHERE user_id LIKE $user->ID AND ID LIKE $collection_ID";
$collections = $wpdb->get_results( $query );
$collection  = $collections[0];

$items = unserialize( $collection->items ) ?: [];
?>

<div class="grid grid-cols-2 lg:grid-cols-12 mb-5 max-lg:mt-5">
    
    <div class="col-span-2 lg:col-span-6 flex max-lg:justify-between lg:gap-x-8 items-center">
        
        <h4 class="line-clamp-1"> مجموع بازی های <?php echo esc_html( $collection->title ); ?></h4>
        
        <button type="button" class="edit-collection">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
                <rect width="28" height="28" rx="6" fill="#E4EBF0"/>
                <path d="M10.25 10.25H9.5C9.10218 10.25 8.72064 10.408 8.43934 10.6893C8.15804 10.9706 8 11.3522 8 11.75V18.5C8 18.8978 8.15804 19.2794 8.43934 19.5607C8.72064 19.842 9.10218 20 9.5 20H16.25C16.6478 20 17.0294 19.842 17.3107 19.5607C17.592 19.2794 17.75 18.8978 17.75 18.5V17.75" stroke="#889BAD" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M17 8.75008L19.25 11.0001M20.2887 9.93883C20.5841 9.64345 20.7501 9.24282 20.7501 8.82508C20.7501 8.40734 20.5841 8.00672 20.2887 7.71133C19.9934 7.41595 19.5927 7.25 19.175 7.25C18.7573 7.25 18.3566 7.41595 18.0613 7.71133L11.75 14.0001V16.2501H14L20.2887 9.93883Z" stroke="#889BAD" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        
        <div class="edit-collection-modal z-50 hidden">
            <div class="fixed bg-black/40 z-40 w-full h-full right-0 top-0"></div>
            <div class="fixed bg-white w-[430px] max-w-[100%] h-auto right-[50%] translate-x-[50%] top-[50%] -translate-y-[50%] p-6 rounded-xl border z-50 flex flex-col gap-4">
                <input value="<?php echo esc_attr( $collection->title ) ?>" id="edit-collection-name" class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl" placeholder="اسم کالکشن را وارد کنید" type="text">
                <button disabled type="button" id="edit-collection-submit" class="bg-primaryColor p-4 text-white rounded-2xl shadow-primary-3 shadow-13 disabled:opacity-50">
                    ویرایش نام کالکشن
                </button>
            </div>
        </div>
        
    </div>
    
    <div class="lg:col-span-2 max-lg:order-2 flex items-center gap-x-2 max-lg:justify-end">
        <button type="button" id="show-on-profile" role="switch" aria-checked="false" data-state="<?php echo $collection->active && count($items) > 0 ? "checked" : "unchecked"; ?>" value="on" class="focus-visible:ring-ring p- peer inline-flex shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent shadow-none transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary-500 data-[state=unchecked]:bg-slate-120 h-5 w-9">
            <span data-state="<?php echo $collection->active && count($items) > 0 ? "checked" : "unchecked"; ?>" class="pointer-events-none block rounded-full bg-background shadow-lg ring-0 transition-transform h-4 w-4 data-[state=checked]:-translate-x-4 data-[state=unchecked]:translate-x-0"></span>
        </button>
        <label for="show-on-profile">نمایش در پروفایل</label>
    </div>
    
    <div class="col-span-2 lg:col-span-4 max-lg:order-3">
        <div class="flex items-center gap-x-4 mb-4 lg:mb-0 rounded-[10px] border border-[#E8EDF1] px-6 py-3 shadow-13 relative">
            <span>
                <svg class="mx-0 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
                    <rect y="0.5" width="18" height="18" rx="4" fill="#FD7013"></rect>
                    <g filter="url(#filter0_d_9708_739)">
                        <path d="M4.62609 9.5C4.62619 9.33739 4.69083 9.18146 4.80582 9.06648C4.9208 8.95149 5.07673 8.88685 5.23934 8.88675L8.38603 8.88617L8.38661 5.73948C8.38661 5.65894 8.40247 5.5792 8.43329 5.5048C8.46411 5.43039 8.50928 5.36279 8.56623 5.30584C8.62317 5.2489 8.69078 5.20372 8.76518 5.1729C8.83959 5.14209 8.91933 5.12622 8.99987 5.12622C9.0804 5.12622 9.16014 5.14209 9.23455 5.1729C9.30895 5.20372 9.37656 5.2489 9.4335 5.30584C9.49045 5.36279 9.53562 5.43039 9.56644 5.5048C9.59726 5.5792 9.61312 5.65894 9.61312 5.73948L9.6137 8.88617L12.7604 8.88675C12.923 8.88675 13.079 8.95136 13.194 9.06636C13.309 9.18137 13.3736 9.33735 13.3736 9.5C13.3736 9.66265 13.309 9.81863 13.194 9.93364C13.079 10.0486 12.923 10.1133 12.7604 10.1133L9.6137 10.1138L9.61312 13.2605C9.61312 13.4232 9.54851 13.5792 9.4335 13.6942C9.31849 13.8092 9.16251 13.8738 8.99987 13.8738C8.83722 13.8738 8.68124 13.8092 8.56623 13.6942C8.45122 13.5792 8.38661 13.4232 8.38661 13.2605L8.38603 10.1138L5.23934 10.1133C5.07673 10.1132 4.9208 10.0485 4.80582 9.93352C4.69083 9.81854 4.62619 9.66261 4.62609 9.5Z" fill="white"></path>
                        <path d="M4.62609 9.5C4.62619 9.33739 4.69083 9.18146 4.80582 9.06648C4.9208 8.95149 5.07673 8.88685 5.23934 8.88675L8.38603 8.88617L8.38661 5.73948C8.38661 5.65894 8.40247 5.5792 8.43329 5.5048C8.46411 5.43039 8.50928 5.36279 8.56623 5.30584C8.62317 5.2489 8.69078 5.20372 8.76518 5.1729C8.83959 5.14209 8.91933 5.12622 8.99987 5.12622C9.0804 5.12622 9.16014 5.14209 9.23455 5.1729C9.30895 5.20372 9.37656 5.2489 9.4335 5.30584C9.49045 5.36279 9.53562 5.43039 9.56644 5.5048C9.59726 5.5792 9.61312 5.65894 9.61312 5.73948L9.6137 8.88617L12.7604 8.88675C12.923 8.88675 13.079 8.95136 13.194 9.06636C13.309 9.18137 13.3736 9.33735 13.3736 9.5C13.3736 9.66265 13.309 9.81863 13.194 9.93364C13.079 10.0486 12.923 10.1133 12.7604 10.1133L9.6137 10.1138L9.61312 13.2605C9.61312 13.4232 9.54851 13.5792 9.4335 13.6942C9.31849 13.8092 9.16251 13.8738 8.99987 13.8738C8.83722 13.8738 8.68124 13.8092 8.56623 13.6942C8.45122 13.5792 8.38661 13.4232 8.38661 13.2605L8.38603 10.1138L5.23934 10.1133C5.07673 10.1132 4.9208 10.0485 4.80582 9.93352C4.69083 9.81854 4.62619 9.66261 4.62609 9.5Z" stroke="white"></path>
                    </g>
                    <defs>
                        <filter id="filter0_d_9708_739" x="3.12598" y="4.62598" width="11.748" height="11.748" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"></feColorMatrix>
                            <feOffset dy="1"></feOffset>
                            <feGaussianBlur stdDeviation="0.5"></feGaussianBlur>
                            <feComposite in2="hardAlpha" operator="out"></feComposite>
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"></feColorMatrix>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_9708_739"></feBlend>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_9708_739" result="shape"></feBlend>
                        </filter>
                    </defs>
                </svg>
            </span>

            <input placeholder="افزودن بازی جدید به لیست" class="w-full focus:outline-0" type="text" id="search-field">

            <div id="search-result" class="items-center gap-x-4 rounded-[10px] border border-[#E8EDF1] bg-white px-6 py-3 shadow-13 absolute top-[calc(100%+10px)] right-0 w-full hidden"></div>
        </div>
    </div>
    
    <div class="lg:col-span-4 max-lg:order-1 flex items-center">
        <span class="text-secondary-600">
            <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none">
              <path d="M14.1823 2.06792C14.9948 2.88031 15.4658 3.97273 15.4987 5.12123C15.5316 6.26974 15.1239 7.38733 14.3593 8.24492L7.99927 14.6139L1.64077 8.24492C0.875208 7.38689 0.467169 6.26834 0.500419 5.11891C0.533668 3.96948 1.00568 2.87638 1.81956 2.06404C2.63344 1.2517 3.72742 0.781748 4.87691 0.750669C6.02641 0.71959 7.14418 1.12974 8.00077 1.89692C8.85785 1.13016 9.97597 0.720638 11.1255 0.752439C12.2751 0.78424 13.3689 1.25495 14.1823 2.06792ZM2.87902 3.12917C2.34221 3.66595 2.02886 4.38628 2.00214 5.14494C1.97542 5.90361 2.23731 6.6442 2.73502 7.21742L8.00002 12.4907L13.265 7.21817C13.7628 6.64474 14.0246 5.90389 13.9976 5.14505C13.9706 4.3862 13.6569 3.66581 13.1197 3.12917C12.5825 2.59253 11.8617 2.27957 11.1029 2.2534C10.344 2.22723 9.60342 2.48981 9.03052 2.98817L5.87902 6.14042L4.81777 5.07992L6.93652 2.95967L6.87502 2.90792C6.29847 2.44654 5.57203 2.21401 4.83473 2.25483C4.09744 2.29566 3.4011 2.60697 2.87902 3.12917Z" fill="#F21543"></path>
            </svg>
            </span>
        <span class="w-full">
            <span class="ml-2 mr-3 text-lg"><?php echo esc_html( $collection->users ? count( unserialize( $collection->users ) ) : 0 ) ?></span>
            نفر پسندیدند
        </span>
    </div>
    
</div>

<?php if ( count( $items ) > 0 ) { ?>
    <div id="collection" class="collection grid border-slate-120 lg:grid-cols-2 gap-7 lg:pb-8 2xl:grid-cols-3 2xl:gap-5 3xl:gap-10">
        <?php foreach ( $items as $item ) {
            $terms = [];
            foreach ( get_the_terms( $item, 'product_cat' ) as $term ) {
                $terms[] = $term->name;
            } ?>
            <div class="col-span-1 lg:border lg:rounded-xl border-b max-lg:flex max-lg:justify-between">
                <div class="p-4 flex items-center gap-4 max-lg:py-">
                    <?php echo get_the_post_thumbnail( $item, 'large', [
                        'class' => 'max-lg:hidden w-[62px] rounded-xl',
                        'style' => 'height:70px',
                    ] ) ?>

                    <?php echo get_the_post_thumbnail( $item, 'large', [
                        'class' => 'lg:hidden w-[30px] rounded-xl',
                    ] ) ?>
                    <div>
                        <h3 class="text-2xl font-bold max-lg:text-lg"><?php echo get_the_title( $item ); ?></h3>
                        <div class="max-lg:hidden">
                            <?php echo get_field( "room_loc", $item ) . ' . ' . implode( '<b class="mx-3">.</b>', $terms ); ?>
                        </div>
                    </div>
                </div>
                <div class="flex lg:border-t">
                    <a href="#" data-id="<?php echo $item ?>" data-action="delete-from-collection" class="grow flex justify-center items-center gap-4 p-2 border-l max-lg:border-none">
                        حذف از لیست
                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none" class="mx-0">
                            <rect x="0.192383" width="18" height="18" rx="4" fill="#232323"/>
                            <path d="M6.09954 5.90716C6.2146 5.79224 6.37056 5.7277 6.53318 5.7277C6.69579 5.7277 6.85176 5.79224 6.96681 5.90716L9.19227 8.13179L11.4177 5.90716C11.4747 5.85021 11.5423 5.80504 11.6167 5.77422C11.6911 5.7434 11.7708 5.72754 11.8514 5.72754C11.9319 5.72754 12.0116 5.7434 12.086 5.77422C12.1604 5.80504 12.228 5.85021 12.285 5.90716C12.3419 5.9641 12.3871 6.03171 12.4179 6.10611C12.4488 6.18051 12.4646 6.26026 12.4646 6.34079C12.4646 6.42133 12.4488 6.50107 12.4179 6.57548C12.3871 6.64988 12.3419 6.71748 12.285 6.77443L10.0604 8.99988L12.285 11.2253C12.4 11.3403 12.4646 11.4963 12.4646 11.659C12.4646 11.8216 12.4 11.9776 12.285 12.0926C12.17 12.2076 12.014 12.2722 11.8514 12.2722C11.6887 12.2722 11.5327 12.2076 11.4177 12.0926L9.19227 9.86798L6.96681 12.0926C6.85181 12.2076 6.69582 12.2722 6.53318 12.2722C6.37053 12.2722 6.21455 12.2076 6.09954 12.0926C5.98453 11.9776 5.91992 11.8216 5.91992 11.659C5.91992 11.4963 5.98453 11.3403 6.09954 11.2253L8.32418 8.99988L6.09954 6.77443C5.98463 6.65937 5.92008 6.50341 5.92008 6.34079C5.92008 6.17818 5.98463 6.02221 6.09954 5.90716Z" fill="white"/>
                        </svg>
                    </a>
                    <a href="<?php echo get_the_permalink( $item ); ?>" class="grow flex max-lg:hidden justify-center items-center gap-4 p-2">
                        مشاهده بازی
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                            <rect width="18" height="18" rx="4" fill="#FD7013"/>
                            <path d="M9.00042 10.7098C9.94503 10.7098 10.7108 9.94406 10.7108 8.99944C10.7108 8.05483 9.94503 7.28906 9.00042 7.28906C8.0558 7.28906 7.29004 8.05483 7.29004 8.99944C7.29004 9.94406 8.0558 10.7098 9.00042 10.7098Z" stroke="white"/>
                            <path d="M13.6682 8.39211C13.8894 8.66121 14 8.79519 14 8.99986C14 9.20454 13.8894 9.33852 13.6682 9.60762C12.8586 10.5905 11.073 12.4206 9.00001 12.4206C6.92703 12.4206 5.14139 10.5905 4.33181 9.60762C4.1106 9.33852 4 9.20454 4 8.99986C4 8.79519 4.1106 8.66121 4.33181 8.39211C5.14139 7.40921 6.92703 5.5791 9.00001 5.5791C11.073 5.5791 12.8586 7.40921 13.6682 8.39211Z" stroke="white"/>
                        </svg>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } else {

    $wpdb->update(
        'collections',
        array(
            'active' => '0',
        ),
        array(
            'ID' => $collection_ID,
        )
    );

?>
    <div class="h-[300px] text-lg flex justify-center items-center text-slate-350">
        شما تاکنون بازی به کالکشن اضافه نکرده اید!
    </div>
<?php } ?>

<script>
    jQuery(document).ready(function ($) {
        /**
         * Initialize Toast
         */
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-start',
            showConfirmButton: false,
            timer: 3000,
        })

        /**
         * Toggle Collection Activation
         */
        $("#show-on-profile").on('click', function () {
            let _ = $(this)

            function ToggleState() {
                _.attr('data-state', (_, attr) => {
                    if (attr === 'checked') {
                        return 'unchecked'
                    } else {
                        return 'checked'
                    }
                })

                _.find('span').attr('data-state', (_, attr) => {
                    if (attr === 'checked') {
                        return 'unchecked'
                    } else {
                        return 'checked'
                    }
                })
            }

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                    'callback': 'panel_collection_toggle',
                    'collection': '<?php echo $collection_ID;?>',
                    'active': _.data('state')
                },
                beforeSend: function () {
                    _.attr('disabled', true).css('opacity', '.5')
                },
                success: function (response) {


                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.data
                    })

                    _.attr('disabled', false).css('opacity', '1')

                    if (response.success) {
                        ToggleState()
                    }
                },
            })
        })

        /**
         * Search Field
         */
        $("#search-field").keyup(function () {
            let value = $(this).val()
            if (value !== '') {
                $("#search-result").addClass('flex').removeClass('hidden')
                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url( 'web-service/queryable.php' ) ?>",
                    data: {
                        "source": "collection",
                        "term": value,
                        "type": "<?php echo get_product_type_equivalent( $collection->type );?>"
                    },
                    beforeSend: function () {
                        $("#search-result").text('در حال جست و جو')
                    },
                    success: function (res) {
                        if (res !== "null") {
                            let response = JSON.parse(res)

                            let out = '<div class="flex w-full flex-col max-h-54 overflow-y-auto no-scrollbar">'
                            response.forEach(item => {
                                let title = item.title.replaceAll(value, `<mark class="bg-primaryColor text-white">${value}</mark>`)

                                out += `<div class="flex cursor-pointer gap-2 items-center border-b py-4">
<button type="button" class="text-green-500 font-bold text-2xl" data-add-to-collection="${item.product_id}">+</button>
<div class="grow">${title}</div>
<img src="${item.image}" alt="${item.title}" style="width: 20px; height: 24px; border-radius: 4px;">
</div>`
                            })
                            out += '</div>'

                            $("#search-result").html(out)

                            $("[data-add-to-collection]").parent().on('click', function () {
                                let button = $(this).find('[data-add-to-collection]')
                                let id = button.data('add-to-collection')

                                $.ajax({
                                    type: 'POST',
                                    url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                                    data: {
                                        'action': 'v2_ajax_handler',
                                        'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                                        'callback': 'panel_collection_product_add',
                                        'collection': '<?php echo $collection_ID;?>',
                                        'item': id
                                    },
                                    beforeSend: function () {
                                        button.html('<div class="spinner" style="border-color: #FD7013; border-width: 2px; width: 11px; margin-inline: auto"></div>')
                                    },
                                    success: function (data) {
                                        Toast.fire({
                                            icon: data.success ? 'success' : 'error',
                                            title: data.data
                                        })

                                        if (data.success) {
                                            $.ajax({
                                                type: 'POST',
                                                url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                                                data: {
                                                    'action': 'v2_ajax_handler',
                                                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                                                    'callback': 'panel_collection_get',
                                                    'collection': '<?php echo $collection_ID;?>'
                                                },
                                                beforeSend: function () {
                                                    $("#collections-list").html(function () {
                                                        let out = '<div class="w-full h-12 rounded-xl skeleton lg:mb-5"></div>'
                                                        out += '<div class="collection grid border-slate-120 lg:grid-cols-2 gap-7 lg:pb-8 2xl:grid-cols-3 2xl:gap-5 3xl:gap-10">'
                                                        for (let i = 0; i < 6; i++) {
                                                            out += '<div class="w-full h-44 rounded-xl skeleton"></div>'
                                                        }
                                                        out += '</div>'
                                                        return out
                                                    })
                                                },
                                                success: function (data) {
                                                    $("#collections-list").html(data)
                                                },
                                            })
                                        }
                                    }
                                })
                            })
                        } else {
                            $("#search-result").text('چیزی پیدا نشد')
                        }
                    },
                })
            } else {
                $("#search-result").addClass('hidden').removeClass('flex')
            }
        })

        /**
         * Delete from collection
         */
        $("[data-action='delete-from-collection']").on('click', function (e) {
            e.preventDefault()

            let _ = $(this)
            let id = _.data('id')

            Swal.mixin({
                iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="95" height="97" viewBox="0 0 95 97" fill="none" class="-mr-2.5">
<g filter="url(#filter0_d_25138_8856)">
<mask id="path-1-inside-1_25138_8856" fill="white">
<path d="M71 31.5C71 48.897 56.897 63 39.5 63C22.103 63 8 48.897 8 31.5C8 14.103 22.103 0 39.5 0C56.897 0 71 14.103 71 31.5Z"/>
</mask>
<path d="M71 31.5C71 48.897 56.897 63 39.5 63C22.103 63 8 48.897 8 31.5C8 14.103 22.103 0 39.5 0C56.897 0 71 14.103 71 31.5Z" fill="white"/>
<path d="M70.5 31.5C70.5 48.6208 56.6208 62.5 39.5 62.5V63.5C57.1731 63.5 71.5 49.1731 71.5 31.5H70.5ZM39.5 62.5C22.3792 62.5 8.5 48.6208 8.5 31.5H7.5C7.5 49.1731 21.8269 63.5 39.5 63.5V62.5ZM8.5 31.5C8.5 14.3792 22.3792 0.5 39.5 0.5V-0.5C21.8269 -0.5 7.5 13.8269 7.5 31.5H8.5ZM39.5 0.5C56.6208 0.5 70.5 14.3792 70.5 31.5H71.5C71.5 13.8269 57.1731 -0.5 39.5 -0.5V0.5Z" fill="#EFC101" mask="url(#path-1-inside-1_25138_8856)"/>
</g>
<g filter="url(#filter1_i_25138_8856)">
<rect x="36" y="42" width="8" height="8" rx="4" fill="url(#paint0_linear_25138_8856)"/>
</g>
<g filter="url(#filter2_i_25138_8856)">
<rect x="36" y="11" width="8" height="27" rx="4" fill="url(#paint1_linear_25138_8856)"/>
</g>
<defs>
<filter id="filter0_d_25138_8856" x="0" y="0" width="95" height="97" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
<feFlood flood-opacity="0" result="BackgroundImageFix"/>
<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
<feOffset dx="8" dy="18"/>
<feGaussianBlur stdDeviation="8"/>
<feComposite in2="hardAlpha" operator="out"/>
<feColorMatrix type="matrix" values="0 0 0 0 0.306354 0 0 0 0 0.36728 0 0 0 0 0.425 0 0 0 0.08 0"/>
<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_25138_8856"/>
<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_25138_8856" result="shape"/>
</filter>
<filter id="filter1_i_25138_8856" x="35" y="39" width="9" height="11" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
<feFlood flood-opacity="0" result="BackgroundImageFix"/>
<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
<feOffset dx="-1" dy="-5"/>
<feGaussianBlur stdDeviation="1.5"/>
<feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
<feColorMatrix type="matrix" values="0 0 0 0 1 0 0 0 0 0.881618 0 0 0 0 0.3875 0 0 0 1 0"/>
<feBlend mode="normal" in2="shape" result="effect1_innerShadow_25138_8856"/>
</filter>
<filter id="filter2_i_25138_8856" x="35" y="9" width="9" height="29" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
<feFlood flood-opacity="0" result="BackgroundImageFix"/>
<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
<feOffset dx="-1" dy="-2"/>
<feGaussianBlur stdDeviation="1.5"/>
<feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
<feColorMatrix type="matrix" values="0 0 0 0 1 0 0 0 0 0.881618 0 0 0 0 0.3875 0 0 0 1 0"/>
<feBlend mode="normal" in2="shape" result="effect1_innerShadow_25138_8856"/>
</filter>
<linearGradient id="paint0_linear_25138_8856" x1="50.3447" y1="41.8144" x2="41.454" y2="41.6016" gradientUnits="userSpaceOnUse">
<stop stop-color="#E4B903"/>
<stop offset="1" stop-color="#EFC101"/>
</linearGradient>
<linearGradient id="paint1_linear_25138_8856" x1="50.3447" y1="10.3737" x2="41.4494" y2="10.3106" gradientUnits="userSpaceOnUse">
<stop stop-color="#E4B903"/>
<stop offset="1" stop-color="#EFC101"/>
</linearGradient>
</defs>
</svg>`,
                width: 240,
                customClass: {
                    icon: 'border-0',
                    title: 'text-lg leading-6 pt-0',
                    actions: 'w-full px-4',
                    popup: 'rounded-xl',
                    confirmButton: 'bg-primaryColor p-2 text-white rounded-xl shadow-primary-3 shadow-12 ml-3 leading-5',
                    cancelButton: 'bg-slate-200 text-white p-3 rounded-xl shadow-13 leading-5'
                },
                buttonsStyling: false
            }).fire({
                icon: 'info',
                title: 'آیا از حذف این بازی از این کالکشن مطمئن هستید؟',
                showCancelButton: true,
                confirmButtonText: "حذف شود",
                cancelButtonText: "منصرف شدم",
            }).then(result => {
                if (result.isConfirmed) {

                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                        data: {
                            'action': 'v2_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                            'callback': 'panel_collection_product_remove',
                            'collection': '<?php echo $collection_ID;?>',
                            'item': id
                        },
                        beforeSend: function () {
                            _.html('<div class="spinner" style="border-color: #000000; border-width: 2px; width: 20px; margin-inline: auto"></div>')
                        },
                        success: function (data) {
                            Toast.fire({
                                icon: data.success ? 'success' : 'error',
                                title: data.data
                            })

                            if (data.success) {
                                $.ajax({
                                    type: 'POST',
                                    url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                                    data: {
                                        'action': 'v2_ajax_handler',
                                        'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                                        'callback': 'panel_collection_get',
                                        'collection': '<?php echo $collection_ID;?>'
                                    },
                                    beforeSend: function () {
                                        $("#collections-list").html(function () {
                                            let out = '<div class="w-full h-12 rounded-xl skeleton lg:mb-5"></div>'
                                            out += '<div class="collection grid border-slate-120 lg:grid-cols-2 gap-7 lg:pb-8 2xl:grid-cols-3 2xl:gap-5 3xl:gap-10">'
                                            for (let i = 0; i < 6; i++) {
                                                out += '<div class="w-full h-44 rounded-xl skeleton"></div>'
                                            }
                                            out += '</div>'
                                            return out
                                        })
                                    },
                                    success: function (data) {
                                        $("#collections-list").html(data)
                                    },
                                })
                            }

                        }
                    })
                }
            })
        })

        $(".edit-collection").on('click', function () {
            let modal = $(".edit-collection-modal")

            modal.removeClass('hidden')
            modal.find('>div:first-child').on('click', function () {
                modal.addClass('hidden')
            })
        })

        $("#edit-collection-name").on('input change', function () {
            let value = "<?php echo esc_attr( $collection->title );?>"

            if ($(this).val().trim().length < 3) {
                $("#edit-collection-submit").attr('disabled', 'disabled')
            } else {
                if ($(this).val().trim() !== value) {
                    $("#edit-collection-submit").removeAttr('disabled')
                } else {
                    $("#edit-collection-submit").attr('disabled', 'disabled')
                }
            }
        })

        $("#edit-collection-submit").on('click', function () {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                    'callback': 'panel_collection_edit_name',
                    'collection': <?php echo esc_attr( $collection->ID ) ?>,
                    'title': $("#edit-collection-name").val()
                },
                beforeSend: function () {
                    $("#edit-collection-submit").attr('disabled', 'disabled')
                        .html('<div class="spinner" style="width: 33px;margin-inline: auto;border-width: 5px;border-color: #FFFF;"></div>')
                },
                success: function (response) {


                    $("#edit-collection-submit").html('ویرایش نام کالکشن')

                    Toast.fire({
                        icon: response.success ? "success" : "error",
                        title: response.data
                    })

                    if (response.success) {
                        setTimeout(() => window.location.reload(), 3000)
                    } else {
                        $("#edit-collection-submit").removeAttr('disabled')
                    }
                },
            })
        })
    })
</script>