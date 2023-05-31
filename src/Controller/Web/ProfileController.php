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
use PhpParser\JsonDecoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function MongoDB\BSON\fromJSON;

class ProfileController extends AbstractController
{
    #[Route('/api/v1/web/profile', name: 'get_web_profile_v1', methods: ['GET'])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getProfile(WebProfileService $webProfileService): JsonResponse
    {
        /** @var Login $user */
        $user = $this->getUser();

        return new JsonResponse($webProfileService->getLoginProfileInfo($user), 200);
    }

    #[Route('/api/v1/web/profile', name: 'update_web_profile_v1', methods: ['PUT'])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function updateProfile(Request $request): JsonResponse
    {
        $response = null;
        /** @var Login $user */
        $user = $this->getUser();
        $roles = $user->getRoles();

        $parameters = $request->query->all();

        if (in_array('ROLE_COMPANY', $roles)) {
            $response = $this->forward('App\Controller\CompanyController::updateCompany', $parameters);
        }
        if (in_array('ROLE_ADMIN', $roles)) {
            $response = $this->forward('App\Controller\AdminController::updateAdmin', $parameters);
        }

        return new JsonResponse((new JsonDecoder)->decode($response->getContent()), 200);
    }
}