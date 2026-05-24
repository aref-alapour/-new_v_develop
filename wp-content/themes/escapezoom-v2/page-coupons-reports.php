<?php

//get_header();

if (!(current_user_can('administrator') || current_user_can('shopist') || current_user_can('accounting') || current_user_can('poshtiban'))) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}

global $wpdb;

$sql = "  
    SELECT   
        wp_posts.ID AS order_id,
        woi.order_item_name AS coupon_code,  
        wp_posts.post_date AS order_date,  
        pm_email.meta_value AS user_email,  
        CASE   
            WHEN wp_users.ID IS NULL THEN 'Guest'   
            ELSE wp_users.user_login   
        END AS username,  
        COALESCE(wp_users.ID, 0) AS user_id  
    FROM   
        {$wpdb->posts} AS wp_posts  
    LEFT JOIN   
        {$wpdb->prefix}woocommerce_order_items AS woi ON wp_posts.ID = woi.order_id  
    LEFT JOIN   
        {$wpdb->users} AS wp_users ON wp_posts.post_author = wp_users.ID  
    LEFT JOIN   
        {$wpdb->prefix}postmeta AS pm_email ON pm_email.post_id = wp_posts.ID AND pm_email.meta_key = '_billing_email'  
    WHERE   
        wp_posts.post_type = 'shop_order'   
        AND (  
            wp_posts.post_status IN (  
                'wc-pending',   
                'wc-processing',   
                'wc-on-hold',   
                'wc-completed',   
                'wc-cancelled',   
                'wc-refunded',   
                'wc-failed',   
                'wc-conflict',   
                'wc-walletx',   
                'wc-admin-cancelled',   
                'wc-held',   
                'wc-partially-paid'  
            )  
        )  
        AND woi.order_item_type = 'coupon';  
    ";

$results = $wpdb->get_results($sql, ARRAY_A);
ob_start();

if ($results) {
    echo "<style>  
            table {  
                width: 100%;  
                border-collapse: collapse;  
                font-family: Calibri;  
                text-align: center;  
            }  
            th, td {  
                border: 1px solid #000;  
                padding: 8px;  
            }  
            tr:nth-child(even) {  
                background-color: #ebebeb; /* Light gray for even rows */  
            }  
            tr:nth-child(odd) {  
                background-color: #ffffff; /* White for odd rows */  
            }  
          </style>";

    echo "<table>  
            <tr>  
                <th>Coupon Code</th>  
                <th>Order Date</th>  
                <th>User ID</th>  
                <th>Username</th>  
                <th>Player Name</th>  
                <th>Coupon Price</th>  
                <th>Product Name</th>  
                <th>Product ID</th>  
                <th>Quantity</th>  
            </tr>";

    foreach ($results as $row) {
        $user   = get_user_by('email', $row['user_email']);
        $order  = wc_get_order($row['order_id']);

        $user_id        = $user ? $user->ID : '';
        $phone          = $user ? $user->user_login : $order->get_billing_phone();
        $display_name   = $user ? $user->display_name : $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

        $order_items = $order->get_items();
        $product_id       = end($order_items)->get_product_id();
        $product_quantity = end($order_items)->get_quantity();

        echo "<tr>  
                <td>" . esc_html($row['coupon_code']) . "</td>  
                <td>" . esc_html(date('Y-m-d H:i:s', strtotime($row['order_date']))) . "</td>  
                <td>" . $user_id . "</td>  
                <td>" . esc_html($phone) . "</td>  
                <td>" . esc_html($display_name) . "</td>  
                <td>" . esc_html(get_coupon_discount_amount($row['coupon_code'])) . "</td>  
                <td>" . esc_html(get_the_title($product_id)) . "</td>  
                <td>" . esc_html($product_id) . "</td>  
                <td>" . esc_html($product_quantity) . "</td>  
              </tr>";
    }
    echo "</table>"; ?>

    <button id="exportBtn">Export as CSV</button>

    <script>
        // Function to export the table data to CSV
        function exportTableToCSV(filename) {
            const csv = [];
            const rows = document.querySelectorAll("table tr");

            for (let row of rows) {
                const cols = row.querySelectorAll("th, td");
                const csvRow = [];
                for (let col of cols) {
                    csvRow.push(col.innerText);
                }
                csv.push(csvRow.join(",")); // Join columns with a comma
            }

            // Create a Blob from the CSV string
            const csvString = csv.join("\n");
            const blob = new Blob([csvString], { type: "text/csv" });
            const url = URL.createObjectURL(blob);

            // Create a temporary link element
            const link = document.createElement("a");
            link.setAttribute("href", url);
            link.setAttribute("download", filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Event listener for the button
        document.getElementById("exportBtn").addEventListener("click", function () {
            exportTableToCSV("data.csv");
        });
    </script>

    <?php
} else {
    echo "<p>No coupon usage data found.</p>";
}

echo ob_get_clean();


function get_coupon_discount_amount($coupon_code) {
    $coupon = new WC_Coupon($coupon_code);

    if (!$coupon->get_id())
        return false;

    $discount_type = $coupon->get_discount_type();
    $discount_amount = $coupon->get_amount();

    if ($discount_type === 'fixed_cart' || $discount_type === 'fixed_product')
        return $discount_amount;
    elseif ($discount_type === 'percent')
        return $discount_amount;

    return 0;
} ?>


<?php //get_footer();