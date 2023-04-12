<?php

namespace App\Service;

use App\Entity\Administrator;
use App\Entity\Login;
use App\Repository\AdministratorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdminService
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, UserProviderInterface $userProvider)
    {
        $this->doctrine = $doctrine;
    }

    public function getAdminByLogin(Login $user): ?Administrator
    {
        try {
            $manager = $this->doctrine->getManager();

            /** @var AdministratorRepository $administratorRepository */
            $administratorRepository = $manager->getRepository(Administrator::class);

            $student = $administratorRepository->findByLogin($user);
        } catch (\Exception $e) {
            return null;
        }


        return $student;
    }

    public function getAdminInformation(Administrator $admin): array
    {
        return [
            "id" => $admin->getId(),
            "name" => $admin->getName(),
        ];
    }
}