<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/../aws/s3.php';
require __DIR__ . '/../config/database.php';

// Validasi input
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gambar kendaraan belum dipilih atau gagal diupload.'
    ]);
    exit;
}

$plateNumber = trim($_POST['plate_number'] ?? '');

if ($plateNumber === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Nomor plat tidak boleh kosong.'
    ]);
    exit;
}

$fileTmp  = $_FILES['image']['tmp_name'];
$fileName = time() . '_' . str_replace(' ', '_', basename($_FILES['image']['name']));

try {

    // 1. Upload ke S3
    $s3->putObject([
        'Bucket'     => 'smartparking-bucket',
        'Key'        => $fileName,
        'SourceFile' => $fileTmp
    ]);

    // 2. Simpan ke MySQL
    $stmt = $conn->prepare("
        INSERT INTO vehicle_uploads (plate_number, vehicle_image)
        VALUES (?, ?)
    ");

    if (!$stmt) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Database error: ' . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param("ss", $plateNumber, $fileName);
    $stmt->execute();

    // 3. Kirim notifikasi SQS (opsional, tidak hentikan proses jika gagal)
    try {
        require __DIR__ . '/../aws/sqs.php';

        $sqs->sendMessage([
            'QueueUrl'    =>
                'http://smart-parking-localstack:4566/000000000000/parking-notification',
            'MessageBody' => json_encode([
                'plate_number' => $plateNumber,
                'message'      => 'Kendaraan berhasil masuk ke area parkir.',
                'time'         => date('Y-m-d H:i:s')
            ])
        ]);

    } catch (Exception $sqsErr) {
        // SQS opsional — lanjut meski gagal
    }

    echo json_encode([
        'status'  => 'success',
        'message' => "Kendaraan {$plateNumber} berhasil diupload ke sistem."
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Upload gagal: ' . $e->getMessage()
    ]);
}