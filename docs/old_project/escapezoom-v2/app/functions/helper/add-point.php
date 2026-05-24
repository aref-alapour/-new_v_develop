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
                'point'  => 50,
                'action' => 'رزرو بازی',
            ],
            'place-order-teammate' => [
                'point'  => 30,
                'action' => 'امتیاز هم گروهی',
            ],
            'submit-comment'       => [
                'point'  => 30,
                'action' => 'ثبت نظر',
            ],
            'add-collection'       => [
                'point'  => 70,
                'action' => 'ایجاد کالکشن',
            ],
            'collection-liked'     => [
                'point'  => 20,
                'action' => 'لایک گرفتن کالکشن',
            ],
            'register'             => [
                'point'  => 20,
                'action' => 'ثبت نام',
            ],
            'complete-info'        => [
                'point'  => 30,
                'action' => 'تکمیل اطلاعات کاربری',
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