<?php

namespace App\Controller\Mobile;

use App\Service\MinioService;
use App\Service\StudentService;
use Doctrine\Persistence\ManagerRegistry;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StudentResumeController extends AbstractController
{
    #[Route('/api/mobile/student/resume', name: 'save_resume', methods: ['POST'])]
    public function saveResume(Request        $request,
                               MinioService   $minioService,
                               StudentService $studentService
    ): JsonResponse
    {
        $studentId  = $request->get("student_id");
        $file = $request->files->get('file');

        $filename = sprintf('%s.%s', uniqid(), $file->guessExtension());

        $uri = $minioService->upload($file, $filename, 'my-bucket');

        $studentService->setStudentResumeUri($uri, $studentId);

        return $this->json([]);
    }
}