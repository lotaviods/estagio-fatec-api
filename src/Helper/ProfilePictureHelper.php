<?php

namespace App\Helper;

use App\Service\MinioService;

class ProfilePictureHelper
{
    private MinioService $minioService;

    public function __construct(MinioService $minioService)
    {
        $this->minioService = $minioService;
    }

    public function getFullProfileUrl(?string $profileUri): ?string
    {
        if(!$profileUri) return null;
        return "{$this->minioService->getEndpoint()}$profileUri";
    }
}