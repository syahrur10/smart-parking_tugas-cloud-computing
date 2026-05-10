<?php

$conn = mysqli_connect(
    "host.docker.internal",
    "root",
    "",
    "smart_parking",
    3306
);

if(!$conn){
    die("Koneksi gagal: " . mysqli_connect_error());
}

?>