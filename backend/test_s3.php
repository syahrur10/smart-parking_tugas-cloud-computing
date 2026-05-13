<?php

require __DIR__ . '/aws/s3.php';

$result = $s3->putObject([
    'Bucket' => 'smartparking-bucket',
    'Key'    => 'test.txt',
    'Body'   => 'Hello Smart Parking'
]);

echo "<pre>";
print_r($result);