<?php

namespace App\Service;

use App\Constants\LoginType;
use App\DTO\LoginDTO;
use App\Entity\AccessToken;
use App\Entity\Administrator;
use App\Entity\AdminCreationInvite;
use App\Entity\Company;
use App\Entity\Login;
use App\Entity\Student;
use App\Repository\AdministratorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthService
{
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    private UserProviderInterface $userProvider;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, UserProviderInterface $userProvider)
    {
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->userProvider = $userProvider;
    }

    public function login(LoginDTO $loginDTO): AccessToken
    {
        try {
            $loginUser = $this->userProvider->loadUserByIdentifier($loginDTO->getEmail() ?? "");
        } catch (\RuntimeException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if (!$this->passwordHasher->isPasswordValid($loginUser, $loginDTO->getPassword() ?? "")) {
            throw new BadRequestHttpException();
        }

        return $this->createTokenByUser($loginUser);
    }

    private function createTokenByUser(Login $user): AccessToken
    {
        $token = bin2hex(random_bytes(32));

        // Create a new AccessToken entity
        $accessToken = new AccessToken();
        $accessToken->setAccessToken($token);
        $accessToken->setUser($user);

        $date = new \DateTime();
        $date->add(new \DateInterval('P1D')); // add 1 day

        $accessToken->setExpiresAt($date);

        // Save the access token to the database
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($accessToken);
        $entityManager->flush();

        return $accessToken;
    }

    public function createLogin(?string $email, ?string $password, ?string $name, ?int $type): Login
    {
        try {
            if (!$password || !$email || !$name || !$type) throw new BadRequestHttpException();

            $login = new Login();
            $login->setEmail($email);

            $hashedPassword = $this->passwordHasher->hashPassword($login, $password);

            $login->setPassword($hashedPassword);
            $login->setName($name);
            $login->SetType($type);

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($login);
            $entityManager->flush();
        } catch (\Exception) {
            throw new BadRequestHttpException();
        }

        return $login;
    }

    public function registerStudent(Student $student, LoginDTO $dto, int $type = LoginType::STUDENT): void
    {
        //TODO Add more types of register not only student

        $login = $this->createLogin(
            $dto->getEmail(),
            $dto->getPassword(),
            $dto->getName(),
            $type,
        );

        $login->setRoles(["ROLE_STUDENT"]);
        $student->setLogin($login);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($login);
        $entityManager->persist($student);
        $entityManager->flush();
    }

    public function registerInvitedAdmin(string $token, LoginDTO $loginDTO, Administrator $admin): void
    {
        $type = LoginType::ADMIN;

        $manager = $this->doctrine->getManager();

        /** @var AdministratorRepository $adminRepo */
        $adminRepo = $manager->getRepository(AdminCreationInvite::class);

        /** @var AdminCreationInvite $invite */
        $invite = $adminRepo->findOneBy(['token' => $token]);

        if (!$invite)
            throw new UnauthorizedHttpException('token');
        if ($invite->isExpired())
            throw new UnauthorizedHttpException('token');

        $this->createAdminLogin($loginDTO, $type, $admin);
    }

    public function registerAdmin(LoginDTO $loginDTO, Administrator $admin): void
    {
        $type = LoginType::ADMIN;

        $this->createAdminLogin($loginDTO, $type, $admin);
    }

    public function createAdminLogin(LoginDTO $loginDTO, int $type, Administrator $admin): void
    {
        $login = $this->createLogin(
            $loginDTO->getEmail(),
            $loginDTO->getPassword(),
            $loginDTO->getName(),
            $type,
        );

        $login->setRoles(["ROLE_ADMIN"]);
        $admin->setLogin($login);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($login);
        $entityManager->persist($admin);
        $entityManager->flush();
    }

    public function registerCompany(LoginDTO $loginDTO, Company $company): void
    {
        $type = LoginType::COMPANY;

        $login = $this->createLogin(
            $loginDTO->getEmail(),
            $loginDTO->getPassword(),
            $loginDTO->getName(),
            $type,
        );

        $login->setRoles(["ROLE_COMPANY"]);

        $company->setLogin($login);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($login);
        $entityManager->persist($company);
        $entityManager->flush();

    }
}
