<?php
if ( ! function_exists( 'add_point' ) ) {
    /**
     * @param $type
     * @param int $user
     * @param string $description
     *
     * @return void
     */
    function add_point( $type, int $user = 0, string $description = '' ): void {
        global $wpdb;

        if ( $user == 0 ) {
            $user = get_current_user_id();
        }

        if ( $user == 3325 )
            return;

        $list = apply_filters( 'points_list', [
            'place-order-leader'   => [
                'point'  => 65,
                'action' => 'رزرو بازی',
            ],
            'place-order-teammate' => [
                'point'  => 25,
                'action' => 'امتیاز هم گروهی',
            ],
            'submit-comment'       => [
                'point'  => 60,
                'action' => 'ثبت نظر',
            ],
            'add-collection'       => [
                'point'  => 65,
                'action' => 'ایجاد کالکشن',
            ],
            'register'             => [
                'point'  => 5,
                'action' => 'ثبت نام',
            ],
            'complete-info'        => [
                'point'  => 15,
                'action' => 'تکمیل اطلاعات کاربری',
            ],
            'owner_satisfaction' => [
                'point'       => 10,
                'action'      => 'رضایت مجموعه دار',
                'description' => 'رضایت مجموعه دار',
            ],
            'collection_successful_sale' => [
                'point'       => 65,
                'action'      => 'فروش موفق از کالکشن',
                'description' => 'فروش موفق از کالکشن',
            ],
            'popular_badge_received' => [
                'point'       => 500,
                'action'      => 'دریافت بج محبوب',
                'description' => 'دریافت بج محبوب',
            ],
        ] );

        $wpdb->insert( 'points', [
            'user_id'     => $user,
            'point'       => $list[ $type ]['point'],
            'action'      => $list[ $type ]['action'],
            'description' => $description,
            'created_at'  => time(),
        ] );
    }
}