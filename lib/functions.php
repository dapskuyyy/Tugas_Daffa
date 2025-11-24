<?php
function isLoggedIn() {
    return isset($_SESSION['id_user']);
}

function currentUser($koneksi) {
    if (!isLoggedIn()) return null;
    $id = $_SESSION['id_user'];
    $stmt = $koneksi->prepare("SELECT id_user, username, email, bio, avatar FROM user WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res;
}

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return $diff . 's';
    if ($diff < 3600) return floor($diff/60) . 'm';
    if ($diff < 86400) return floor($diff/3600) . 'h';
    return date('d M Y', strtotime($datetime));
}
