<?php

namespace App\Controller;

use App\Constraints\CompanyAddressConstraints;
use App\Constraints\CompanyConstraints;
use App\DTO\LoginDTO;
use App\Entity\Company;
use App\Entity\CompanyAddress;
use App\Form\Company\CompanyAddressForm;
use App\Helper\MinioS3Helper;
use App\Helper\ResponseHelper;
use App\Mapper\CompanyAddressMapper;
use App\Mapper\CompanyMapper;
use App\Repository\CompanyRepository;
use App\Service\AuthService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyController extends AbstractController
{
    private MinioS3Helper $pictureHelper;
    private UserPasswordHasherInterface $passwordHasher;

    private UserProviderInterface $userProvider;

    private ValidatorInterface $validator;

    private TranslatorInterface $translator;

    public function __construct(MinioS3Helper               $profilePictureHelper,
                                UserPasswordHasherInterface $passwordHasher,
                                UserProviderInterface       $userProvider,
                                ValidatorInterface          $validator,
                                TranslatorInterface         $translator)
    {
        $this->pictureHelper = $profilePictureHelper;
        $this->passwordHasher = $passwordHasher;
        $this->userProvider = $userProvider;
        $this->validator = $validator;
        $this->translator = $translator;
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
        $array["profile_picture"] = $this->pictureHelper->getFullUrl($company->getProfilePicture());

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
        $newName = $request->get("full_name");
        $newEmail = $request->get("email");
        $newPassword = $request->get("password");
        $newProfilePicture = $request->get("profile_picture");


        if ($id == null) return ResponseHelper::missingParameterResponse("id");


        /** @var CompanyRepository $repository */
        $repository = $doctrine->getRepository(Company::class);
        $repositoryAddress = $doctrine->getRepository(CompanyAddress::class);

        /** @var Company $company */
        $company = $repository->find($id);

        if (!$company)
            throw new BadRequestHttpException();

        /** @var CompanyAddress $address */
        $address = $repositoryAddress->findOneBy(['company' => $company->getId()]);
        $newAddress = CompanyAddressMapper::fromRequestToAddress($request, $address);

        if (!is_null($newProfilePicture)) {
            if (!empty($newProfilePicture)) {
                $path = $this->pictureHelper->saveImageBase64($newProfilePicture);
                if ($path)
                    $company->setProfilePicture($path);
            } else {
                $company->setProfilePicture(null);
            }
        }

        if ($newPassword) {
            $pass = $this->passwordHasher->hashPassword($company->getLogin(), $newPassword);
            $this->userProvider->upgradePassword($company->getLogin(), $pass);
        }

        if ($newEmail) {
            $company->getLogin()?->setEmail($newEmail);
        }

        $login = $company->getLogin();

        if ($newName) {
            $login?->setName($newName);
        }


        $manager->persist($login);
        $manager->persist($company);
        $manager->persist($newAddress);
        $manager->flush();

        return new JsonResponse([], Response::HTTP_OK, [], false);;
    }

    #[Route('api/v1/register/company', name: 'companyRegister_v1', methods: ['POST'])]
    public function companyRegister(Request $request, AuthService $service): JsonResponse
    {
        $data = $request->request->all();

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $constraints = CompanyConstraints::getConstraints($this->translator);
        $constraintsAddress = CompanyAddressConstraints::getConstraints($this->translator);

        $companyViolations = $this->validator->validate($data, $constraints);
        $addressViolations = $this->validator->validate($data, $constraintsAddress);

        if (count($companyViolations) > 0 || count($addressViolations) > 0) {
            $errors = [];
            foreach ($companyViolations as $v) {
                $errors[] = str_replace("\"", "", $v->getMessage());
            }
            foreach ($addressViolations as $v) {
                $errors[] = str_replace("\"", "", $v->getMessage());
            }
            return new JsonResponse([$this->translator->trans('errors') => $errors], Response::HTTP_BAD_REQUEST);

        }

        $service->registerCompany(
            loginDTO: LoginDTO::fromRequest($request),
            company: CompanyMapper::fromRequest(),
            companyAddress: CompanyAddressMapper::fromRequest($request),
            profileImage: $request->get("profile_picture")
        );

        return $this->json([], Response::HTTP_CREATED);
    }
}