<?php

namespace App\Controller\Web;

use App\DTO\LoginDTO;
use App\Service\AuthService;
use App\Service\StudentService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LoginWebController extends AbstractController
{
    #[Route('/api/v1/web/login', name: 'login', methods: ['POST'])]
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