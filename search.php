<?php
session_start();
require 'lib/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$q = $_GET['q'] ?? '';
if ($q == '') {
    header("Location: index.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT id_user, username, profile_pic FROM user WHERE username LIKE ?");
$search = "%$q%";
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Pencarian</title>
<style>
    body { font-family: Arial; padding: 20px; }
    .user-box {
        display: flex;
        align-items: center;
        background: #fff;
        padding: 12px;
        border: 1px solid #ddd;
        margin-bottom: 10px;
        border-radius: 10px;
    }
    .user-box img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 15px;
    }
    .follow-btn {
        margin-left: auto;
        padding: 6px 12px;
        background: #0095f6;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .unfollow-btn {
        background: #ff4e4e;
    }
</style>
</head>
<body>

<h2>Hasil pencarian: <?= htmlspecialchars($q) ?></h2>

<?php while($u = $result->fetch_assoc()): ?>

    <?php
    // cek sudah follow atau belum
    $cek = $koneksi->prepare("SELECT 1 FROM follow WHERE follower_id=? AND following_id=?");
    $cek->bind_param("ii", $_SESSION['id_user'], $u['id_user']);
    $cek->execute();
    $isFollow = $cek->get_result()->num_rows > 0;
    ?>

    <div class="user-box">
        <img src="<?= $u['profile_pic'] ?: 'assets/default_profile.png' ?>">
        <a href="profile.php?u=<?= $u['username'] ?>">
            <b>@<?= $u['username'] ?></b>
        </a>

        <?php if($u['id_user'] != $_SESSION['id_user']): ?>
            <form action="follow_action.php" method="post">
                <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">

                <?php if($isFollow): ?>
                    <button class="follow-btn unfollow-btn" name="action" value="unfollow">
                        Unfollow
                    </button>
                <?php else: ?>
                    <button class="follow-btn" name="action" value="follow">
                        Follow
                    </button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>

<?php endwhile; ?>

</body>
</html>
