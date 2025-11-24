<?php
session_start();
require 'lib/koneksi.php';
require 'lib/functions.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$username = $_GET['u'] ?? '';
if ($username == '') {
    header("Location: index.php");
    exit;
}

// Ambil data user
$stmt = $koneksi->prepare("SELECT * FROM user WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$userData) {
    die("User tidak ditemukan!");
}

// Ambil postingan user
$stmt = $koneksi->prepare("
    SELECT id_post, image, caption, created_at 
    FROM post WHERE id_user=? ORDER BY created_at DESC
");
$stmt->bind_param("i", $userData['id_user']);
$stmt->execute();
$posts = $stmt->get_result();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@<?= htmlspecialchars($userData['username']) ?> • Profil • InstaClone</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: #fafafa;
      color: #262626;
    }

    /* 🌈 NAVBAR */
    .navbar {
      background: #fff;
      border-bottom: 1px solid #dbdbdb;
      padding: 12px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .navbar-left h2 {
      font-size: 22px;
      font-weight: 700;
      color: #0095f6;
    }
    .navbar-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .nav-btn {
      background: #0095f6;
      border: none;
      color: #fff;
      padding: 8px 15px;
      border-radius: 8px;
      font-weight: 500;
      font-size: 14px;
      cursor: pointer;
      transition: 0.25s;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .nav-btn:hover { background: #007ad1; transform: translateY(-1px); }
    .nav-btn.logout { background: #ff3b3b; }
    .nav-btn.logout:hover { background: #e42f2f; }
    .nav-btn.home { background: #f0f0f0; color: #333; }
    .nav-btn.home:hover { background: #e5e5e5; }

    /* 👤 PROFIL */
    .profile-header {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-wrap: wrap;
      gap: 40px;
      padding: 40px 20px;
      border-bottom: 1px solid #dbdbdb;
      background: #fff;
    }
    .profile-pic {
      width: 130px; height: 130px;
      border-radius: 50%;
      border: 3px solid #0095f6;
      object-fit: cover;
      background: #eee url('assets/default_profile.png') center/cover no-repeat;
    }
    .profile-info h2 { font-size: 24px; margin-bottom: 10px; }
    .stats { display: flex; gap: 20px; margin-bottom: 10px; }
    .stats div { font-size: 14px; color: #555; }
    .stats span { font-weight: 600; color: #111; }
    .bio { color: #444; font-size: 15px; white-space: pre-line; }
    .edit-btn {
      background: #0095f6;
      color: #fff;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.3s;
    }
    .edit-btn:hover { background: #007ad1; }

    /* 🖼️ GALERI */
    .gallery {
      max-width: 950px;
      margin: 30px auto;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 10px;
      padding: 0 15px 50px;
    }
    .post-box {
      position: relative;
      overflow: hidden;
      border-radius: 10px;
    }
    .post-box img {
      width: 100%;
      height: 280px;
      object-fit: cover;
      border-radius: 10px;
      transition: transform 0.3s ease, filter 0.3s ease;
    }
    .post-box:hover img {
      transform: scale(1.03);
      filter: brightness(80%);
    }
    .delete-btn {
      position: absolute;
      top: 10px; right: 10px;
      background: rgba(0,0,0,0.6);
      color: #fff;
      border-radius: 6px;
      padding: 4px 8px;
      font-size: 13px;
      text-decoration: none;
      transition: 0.3s;
      opacity: 0;
    }
    .post-box:hover .delete-btn { opacity: 1; }
    .delete-btn:hover { background: rgba(255,0,0,0.8); }

    .no-post {
      text-align: center;
      color: #777;
      font-size: 15px;
      margin-top: 60px;
    }

    /* 🟢 Notifikasi */
    .notif {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 12px 18px;
      border-radius: 8px;
      color: #fff;
      font-weight: 500;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      animation: fadeOut 3s forwards;
      z-index: 999;
    }
    @keyframes fadeOut {
      0% { opacity: 1; }
      70% { opacity: 1; }
      100% { opacity: 0; visibility: hidden; }
    }

    @media (max-width: 700px) {
      .profile-header { flex-direction: column; text-align: center; }
      .nav-btn { font-size: 13px; padding: 7px 12px; }
    }
  </style>
</head>
<body>

<?php if (isset($_GET['status'])): ?>
  <div class="notif" style="background: <?= $_GET['status'] === 'deleted' ? '#28a745' : '#ff4e4e' ?>;">
    <?= $_GET['status'] === 'deleted' ? '✅ Postingan berhasil dihapus!' : '⚠️ Kamu tidak bisa hapus postingan orang lain!' ?>
  </div>
<?php endif; ?>

  <!-- 🔝 Navbar -->
  <div class="navbar">
    <div class="navbar-left">
      <h2>InstaClone</h2> 
    </div>
    <div class="navbar-right">
      <a href="index.php"><button class="nav-btn home"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M3 9.5L12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V9.5z"/>
</svg></button></a>
      <a href="upload.php"><button class="nav-btn"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <rect x="3" y="3" width="18" height="18" rx="2"/>
  <line x1="12" y1="8" x2="12" y2="16"/>
  <line x1="8" y1="12" x2="16" y2="12"/>
</svg>
</button></a>
      <a href="logout.php"><button class="nav-btn logout"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
  <polyline points="16 17 21 12 16 7"/>
  <line x1="21" y1="12" x2="9" y2="12"/>
</svg>
</button></a>
    </div>
  </div>

  <!-- 👤 Profil -->
  <div class="profile-header">
   <a href="profile_pic.php?u=<?= htmlspecialchars($userData['username']) ?>">
  <img src="<?= htmlspecialchars($userData['profile_pic'] ?: 'assets/default_profile.png') ?>" 
       class="profile-pic" alt="foto profil">
</a>
 <div class="profile-info">
  <div class="profile-top" style="display:flex; align-items:center; gap:10px;">
    <h2 style="margin:0;">@<?= htmlspecialchars($userData['username']) ?></h2>
    <?php if ($userData['id_user'] == $_SESSION['id_user']): ?>
      <a href="edit_profile.php"><button class="edit-btn" style="padding:5px 12px; font-size:14px;">✏️ Edit Profil</button></a>
    <?php endif; ?>
  </div>
  <div class="stats" style="margin-top:10px;">
    <div><span><?= $posts->num_rows ?></span> postingan</div>
    <div><span>124</span> pengikut</div>
    <div><span>98</span> mengikuti</div>
  </div>
  <div class="bio" style="margin-top:10px;"><?= htmlspecialchars($userData['bio'] ?? 'Belum ada bio.') ?></div>
</div>

  </div>
<!-- 🖼️ Galeri -->
<div class="gallery">
  <?php 
  // Ambil ulang posts karena sebelumnya sudah di-loop dalam stats
  $stmt = $koneksi->prepare("
      SELECT id_post, image, caption, created_at 
      FROM post WHERE id_user=? ORDER BY created_at DESC
  ");
  $stmt->bind_param("i", $userData['id_user']);
  $stmt->execute();
  $posts = $stmt->get_result();
  ?>

  <?php if ($posts->num_rows > 0): ?>
    <?php while($p = $posts->fetch_assoc()): ?>
      <?php if (!empty($p['image'])): ?>
      <div class="post-box">

        <!-- FOTO BISA DIKLIK KE HALAMAN POST -->
        <a href="post.php?id=<?= $p['id_post'] ?>">
          <img src="<?= htmlspecialchars($p['image']) ?>" alt="post">
        </a>

        <!-- TOMBOL HAPUS (Hanya muncul jika pemilik profil) -->
        <?php if ($userData['id_user'] == $_SESSION['id_user']): ?>
          <a href="hapus_post.php?id=<?= $p['id_post'] ?>&from=profile"
            onclick="return confirm('Yakin mau hapus postingan ini? 😢')"
            class="delete-btn">🗑️</a>
        <?php endif; ?>

      </div>
      <?php endif; ?>
    <?php endwhile; ?>

  <?php else: ?>
    <div class="no-post">Belum ada postingan 😔</div>
  <?php endif; ?>
</div>
  

</body>
</html>
