<?php

namespace App\Service;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Uri;

class MinioService
{
    private S3Client $client;

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

    public function uploadFile(mixed $file, string $fileName, string $bucketName): string
    {
        $this->createBucketIfNotExist($bucketName);

        $this->client->putObject([
            'Bucket' => $bucketName,
            'Key' => $fileName,
            'Body' => fopen($file, 'r'),
            'ACL' => 'public-read',
        ]);

        $url = $this->client->getObjectUrl($bucketName, $fileName);

        return str_replace($this->client->getEndpoint(), '', $url);
    }

    public function upload(mixed $file, string $fileName, string $bucketName): string
    {
        $this->createBucketIfNotExist($bucketName);

        $this->client->putObject([
            'Bucket' => $bucketName,
            'Key' => $fileName,
            'Body' => $file,
            'ACL' => 'public-read',
            'ContentType' => 'image/png'
        ]);

        $url = $this->client->getObjectUrl($bucketName, $fileName);

        return str_replace($this->client->getEndpoint(), '', $url);
    }

    private function createBucketIfNotExist(string $bucketName): void
    {
        // Check if the bucket already exists
        try {
            $result = $this->client->headBucket([
                'Bucket' => $bucketName,
            ]);
        } catch (S3Exception) {
            $this->client->createBucket([
                'Bucket' => $bucketName
            ]);
            $this->client->putBucketPolicy([
                'Bucket' => $bucketName,
                'Policy' => json_encode([
                    'Version' => '2012-10-17',
                    'Statement' => [
                        [
                            'Sid' => 'PublicReadGetObject',
                            'Effect' => 'Allow',
                            'Principal' => '*',
                            'Action' => 's3:GetObject',
                            'Resource' => "arn:aws:s3:::$bucketName/*",
                        ],
                    ],
                ])
            ]);
        }
    }
    public function getEndpoint(): Uri|string
    {
        return $this->client->getEndpoint();
    }
}