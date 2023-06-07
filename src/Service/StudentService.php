<?php

namespace App\Service;

use App\Entity\AccessToken;
use App\Entity\JobOffer;
use App\Entity\Login;
use App\Entity\Student;
use App\Entity\StudentJobApplicationStatus;
use App\Entity\StudentResume;
use App\Helper\MinioS3Helper;
use App\Repository\StudentJobApplicationStatusRepository;
use App\Repository\StudentRepository;
use App\Repository\StudentResumeRepository;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use stdClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StudentService
{
    private ObjectManager $manager;
    private StudentRepository $repository;
    private StudentResumeRepository $resumeRepository;

    private MinioS3Helper $minioS3Helper;

    public function __construct(ManagerRegistry $doctrine, MinioS3Helper $pictureHelper)
    {
        $this->manager = $doctrine->getManager();
        $this->repository = $doctrine->getManager()->getRepository(Student::class);
        $this->resumeRepository = $doctrine->getManager()->getRepository(StudentResume::class);
        $this->minioS3Helper = $pictureHelper;
    }

    public function setStudentResumeUri(string $uri, string $studentId): void
    {

        $student = $this->repository->findOneBy(["id" => $studentId]);
        $resume = $this->resumeRepository->findOneBy(["student" => $studentId]);

        if ($resume) {
            $this->resumeRepository->remove($resume, true);
        }

        if (!$student) throw new BadRequestHttpException();

        $resume = new StudentResume();

        $resume->setUri($uri);

        $resume->setStudent($student);
        $student->setResume($resume);

        $this->manager->persist($student);
        $this->manager->persist($resume);

        $this->manager->flush();
    }

    public function getStudentByLogin(Login $user): ?Student
    {
        try {
            $student = $this->repository->findByLogin($user);
        } catch (\Exception $e) {
            return null;
        }


        return $student;
    }

    public function getStudentInformation(Student $student): array
    {
        $course = $student->getCourse();

        return [
            "id" => $student->getId(),
            "name" => $student->getName(),
            "course" => $course?->toArray() ?? new stdClass(),
            "ra" => $student->getRa()
        ];
    }

    public function loadApplicationDetail(Student $student, JobOffer $job): StudentJobApplicationStatus
    {
        /** @var StudentJobApplicationStatusRepository $appStatusRepository */
        $appStatusRepository = $this->manager->getRepository(StudentJobApplicationStatus::class);
        $studentApplicationInfo = $appStatusRepository->findOneBy(['student' => $student->getId(), 'jobOffer' => $job->getId()]);

        if (is_null($studentApplicationInfo)) {
            $newAppStatus = new StudentJobApplicationStatus();
            $newAppStatus->setStatus(\JobOfferApplicationStatus::None);
            $newAppStatus->setStudent($student);
            $newAppStatus->setJobOffer($job);
            $newAppStatus->setUpdatedAtNow();
            $appStatusRepository->save($newAppStatus, true);

            return $newAppStatus;
        }
        return $studentApplicationInfo;
    }

    public function getStudentApplicationsDetailByJob(Student $student, JobOffer $job): array
    {
        return [
            "id" => $student->getId(),
            "ra" => $student->getRa(),
            "full_name" => $student->getName(),
            "profile_picture" => $this->minioS3Helper->getFullUrl($student->getLogin()?->getProfilePicture()),
            "resume" => $this->minioS3Helper->getFullUrl($student->getResume()?->getUri()),
            "email" => $student->getLogin()?->getEmail(),
            "application_status" => $this->loadApplicationDetail($student, $job)->getStatus()
        ];
    }

    public function getStudentAllApplications(Student $student): array
    {
        $appStatusRepository = $this->manager->getRepository(StudentJobApplicationStatus::class);
        $statuses = $appStatusRepository->findBy(['student' => $student->getId()]);
        $array = [];

        foreach ($statuses as $status) {
            $jobOffer = $status?->getJobOffer();
            $company = $jobOffer?->getCompany();
            $address = $company?->getAddress();
            if ($student->getAppliedJobOffers()->contains($jobOffer) && !$status->getStatus() == 0)
                $array[] = [
                    "notification_type" => 1,
                    "company_name" => $company?->getName(),
                    "job_title" => $jobOffer?->getTitle(),
                    "status_changed_date" => $status?->getUpdatedAt()?->format(DateTimeInterface::ATOM),
                    "approved" => ($status->getStatus() === 1) ? true : false,
                    "company_profile_picture" => $this->minioS3Helper->getFullUrl($company?->getProfilePicture()),
                    "location" => "{$address?->getCity()}, {$address?->getState()}, {$address?->getCountry()}"
                ];
        }

        return $array;
    }

    public function setStudentProfilePicture(string $uri, mixed $studentId): void
    {
        $student = $this->repository->findOneBy(["id" => $studentId]);

        $student->setProfilePicture($uri);

        $this->manager->persist($student);

        $this->manager->flush();
    }

}