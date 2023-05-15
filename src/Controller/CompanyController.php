<?php

namespace App\Controller;

use App\Entity\Company;
use App\Helper\ProfilePictureHelper;
use App\Helper\ResponseHelper;
use App\Repository\CompanyRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    private ProfilePictureHelper $profilePictureHelper;

    public function __construct(ProfilePictureHelper $profilePictureHelper)
    {
        $this->profilePictureHelper = $profilePictureHelper;
    }

    #[Route('/api/v1/company', name: 'company_v1', methods: ['GET'])]
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

        return new JsonResponse($companiesArray, Response::HTTP_OK, [], false);
    }

    #[Route('/api/v1/company/{id}', name: 'company_by_id_v1', methods: ['GET'])]
    public function getCompanyById(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $request->get("id");

        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $entityManager = $doctrine->getManager();
        /** @var CompanyRepository $repository */

        $repository = $entityManager->getRepository(Company::class);
        $company = $repository->find($id);

        $array = $company->toArray();
        $array["profile_picture"] = $this->profilePictureHelper->getFullProfileUrl($company->getProfilePicture());

        return new JsonResponse($array, Response::HTTP_OK, [], false);
    }

    #[Route('/api/v1/company', name: 'delete_company_v1', methods: ['DELETE'])]
    public function deleteCompany(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $request->get("id");

        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $entityManager = $doctrine->getManager();
        /** @var CompanyRepository $repository */

        $repository = $entityManager->getRepository(Company::class);
        $course = $repository->find($id);

        $repository->remove($course, true);

        return new JsonResponse(array(), Response::HTTP_OK, [], false);
    }
}