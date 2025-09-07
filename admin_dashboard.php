<?php
session_start();
// ตรวจสอบสิทธิ์แอดมิน (แก้ตามระบบล็อกอินของคุณ)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$dbname = "hotel_system";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update_hotel'])) {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $address = $_POST['address'] ?? '';
            $phone = $_POST['phone'] ?? '';

            $stmt = $pdo->prepare("UPDATE hotel_info SET name = ?, description = ?, address = ?, phone = ? WHERE id = 1");
            $stmt->execute([$name, $description, $address, $phone]);
            $msg = "อัพเดตข้อมูลโรงแรมเรียบร้อยแล้ว";
        }
    }

    $stmtHotel = $pdo->query("SELECT * FROM hotel_info WHERE id = 1");
    $hotel = $stmtHotel->fetch(PDO::FETCH_ASSOC);

    $stmtRooms = $pdo->query("SELECT * FROM meeting_rooms ORDER BY id ASC");
    $rooms = $stmtRooms->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>แก้ไขหน้าแรก</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
    }
    .container {
      max-width: 900px;
      margin-top: 40px;
      margin-bottom: 40px;
      background: white;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    h1, h2 {
      color: #343a40;
    }
    .btn-back {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<div class="container">
  <a href="admin.php" class="btn btn-outline-secondary btn-back">
    &larr; กลับหน้าแอดมิน
  </a>

  <h1 class="mb-4">แก้ไขข้อมูลหน้าINDEX</h1>

  <?php if (!empty($msg)) : ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <h2 class="mb-3">ข้อมูลโรงแรม</h2>
  <form method="POST" class="mb-5">
    <input type="hidden" name="update_hotel" value="1" />
    <div class="mb-3">
      <label class="form-label">ชื่อโรงแรม</label>
      <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($hotel['name'] ?? '') ?>" />
    </div>
    <div class="mb-3">
      <label class="form-label">คำบรรยาย</label>
      <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($hotel['description'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">ที่อยู่</label>
      <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($hotel['address'] ?? '') ?>" />
    </div>
    <div class="mb-3">
      <label class="form-label">โทรศัพท์</label>
      <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($hotel['phone'] ?? '') ?>" />
    </div>
    <button type="submit" class="btn btn-primary">บันทึกข้อมูลโรงแรม</button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
