<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = 'rpl12345';
$DB_NAME = 'insta_clone';

$koneksi = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($koneksi->connect_error) {
    die("Koneksi DB gagal: " . $koneksi->connect_error);
}
$koneksi->set_charset('utf8mb4');
