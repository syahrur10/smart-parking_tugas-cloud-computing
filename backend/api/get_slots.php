<?php

header('Content-Type: application/json');

require __DIR__ . '/../aws/dynamodb.php';

try {

    $result = $dynamodb->scan([

        'TableName' => 'parking_slots'

    ]);

    $slots = [];

    foreach ($result['Items'] as $item) {

        $slots[] = [

            'slot_id' =>
            $item['slot_id']['S'],

            'status' =>
            $item['status']['S']

        ];
    }

    usort($slots, function($a, $b){

        return strcmp(
            $a['slot_id'],
            $b['slot_id']
        );
    });

    echo json_encode($slots);

} catch (Exception $e) {

    echo json_encode([

        'status'  => 'error',

        'message' => $e->getMessage()

    ]);
}