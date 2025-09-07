<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$dbname = "hotel_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmtRooms = $pdo->query("SELECT room_code AS id, room_name, capacity, tools, price_per_hour, image FROM meeting_rooms ORDER BY room_name ASC");
    $rooms = $stmtRooms->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8" />
<title>จองห้องประชุม</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
.room-image-wrapper {
  width: 300px; height: 200px; overflow: hidden; border: 1px solid #ccc; margin-top: 10px;
}
#roomImage {
  width: 100%; height: 100%; object-fit: cover; display: none;
}
</style>
</head>
<body>
<div class="container">
  <h1 class="mb-4 text-primary">จองห้องประชุม</h1>

  <form method="POST" action="payment.php" id="bookingForm" novalidate>
    <div class="mb-3">
      <select name="room_id" id="room_id" class="form-select" required>
        <option value="">-- เลือกห้องประชุม --</option>
        <?php foreach ($rooms as $room): ?>
          <option value="<?= $room['id'] ?>"
                  data-price="<?= $room['price_per_hour'] ?>"
                  data-image="<?= htmlspecialchars($room['image']) ?>">
            <?= htmlspecialchars($room['room_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <div class="room-image-wrapper">
        <img id="roomImage" alt="รูปห้องประชุม" />
      </div>
    </div>

    <div class="mb-3">
      <label for="booking_date" class="form-label">วันที่จอง</label>
      <input type="date" name="booking_date" id="booking_date" class="form-control" required min="<?= date('Y-m-d') ?>" />
    </div>

    <div class="row g-3">
      <div class="col-md-6">
        <label for="start_time" class="form-label">เวลาเริ่ม</label>
        <select name="start_time" id="start_time" class="form-select" required>
          <option value="">-- เลือกเวลาเริ่ม --</option>
          <?php
            for ($h = 8; $h <= 20; $h++) {
              foreach ([0, 30] as $m) {
                $time = sprintf('%02d:%02d', $h, $m);
                echo "<option value=\"$time\">$time</option>";
              }
            }
          ?>
        </select>
      </div>
      <div class="col-md-6">
        <label for="end_time" class="form-label">เวลาสิ้นสุด</label>
        <select name="end_time" id="end_time" class="form-select" required>
          <option value="">-- เลือกเวลาสิ้นสุด --</option>
          <?php
            for ($h = 8; $h <= 20; $h++) {
              foreach ([0, 30] as $m) {
                $time = sprintf('%02d:%02d', $h, $m);
                echo "<option value=\"$time\">$time</option>";
              }
            }
          ?>
        </select>
      </div>
    </div>

    <div id="priceInfo" class="mt-3"></div>

    <!-- ปุ่มต่างๆ -->
    <div class="action-bar mt-4">
      <div class="row g-2 row-cols-1 row-cols-md-2">
        <div class="col d-grid">
          <button type="button" id="submitBtn" class="btn btn-primary btn-lg">
            <i class=""></i> ไปหน้าชำระเงิน
          </button>
        </div>
        <div class="col d-grid">
          <a href="hisroom_upload.php" class="btn btn-outline-primary btn-lg">
            <i class="bi bi-paperclip"></i> แนบสลิปการจอง
          </a>
        </div>
        <div class="col d-grid">
          <a href="check_status.php" class="btn btn-outline-dark btn-lg">
            <i class="bi bi-clipboard-check"></i> ตรวจสอบสถานะการจอง
          </a>
        </div>
        <div class="col d-grid">
          <a href="home.php" class="btn btn-outline-secondary btn-lg">
            <i class=""></i> กลับหน้าหลัก
          </a>
        </div>
      </div>
    </div>
  </form>
</div>

<!-- Modal แจ้งเตือน -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">แจ้งเตือน</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        กรุณากรอกข้อมูลให้ครบทุกช่องก่อนดำเนินการ
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
      </div>
    </div>
  </div>
</div>

<script>
const roomSelect = document.getElementById('room_id');
const startTimeInput = document.getElementById('start_time');
const endTimeInput = document.getElementById('end_time');
const priceInfo = document.getElementById('priceInfo');
const roomImage = document.getElementById('roomImage');

function updateRoomImage() {
  const selected = roomSelect.options[roomSelect.selectedIndex];
  if (selected && selected.value !== '') {
    const imgSrc = selected.getAttribute('data-image');
    if (imgSrc) {
      roomImage.src = 'uploads/' + imgSrc;
      roomImage.style.display = 'block';
    } else {
      roomImage.style.display = 'none';
    }
  } else {
    roomImage.style.display = 'none';
  }
}

function calculatePrice() {
  const selectedRoom = roomSelect.options[roomSelect.selectedIndex];
  const pricePerHour = selectedRoom ? parseFloat(selectedRoom.getAttribute('data-price')) : 0;
  const start = startTimeInput.value;
  const end = endTimeInput.value;

  if (pricePerHour && start && end) {
    const startTimestamp = new Date(`1970-01-01T${start}:00`).getTime();
    const endTimestamp = new Date(`1970-01-01T${end}:00`).getTime();
    if (startTimestamp < endTimestamp) {
      const hours = (endTimestamp - startTimestamp) / (1000 * 60 * 60);
      const total = hours * pricePerHour;
      priceInfo.textContent = `ราคาที่คำนวณได้: ${total.toFixed(2)} บาท (${hours.toFixed(2)} ชั่วโมง x ${pricePerHour.toFixed(2)} บาท/ชม.)`;
    } else {
      priceInfo.textContent = '';
    }
  } else {
    priceInfo.textContent = '';
  }
}

roomSelect.addEventListener('change', () => {
  updateRoomImage();
  calculatePrice();
});
startTimeInput.addEventListener('change', calculatePrice);
endTimeInput.addEventListener('change', calculatePrice);

window.onload = () => {
  updateRoomImage();
};

// ตรวจสอบฟอร์มก่อนส่ง
document.getElementById('submitBtn').addEventListener('click', function () {
  const form = document.getElementById('bookingForm');
  if (!form.checkValidity()) {
    const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
    alertModal.show();
  } else {
    form.submit();
  }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
