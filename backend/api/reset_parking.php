<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Hanya izinkan POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../aws/dynamodb.php';

$errors = [];

// 1. Hapus semua data di vehicle_uploads (MySQL)
$deleteVehicles = $conn->query("DELETE FROM vehicle_uploads");
if (!$deleteVehicles) {
    $errors[] = 'Gagal hapus vehicle_uploads: ' . $conn->error;
}

// 2. Reset semua slot di DynamoDB ke 'available'
try {
    // Scan semua slot
    $result = $dynamodb->scan([
        'TableName' => 'parking_slots'
    ]);

    foreach ($result['Items'] as $item) {
        $slotId = $item['slot_id']['S'];

        // Update status ke available, hapus vehicle_id jika ada
        $dynamodb->updateItem([
            'TableName' => 'parking_slots',
            'Key' => [
                'slot_id' => ['S' => $slotId]
            ],
            'UpdateExpression' => 'SET #s = :available REMOVE vehicle_id',
            'ExpressionAttributeNames' => [
                '#s' => 'status'
            ],
            'ExpressionAttributeValues' => [
                ':available' => ['S' => 'available']
            ]
        ]);
    }

} catch (Exception $e) {
    $errors[] = 'Gagal reset DynamoDB: ' . $e->getMessage();
}

if (empty($errors)) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Semua data parkir berhasil direset.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => implode(' | ', $errors)
    ]);
}