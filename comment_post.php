<?php
session_start();
require 'lib/koneksi.php';

if (!isset($_SESSION['id_user'])) {
  header("Location: login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_post = intval($_POST['id_post']);
  $id_user = $_SESSION['id_user'];
  $content = trim($_POST['content']);

  if ($content !== '') {
    $stmt = $koneksi->prepare("INSERT INTO comment (id_post, id_user, content, created_at) VALUES (?,?,?,NOW())");
    $stmt->bind_param("iis", $id_post, $id_user, $content);
    $stmt->execute();
    $stmt->close();
  }
}

header("Location: index.php");
exit;
?>
