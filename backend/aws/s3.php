<?php

require __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1',
    'endpoint' => 'http://smart-parking-localstack:4566',
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => 'test',
        'secret' => 'test'
    ]
]);