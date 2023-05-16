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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CompanyController extends AbstractController
{
    private ProfilePictureHelper $profilePictureHelper;
    private UserPasswordHasherInterface $passwordHasher;

    private UserProviderInterface $userProvider;

    public function __construct(ProfilePictureHelper        $profilePictureHelper,
                                UserPasswordHasherInterface $passwordHasher,
                                UserProviderInterface       $userProvider)
    {
        $this->profilePictureHelper = $profilePictureHelper;
        $this->passwordHasher = $passwordHasher;
        $this->userProvider = $userProvider;
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

    #[Route('/api/v1/company', name: 'company-update_v1', methods: ['PUT'])]
    public function updateCompany(ManagerRegistry $doctrine, Request $request): Response
    {
        $manager = $doctrine->getManager();

        /** TODO: Refactor and set most of this in services */
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $request->get("id");
        $isActive = $request->get("is_active");
        $newName = $request->get("name");
        $newEmail = $request->get("email");
        $newPassword = $request->get("password");
        $newProfilePicture = $request->get("profile_picture");

        if ($id == null) return ResponseHelper::missingParameterResponse("id");


        /** @var CompanyRepository $repository */
        $repository = $doctrine->getRepository(Company::class);

        /** @var Company $company */
        $company = $repository->find($id);

        if (!$company)
            throw new BadRequestHttpException();

        if ($newProfilePicture) {
            $path = $this->profilePictureHelper->saveImageBase64($newProfilePicture);
            if ($path)
                $company->setProfilePicture($path);
        }

        if ($isActive != null) {
            $company->setActive($isActive);
        }

        if ($newPassword) {
            $pass = $this->passwordHasher->hashPassword($company->getLogin(), $newPassword);
            $this->userProvider->upgradePassword($company->getLogin(), $pass);
        }

        if ($newEmail) {
            $company->getLogin()?->setEmail($newEmail);
        }

        $login = $company->getLogin()

        if ($newName) {
            $login?->setName($newName);
        }
        
        $manager->persist($login);
        $manager->persist($company);
        $manager->flush();

        return new JsonResponse([], Response::HTTP_OK, [], false);;
    }
}