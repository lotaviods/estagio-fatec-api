<?php

namespace App\Controller\Mobile;

use App\DTO\LoginDTO;
use App\Service\AuthService;
use App\Service\StudentService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LoginMobileController extends AbstractController
{
    #[Route('/api/v1/mobile/login', name: 'login_mobile_v1', methods: ['POST'])]
    public function login(Request         $request,
                          AuthService     $authService,
                          ManagerRegistry $doctrine,
                          StudentService  $studentService
    ): JsonResponse
    {
        $loginDTO = LoginDTO::fromRequest($request);
        $loginData = $authService->login($loginDTO);

        return $this->json(
            $loginData,
        );
    }
}