<?php

session_start();

include '../backend/config/database.php';

if(!isset($_SESSION['user'])){
    header("Location: auth/login.php");
}

$data = mysqli_query($conn,
"SELECT * FROM parking_slots");

$total = mysqli_num_rows($data);

$kosong = mysqli_query($conn,
"SELECT * FROM parking_slots
WHERE status='kosong'");

$totalKosong = mysqli_num_rows($kosong);

$penuh = mysqli_query($conn,
"SELECT * FROM parking_slots
WHERE status='penuh'");

$totalPenuh = mysqli_num_rows($penuh);

?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Dashboard Smart Parking</title>

<link rel="stylesheet"
href="assets/css/style.css">

</head>

<body>

<div class="overlay"></div>

<div class="dashboard-container">

<!-- NAVBAR -->

<div class="navbar">

<div>

<h2>
Smart Parking
</h2>

<p>
Selamat datang,
<b><?= $_SESSION['user']['nama']; ?></b>

<br>

Kelola reservasi slot parkir Anda dengan cepat,
mudah, dan modern.
</p>

</div>

<a class="logout-btn"
href="auth/logout.php">
Logout
</a>

</div>

<!-- STATISTIK -->

<div class="stats-grid">

<div class="stats-card">

<h3>
Total Slot
</h3>

<h1>
<?= $total; ?>
</h1>

</div>

<div class="stats-card">

<h3>
Slot Tersedia
</h3>

<h1>
<?= $totalKosong; ?>
</h1>

</div>

<div class="stats-card">

<h3>
Slot Terisi
</h3>

<h1>
<?= $totalPenuh; ?>
</h1>

</div>

</div>

<!-- SLOT PARKIR -->

<div class="grid">

<?php while($slot = mysqli_fetch_assoc($data)){ ?>

<div class="slot-card">

<h2>
<?= $slot['slot_name']; ?>
</h2>

<?php if($slot['status'] == 'kosong'){ ?>

<p class="status-kosong">
🟢 Slot Tersedia
</p>

<form action="process_booking.php"
method="POST">

<input type="hidden"
name="slot_id"
value="<?= $slot['id']; ?>">

<button class="booking-btn"
name="booking">

Booking Sekarang

</button>

</form>

<?php } else { ?>

<p class="status-penuh">
🔴 Slot Terisi
</p>

<button class="full-btn">

Sudah Penuh

</button>

<?php } ?>

</div>

<?php } ?>

</div>

</div>

</body>
</html>