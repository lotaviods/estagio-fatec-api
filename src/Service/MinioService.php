<?php

namespace App\Service;

use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Uri;

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

    public function upload(mixed $file, string $fileName, string $bucketName): string
    {
        $this->client->putObject([
            'Bucket' => $bucketName,
            'Key' => $fileName,
            'Body' => fopen($file, 'r'),
            'ACL' => 'public-read',
        ]);

        $url = $this->client->getObjectUrl($bucketName, $fileName);

        return str_replace($this->client->getEndpoint(), '', $url);
    }

    public function getEndpoint(): Uri|string
    {
        return $this->client->getEndpoint();
    }
}