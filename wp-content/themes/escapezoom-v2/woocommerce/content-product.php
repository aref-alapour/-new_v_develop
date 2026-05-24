<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */
 
defined( 'ABSPATH' ) || exit;

global $product, $wpdb;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

$user_id = get_current_user_id();


    $current_category = get_queried_object();  
   echo  $category_id = $current_category->term_id; 


$product_type       = $type_term->name;
$product_type_equ   = get_product_type_equivalent($product_type);

$is_escaperoom = false;
if ( $product_type == 'اتاق فرار' )
    $is_escaperoom = true;

/*===============================================================*/
// ویدئو + متن

$data[] = [
    'type'  => 'video_text',
    'title' => '',
    'data'  => [
        'title' => 'اتاق فرار EscapeRoom',
        'text'  => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
        'video' => '<style>.r1_iframe_embed {position: relative; overflow: hidden; width: 100%; height: auto; padding-top: 56.25%; } .r1_iframe_embed iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }</style><div class="r1_iframe_embed"><iframe src="https://player.arvancloud.ir/index.html?config=https://ez.arvanvod.ir/2MP5ZV5a1r/0WMB6w3q2E/origin_config.json&skin=shaka" style="border:0 #ffffff none;" name="cirota2.mp4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe></div>',
    ]
];

/*===============================================================*/
// اتاق فرارهای ایران

if ( $is_escaperoom ) :

    $args = [
        'source'    => 'type_page_cat_' . $product_type_equ . '_-1',
        'params'    => $params,
    ];
    $products = json_decode( ez_webservice( array ('type' => 'sort_products_get', 'data' => $args) ) );

    $data[] = [
        'source'=> 'type_page_cat_' . $product_type_equ . '_-1',
        'type'  => 'products_slider',
        'title' => 'اتاق فرار های <b>ایران</b>',
        'icon'  => '',
        'url'   => '',
        'data'  => [
            'tabs'  => [
                'type'  => 'order',
                'title' => '',
                'key'   => 'sort_type',
                'items' => [
                    [
                        'title' => 'همه',
                        'id'    => -1,
                    ],
                    [
                        'title' => 'محبوب ترین',
                        'id'    => 'popular',
                    ],
                    [
                        'title' => 'پرفروش ترین',
                        'id'    => 'topsale',
                    ],
                    [
                        'title' => 'جدیدترین',
                        'id'    => 'recent',
                    ],
                ],
            ],
            'items' => $products,
        ]
    ];

endif;

/*===============================================================*/
// اتاق فرارهای تهران

if ( $is_escaperoom ) :

    $args = [
        'source'    => 'type_page_cat_' . $product_type_equ . '_15',
        'params'    => $params,
    ];
    $products = json_decode( ez_webservice( array ('type' => 'sort_products_get', 'data' => $args) ) );

    $data[] = [
        'source'=> 'type_page_cat_' . $product_type_equ . '_15',
        'type'  => 'products_slider',
        'title' => 'اتاق فرار های <b>ایران</b> و دارای سانس',
        'icon'  => '',
        'url'   => '',
        'data'  => [
            'tabs'  => [
                [
                    'type'  => 'city_id',
                    'title' => 'شهر مورد نظر',
                    'key'   => 'city_id',
                    'items' => [
                        [
                            'title' => 'تهران',
                            'value' => 15,
                        ],
                        [
                            'title' => 'کرج',
                            'value' => 162,
                        ],
                        [
                            'title' => 'اصفهان',
                            'value' => 122,
                        ],
                        [
                            'title' => 'مشهد',
                            'value' => 121,
                        ],
                        [
                            'title' => 'کرمانشاه',
                            'value' => 293,
                        ],
                        [
                            'title' => 'قزوین',
                            'value' => 270,
                        ],
                        [
                            'title' => 'کاشان',
                            'value' => 304,
                        ],
                    ],
                ],
                [
                    'type'  => 'tag',
                    'title' => 'سبک بازی',
                    'key'   => 'tag',
                    'items' => [
                        [
                            'title' => 'ترسناک',
                            'value' => 124,
                        ],
                        [
                            'title' => 'اکشن',
                            'value' => 346,
                        ],
                        [
                            'title' => 'درام',
                            'value' => 342,
                        ],
                        [
                            'title' => 'دلهره آور',
                            'value' => 126,
                        ],
                        [
                            'title' => 'غیرترسناک',
                            'value' => 125,
                        ],
                        [
                            'title' => 'هیجانی',
                            'value' => 178,
                        ],
                        [
                            'title' => 'جنایی',
                            'value' => 127,
                        ],
                    ],
                ],
                [
                    'type'  => 'order',
                    'title' => 'براساس',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => -1,
                        ],
                        [
                            'title' => 'محبوب ترین',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ترین',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدیدترین',
                            'id'    => 'recent',
                        ],
                    ],
                ],
            ],
            'items' => $products,
        ]
    ];

endif;

/*===============================================================*/
// سرگرمی های مختلف در شهرهای مختلف (سینماترس تهران، اتاق خشم تهران ....)

if ( !$is_escaperoom ) :

    $type_city_list = [
        'lasertag'  => [1149, 1158],
        'rageroom'  => [1186, 1074],
        'cinema'    => [913, 1009],
    ];

    foreach ( $type_city_list[$product_type_equ] as $type_city_item ) {

        $args = [
            'source'    => 'type_page_cat_' . $product_type_equ . '_' . $type_city_item,
            'params'    => $params,
        ];
        $products = json_decode( ez_webservice( array ('type' => 'sort_products_get', 'data' => $args) ) );

        $data[] = [
            'source'=> 'type_page_cat_' . $product_type_equ . '_' . $type_city_item,
            'type'  => 'products_slider',
            'title' => $product_type . ' های <b>' . get_term( $type_city_item )->name . '</b>' . 'و دارای سانس',
            'icon'  => '',
            'url'   => '',
            'data'  => [
                'tabs'  => [
                    'type'  => 'order',
                    'title' => '',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => -1,
                        ],
                        [
                            'title' => 'محبوب ترین',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ترین',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدیدترین',
                            'id'    => 'recent',
                        ],
                    ],
                ],
                'items' => $products,
            ]
        ];
    }

endif;

/*===============================================================*/
// تخفیف ویژه برای سرگرمی جاری (مشترک)

$args = [
    'source' => 'type_page_discounts_event_' . $product_type_equ,
];
$products = json_decode( ez_webservice( array ('type' => 'sort_products_get', 'data' => $args) ) );

$data[] = [
    'source'=> 'type_page_discounts_event_' .  $product_type_equ,
    'type'  => 'event',
    'title' => '<b>تخفیف های ویژه</b> و دارای سانس',
    'icon'  => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/Takhfif.svg',
    'url'   => '',
    'data'  => [
        'color' => '#eee',
        'items' => $products,
        'tabs'  => [
            'type'  => 'schedule',
            'title' => '',
            'key'   => 'schedule',
            'items' => [
                [
                    'title' => 'همه',
                    'min'   => -1,
                    'max'   => -1,
                ],
                [
                    'title' => 'فقط امروز',
                    'min'   => 'dynamic',
                    'max'   => 'dynamic',
                ],
                [
                    'title' => 'فقط فردا',
                    'min'   => 'dynamic',
                    'max'   => 'dynamic',
                ],
                [
                    'title' => 'فقط پس فردا',
                    'min'   => 'dynamic',
                    'max'   => 'dynamic',
                ],
            ],
        ],
    ]
];

/*===============================================================*/
// باکس شهرها برای سرگرمی های غیر اتاق فرار

if ( !$is_escaperoom ) :

    $data[] = [
        'type'  => 'genres',
        'title' => '',
        'icon'  => '',
        'data'  => [
            'items' => [
                [
                    'image'     => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/new/action.svg',
                    'title'     => 'اکشن',
                    'popular'   => true,
                    'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                ],
                [
                    'image'     => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/new/non-scary.svg',
                    'title'     => 'غیرترسناک',
                    'popular'   => false,
                    'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                ],
                [
                    'image'     => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/new/scary.svg',
                    'title'     => 'ترسناک',
                    'popular'   => true,
                    'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                ],
                [
                    'image'     => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/new/dram.svg',
                    'title'     => 'درام',
                    'popular'   => false,
                    'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                ],
                [
                    'image'     => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/new/exciting.svg',
                    'title'     => 'هیجانی',
                    'popular'   => false,
                    'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                ],
            ],
        ]
    ];

endif;

/*===============================================================*/
// اتاق فرارهای ترسناک

if ( $is_escaperoom ) :

    $args = [
        'source' => 'type_page_escaperoom_genre_horror',
    ];
    $products = json_decode( ez_webservice( array ('type' => 'sort_products_get', 'data' => $args) ) );

    $data[] = [
        'source'=> 'type_page_escaperoom_genre_horror',
        'type'  => 'products_slider',
        'title' => 'اتاق فرارهای ترسناک',
        'data'  => [
            'tabs'  => [
                'type'  => 'order',
                'title' => '',
                'key'   => 'sort_type',
                'items' => [
                    [
                        'title' => 'همه',
                        'id'    => -1,
                    ],
                    [
                        'title' => 'محبوب ها',
                        'id'    => 'popular',
                    ],
                    [
                        'title' => 'پرفروش ها',
                        'id'    => 'topsale',
                    ],
                    [
                        'title' => 'جدید ها',
                        'id'    => 'recent',
                    ],
                ],
            ],
            'items' => $products,
        ]
    ];

endif;

/*===============================================================*/
// اتاق فرارهای غیرترسناک

if ( $is_escaperoom ) :

    $args = [
        'source' => 'type_page_escaperoom_genre_nonhorror',
    ];
    $products = json_decode( ez_webservice( array ('type' => 'sort_products_get', 'data' => $args) ) );

    $data[] = [
        'source'=> 'type_page_escaperoom_genre_horror',
        'type'  => 'products_slider',
        'title' => 'اتاق فرارهای غیرترسناک و هیجانی',
        'data'  => [
            'tabs'  => [
                'type'  => 'order',
                'title' => '',
                'key'   => 'sort_type',
                'items' => [
                    [
                        'title' => 'همه',
                        'id'    => -1,
                    ],
                    [
                        'title' => 'محبوب ها',
                        'id'    => 'popular',
                    ],
                    [
                        'title' => 'پرفروش ها',
                        'id'    => 'topsale',
                    ],
                    [
                        'title' => 'جدید ها',
                        'id'    => 'recent',
                    ],
                ],
            ],
            'items' => $products,
        ]
    ];

endif;

/*===============================================================*/
// ژانرهای اتاق فرار

if ( $is_escaperoom ) :

    $data[] = [
        'type'  => 'genres',
        'title' => '',
        'icon'  => '',
        'data'  => [
            'items' => [
                [
                    'image'     => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/new/action.svg',
                    'title'     => 'اکشن',
                    'popular'   => true,
                    'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                ],
                [
                    'image'     => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/new/non-scary.svg',
                    'title'     => 'غیرترسناک',
                    'popular'   => false,
                    'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                ],
                [
                    'image'     => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/new/scary.svg',
                    'title'     => 'ترسناک',
                    'popular'   => true,
                    'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                ],
                [
                    'image'     => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/new/dram.svg',
                    'title'     => 'درام',
                    'popular'   => false,
                    'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                ],
                [
                    'image'     => 'https://escapezoom.ir/wp-content/themes/escapezoom-v1/img/new/exciting.svg',
                    'title'     => 'هیجانی',
                    'popular'   => false,
                    'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                ],
            ],
        ]
    ];

endif;

/*===============================================================*/
// زوم کلاب

$params = [
    'city_id'       => -1,
    'monopoly'      => 1,
    'product_type'  => $product_type,
];

$args = [
    'params'        => $params,
    'image_type'    => 'url',
    'limit'         => 20,
    'page'          => 1,
    'max_num_pages' => true,
    "format"        => 'api',
    'is_mobile'     => wp_is_mobile(),
    'sort_type'     => 'popular',
    'exclude_ads'   => false,
    'unpin_ads'     => true,
    'badge_ads'     => false,
    'show_more'     => 0,
    'random'        => true
];
$products = json_decode( ez_webservice( array ('type' => 'sort_products_get', 'data' => $args) ) );

$data[] = [
    'source'=> '',
    'type'  => 'products_slider',
    'title' => 'زوم کلاب',
    'data'  => [
        'tabs'  => [],
        'items' => $products,
    ]
];

/*===============================================================*/
// کالکشن ها

$items_per_page = 10;

$collections = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM collections WHERE active LIKE 1 ORDER BY likes_count DESC LIMIT {$items_per_page}" ) );
foreach ( $collections as $collection ) {

    $images = [];
    foreach ( unserialize($collection->items) as $product_id )
        $images[] = wp_get_attachment_url( get_post_thumbnail_id($product_id) );

    $collection_items[] =  [
        'title'         => $collection->title,
        'user_title'    => 'فاطمه خداپرست',
        'user_level'    => 2,
        'likes_count'   => (int)$collection->likes_count,
        'url'           => "/profile/" . (int)$collection->user_id,
        'count'         => count(unserialize($collection->items)),
        'items'         => $images,
    ];
}

$data[] = [
    'type'  => 'collections',
    'title' => 'کالکشن های محبوب کاربران',
    'icon'  => '',
    'url'   => '/collections/',
    'data'  => [
        'items' => $collection_items,
    ]
];

/*===============================================================*/
// FAQ

$data[] = [
    'type'  => 'faq',
    'title' => 'سوالات متداول',
    'icon'  => '',
    'url'   => '',
    'data'  => [
        'items' => [
            [
                'question'  => 'لیزرتگ ترسناک است؟',
                'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
            ],
            [
                'question'  => 'لیزرتگ چیست؟',
                'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
            ],
            [
                'question'  => 'آیا در لیزرتگ مثل پینت بال آسیب وجود دارد؟',
                'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
            ],
            [
                'question'  => 'مهارت محوری دوره چطوره انجام میشود؟',
                'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
            ],
            [
                'question'  => 'دسترسی به جزوات دانشگاهی چگونه است؟',
                'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
            ],
            [
                'question'  => 'دسترسی به جزوات دانشگاهی چگونه است؟',
                'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
            ],
        ],
    ]
];

/*===============================================================*/
// محتوای انتهای صفحه

$data[] = [
    'type'  => 'html',
    'title' => '',
    'icon'  => '',
    'url'   => '',
    'data'  => $type_term->description
];

saeed_print($data);



?>


