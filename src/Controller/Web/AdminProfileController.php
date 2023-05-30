<?php

namespace App\Controller\Web;

use App\Entity\Administrator;
use App\Helper\MinioS3Helper;
use App\Repository\AdministratorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminProfileController extends AbstractController
{
    private MinioS3Helper $profilePictureHelper;

    public function __construct(MinioS3Helper $profilePictureHelper)
    {
        $this->profilePictureHelper = $profilePictureHelper;
    }

    #[Route('/api/v1/web/admin/profiles', name: 'profiles_admin_v1', methods: ['GET'])]
    public function getAdminProfiles(ManagerRegistry $doctrine): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $doctrine->getManager();

        /** @var AdministratorRepository $repository */
        $repository = $entityManager->getRepository(Administrator::class);

        $admins = $repository->findByAdminFilteringId($this->getUser()->getId());
        $adminArray = [];

        /** @var Administrator $admin */
        foreach ($admins as $admin) {
            $currentAdmin = $admin->toArray();
            $currentAdmin["profile_picture"] = $this->profilePictureHelper->getFullUrl($admin->getLogin()?->getProfilePicture());
            $adminArray[] = $currentAdmin;
        }

        return new JsonResponse($adminArray, 200);
    }
}