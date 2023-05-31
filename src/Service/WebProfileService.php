<?php

namespace App\Service;

use App\Constants\LoginType;
use App\Entity\Administrator;
use App\Entity\Company;
use App\Entity\Login;
use App\Helper\MinioS3Helper;
use App\Repository\AdministratorRepository;
use App\Repository\CompanyRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WebProfileService
{
    private ManagerRegistry $doctrine;
    private MinioS3Helper $profilePictureHelper;

    public function __construct(ManagerRegistry $doctrine, MinioS3Helper $profilePictureHelper)
    {
        $this->doctrine = $doctrine;
        $this->profilePictureHelper = $profilePictureHelper;
    }

    public function getLoginProfileInfo(Login $user): array
    {
        $info = [];
        $roles = $user->getRoles();

        if(in_array('ROLE_ADMIN', $roles)) {
            /** @var AdministratorRepository $repository */
            $repository = $this->doctrine->getRepository(Administrator::class);
            $info = $repository->findOneByLoginId($user->getId())->toArray();
            $info['profile_picture'] = $user->getProfilePicture();
        }
        if(in_array('ROLE_COMPANY', $roles)) {
            /** @var CompanyRepository $repository */
            $repository = $this->doctrine->getRepository(Company::class);
            $info = $repository->findOneByLoginId($user->getId())?->toArray();
            $info['profile_picture'] = $user->getProfilePicture();
        }

        if (array_key_exists('profile_picture', $info)) {
            $info["profile_picture"] = $this->profilePictureHelper->getFullUrl($info["profile_picture"]);
        }

        return $info;
    }
}