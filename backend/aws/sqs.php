<?php

require __DIR__ . '/../vendor/autoload.php';

use Aws\Sqs\SqsClient;

$sqs = new SqsClient([
    'version' => 'latest',
    'region'  => 'us-east-1',
    'endpoint' => 'http://smart-parking-localstack:4566',
    'credentials' => [
        'key'    => 'test',
        'secret' => 'test'
    ]
]);