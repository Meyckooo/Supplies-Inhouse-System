<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDBConnection() {
    $host     = "192.168.12.7";
    $username = "root";
    $password = "atlantic123";

    $dbname   = "db_inhouse_supplies"; 

    $conn = mysqli_connect($host, $username, $password, $dbname);

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    mysqli_set_charset($conn, "utf8mb4");

    return $conn;
}