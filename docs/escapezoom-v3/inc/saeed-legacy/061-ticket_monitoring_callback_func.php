<?php
/**
 * ticket_monitoring_callback_func
 *
 * توابع: ticket_monitoring_callback_func
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5455-5515)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ticket_monitoring_callback_func () { ?>

    <div id="tav_wallet_admin_main_wrapper">
        <div id="tav_wallet_admin_main_transaction_table_wrapper">

            <?php
            $departments = [
                "financial" => "مالی",
                "technical" => "فنی",
                "support"   => "پشتیبانی",
            ];

            foreach ( $departments as $key => $department )
                $rates[$key]['rate'] = [];

            $query = new WP_Query(array(
                'post_type'         => 'ticketing',
                'posts_per_page'    => -1
            ));

            if ( $query->have_posts() ) :
                while ($query->have_posts()) : $query->the_post();

                    $ticket_type = get_the_content();
                    $ticket_type = array_search ($ticket_type, $departments);
                    $ticket_rate = get_post_meta(get_the_ID(), 'ticket_rate', true);

                    $rates[$ticket_type]['rate'][] = (int)$ticket_rate;

                endwhile;
                wp_reset_query();  ?>

                <table id="tav_wallet_table">
                    <tr>
                        <th>دپارتمان</th>
                        <th>میانگین امتیاز</th>
                    </tr>

                    <?php
                    foreach ( $rates as $key => $rate ) : ?>

                        <tr>
                            <td><?php echo $departments[$key]; ?></td>
                            <td><?php
                                $rate_values = isset( $rate['rate'] ) && is_array( $rate['rate'] ) ? $rate['rate'] : array();
                                $rate_count  = count( $rate_values );
                                echo $rate_count > 0 ? array_sum( $rate_values ) / $rate_count : '-';
                            ?></td>
                        </tr>

                    <?php
                    endforeach; ?>
                </table>

            <?php
            else: ?>
                <p id="tav_wallet_no_transactions"><?php echo 'تیکتی وجود ندارد!' ?></p>
            <?php
            endif; ?>

        </div>
    </div>

    <?php
}
