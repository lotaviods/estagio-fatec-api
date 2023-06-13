<?php

namespace App\Controller\Mobile;

use App\Entity\Login;
use App\Service\StudentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeviceController extends AbstractController
{
    #[Route('/api/v1/mobile/device', name: 'mobile_device_v1', methods: ['POST'])]
    public function saveDevice(Request $request): JsonResponse
    {
        return new JsonResponse([], 200);
    }
}