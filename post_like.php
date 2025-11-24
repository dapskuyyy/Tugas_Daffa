<?php
session_start();
require 'lib/koneksi.php';
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }

$id_post = intval($_POST['id_post']);
$action = $_POST['action'];
$id_user = $_SESSION['id_user'];

if ($action === 'like') {
    $stmt = $koneksi->prepare("INSERT IGNORE INTO post_like (id_post, id_user) VALUES (?,?)");
    $stmt->bind_param("ii",$id_post,$id_user);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $koneksi->prepare("DELETE FROM post_like WHERE id_post=? AND id_user=?");
    $stmt->bind_param("ii",$id_post,$id_user);
    $stmt->execute();
    $stmt->close();
}
header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
