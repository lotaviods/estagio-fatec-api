<?php

namespace App\Controller;

use App\Entity\Administrator;
use App\Helper\MinioS3Helper;
use App\Helper\ResponseHelper;
use App\Repository\AdministratorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminController extends AbstractController
{
    private MinioS3Helper $profilePictureHelper;
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
        $this->profilePictureHelper = $profilePictureHelper;
        $this->passwordHasher = $passwordHasher;
        $this->userProvider = $userProvider;
        $this->validator = $validator;
        $this->translator = $translator;
    }

    #[Route('/api/v1/admin', name: 'list_admin_v1', methods: ['GET'])]
    public function getAllAdmins(ManagerRegistry $doctrine): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $doctrine->getManager();

        $repository = $entityManager->getRepository(Administrator::class);

        $admins = $repository->findByFilteringLoginId($this->getUser()->getId());
        $adminArray = [];

        /** @var Administrator $admin */
        foreach ($admins as $admin) {
            $currentAdmin = $admin->toArray();
            $currentAdmin["profile_picture"] = $this->profilePictureHelper->getFullUrl($admin->getLogin()?->getProfilePicture());
            $adminArray[] = $currentAdmin;
        }

        return new JsonResponse($adminArray, Response::HTTP_OK, [], false);
    }

    #[Route('/api/v1/admin', name: 'update_admin_v1', methods: ['PUT'])]
    public function updateAdmin(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $id = $request->get("id");
        $newName = $request->get("full_name");
        $newEmail = $request->get("email");
        $newPassword = $request->get("password");
        $newProfilePicture = $request->get("profile_picture");

        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $doctrine->getManager();
        /** @var AdministratorRepository $repository */
        $repository = $entityManager->getRepository(Administrator::class);

        $admin = $repository->findOneBy(['id' => $id]);

        if ($admin == null) return new
        JsonResponse(array('error' => "admin does not exist"),
            Response::HTTP_BAD_REQUEST, [], false);

        $manager = $doctrine->getManager();
        $login = $admin->getLogin();

        if(!is_null($newName))
            $login->setName($newName);

        if (!is_null($newProfilePicture)) {
            if (!empty($newProfilePicture)) {
                $path = $this->profilePictureHelper->saveImageBase64($newProfilePicture);
                if ($path)
                    $login->setProfilePicture($path);
            } else {
                $login->setProfilePicture(null);
            }
        }


        if (!is_null($newPassword)) {
            $pass = $this->passwordHasher->hashPassword($login, $newPassword);
            $this->userProvider->upgradePassword($login, $pass);
        }

        if (!is_null($newEmail)) {
            $login->setEmail($newEmail);
        }

        $manager->persist($login);
        $manager->persist($admin);
        $manager->flush();

        return new JsonResponse([], Response::HTTP_OK, [], false);
    }

    #[Route('/api/v1/admin', name: 'admin-delete_v1', methods: ['DELETE'])]
    public function deleteAdmin(ManagerRegistry $doctrine, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $request->get("id");

        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $entityManager = $doctrine->getManager();
        /** @var AdministratorRepository $repository */

        $repository = $entityManager->getRepository(Administrator::class);
        $admin = $repository->find($id);

        $repository->remove($admin, true);

        return new JsonResponse(array(), Response::HTTP_OK, [], false);
    }
}