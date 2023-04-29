<?php

namespace App\Controller;

use App\Entity\Administrator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/api/v1/admin', name: 'list_admin_v1', methods: ['GET'])]
    public function getAllAdmins(ManagerRegistry $doctrine): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $doctrine->getManager();

        $repository = $entityManager->getRepository(Administrator::class);

        $repository->findAll();

        $admins = $repository->findAll();
        $adminArray = [];

        foreach ($admins as $admin) {
            $adminArray[] = $admin->toArray();
        }

        return new JsonResponse($adminArray, Response::HTTP_OK, [], false);
    }
}