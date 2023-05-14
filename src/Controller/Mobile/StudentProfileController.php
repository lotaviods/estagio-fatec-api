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

class StudentProfileController extends AbstractController
{
    #[Route('/api/v1/mobile/student/resume', name: 'save_resume_v1', methods: ['POST'])]
    public function saveResume(Request        $request,
                               MinioService   $minioService,
                               StudentService $studentService
    ): JsonResponse
    {
        $studentId  = $request->get("student_id");
        $file = $request->files->get('file');

        $filename = sprintf('%s.%s', uniqid(), $file->guessExtension());

        $uri = $minioService->uploadFile($file, $filename, 'resumes');

        $studentService->setStudentResumeUri($uri, $studentId);

        return $this->json([]);
    }

    #[Route('/api/v1/mobile/student/profile_picture', name: 'save_student_profile_picture', methods: ['POST'])]
    public function saveStudentProfilePicture(Request        $request,
                               MinioService   $minioService,
                               StudentService $studentService
    ): JsonResponse
    {
        $studentId  = $request->get("student_id");
        $file = $request->files->get('file');

        $filename = sprintf('%s.%s', uniqid(), $file->guessExtension());

        $uri = $minioService->uploadFile($file, $filename, 'pictures');

        $studentService->setStudentProfilePicture($uri, $studentId);

        return $this->json([]);
    }

}