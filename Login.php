<?php
require 'config.php';
session_start();

$error = "";

$hardcoded_admin_email = "admin@gmail.com";  
$hardcoded_admin_password = "admin1234";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if ($email === $hardcoded_admin_email && $password === $hardcoded_admin_password) {
        $_SESSION["user_id"] = 0;
        $_SESSION["username"] = "Admin";
        $_SESSION["email"] = $email;
        $_SESSION["user_role"] = "admin";  
    
        header("Location: admin.php");
        exit;
    }
    

    $stmt = $userConn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["email"] = $email;
            $_SESSION["user_role"] = $role;

            if ($role === "admin") {
                header("Location: admin.php");
            } else {
                header("Location: home.php");
            }
            exit;
        } else {
            $error = " รหัสผ่านไม่ถูกต้อง!";
        }
    } else {
        $error = " ไม่พบอีเมลนี้ในระบบ!";
    }

    $stmt->close();
    $userConn->close();
}
?>




<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>เข้าสู่ระบบ - โรงแรมขอนแก่นโฮเต็ล</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Sarabun', sans-serif;
    }
     
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(to bottom right, #ffffffff);
    }

    .container {
      background: rgba(37, 37, 37, 0.74);
      border-radius: 20px;
      padding: 40px;
      width: 90%;
      max-width: 420px;
      backdrop-filter: blur(20px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      color: #fff;
      text-align: center;
    }

    .container h2 {
      margin-bottom: 10px;
    }

    .container p {
      margin-bottom: 30px;
      font-size: 14px;
      color: #f0f0f0;
    }

    .form-group {
      position: relative;
      margin-bottom: 20px;
    }

    .form-group img {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      width: 22px;
      opacity: 0.6;
    }

    .form-input {
      width: 100%;
      padding: 12px 15px 12px 45px;
      border-radius: 30px;
      border: none;
      background-color: rgba(255, 255, 255, 0.7);
      color: #000;
      font-size: 15px;
    }

    .login-button {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 30px;
      background: linear-gradient(to right, #329252ff);
      color: white;
      font-size: 16px;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    }

    .error-message {
      color: #ffcccb;
      margin-bottom: 15px;
      font-size: 14px;
    }

    .register {
      margin-top: 20px;
      font-size: 14px;
      text-align: center;
      color: #ccc;
    }

    .register a {
      color: #00bcd4;
      text-decoration: none;
      font-weight: bold;
    }

    .register a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>ระบบจองห้องประชุม</h2>
  <p>โรงแรมขอนแก่นโฮเต็ล (Khonkaen Hotel)</p>

  <?php if (!empty($error)) : ?>
    <div class="error-message"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="User">
      <input type="email" name="email" class="form-input" placeholder="อีเมล" required>
    </div>

    <div class="form-group">
      <img src="https://cdn-icons-png.flaticon.com/512/3064/3064155.png" alt="Password">
      <input type="password" name="password" class="form-input" placeholder="รหัสผ่าน" required>
    </div>

    <button type="submit" class="login-button">เข้าสู่ระบบ</button>
  </form>

  <div class="register">
    คุณยังไม่ได้เป็นสมาชิกใช่หรือไม่ ? <a href="regis.php">สมัครสมาชิก</a>
  </div>
</div>

</body>
</html>
