<?php

session_start();

include '../backend/config/database.php';

if(isset($_POST['booking'])){

    $slot_id = $_POST['slot_id'];

    $user_id = $_SESSION['user']['id'];

    $nama = $_SESSION['user']['nama'];

    // CHECK APAKAH USER SUDAH BOOKING

    $check = mysqli_query($conn,
    "SELECT * FROM parking_slots
    WHERE booked_by='$user_id'");

    if(mysqli_num_rows($check) > 0){

        echo "

        <!DOCTYPE html>
        <html lang='id'>

        <head>

        <meta charset='UTF-8'>

        <meta name='viewport'
        content='width=device-width, initial-scale=1.0'>

        <title>Smart Parking</title>

        <link rel='stylesheet'
        href='assets/css/style.css'>

        </head>

        <body>

        <div class='overlay'></div>

        <div class='custom-alert'>

            <div class='alert-box'>

                <div class='alert-icon alert-error'>
                    !
                </div>

                <h2>
                    Booking Gagal
                </h2>

                <p>

                    Selamat datang kembali,
                    <b>$nama</b>

                </p>

                <p>

                    Anda sudah memiliki
                    slot parkir yang aktif.

                </p>

                <p class='small-text'>

                    Sistem Smart Parking hanya
                    mengizinkan satu slot aktif
                    untuk setiap pengguna.

                </p>

                <button onclick=\"window.location='dashboard.php'\">

                    Kembali ke Dashboard

                </button>

            </div>

        </div>

        </body>
        </html>

        ";

        exit();
    }

    // UPDATE SLOT

    mysqli_query($conn,
    "UPDATE parking_slots
    SET status='penuh',
    booked_by='$user_id'
    WHERE id='$slot_id'");

    // AMBIL DATA SLOT

    $slot = mysqli_query($conn,
    "SELECT * FROM parking_slots
    WHERE id='$slot_id'");

    $dataSlot = mysqli_fetch_assoc($slot);

    $namaSlot = $dataSlot['slot_name'];

    // ALERT SUKSES

    echo "

    <!DOCTYPE html>
    <html lang='id'>

    <head>

    <meta charset='UTF-8'>

    <meta name='viewport'
    content='width=device-width, initial-scale=1.0'>

    <title>Smart Parking</title>

    <link rel='stylesheet'
    href='assets/css/style.css'>

    </head>

    <body>

    <div class='overlay'></div>

    <div class='custom-alert'>

        <div class='alert-box'>

            <div class='alert-icon'>
                ✓
            </div>

            <h2>
                Booking Berhasil
            </h2>

            <p>

                Selamat,
                <b>$nama</b>

            </p>

            <p>

                Slot parkir
                <b>$namaSlot</b>

                berhasil dipesan.

            </p>

            <p class='small-text'>

                Terima kasih telah menggunakan
                Smart Parking Reservation System.

            </p>

            <button onclick=\"window.location='dashboard.php'\">

                Kembali ke Dashboard

            </button>

        </div>

    </div>

    </body>
    </html>

    ";
}

?>