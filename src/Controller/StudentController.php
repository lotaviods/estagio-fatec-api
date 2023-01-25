<?php /** @noinspection ALL */

namespace App\Controller;

use App\Entity\Company;
use App\Entity\JobOffer;
use App\Entity\Student;
use App\Repository\JobOfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use App\Helper\ResponseHelper;

class StudentController extends AbstractController
{
    #[Route('/api/student/detail', name: 'student-detail')]
    public function getStudentDetail(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $studentId = $request->get("student_id");
        $hasStudentId = $studentId !== null;

        if(!$hasStudentId) return ResponseHelper::missingParameterResponse("student_id");

        $repository = $entityManager->getRepository(Student::class);
        /** @var Student $student */
        $student = $repository->find($studentId);

        return new JsonResponse($student->toArray(), Response::HTTP_OK, [], false);;
    }


    #[Route('/api/student/job-offer/subscribe', name: 'subscribe-job-offer')]
    public function subscribeToJobOffer(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $jobId = $request->get("job_id");
        $studentId = $request->get("student_id");

        $hasJobId = $jobId !== null;
        $hasStudentId = $studentId !== null;

        if(!$hasJobId) return ResponseHelper::missingParameterResponse("job_id");
        if(!$hasStudentId) return ResponseHelper::missingParameterResponse("student_id");

        $jobRepository = $entityManager->getRepository(JobOffer::class);
        $repository = $entityManager->getRepository(Student::class);

        /** @var JobOffer $job */
        $job = $jobRepository->find($jobId);

        if($job == null) return new JsonResponse(array('error' => "job offer must exist"));
        if(!$job->isActive()) return new JsonResponse(array('error' => "job offer must be active"));

        /** @var Student $student */
        $student = $repository->find($studentId);
        $student->applyToJobOffer($job);

        $repository->save($student, true);

        return new JsonResponse($student->toArray(), Response::HTTP_OK, [], false);;
    }

    #[Route('/api/student/job-offer/unsubscribe', name: 'unsubscribe-job-offer')]
    public function unsubscribeToJobOffer(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $jobId = $request->get("job_id");
        $studentId = $request->get("student_id");

        $hasJobId = $jobId !== null;
        $hasStudentId = $studentId !== null;

        if(!$hasJobId) return ResponseHelper::missingParameterResponse("job_id");
        if(!$hasStudentId) return ResponseHelper::missingParameterResponse("student_id");

        $jobRepository = $entityManager->getRepository(JobOffer::class);
        $repository = $entityManager->getRepository(Student::class);

        $job = $jobRepository->find($jobId);

        if($job == null) return new JsonResponse(array('error' => "job offer must exist"));

        /** @var Student $student */
        $student = $repository->find($studentId);
        $student->unSubscribeToJobOffer($job);

        $repository->save($student, true);

        return new JsonResponse($student->toArray(), Response::HTTP_OK, [], false);;
    }
}