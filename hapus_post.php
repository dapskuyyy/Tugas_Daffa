<?php
session_start();
require 'lib/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_post = $_GET['id'] ?? 0;
$id_user = $_SESSION['id_user'];

if ($id_post > 0) {
    // Cek apakah postingan milik user yang login
    $stmt = $koneksi->prepare("
        SELECT image 
        FROM post 
        WHERE id_post = ? AND id_user = ? 
        LIMIT 1
    ");
    $stmt->bind_param("ii", $id_post, $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();

    if ($post) {
        // Hapus file gambar dari server kalau ada
        if (!empty($post['image']) && file_exists($post['image'])) {
            unlink($post['image']);
        }

        // Hapus data postingan dari database
        $stmt = $koneksi->prepare("
            DELETE FROM post 
            WHERE id_post = ? AND id_user = ?
        ");
        $stmt->bind_param("ii", $id_post, $id_user);
        $stmt->execute();
        $stmt->close();

        // Redirect ke profil dengan notifikasi sukses
        header("Location: profile.php?u=" . urlencode($_SESSION['username']) . "&status=deleted");
        exit;
    } else {
        // Kalau bukan punya dia, balikin tanpa izin
        header("Location: profile.php?u=" . urlencode($_SESSION['username']) . "&status=unauthorized");
        exit;
    }
} else {
    header("Location: profile.php?u=" . urlencode($_SESSION['username']));
    exit;
}
?>
