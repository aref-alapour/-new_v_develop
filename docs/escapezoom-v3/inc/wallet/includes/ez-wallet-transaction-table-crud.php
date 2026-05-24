<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class EZ_Transaction_CRUD
{
    private $table = EZ_TRANSACTION_TABLE;

    public function insert($new_transaction) {
        global $wpdb;
        $new_transaction['created_at'] = time();
        $res = $wpdb->insert( $this->table, $new_transaction);
        return $res;
    }

    public function update($update_transaction, $transaction_id){
        global $wpdb;
        $wpdb->update( $this->table, $update_transaction, array('ID' => $transaction_id ) );
    }

    public function delete($user_id) {
        global $wpdb;

        return $wpdb->delete(
            $this->table,
            array( 'user_id' => (int) $user_id ),
            array( '%d' )
        );
    }

    public function get2($transaction, $count, $single = false){
        global $wpdb;

        $sql = "SELECT * FROM `$this->table` WHERE ";

        if ( $transaction['user_id'] == -1 )
            $sql .= "1 LIKE 1 AND ";
        else
            foreach ($transaction as $key => $value)
                $sql .= '`' . $key . "` LIKE '" . $value . "' AND ";

        $sql = rtrim($sql, ' AND');
        $sql .= " ORDER BY ID DESC";
        $sql .= " LIMIT " . ($count === -1 ? 10000 : $count);

        $res = $wpdb->get_results( $sql );

        if ( count( $res ) == 1 && $single )
            return $res[0];

        return $res;
    }

    public function get($transaction, $count, $single = false, $page_number = 1) {
        global $wpdb;

        $page_number    = max(1, intval($page_number));
        $count          = max(1, intval($count));

        $offset = ($page_number - 1) * $count;

        $sql = "SELECT * FROM `$this->table` WHERE ";

        if ($transaction['user_id'] == -1)
            $sql .= "1 LIKE 1 AND ";

        else
            foreach ($transaction as $key => $value)
                $sql .= '`' . esc_sql($key) . "` LIKE '" . esc_sql($value) . "' AND ";

        $sql = rtrim($sql, ' AND');
        $sql .= " ORDER BY ID DESC";
        $sql .= " LIMIT $offset, $count";

        $res = $wpdb->get_results( $sql );

        if (count($res) == 1 && $single)
            return $res[0];

        return $res;
    }

    public function get_balance($user_id) {
        $transaction = array (
            'user_id' => $user_id,
        );

        $balance = $this->get($transaction, 1, true);

        // if it's the user's first transaction so balance is 0
        if ( empty( $balance ) )
            return 0;

        return (int)$balance->balance;
    }
}


