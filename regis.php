<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST["username"]);
  $email = trim($_POST["email"]);
  $password = $_POST["password"];
  $confirm_password = $_POST["confirm_password"];

  if ($password !== $confirm_password) {
      $error = "❌ รหัสผ่านไม่ตรงกัน";
  } else {
      // ✅ ตรวจสอบว่าอีเมลนี้มีอยู่แล้วหรือยัง
      $check_stmt = $userConn->prepare("SELECT id FROM users WHERE email = ?");
      $check_stmt->bind_param("s", $email);
      $check_stmt->execute();
      $check_stmt->store_result();

      if ($check_stmt->num_rows > 0) {
          $error = "❌ อีเมลนี้ถูกใช้งานแล้ว";
      } else {
          $hashed_password = password_hash($password, PASSWORD_DEFAULT);
          $role = "user";

          // 🔵 เพิ่มข้อมูลผู้ใช้ใหม่
          $stmt = $userConn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
          $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

          if ($stmt->execute()) {
              header("Location: index.php");
              exit();
          } else {
              $error = "❌ เกิดข้อผิดพลาด: " . $userConn->error;
          }

          $stmt->close();
      }

      $check_stmt->close();
  }

  $userConn->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>สมัครสมาชิกcenter - โรงแรมขอนแก่นโฮเต็ล</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet" />
  <style>
    /* (โค้ด CSS เหมือนเดิม) */
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
  background: linear-gradient(to bottom right, #e8ebec, #e5e8e9, #fafcfc);
  background-size: cover;
}

.container {
  background: rgba(105, 102, 102, 0.9); /* ขุ่นมาก */
  color: #333; /* ตัวอักษรอ่านง่าย */
  border-radius: 20px;
  padding: 40px;
  width: 90%;
  max-width: 420px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.3);

  display: flex;
  flex-direction: column;
  align-items: center; /* จัดทุกอย่างตรงกลางแนวนอน */
}

.container h2 {
  text-align: center;
  width: 100%; /* ให้ text-align มีผลเต็มที่ */
  margin-bottom: 10px;
  font-weight: 600;
  font-size: 26px;
}

.container p {
  text-align: center;
  margin-bottom: 30px;
  color: #f0f0f0;
  font-size: 14px;
}

    
    .form-group {
      position: relative;
      margin-bottom: 20px;
    }
    .form-group img {
      position: absolute;
      top: 50%; left: 15px;
      transform: translateY(-50%);
      width: 22px;
      opacity: 0.6;
    }
    .form-input {
      width: 100%;
      padding: 12px 15px 12px 45px;
      border-radius: 30px;
      border: none;
      background-color: rgba(255, 255, 255, 0.15);
      color: #fff;
      font-size: 15px;
      transition: 0.3s;
    }
    .form-input::placeholder {
      color: #ddd;
    }
    .form-input:focus {
      outline: none;
      background-color: rgba(255, 255, 255, 0.3);
      color: #000;
    }
    .register-button {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 30px;
      background: linear-gradient(to right, #00c6ff, #0072ff);
      color: white;
      font-size: 16px;
      cursor: pointer;
      transition: transform 0.2s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    }
    .register-button:hover {
      transform: scale(1.03);
    }
    .login-link {
      margin-top: 20px;
      font-size: 14px;
      text-align: center;
      color: #ccc;
    }
    .login-link a {
      color: #00bcd4;
      text-decoration: none;
      font-weight: bold;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
    @media (max-width: 480px) {
      .container {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>สมัครสมาชิก</h2>
    <p>โรงแรมขอนแก่นโฮเต็ล (Khonkaen Hotel)</p>

    <?php if (!empty($error)) : ?>
      <div class="error-message" style="color:#ffcccb; margin-bottom: 15px; text-align: center;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="regis.php" method="post">
      <div class="form-group">
        <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="User" />
        <input type="text" name="username" class="form-input" placeholder="ชื่อผู้ใช้" required />
      </div>

      <div class="form-group">
        <img src="https://cdn-icons-png.flaticon.com/512/732/732021.png" alt="Email" />
        <input type="email" name="email" class="form-input" placeholder="อีเมล" required />
      </div>

      <div class="form-group">
        <img src="https://cdn-icons-png.flaticon.com/512/3064/3064155.png" alt="Password" />
        <input type="password" name="password" class="form-input" placeholder="รหัสผ่าน" required />
      </div>

      <div class="form-group">
        <img src="https://cdn-icons-png.flaticon.com/512/3064/3064155.png" alt="Confirm Password" />
        <input type="password" name="confirm_password" class="form-input" placeholder="ยืนยันรหัสผ่าน" required />
      </div>

      <button type="submit" class="register-button">สมัครสมาชิก</button>
    </form>

    <div class="login-link">
      คุณมีบัญชีแล้ว ? <a href="index.php">เข้าสู่ระบบ</a>
    </div>
  </div>

</body>
</html>
