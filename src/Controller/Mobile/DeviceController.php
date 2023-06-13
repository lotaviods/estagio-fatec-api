<?php

namespace App\Controller\Mobile;

use App\Entity\Login;
use App\Service\DeviceService;
use App\Service\StudentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeviceController extends AbstractController
{
    #[Route('/api/v1/mobile/device', name: 'mobile_device_v1', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT', message: 'You are not allowed to access the mobile device route.')]
    public function saveDevice(Request       $request,
                               DeviceService $service
    ): JsonResponse
    {
        $uuid = $request->get("uuid");
        $desc = $request->get("description");
        $device_token = $request->get("token");
        /** @var Login $user */
        $user = $this->getUser();

        $service->saveDevice($user, $uuid, $desc, $device_token);
        return new JsonResponse([], 200);
    }
}