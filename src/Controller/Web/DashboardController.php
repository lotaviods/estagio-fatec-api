<?php

namespace App\Controller\Web;

use App\Service\DashboardService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/api/v1/web/dashboard', name: 'dashboard', methods: ['GET'])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function dashboard(Request          $request,
                              ManagerRegistry  $doctrine,
                              DashboardService $dashboardService
    ): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse($dashboardService->getDashboardInfo($user));
    }
}