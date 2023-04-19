<?php

namespace App\Controller;

use App\Entity\Company;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    #[Route('/api/v1/company', name: 'company_v1')]
    public function getAllCompanies(ManagerRegistry $doctrine): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $doctrine->getManager();

        $repository = $entityManager->getRepository(Company::class);

        $repository->findAll();

        $companies = $repository->findAll();
        $companiesArray = [];

        foreach ($companies as $company) {
            $companiesArray[] = $company->toArray();
        }

        return new JsonResponse($companiesArray, Response::HTTP_OK, [], false);;
    }
}