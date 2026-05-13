<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/../config/database.php';

try {

    // Ambil 30 data upload terbaru dari MySQL sebagai riwayat aktivitas
    $result = $conn->query("
        SELECT
            plate_number,
            vehicle_image,
            created_at
        FROM vehicle_uploads
        ORDER BY created_at DESC
        LIMIT 30
    ");

    $notifications = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'plate_number' => $row['plate_number'],
                'message'      => 'Kendaraan berhasil masuk ke area parkir.',
                'time'         => $row['created_at']
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data'   => $notifications
    ]);

} catch (Exception $e) {

    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}