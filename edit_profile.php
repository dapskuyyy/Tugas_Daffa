<?php
// aktifkan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'lib/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// ambil data user
$sql = "SELECT username, bio, profile_pic FROM user WHERE id_user=?";
$stmt = $koneksi->prepare($sql);
if (!$stmt) {
    die("DB error: " . $koneksi->error);
}
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User tidak ditemukan.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $profilePic = $user['profile_pic'] ?? null;

    // validasi nama
    if (empty($username)) {
        $error = "Username tidak boleh kosong.";
    } else {
        // cek apakah username sudah dipakai user lain
        $check = $koneksi->prepare("SELECT id_user FROM user WHERE username=? AND id_user<>?");
        $check->bind_param("si", $username, $id_user);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows > 0) {
            $error = "Username sudah dipakai, pilih nama lain.";
        }
        $check->close();
    }

    // upload foto profil
    if (!$error && !empty($_FILES['profile_pic']['name'])) {
        $targetDir = __DIR__ . "/uploads/profile/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileTmp = $_FILES['profile_pic']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (!in_array($ext, $allowed)) {
            $error = "Format gambar tidak didukung.";
        } else {
            $fileName = uniqid("pp_") . '.' . $ext;
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($fileTmp, $targetFile)) {
                $profilePic = 'uploads/profile/' . $fileName;
            } else {
                $error = "Gagal upload gambar.";
            }
        }
    }

    // update data
    if (!$error) {
        $sql2 = "UPDATE user SET username=?, bio=?, profile_pic=? WHERE id_user=?";
        $stmt2 = $koneksi->prepare($sql2);
        $stmt2->bind_param("sssi", $username, $bio, $profilePic, $id_user);
        $stmt2->execute();
        $stmt2->close();
        header("Location: profile.php?u=" . urlencode($username));
        exit;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Profil - InstaClone</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; }
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(180deg, #fdfdfd, #ececec);
    margin: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .navbar {
    width: 100%;
    background: #fff;
    border-bottom: 1px solid #ddd;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 10;
  }
  .navbar h2 {
    color: #0095f6;
    font-size: 22px;
    font-weight: 700;
  }
  .navbar a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    margin-left: 10px;
    transition: color .2s;
  }
  .navbar a:hover { color: #0095f6; }

  .container {
    background: #fff;
    max-width: 500px;
    width: 100%;
    margin-top: 40px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    padding: 25px;
  }

  h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
  }

  .profile-pic {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
  }

  .profile-pic img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #0095f6;
    background: #f3f3f3;
  }

  label {
    font-weight: 600;
    color: #444;
    display: block;
    margin-top: 15px;
    margin-bottom: 5px;
  }

  textarea, input[type=file], input[type=text] {
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 10px;
    font-family: inherit;
    resize: none;
  }

  button {
    width: 100%;
    background: #0095f6;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 18px;
    transition: background 0.3s;
  }

  button:hover { background: #007ad9; }

  .error {
    color: #e74c3c;
    background: #ffeaea;
    border: 1px solid #f5b7b1;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
  }

  .back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #0095f6;
    text-decoration: none;
    font-weight: 500;
  }

  .back-link:hover { text-decoration: underline; }
</style>
</head>
<body>

  <div class="navbar">
    <h2>InstaClone</h2>
    <div>
      <a href="index.php">Beranda</a>
      <a href="profile.php?u=<?= urlencode($user['username']) ?>">Profil</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <div class="container">
    <h2>Edit Profil</h2>

    <?php if($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-pic">
      <?php if(!empty($user['profile_pic'])): ?>
        <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Foto Profil">
      <?php else: ?>
        <img src="assets/default_profile.png" alt="Default Profil">
      <?php endif; ?>
    </div>

    <form method="post" enctype="multipart/form-data">
      <label>Nama Pengguna (Username)</label>
      <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

      <label>Foto Profil</label>
      <input type="file" name="profile_pic" accept="image/*">

      <label>Bio</label>
      <textarea name="bio" rows="4" placeholder="Tulis sesuatu tentang dirimu..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>

      <button type="submit">💾 Simpan Perubahan</button>
    </form>

    <a class="back-link" href="profile.php?u=<?= urlencode($user['username']) ?>">← Kembali ke Profil</a>
  </div>

</body>
</html>
