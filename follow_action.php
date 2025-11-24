<?php
session_start();
require 'lib/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$me = $_SESSION['id_user'];
$target = $_POST['id_user'];
$action = $_POST['action'];

if ($action == "follow") {
    $stmt = $koneksi->prepare("INSERT INTO follow (follower_id, following_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $me, $target);
    $stmt->execute();
}

if ($action == "unfollow") {
    $stmt = $koneksi->prepare("DELETE FROM follow WHERE follower_id=? AND following_id=?");
    $stmt->bind_param("ii", $me, $target);
    $stmt->execute();
}

header("Location: search.php?q=");
exit;
