<?php
require $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/escapezoom-v2/inc/medoo/Medoo.php';

use Medoo\Medoo;

function medoo() {
    static $medoo = null;

    if ($medoo === null)
        $medoo = new Medoo([
            'type'      => 'mysql',
            'host'      => 'localhost',
            'database'  => 'escapezo_ez9920',
            'username'  => 'escapezo_ez9920',
            'password'  => '+)BxI4K)9bc!WUn#',
            'charset'   => 'utf8mb4',   // اضافه کردن charset
            'option'    => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ],
        ]);

    return $medoo;
}
function medoo_queries()
{
    static $medoo_queries = null;

    if ($medoo_queries === null)
        $medoo_queries = new Medoo([
            'type'      => 'mysql',
            'host'      => 'localhost',
            'database'  => 'escapezo_queries',
            'username'  => 'escapezo_escapezoom',
            'password'  => '}lg#0#SaA}0%$zQn',
            'charset'   => 'utf8mb4',
            'option'    => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ],
        ]);

    return $medoo_queries;
}