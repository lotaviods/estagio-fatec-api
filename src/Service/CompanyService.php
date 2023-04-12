<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Login;
use App\Repository\CompanyRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CompanyService
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, UserProviderInterface $userProvider)
    {
        $this->doctrine = $doctrine;
    }

    public function getCompanyByLogin(Login $user): ?Company
    {
        try {
            $manager = $this->doctrine->getManager();

            /** @var CompanyRepository $companyRepository */
            $companyRepository = $manager->getRepository(Company::class);

            $company = $companyRepository->findByLogin($user);
        } catch (\Exception $e) {
            return null;
        }


        return $company;
    }

    public function getCompanyInformation(Company $company): array
    {
        return [
            "id" => $company->getId(),
            "name" => $company->getName(),
        ];
    }
}