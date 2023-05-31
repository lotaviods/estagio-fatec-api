<?php

namespace App\Controller\Web;

use App\Constants\LoginType;
use App\Entity\Administrator;
use App\Entity\Login;
use App\Helper\MinioS3Helper;
use App\Repository\AdministratorRepository;
use App\Service\WebProfileService;
use AWS\CRT\Log;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/api/v1/web/profile', name: 'web_profile_v1', methods: ['GET'])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getProfile(WebProfileService $webProfileService): JsonResponse
    {
        /** @var Login $user */
        $user = $this->getUser();

        return new JsonResponse($webProfileService->getLoginProfileInfo($user), 200);
    }
}