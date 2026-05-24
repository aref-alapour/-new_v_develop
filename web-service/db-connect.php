<?php
$servername = "localhost";
$dbname     = "escapezo_queries";
$username   = "escapezo_escapezoom";
$password   = '}lg#0#SaA}0%$zQn';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

$conn -> set_charset("utf8");