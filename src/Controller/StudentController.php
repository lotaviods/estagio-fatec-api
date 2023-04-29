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
    #[Route('/api/v1/student', name: 'student-list_v1')]
    public function getStudents(ManagerRegistry $doctrine, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $doctrine->getRepository(Student::class);
        /** @var Student $student */

        $students = $repository->findAll();
        $studentArray = [];

        foreach ($students as $student) {
            $studentArray[] = $student->toArray();
        }

        return new JsonResponse($studentArray, Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/student/{student_id}/detail', name: 'student-detail_v1')]
    public function getStudentDetail(ManagerRegistry $doctrine, Request $request): Response
    {
        $studentId = $request->get("student_id");
        $hasStudentId = $studentId !== null;

        if (!$hasStudentId) return ResponseHelper::missingParameterResponse("student_id");

        $repository = $doctrine->getRepository(Student::class);
        /** @var Student $student */
        $student = $repository->find($studentId);

        if (!$student) return new JsonResponse([], Response::HTTP_BAD_REQUEST, [], false);

        $qb = $doctrine->getManager()->createQueryBuilder();

        $studentLogin = $this->getUser();

        if ($student->getLogin()->getId() !== $studentLogin->getId())
            return new JsonResponse([], 403);

        $entityManager = $doctrine->getManager();


        return new JsonResponse($student->toArray(), Response::HTTP_OK, [], false);;
    }


    #[Route('/api/v1/student/job-offer/subscribe', name: 'subscribe-job-offer_v1', methods: ['POST'])]
    public function subscribeToJobOffer(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $jobId = $request->get("job_id");
        $studentId = $request->get("student_id");

        $hasJobId = $jobId !== null;
        $hasStudentId = $studentId !== null;

        if (!$hasJobId) return ResponseHelper::missingParameterResponse("job_id");
        if (!$hasStudentId) return ResponseHelper::missingParameterResponse("student_id");

        $jobRepository = $entityManager->getRepository(JobOffer::class);
        $repository = $entityManager->getRepository(Student::class);

        /** @var JobOffer $job */
        $job = $jobRepository->find($jobId);

        if ($job == null) return new JsonResponse(status: 400, data: array('error' => "job offer must exist"));
        if (!$job->isActive()) return new JsonResponse(array('error' => "job offer must be active"));

        /** @var Student $student */
        $student = $repository->find($studentId);
        $student->applyToJobOffer($job);

        $repository->save($student, true);

        return new JsonResponse([], Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/student/job-offer/unsubscribe', name: 'unsubscribe-job-offer_v1', methods: ['POST'])]
    public function unsubscribeToJobOffer(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $jobId = $request->get("job_id");
        $studentId = $request->get("student_id");

        $hasJobId = $jobId !== null;
        $hasStudentId = $studentId !== null;

        if (!$hasJobId) return ResponseHelper::missingParameterResponse("job_id");
        if (!$hasStudentId) return ResponseHelper::missingParameterResponse("student_id");

        $jobRepository = $entityManager->getRepository(JobOffer::class);
        $repository = $entityManager->getRepository(Student::class);

        $job = $jobRepository->find($jobId);

        if ($job == null) return new JsonResponse(status: 400, data: array('error' => "job offer must exist"));

        /** @var Student $student */
        $student = $repository->find($studentId);
        $student->unSubscribeToJobOffer($job);

        $repository->save($student, true);

        return new JsonResponse([], Response::HTTP_OK, [], false);;
    }
}