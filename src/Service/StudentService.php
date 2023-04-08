<?php

namespace App\Service;

use App\Entity\AccessToken;
use App\Entity\Login;
use App\Entity\Student;
use App\Repository\StudentRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class StudentService
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, UserProviderInterface $userProvider)
    {
        $this->doctrine = $doctrine;
    }

    public function getStudentByLogin(Login $user): ?Student
    {
        try {
            $manager = $this->doctrine->getManager();

            /** @var StudentRepository $studentRepo */
            $studentRepo = $manager->getRepository(Student::class);

            $student = $studentRepo->findByLogin($user);
        } catch (\Exception $e) {
            return null;
        }


        return $student;
    }

}