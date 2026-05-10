<?php
session_start();

include '../config/database.php';

if(isset($_POST['booking'])){

    $slot_id = $_POST['slot_id'];

    $user_id = $_SESSION['user']['id'];

    mysqli_query($conn,
    "UPDATE parking_slots
    SET status='penuh',
    booked_by='$user_id'
    WHERE id='$slot_id'");

    header("Location: ../../frontend/dashboard.php");
}
?>