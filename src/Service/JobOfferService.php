<?php

namespace App\Service;

use App\Entity\JobOffer;
use App\Entity\Student;
use App\Helper\MinioS3Helper;
use App\Repository\JobOfferRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class JobOfferService
{

    private JobOfferRepository $repository;

    private MinioS3Helper $minioS3Helper;

    private TranslatorInterface $translator;

    private RabbitMQService $mqService;

    public function __construct(ManagerRegistry $doctrine,
                                MinioS3Helper $minioS3Helper,
                                TranslatorInterface $translator,
                                RabbitMQService $mQService)
    {
        $this->repository = $doctrine->getManager()->getRepository(JobOffer::class);
        $this->minioS3Helper = $minioS3Helper;
        $this->translator = $translator;
        $this->mqService = $mQService;
    }

    public function getAllStudentApplications(int $jobId, StudentService $studentService): array
    {
        $job = $this->repository->find($jobId);
        if (!$job) throw new BadRequestHttpException();

        $studentArray = [];

        /** @var Student $student */
        foreach ($job->getSubscribedStudents() as $student) {
            $studentArray[] = $studentService->getStudentApplicationsDetailByJob($student, $job);
        }
        $jobArray = $job->toArray();
        $jobArray["company_profile_picture"] = $job->getCompany()?->getLogin()?->getProfilePictureUrl($this->minioS3Helper);

        return [
            "job" => $jobArray,
            "students" => $studentArray
        ];
    }

    function sendNewJobPushNotification(Collection $students): void
    {
        $msg = $this->translator->trans('new_job_offer');

        $messageTemplate = [
            'to' => '%s',
            'message' => $msg,
            'title' => 'Fatec Estágios',
        ];

        foreach ($students as $std) {
            $devices = $std->getLogin()->getDevices()->toArray();
            foreach ($devices as $device) {
                $payload = json_encode($messageTemplate, JSON_UNESCAPED_UNICODE);
                $formattedPayload = sprintf($payload, $device->getToken());
                $this->mqService->publish($formattedPayload, "link-fatec-push");
            }
        }
    }

}