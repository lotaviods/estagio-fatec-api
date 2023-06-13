<?php

namespace App\Controller\Mobile;

use App\Entity\Login;
use App\Entity\Student;
use App\Repository\StudentRepository;
use App\Service\AuthService;
use App\Service\StudentService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class JobStatusNotificationController extends AbstractController
{
    #[Route('/api/v1/mobile/user/notification', name: 'mobile_user_notification_v1', methods: ['GET'])]
    #[IsGranted('ROLE_STUDENT', message: 'You are not allowed to access the mobile notification route.')]
    public function getNotificationFromUser(Request        $request,
                                            StudentService $studentService
    ): JsonResponse
    {
        /** @var Login $user */
        $user = $this->getUser();

        $student = $studentService->getStudentByLogin($user);

        return new JsonResponse($studentService->getStudentAllApplications($student), 200);
    }
}