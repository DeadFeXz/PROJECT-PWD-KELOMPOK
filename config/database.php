<?php
// config/database.php

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'makananupn_db';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Fungsi fetchAll - mengambil semua data
function fetchAll($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return [];
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fungsi fetchOne - mengambil satu data
function fetchOne($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) == 0) {
        return null;
    }
    return mysqli_fetch_assoc($result);
}

// Fungsi query - untuk insert, update, delete
function query($sql) {
    global $conn;
    return mysqli_query($conn, $sql);
}

// Fungsi escape string (gunakan prepared statement untuk keamanan lebih baik)
function escape($string) {
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}
?>