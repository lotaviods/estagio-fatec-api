<?php

namespace App\Service;

use Aws\S3\S3Client;

class MinioService
{
    private $client;

    public function __construct(string $endpoint, string $accessKey, string $secretKey)
    {
        $this->client = new S3Client(
            [
                'version' => 'latest',
                'region' => 'us-west-2', // Set your region here
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);

    }

    public function upload(mixed $file, string $fileName, string $bucketName): void
    {
        $this->client->putObject([
            'Bucket' => $bucketName,
            'Key' => $fileName,
            'Body' => fopen($file, 'r'),
        ]);
    }
}