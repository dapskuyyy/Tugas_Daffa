<?php
session_start();
require 'lib/koneksi.php';
require 'lib/functions.php';

if (!isset($_GET['id'])) {
    die("Post tidak ditemukan!");
}

$id_post = (int)$_GET['id'];

// Ambil data postingan
$stmt = $koneksi->prepare("
    SELECT post.*, user.username, user.profile_pic 
    FROM post 
    JOIN user ON post.id_user = user.id_user
    WHERE id_post = ? LIMIT 1
");
$stmt->bind_param("i", $id_post);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Postingan tidak ditemukan!");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@<?= htmlspecialchars($data['username']) ?> - Post</title>

<style>
body {
    margin: 0;
    background: #000;
    color: #fff;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.container {
    display: flex;
    height: 100vh;
    justify-content: center;
    align-items: center;
}

.post-box {
    display: flex;
    background: #111;
    border: 1px solid #333;
    border-radius: 10px;
    max-width: 900px;
    height: 90vh;
    overflow: hidden;
}

.left {
    flex: 1;
    background: #000;
    display:flex;
    justify-content:center;
    align-items:center;
}

.left img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.right {
    width: 350px;
    background: #111;
    display: flex;
    flex-direction: column;
    border-left: 1px solid #333;
}

.header {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #333;
}

.header img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
}

.comments {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    font-size: 14px;
}

.comment {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
}

.comment-user {
    font-weight: 600;
}

.bottom {
    padding: 15px;
    border-top: 1px solid #333;
}

.caption-box {
    margin-bottom: 10px;
}

.back-btn {
    position: fixed;
    top: 20px;
    left: 20px;
    background:#fff;
    color:#000;
    padding:8px 14px;
    border-radius: 6px;
    font-weight: bold;
    text-decoration: none;
}
</style>
</head>

<body>

<a href="javascript:history.back()" class="back-btn">← Back</a>

<div class="container">
    <div class="post-box">

        <!-- FOTO -->
        <div class="left">
            <img src="<?= htmlspecialchars($data['image']) ?>" alt="foto">
        </div>

        <!-- KOMENTAR + CAPTION -->
        <div class="right">

            <!-- HEADER USER -->
            <div class="header">
                <img src="<?= htmlspecialchars($data['profile_pic']) ?>">
                <b>@<?= htmlspecialchars($data['username']) ?></b>
            </div>

            <!-- KOMENTAR -->
            <div class="comments">

                <div class="comment caption-box">
                    <div>
                        <span class="comment-user">@<?= htmlspecialchars($data['username']) ?></span>
                        <?= nl2br(htmlspecialchars($data['caption'])) ?>
                    </div>
                </div>

            </div>

            <!-- LIKE & INPUT KOMENTAR (VISUAL DOANG DULU) -->
            <div class="bottom">
                ❤️ 999 • 💬 Comment
                <br><br>
                <input type="text" placeholder="Add a comment..." 
                       style="width:100%; padding:8px; border-radius:6px; border:1px solid #444; background:#222; color:#fff;">
            </div>

        </div>
    </div>
</div>

</body>
</html>
