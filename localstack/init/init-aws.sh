#!/bin/bash

echo "Create S3 Bucket..."
awslocal s3 mb s3://smartparking-bucket

echo "Create DynamoDB Table..."
awslocal dynamodb create-table \
    --table-name parking_slots \
    --attribute-definitions \
        AttributeName=slot_id,AttributeType=S \
    --key-schema \
        AttributeName=slot_id,KeyType=HASH \
    --billing-mode PAY_PER_REQUEST

echo "Insert Parking Slots..."

for row in A B C D
do
    for num in 1 2 3 4
    do
        awslocal dynamodb put-item \
        --table-name parking_slots \
        --item "{
            \"slot_id\":{\"S\":\"${row}${num}\"},
            \"status\":{\"S\":\"available\"}
        }"
    done
done

echo "Create SQS Queue..."
awslocal sqs create-queue \
    --queue-name parking-notification

echo "AWS Resources Ready!"