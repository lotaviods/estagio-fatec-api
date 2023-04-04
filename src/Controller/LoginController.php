<?php

namespace App\Controller;

use App\DTO\LoginDTO;
use App\Mapper\StudentMapper;
use App\Service\AuthService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, AuthService $service): JsonResponse
    {
        $loginDTO = LoginDTO::fromRequest($request);
        $accessToken = $service->login($loginDTO);

        return $this->json(['token' => $accessToken->getAccessToken()]);
    }

    #[Route('api/student/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, AuthService $service): JsonResponse
    {
        //TODO make link student to class
        $accessToken = $service->registerStudent(StudentMapper::fromRequest($request), LoginDTO::fromRequest($request));

        return $this->json(['message' => 'User created successfully', 'token' => $accessToken->getAccessToken()], Response::HTTP_CREATED);
    }
}
