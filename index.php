<?php
session_start();
require 'lib/koneksi.php';
require 'lib/functions.php';
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
$user = currentUser($koneksi);

$stmt = $koneksi->prepare("
  SELECT p.id_post, p.id_user, p.caption, p.image, p.created_at, u.username, u.profile_pic
  FROM post p
  JOIN user u ON p.id_user = u.id_user
  ORDER BY p.created_at DESC
");
$stmt->execute();
$posts = $stmt->get_result();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Beranda - InstaClone</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{
    font-family:'Poppins',sans-serif;
    background:#f6f7f9;
    color:#222;
  }
  a{text-decoration:none;color:inherit;}

  /* Navbar */
  .navbar{
    background:#fff;
    border-bottom:1px solid #e0e0e0;
    padding:15px 25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:sticky;
    top:0;
    z-index:100;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
  }
  .navbar h2{
    color:#0095f6;
    font-weight:700;
    font-size:22px;
  }

  .nav-right{
    display:flex;
    align-items:center;
    gap:16px;
  }

  .nav-user{
    display:flex;
    align-items:center;
    gap:10px;
    font-weight:500;
    color:#333;
  }

  .nav-user img{
    width:35px;
    height:35px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #0095f6;
    transition:.2s;
  }
  .nav-user img:hover{
    transform:scale(1.05);
  }

  .nav-btn{
    background:#0095f6;
    color:#fff;
    border:none;
    border-radius:25px;
    padding:8px 16px;
    font-weight:500;
    cursor:pointer;
    transition:background .2s, transform .2s;
    display:flex;
    align-items:center;
    gap:6px;
  }
  .nav-btn:hover{
    background:#007ad1;
    transform:translateY(-1px);
  }

  .nav-btn.logout{
    background:#ff4e4e;
  }
  .nav-btn.logout:hover{
    background:#e03939;
  }

  /* Feed */
  .wrap{
    max-width:650px;
    margin:30px auto 60px;
    display:flex;
    flex-direction:column;
    gap:30px;
    padding:0 15px;
  }
  .card{
    background:#fff;
    border:1px solid #ddd;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    transition:transform .2s ease, box-shadow .2s ease;
  }
  .card:hover{
    transform:translateY(-3px);
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
  }
  .card-header{
    padding:14px 18px;
    display:flex;
    align-items:center;
    gap:12px;
    border-bottom:1px solid #f2f2f2;
  }
  .profile-thumb{
    width:42px;
    height:42px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #0095f6;
  }
  .username{
    font-weight:600;
    color:#333;
  }
  .time{
    color:#999;
    font-size:0.8em;
    margin-left:auto;
  }
  .post-img{
    width:100%;
    max-height:600px;
    object-fit:cover;
    background:#fafafa;
    display:block;
  }
  .card-body{
    padding:14px 18px;
  }
  .card-body p{
    margin:0;
    color:#222;
    line-height:1.5em;
    word-wrap:break-word;
  }
  .actions{
    margin-top:10px;
    display:flex;
    align-items:center;
    gap:12px;
  }
  .btn{
    background:#0095f6;
    border:none;
    padding:7px 14px;
    border-radius:8px;
    color:#fff;
    cursor:pointer;
    font-weight:500;
    font-size:14px;
    transition:background .2s;
  }
  .btn:hover{background:#007bd1}
  .like-count{font-size:0.9em;color:#555}
  .comment-section{
    padding:12px 18px 16px;
    background:#fafafa;
    border-top:1px solid #eee;
  }
  .comment-form{
    display:flex;
    gap:8px;
    margin-bottom:10px;
  }
  .comment-form input{
    flex:1;
    padding:8px 10px;
    border:1px solid #ccc;
    border-radius:8px;
    outline:none;
    font-family:inherit;
    font-size:14px;
  }
  .comment-form input:focus{border-color:#0095f6;}
  .comment{
    background:#fff;
    border-radius:8px;
    padding:8px 10px;
    margin-bottom:6px;
    border:1px solid #eee;
    box-shadow:0 1px 3px rgba(0,0,0,0.03);
  }
  .comment strong{color:#222;font-weight:600;}
  .small{font-size:0.8em;color:#888;margin-top:2px;}
  footer{
    text-align:center;
    padding:25px;
    font-size:0.9em;
    color:#aaa;
    border-top:1px solid #e5e5e5;
    background:#fff;
  }
  @media(max-width:700px){
    .wrap{margin:20px auto;padding:0 10px;}
    .navbar h2{font-size:18px;}
    .nav-btn{padding:6px 10px;font-size:13px;}
  }
</style>
</head>
<body>

 <div class="navbar">
    <h2>InstaClone</h2>

   
    <!-- 🔍 Search Bar -->
  <form action="search.php" method="GET" class="search-box">
    <input type="text" name="q" placeholder="Cari pengguna..." required>
    <button type="submit">🔎</button>
  </form>

    <div class="nav-right">
      <div class="nav-user">
        <?php if(!empty($user['profile_pic'])): ?>
          <a href="profile.php?u=<?=urlencode($user['username'])?>">
            <img src="<?=htmlspecialchars($user['profile_pic'])?>" alt="profil">
          </a>
        <?php else: ?>
          <a href="profile.php?u=<?=urlencode($user['username'])?>">
            <img src="aset/img/11.jpg" alt="default profil">
          </a>
        <?php endif; ?>
        <span>Hai, <b><?=htmlspecialchars($user['username'])?></b></span>
      </div>
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


  <!-- Feed -->
  <div class="wrap">
    <?php while($p = $posts->fetch_assoc()): ?>
      <div class="card">
        <div class="card-header">
          <img src="<?= htmlspecialchars($p['profile_pic'] ?: 'assets/default_profile.png') ?>" class="profile-thumb" alt="">
          <span class="username">@<?=htmlspecialchars($p['username'])?></span>
          <span class="time"><?=timeAgo($p['created_at'])?></span>
        </div>

        <?php if(!empty($p['image'])): ?>
          <img src="<?=htmlspecialchars($p['image'])?>" class="post-img" alt="post">
        <?php endif; ?>

        <div class="card-body">
          <p><?=nl2br(htmlspecialchars($p['caption']))?></p>
          <?php
            $stmtLike = $koneksi->prepare("SELECT COUNT(*) as cnt FROM post_like WHERE id_post=?");
            $stmtLike->bind_param("i",$p['id_post']);
            $stmtLike->execute();
            $likes = $stmtLike->get_result()->fetch_assoc()['cnt'];
            $stmtLike->close();

            $stmtCheck = $koneksi->prepare("SELECT id_like FROM post_like WHERE id_post=? AND id_user=? LIMIT 1");
            $stmtCheck->bind_param("ii",$p['id_post'], $_SESSION['id_user']);
            $stmtCheck->execute();
            $liked = $stmtCheck->get_result()->num_rows > 0;
            $stmtCheck->close();
          ?>
          <div class="actions">
            <form action="post_like.php" method="post" style="display:inline">
              <input type="hidden" name="id_post" value="<?=$p['id_post']?>">
              <input type="hidden" name="action" value="<?= $liked ? 'unlike' : 'like' ?>">
              <button class="btn" type="submit"><?= $liked ? '<svg width="26" height="26" viewBox="0 0 24 24" fill="red" stroke="red" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/>
</svg>
' : '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/>
</svg>
' ?></button>
            </form>
            <span class="like-count"><?= $likes ?> suka</span>
          </div>
        </div>

        <div class="comment-section">
          <form action="comment_post.php" method="post" class="comment-form">
            <input type="hidden" name="id_post" value="<?=$p['id_post']?>">
            <input name="content" placeholder="Tulis komentar..." required>
            <button class="btn" type="submit">Kirim</button>
          </form>
          <?php
            $stmtC = $koneksi->prepare("
              SELECT c.content, c.created_at, u.username 
              FROM comment c 
              JOIN user u ON c.id_user=u.id_user 
              WHERE c.id_post=? ORDER BY c.created_at ASC
            ");
            $stmtC->bind_param("i", $p['id_post']);
            $stmtC->execute();
            $resC = $stmtC->get_result();
          ?>
          <?php while($c = $resC->fetch_assoc()): ?>
            <div class="comment">
              <strong>@<?=htmlspecialchars($c['username'])?></strong> <?=htmlspecialchars($c['content'])?>
              <div class="small"><?=timeAgo($c['created_at'])?></div>
            </div>
          <?php endwhile; $stmtC->close(); ?>
        </div>
      </div>
    <?php endwhile; $stmt->close(); ?>
  </div>

  <footer>© <?=date('Y')?> InstaClone — dibuat dengan ❤️ oleh kamu</footer>
</body>
</html>
