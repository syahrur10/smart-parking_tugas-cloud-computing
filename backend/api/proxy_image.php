<?php

if (!isset($_GET['file'])) {
    exit('No file');
}

$file = $_GET['file'];

$url = "http://smart-parking-localstack:4566/smartparking-bucket/" . $file;

$image = file_get_contents($url);

if ($image === false) {
    exit('Image not found');
}

// deteksi tipe file otomatis
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_buffer($finfo, $image);

header("Content-Type: " . $mime);

echo $image;