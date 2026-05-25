<?php
$servername = getenv('WORDPRESS_DB_EXT_HOST') ?: ( getenv('WORDPRESS_DB_HOST') ?: 'mysql' );
$dbname     = getenv('WORDPRESS_DB_EXT_NAME') ?: 'escapezo_queries';
$username   = getenv('WORDPRESS_DB_EXT_USER') ?: ( getenv('WORDPRESS_DB_USER') ?: 'root' );
$password   = getenv('WORDPRESS_DB_EXT_PASSWORD') ?: ( getenv('WORDPRESS_DB_PASSWORD') ?: 'arefpassword' );

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

$conn -> set_charset("utf8");