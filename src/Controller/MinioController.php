<?php

namespace App\Controller;

use App\Service\AuthService;
use App\Service\MinioService;
use App\Service\StudentService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MinioController extends AbstractController
{
    #[Route('/api/minio', name: 'minio', methods: ['POST'])]
    public function minio(Request         $request,
                          MinioService    $minioService,
                          ManagerRegistry $doctrine
    ): JsonResponse
    {
        // Get the file from the request
        $file = $request->files->get('file');
        $filename = sprintf('%s.%s', uniqid(), $file->guessExtension());

        $minioService->upload($file, $filename, 'my-bucket');
        return $this->json([]);
    }
}