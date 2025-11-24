<?php
session_start();
require 'lib/koneksi.php';
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }
$action = $_POST['action'] ?? '';
$following_id = intval($_POST['following_id']);
$follower_id = $_SESSION['id_user'];

if ($action === 'follow') {
    $stmt = $koneksi->prepare("INSERT IGNORE INTO follow (follower_id, following_id) VALUES (?,?)");
    $stmt->bind_param("ii",$follower_id,$following_id); $stmt->execute(); $stmt->close();
} else {
    $stmt = $koneksi->prepare("DELETE FROM follow WHERE follower_id=? AND following_id=?");
    $stmt->bind_param("ii",$follower_id,$following_id); $stmt->execute(); $stmt->close();
}
header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
