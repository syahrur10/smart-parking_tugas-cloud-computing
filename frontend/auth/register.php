<?php
session_start();

include '../../backend/config/database.php';

if(isset($_POST['register'])){

    $nama = $_POST['nama'];

    $email = $_POST['email'];

    $password = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    $check = mysqli_query($conn,
    "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){

        $error = "Email sudah digunakan!";

    } else {

        mysqli_query($conn,
        "INSERT INTO users(nama,email,password)
        VALUES('$nama','$email','$password')");

        header("Location: login.php?success=1");
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Register</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="overlay"></div>

<div class="login-container">

<div class="login-card">

<h1>Smart Parking</h1>

<p>
Modern Parking Reservation System
</p>

<h3>
Create Account
</h3>

<?php if(isset($error)){ ?>
<div class="error">
<?= $error ?>
</div>
<?php } ?>

<form method="POST">

<input type="text"
name="nama"
placeholder="Nama"
required>

<input type="email"
name="email"
placeholder="Email"
required>

<input type="password"
name="password"
placeholder="Password"
required>

<button type="submit"
name="register">
Daftar
</button>

</form>

<div class="bottom-text">

Sudah punya akun?

<a href="login.php">
Login
</a>

</div>

</div>
</div>

</body>
</html>