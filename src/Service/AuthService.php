<?php

namespace App\Service;

use App\Constants\LoginType;
use App\DTO\LoginDTO;
use App\Entity\AccessToken;
use App\Entity\Administrator;
use App\Entity\CompanyAddress;
use App\Entity\Course;
use App\Entity\MasterAdminCreationInvite;
use App\Entity\Company;
use App\Entity\Login;
use App\Entity\Student;
use App\Helper\MinioS3Helper;
use App\Repository\AdministratorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthService
{
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    private UserProviderInterface $userProvider;

    private StudentService $studentService;

    private AdminService $adminService;

    private CompanyService $companyService;

    private MinioS3Helper $profilePictureHelper;

    public function __construct(ManagerRegistry             $doctrine,
                                UserPasswordHasherInterface $passwordHasher,
                                UserProviderInterface       $userProvider,
                                StudentService              $studentService,
                                AdminService                $adminService,
                                CompanyService              $companyService,
                                MinioS3Helper $profilePictureHelper
    )
    {
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->userProvider = $userProvider;
        $this->studentService = $studentService;
        $this->adminService = $adminService;
        $this->companyService = $companyService;
        $this->profilePictureHelper = $profilePictureHelper;
    }

    public function login(LoginDTO $loginDTO): array
    {
        try {
            $loginUser = $this->userProvider->loadUserByIdentifier($loginDTO->getEmail() ?? "");
        } catch (\RuntimeException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if (!$this->passwordHasher->isPasswordValid($loginUser, $loginDTO->getPassword() ?? "")) {
            throw new BadRequestHttpException();
        }

        $userInfo = $this->getUserInformation($loginUser);
        $token = $this->createTokenByUser($loginUser);

        return ["token" => $token->toArray(), "data" => $userInfo];
    }

    private function getUserInformation(Login $user): array|stdClass
    {
        $data = [
            "login_type" => $user->getType(),
            "profile_picture" => $this->profilePictureHelper->getFullUrl($user->getProfilePicture())
        ];

        if ($user->getType() == LoginType::STUDENT) {
            return array_merge($data, $this->getStudentLoginInformation($user));
        }

        if ($user->getType() == LoginType::ADMIN || $user->getType() == LoginType::ADMIN_MASTER) {
            return array_merge($data, $this->getAdminLoginInformation($user));
        }

        if ($user->getType() == LoginType::COMPANY) {
            return array_merge($data, $this->getCompanyInformation($user));
        }
        return new stdClass();
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

    public function createLogin(?string $email,
                                ?string $password,
                                ?string $name,
                                ?int    $type,
                                ?string $profilePicture): Login
    {
        try {
            if (!$password || !$email || !$name || !$type) throw new BadRequestHttpException();

            $login = new Login();
            $login->setEmail($email);

            $hashedPassword = $this->passwordHasher->hashPassword($login, $password);

            $login->setPassword($hashedPassword);
            $login->setName($name);
            $login->SetType($type);

            if ($profilePicture) {
                $path = $this->profilePictureHelper->saveImageBase64($profilePicture);
                if ($path)
                    $login->setProfilePicture($path);
            }

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($login);
            $entityManager->flush();
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return $login;
    }

    public function registerStudent(Student  $student,
                                    LoginDTO $dto,
                                    ?string  $profileImage,
                                    ?string  $courseId,
                                    int      $type = LoginType::STUDENT): void
    {
        /** @var Course $course */
        if (!is_null($courseId))
            $course = $this->doctrine->getRepository(Course::class)->find($courseId);

        if ($course)
            $student->setCourse($course);

        $login = $this->createLogin(
            $dto->getEmail(),
            $dto->getPassword(),
            $dto->getName(),
            $type,
            $profileImage
        );

        $login->setRoles(["ROLE_STUDENT"]);
        $student->setLogin($login);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($login);
        $entityManager->persist($student);
        $entityManager->flush();
    }

    public function registerAdminMaster(string        $token,
                                        LoginDTO      $loginDTO,
                                        Administrator $admin,
                                        ?string       $profileImage): void
    {
        $type = LoginType::ADMIN_MASTER;

        $manager = $this->doctrine->getManager();

        /** @var AdministratorRepository $adminRepo */
        $adminRepo = $manager->getRepository(MasterAdminCreationInvite::class);

        /** @var MasterAdminCreationInvite $invite */
        $invite = $adminRepo->findOneBy(['token' => $token]);

        if (!$invite)
            throw new UnauthorizedHttpException('token');
        if ($invite->isExpired())
            throw new UnauthorizedHttpException('token');

        $this->createAdminLogin($loginDTO, $type, $admin, $profileImage);

        $login = $admin->getLogin();
        $login->addRoles(["ROLE_MASTER"]);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($login);
        $entityManager->flush();
    }

    public function registerAdmin(LoginDTO $loginDTO, Administrator $admin, ?string $profileImage): void
    {
        $type = LoginType::ADMIN;

        $this->createAdminLogin($loginDTO, $type, $admin, $profileImage);
    }

    public function createAdminLogin(LoginDTO      $loginDTO,
                                     int           $type,
                                     Administrator $admin,
                                     ?string       $profileImage): void
    {
        $login = $this->createLogin(
            $loginDTO->getEmail(),
            $loginDTO->getPassword(),
            $loginDTO->getName(),
            $type,
            $profileImage
        );

        $login->setRoles(["ROLE_ADMIN"]);
        $admin->setLogin($login);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($login);
        $entityManager->persist($admin);
        $entityManager->flush();
    }

    public function registerCompany(LoginDTO       $loginDTO,
                                    Company        $company,
                                    CompanyAddress $companyAddress,
                                    ?string        $profileImage): void
    {
        $type = LoginType::COMPANY;

        $login = $this->createLogin(
            $loginDTO->getEmail(),
            $loginDTO->getPassword(),
            $loginDTO->getName(),
            $type,
            $profileImage
        );

        $login->setRoles(["ROLE_COMPANY"]);

        $company->setLogin($login);
        $companyAddress->setCompany($company);
        $entityManager = $this->doctrine->getManager();

        $entityManager->persist($login);
        $entityManager->persist($company);
        $entityManager->persist($companyAddress);
        $entityManager->flush();

    }

    private function getStudentLoginInformation(Login $user): array
    {
        $student = $this->studentService->getStudentByLogin($user);

        if (!$student)
            return [];

        return $this->studentService->getStudentInformation($student);
    }

    private function getAdminLoginInformation(Login $user): array
    {
        $admin = $this->adminService->getAdminByLogin($user);

        if (!$admin) return [];

        return $this->adminService->getAdminInformation($admin);
    }

    private function getCompanyInformation(Login $user): array
    {
        $company = $this->companyService->getCompanyByLogin($user);

        if (!$company) return [];

        return $this->companyService->getCompanyInformation($company);
    }
}
