<?php
session_start();

include '../../backend/config/database.php';

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query($conn,
    "SELECT * FROM users WHERE email='$email'");

    $user = mysqli_fetch_assoc($query);

    if($user){

        if(password_verify($password, $user['password'])){

            $_SESSION['user'] = $user;

            header("Location: ../dashboard.php");

        } else {

            $error = "Password salah!";
        }

    } else {

        $error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Login</title>

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
Login Account
</h3>

<?php if(isset($_GET['success'])){ ?>
<div class="success">
Register berhasil!
</div>
<?php } ?>

<?php if(isset($error)){ ?>
<div class="error">
<?= $error ?>
</div>
<?php } ?>

<form method="POST">

<input type="email"
name="email"
placeholder="Email"
required>

<input type="password"
name="password"
placeholder="Password"
required>

<button type="submit"
name="login">
Masuk
</button>

</form>

<div class="bottom-text">

Belum punya akun?

<a href="register.php">
Register
</a>

</div>

</div>
</div>

</body>
</html>