<?php
session_start();
require 'lib/koneksi.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $pass = $_POST['password'];

    $stmt = $koneksi->prepare("SELECT id_user, password FROM user WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res && password_verify($pass, $res['password'])) {
        $_SESSION['id_user'] = $res['id_user'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - InstaClone</title>
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f58529, #dd2a7b, #8134af, #515bd4);
      background-size: 400% 400%;
      animation: gradientMove 12s ease infinite;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    @keyframes gradientMove {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }

    .login-box {
      background: white;
      padding: 40px 50px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      text-align: center;
      width: 340px;
      animation: fadeIn 1s ease;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(-20px);}
      to {opacity: 1; transform: translateY(0);}
    }

    h2 {
      color: #262626;
      font-size: 26px;
      margin-bottom: 10px;
      letter-spacing: 1px;
    }

    input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
      outline: none;
      transition: 0.3s;
    }

    input:focus {
      border-color: #0095f6;
      box-shadow: 0 0 5px rgba(0,149,246,0.3);
    }

    button {
      width: 100%;
      background-color: #0095f6;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
    }

    button:hover {
      background-color: #007ad9;
    }

    .error {
      color: #e74c3c;
      font-size: 14px;
      margin-bottom: 10px;
    }

    .register {
      margin-top: 15px;
      font-size: 14px;
    }

    .register a {
      color: #0095f6;
      text-decoration: none;
      font-weight: bold;
    }

    .register a:hover {
      text-decoration: underline;
    }

    .footer {
      position: absolute;
      bottom: 10px;
      color: white;
      font-size: 13px;
      text-align: center;
      width: 100%;
    }
  </style>
</head>
<body>

  <div class="login-box">
    <h2>InstaClone</h2>
    <p style="color:gray;font-size:13px;margin-top:-5px;">Masuk untuk berbagi momen</p>

    <?php if($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Masuk</button>
    </form>

    <div class="register">
      Belum punya akun? <a href="register.php">Daftar sekarang</a>
    </div>
  </div>

  <div class="footer">
    © 2025 InstaClone — Inspired by Instagram
  </div>

</body>
</html>
