<?php /** @noinspection ALL */

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Course;
use App\Entity\JobOffer;
use App\Entity\Student;
use App\Entity\StudentJobApplicationStatus;
use App\Helper\MinioS3Helper;
use App\Repository\JobOfferRepository;
use App\Repository\StudentJobApplicationStatusRepository;
use App\Repository\StudentRepository;
use App\Service\StudentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use App\Helper\ResponseHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class StudentController extends AbstractController
{
    private MinioS3Helper $profilePictureHelper;
    private UserProviderInterface $userProvider;

    private UserPasswordHasherInterface $passwordHasher;

    private TranslatorInterface $translator;

    private StudentService $studentService;

    public function __construct(
        MinioS3Helper               $profilePictureHelper,
        UserProviderInterface       $userProvider,
        TranslatorInterface         $translator,
        UserPasswordHasherInterface $passwordHasher,
        StudentService              $studentService
    )
    {
        $this->userProvider = $userProvider;
        $this->profilePictureHelper = $profilePictureHelper;
        $this->translator = $translator;
        $this->passwordHasher = $passwordHasher;
        $this->studentService = $studentService;
    }

    #[Route('/api/v1/student', name: 'student-list_v1', methods: ['GET'])]
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

    #[Route('/api/v1/student', name: 'student-update_v1', methods: ['PUT'])]
    public function updateStudent(ManagerRegistry $doctrine, Request $request): Response
    {
        $manager = $doctrine->getManager();

        /** TODO: Refactor and set most of this in services */

        $newCourseId = $request->get("course_id");
        $newName = $request->get("full_name");
        $newEmail = $request->get("email");
        $newPassword = $request->get("password");
        $newRa = $request->get("ra");

        $id = $request->get("id");

        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        /** @var StudentRepository $repository */
        $repository = $doctrine->getRepository(Student::class);

        /** @var Student $student */
        $student = $repository->find($id);

        if (!$student)
            throw new BadRequestHttpException();

        if ($newRa) {
            $student->setRa($newRa);
        }

        if ($newPassword) {
            $pass = $this->passwordHasher->hashPassword($student->getLogin(), $newPassword);
            $this->userProvider->upgradePassword($student->getLogin(), $pass);
        }

        if ($newEmail) {
            $student->getLogin()?->setEmail($newEmail);
        }

        if ($newName) {
            $student->getLogin()?->setName($newName);
        }
        if ($newCourseId) {
            /** @var Course $course */
            $course = $doctrine->getRepository(Course::class)->find($newCourseId);

            if (!$course) return ResponseHelper::entityNotFoundBadRequestResponse("course", $translator);
            $student->setCourse($course);
        }

        $manager->persist($student);
        $manager->flush();

        return new JsonResponse([], Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/student/{student_id}/job/{job_id}/application-status', name: 'student-update-application-status_v1', methods: ['PUT'])]
    public function updateStudentJobApplicationStatus(ManagerRegistry $doctrine, Request $request): Response
    {
        $newStatus = (int)$request->get("status");
        $job_id = $request->get("job_id");
        $student_id = $request->get("student_id");

        if ($job_id == null) return ResponseHelper::missingParameterResponse("job_id");
        if ($student_id == null) return ResponseHelper::missingParameterResponse("student_id");

        $entityManager = $doctrine->getManager();
        /** @var StudentJobApplicationStatusRepository $repository */

        $repository = $entityManager->getRepository(StudentJobApplicationStatus::class);
        $applicationStatus = $repository->findOneBy(['student' => $student_id, 'jobOffer' => $job_id]);

        if (is_null($applicationStatus)) {
            /** @var JobOfferRepository $jobOfferRepository */
            $jobOfferRepository = $entityManager->getRepository(JobOffer::class);
            /** @var StudentRepository $studentRepostitory */
            $studentRepostitory = $entityManager->getRepository(Student::class);

            $student = $studentRepostitory->findOneBy(['id' => $student_id]);
            $jobOffer = $jobOfferRepository->findOneBy(['id' => $job_id]);

            if (is_null($student) || is_null($jobOffer))
                throw new  BadRequestHttpException();
            if (!$student->getAppliedJobOffers()->contains($jobOffer))
                throw new  BadRequestHttpException();

            $applicationStatus = $this->studentService->loadApplicationDetail($student, $jobOffer);
        }

        if (!is_null($newStatus) && is_numeric($newStatus)) {
            $applicationStatus?->setStatus($newStatus);
            $applicationStatus->setUpdatedAtNow();
        }

        $entityManager->persist($applicationStatus);
        $entityManager->flush();

        return new JsonResponse(array(), Response::HTTP_OK, [], false);
    }

    #[Route('/api/v1/student', name: 'student-delete_v1', methods: ['DELETE'])]
    public function deleteStudent(ManagerRegistry $doctrine, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $request->get("id");

        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $entityManager = $doctrine->getManager();
        /** @var StudentRepository $repository */

        $repository = $entityManager->getRepository(Student::class);
        $student = $repository->find($id);

        $repository->remove($student, true);

        return new JsonResponse(array(), Response::HTTP_OK, [], false);
    }

    #[Route('/api/v1/student/ra/{student_ra}', name: 'student_ra_v1')]
    public function getStudentByRa(ManagerRegistry $doctrine, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $studentRa = $request->get("student_ra");
        $hasStudentRa = $studentRa !== null;

        if (!$hasStudentRa) return ResponseHelper::missingParameterResponse("student_ra");

        $repository = $doctrine->getRepository(Student::class);
        /** @var Student $student */
        $student = $repository->findByRa($studentRa);

        if (!$student) return new JsonResponse([], Response::HTTP_BAD_REQUEST, [], false);

        $qb = $doctrine->getManager()->createQueryBuilder();

        $studentLogin = $this->getUser();

        $entityManager = $doctrine->getManager();

        $studentArray = $student->toArray();

        $profilePicture = $student["profile_picture"];
        $studentArray["profile_picture"] = $student->getLogin()?->getProfilePictureUrl($this->profilePictureHelper);

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

        /**
         * TODO: Remove these comments when mobile send token.
         *       Maybe change to mobile controller package?
         */

//        if ($student->getLogin()->getId() !== $studentLogin?->getId())
//            return new JsonResponse([], 403);

        $entityManager = $doctrine->getManager();

        $student = $student->toArray();

        $profilePicture = $student["profile_picture"];
        $student["profile_picture"] = $this->profilePictureHelper->getFullUrl($profilePicture);

        return new JsonResponse($student, Response::HTTP_OK, [], false);;
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