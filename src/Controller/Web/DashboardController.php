<?php

namespace App\Controller\Web;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/api/v1/web/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(Request         $request,
                              ManagerRegistry $doctrine
    ): JsonResponse
    {
        return new JsonResponse([]);
    }
}