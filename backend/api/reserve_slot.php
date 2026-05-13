<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/../aws/dynamodb.php';

$userName = trim($_POST['user_name'] ?? '');
$slotId   = strtoupper(trim($_POST['slot_id'] ?? ''));

if ($userName === '' || $slotId === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Nama dan slot parkir wajib diisi.'
    ]);
    exit;
}

try {

    // 1. Cek slot ada dan statusnya di DynamoDB
    $result = $dynamodb->getItem([
        'TableName' => 'parking_slots',
        'Key' => [
            'slot_id' => ['S' => $slotId]
        ]
    ]);

    if (empty($result['Item'])) {
        echo json_encode([
            'status'  => 'error',
            'message' => "Slot {$slotId} tidak ditemukan. Pastikan ID slot benar (contoh: A1, B2)."
        ]);
        exit;
    }

    $slot   = $result['Item'];
    $status = $slot['status']['S'] ?? 'unknown';

    if ($status === 'occupied') {
        echo json_encode([
            'status'  => 'error',
            'message' => "Slot {$slotId} sudah terisi, coba slot lain."
        ]);
        exit;
    }

    // 2. Update status ke occupied di DynamoDB
    $dynamodb->updateItem([
        'TableName' => 'parking_slots',
        'Key' => [
            'slot_id' => ['S' => $slotId]
        ],
        'UpdateExpression' =>
            'SET #s = :occ, booked_by = :name, booked_at = :at',
        'ExpressionAttributeNames' => [
            '#s' => 'status'
        ],
        'ExpressionAttributeValues' => [
            ':occ'  => ['S' => 'occupied'],
            ':name' => ['S' => $userName],
            ':at'   => ['S' => date('Y-m-d H:i:s')]
        ]
    ]);

    // 3. Kirim notifikasi ke SQS (opsional, tidak hentikan proses jika gagal)
    try {
        require __DIR__ . '/../aws/sqs.php';

        $sqs->sendMessage([
            'QueueUrl'    =>
                'http://smart-parking-localstack:4566/000000000000/parking_notifications',
            'MessageBody' => json_encode([
                'plate_number' => $userName,
                'message'      => "Reservasi slot {$slotId}",
                'time'         => date('Y-m-d H:i:s')
            ])
        ]);

    } catch (Exception $e) {
        // SQS opsional
    }

    echo json_encode([
        'status'  => 'success',
        'message' => "Slot {$slotId} berhasil dipesan atas nama {$userName}."
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal terhubung ke database: ' . $e->getMessage()
    ]);
}