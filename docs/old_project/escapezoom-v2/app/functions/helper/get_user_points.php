<?php

if ( ! function_exists( 'get_user_points' ) ) {
    function get_user_points( $user_id, $format = false ): string | null {
        global $wpdb;

        $prepare = $wpdb->prepare( "SELECT SUM(point) as total FROM points WHERE user_id LIKE %d", (int) $user_id );
        $result  = $wpdb->get_results( $prepare )[0];

        if ( $format ) {
            return number_format( $result->total );
        }

        return $result->total;
    }
}