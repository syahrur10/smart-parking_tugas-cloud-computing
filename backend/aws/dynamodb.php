<?php

require __DIR__ . '/../vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;

$dynamodb = new DynamoDbClient([
    'version' => 'latest',
    'region'  => 'us-east-1',
    'endpoint' => 'http://smart-parking-localstack:4566',
    'credentials' => [
        'key'    => 'test',
        'secret' => 'test'
    ]
]);