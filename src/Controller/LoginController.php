<?php

namespace App\Controller;

use App\Constants\LoginType;
use App\Entity\AccessToken;
use App\Entity\Course;
use App\Entity\Login;
use App\Entity\Student;
use Doctrine\Persistence\ManagerRegistry;
use mysql_xdevapi\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, UserProviderInterface $userProvider, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine)
    {

        $username = $request->request->get('username');
        $password = $request->request->get('password');

        /** @var Login $login */
        $login = $userProvider->loadUserByIdentifier($username);

        if (!$login) {
            throw $this->createNotFoundException();
        }

        if (!$passwordHasher->isPasswordValid($login, $password)) {
            throw new BadCredentialsException();
        }

        $accessToken = $this->createTokenByUser($doctrine, $login);

        return $this->json(['token' => $accessToken->getAccessToken()]);
    }

    #[Route('api/student/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine): JsonResponse
    {
        $username = $request->get("username");
        $password = $request->get("password");
        $courseId = $request->get("course_id");
        $ra = $request->get("ra");
        $name = $request->get("full_name");
        $email = $request->get("email");


        try {
            $login = $this->createLogin($doctrine,
                $username,
                $password,
                $passwordHasher,
                LoginType::STUDENT
            );

            $login->setRoles(["ROLE_STUDENT"]);

            $accessToken = $this->createTokenByUser($doctrine, $login);
            $student = new Student();

            $course = $doctrine->getRepository(Course::class)->find($courseId);

            $student->setLogin($login);
            $student->setName($name);
            $student->setCourse($course);
            $student->setRa($ra);
            $student->setEmail($email);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($student);
            $entityManager->flush();

        } catch (Exception) {
            return $this->json(['message' => 'An Unexpected error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['message' => 'User created successfully', 'token' => $accessToken->getAccessToken()], Response::HTTP_CREATED);
    }

    private function createTokenByUser(ManagerRegistry $doctrine, Login $user): AccessToken
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
        $entityManager = $doctrine->getManager();
        $entityManager->persist($accessToken);
        $entityManager->flush();

        return $accessToken;
    }

    private function createLogin(
        ManagerRegistry $doctrine,
        string          $username,
        string          $password,
                        $passwordHasher,
        int             $loginType,
    ): Login
    {
        $login = new Login();

        // Encode the password
        $hashedPassword = $passwordHasher->hashPassword(
            $login,
            $password
        );

        $login->setUsername($username);
        $login->setPassword($hashedPassword);
        $login->setType($loginType);

        // Persist the user to the database
        $entityManager = $doctrine->getManager();
        $entityManager->persist($login);
        $entityManager->flush();

        return $login;
    }
}
