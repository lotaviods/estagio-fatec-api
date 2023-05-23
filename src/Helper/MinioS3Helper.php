<?php

namespace App\Helper;

use App\Service\MinioService;

class MinioS3Helper
{
    private MinioService $minioService;

    public function __construct(MinioService $minioService)
    {
        $this->minioService = $minioService;
    }

    public function getFullUrl(?string $uri): ?string
    {
        if (!$uri) return null;
        return "{$this->minioService->getEndpoint()}$uri";
    }

    public function saveImageBase64(string $base64, ?string $bucket = null): ?string
    {
        try {
            $array = explode(",", $base64);
            $imageData = base64_decode(end($array));

            $name = uniqid('image_');

            $split = explode(';base64', $base64);

            $type = explode('/', $split[0])[1];

            return $this->minioService->upload(
                $imageData,
                $name . ".$type",
                $bucket ?? 'pictures'
            );
        } catch (\Exception) {
            return null;
        }

    }
}