<?php

namespace App\Service;

use App\Entity\JobOffer;
use App\Entity\Student;
use App\Helper\MinioS3Helper;
use App\Repository\JobOfferRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JobOfferService
{

    private JobOfferRepository $repository;

    private MinioS3Helper $minioS3Helper;

    public function __construct(ManagerRegistry $doctrine, MinioS3Helper $minioS3Helper)
    {
        $this->repository = $doctrine->getManager()->getRepository(JobOffer::class);
        $this->minioS3Helper = $minioS3Helper;
    }

    public function getAllStudentApplications(int $jobId, StudentService $studentService): array
    {
        $job = $this->repository->find($jobId);
        if (!$job) throw new BadRequestHttpException();

        $studentArray = [];

        /** @var Student $student */
        foreach ($job->getSubscribedStudents() as $student) {
            $studentArray[] = $studentService->getStudentApplicationsDetail($student, $job);
        }
        $jobArray = $job->toArray();
        $jobArray["company_profile_picture"] = $job->getCompany()?->getLogin()?->getProfilePictureUrl($this->minioS3Helper);

        return [
            "job" => $jobArray,
            "students" => $studentArray
        ];
    }

}