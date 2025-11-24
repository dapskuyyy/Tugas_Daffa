<?php
// Debug sementara
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'lib/koneksi.php';
require 'lib/functions.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

// konfigurasi upload
$ALLOWED_MIME = ['image/jpeg','image/jpg','image/png','image/webp'];
$ALLOWED_EXT  = ['jpg','jpeg','png','webp'];
$MAX_SIZE     = 5 * 1024 * 1024; // 5 MB

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['image'])) {
        $error = "Tidak ada file yang dikirim.";
    } else {
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "Gagal upload. Kode error: " . $file['error'];
        } elseif ($file['size'] > $MAX_SIZE) {
            $error = "Ukuran file terlalu besar. Maksimum 5 MB.";
        } else {
            $tmp = $file['tmp_name'];
            $origName = $file['name'];
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            // gunakan MIME dari $_FILES agar kompatibel di AppServ
            $mime = $_FILES['image']['type'] ?? '';

            if (!in_array($ext, $ALLOWED_EXT)) {
                $error = "Ekstensi tidak didukung. Gunakan JPG, PNG, atau WEBP.";
            } elseif (!in_array($mime, $ALLOWED_MIME)) {
                $error = "Format gambar tidak didukung ($mime).";
            } else {
                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $safeName = uniqid('img_') . '.' . $ext;
                $destPath = $uploadDir . '/' . $safeName;

                if (move_uploaded_file($tmp, $destPath)) {
                    $imagePath = 'uploads/' . $safeName;
                    $caption = trim($_POST['caption'] ?? '');
                    $id_user = $_SESSION['id_user'];

                    $stmt = $koneksi->prepare("INSERT INTO post (id_user, caption, image, created_at) VALUES (?,?,?,NOW())");
                    $stmt->bind_param("iss", $id_user, $caption, $imagePath);

                    if ($stmt->execute()) {
                        header("Location: index.php");
                        exit;
                    } else {
                        $error = "Gagal menyimpan ke database.";
                    }
                    $stmt->close();
                } else {
                    $error = "Gagal memindahkan file ke folder uploads.";
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Upload Post - InstaClone</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(180deg, #fafafa, #eaeaea);
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 600px;
        background: #fff;
        margin: 50px auto;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    h2 {
        text-align: center;
        color: #0095f6;
        margin-bottom: 20px;
    }
    textarea, input[type="file"] {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: inherit;
    }
    button {
        width: 100%;
        padding: 10px;
        background: #0095f6;
        border: none;
        color: white;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s;
    }
    button:hover {
        background: #007bd1;
    }
    .message {
        text-align: center;
        margin-bottom: 15px;
        font-weight: 500;
    }
    .error { color: red; }
    .success { color: green; }
    .back {
        display: block;
        text-align: center;
        margin-top: 15px;
        color: #333;
        text-decoration: none;
    }
    .back:hover {
        color: #0095f6;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Upload Post</h2>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <textarea name="caption" rows="3" placeholder="Tulis caption..."></textarea>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Upload Sekarang</button>
    </form>

    <a class="back" href="index.php">← Kembali ke Feed</a>
</div>
</body>
</html>
