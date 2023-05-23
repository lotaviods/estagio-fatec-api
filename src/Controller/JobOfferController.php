<?php /** @noinspection ALL */

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Course;
use App\Entity\JobOffer;
use App\Entity\Student;
use App\Helper\MinioS3Helper;
use App\Repository\CompanyRepository;
use App\Repository\JobOfferRepository;
use App\Repository\StudentRepository;
use App\Service\JobOfferService;
use App\Service\StudentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManager;
use App\Helper\ResponseHelper;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class JobOfferController extends AbstractController
{
    private MinioS3Helper $pictureHelper;

    public function __construct(MinioS3Helper $profilePictureHelper)
    {
        $this->pictureHelper = $profilePictureHelper;
    }

    #[Route('/api/v1/job-offers/available', name: 'jobs_available_v1', methods: ['GET'])]
    public function getAllAvailableJobs(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $repository = $entityManager->getRepository(JobOffer::class);
        $jobsResult = $repository->findAll();
        $jobsArray = [];

        foreach ($jobsResult as $job) {
            if ($job->isActive()) {
                $currentJob = $job->toArray();
                /** @var JobOffer $job */
                $currentJob["company_profile_picture"] = $job->getCompany()?->getLogin()?->getProfilePictureUrl($this->pictureHelper);
                if ($currentJob["promotional_image_url"]) {
                    $currentJob["promotional_image_url"] = $this->pictureHelper->getFullUrl($currentJob["promotional_image_url"]);
                }
                $jobsArray[] = $currentJob;
            }

        }

        if (empty($jobsArray)) return JsonResponse($jobsArray, Response::HTTP_NO_CONTENT, [], false);

        return new JsonResponse($jobsArray, Response::HTTP_OK, [], false);
    }

    #[Route('/api/v1/job-offer/{id}/like', name: 'like_job_offer_v1', methods: ['POST'])]
    public function likeJobOffer(ManagerRegistry $doctrine, Request $request): Response
    {
        $jobOfferId = $request->get("id");
        $studentId = $request->get("student_id");

        $shouldLike = $request->get("like");

        $entityManager = $doctrine->getManager();

        /** @var StudentRepository $studentRepo */
        $studentRepo = $entityManager->getRepository(Student::class);
        /** @var JobOfferRepository $jobRepo */
        $jobRepo = $entityManager->getRepository(JobOffer::class);

        $student = $studentRepo->find($studentId);
        $job = $jobRepo->find($jobOfferId);

        if ($shouldLike == "true") {
            $student->likeJobOffer($job);
            $studentRepo->save($student, true);
            return new JsonResponse([], Response::HTTP_OK, [], false);
        }
        $student->dislikeJobOffer($job);
        $studentRepo->save($student, true);
        return new JsonResponse([], Response::HTTP_OK, [], false);
    }

    #[Route('/api/v1/job-offer', name: 'create_job_offer_v1', methods: ['POST'])]
    public function createJobOffer(ManagerRegistry $doctrine, Request $request): Response
    {
        $companyId = $request->get("company_id");
        $jobDescription = $request->get("description");
        $is_active = $request->get("is_active");
        $title = $request->get("title");
        $targetCourse = $request->get("target_course_id");
        $jobExperience = $request->get("experience");
        $role = $request->get("role");
        $prom_image = $request->get("prom_image");

        $hasCompany = $companyId !== null;
        $hasDescription = $jobDescription !== null;
        $hasTargetCourseId = $targetCourse !== null;

        if (!$hasCompany) return ResponseHelper::missingParameterResponse("company_id");
        if (!$hasDescription) return ResponseHelper::missingParameterResponse("description");
        if (!$hasTargetCourseId) return ResponseHelper::missingParameterResponse("target_course_id");

        $entityManager = $doctrine->getManager();

        $companyRepository = $entityManager->getRepository(Company::class);

        $company = $companyRepository->find($companyId);

        if ($company == null) return new
        JsonResponse(array('error' => "company does not exist"),
            Response::HTTP_BAD_REQUEST, [], false);

        $courseRepository = $entityManager->getRepository(Course::class);
        $targetCourse = $courseRepository->find($targetCourse);

        if ($targetCourse == null) return new
        JsonResponse(array('error' => "target course does not exist"),
            Response::HTTP_BAD_REQUEST, [], false);

        $repository = $entityManager->getRepository(JobOffer::class);

        $job = new JobOffer();

        $job->setJobExperience($jobExperience);
        $job->setDescription($jobDescription);
        $job->setCompany($company);
        $job->setTargetCourse($targetCourse);
        $job->setRole($role);
        $job->setTitle($title);
        $job->setIsActive($is_active ?: true);

        if ($prom_image) {
            $path = $this->pictureHelper->saveImageBase64($prom_image, "promo-job-images");
            if ($path)
                $job->setPromotionalUrl($path);
        }

        $repository->save($job, true);

        return new JsonResponse($job->toArray(), Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/job-offers', name: 'job-offers_v1', methods: ['GET'])]
    public function getAllJobs(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $repository = $entityManager->getRepository(JobOffer::class);
        $jobsResult = $repository->findAll();
        $jobsArray = [];

        foreach ($jobsResult as $job) {
            /** @var JobOffer $job */
            $currentJob = $job->toArray();
            /** @var JobOffer $job */
            $currentJob["company_profile_picture"] = $job->getCompany()?->getLogin()?->getProfilePictureUrl($this->pictureHelper);
            $currentJob["promotional_image_url"] = $this->pictureHelper->getFullUrl($job->getPromotionalImageUrl());

            $jobsArray[] = $currentJob;
        }

        return new JsonResponse($jobsArray, Response::HTTP_OK, [], false);;
    }

    /**
     *
     *  If user is a company user gives only jobs for this company else
     * gives all jobs.
     */
    #[Route('/api/v1/user/job-offers', name: 'job-offers-from-user_v1', methods: ['GET'])]
    public function getAllJobsFromUser(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        /** @var JobOfferRepository $repository */
        $repository = $entityManager->getRepository(JobOffer::class);

        $jobsResult = $this->getJobsByLogin($this->getUser(), $repository, $entityManager);
        $jobsArray = [];

        foreach ($jobsResult as $job) {
            $currentJob = $job->toArray();
            /** @var JobOffer $job */
            $currentJob["company_profile_picture"] = $job->getCompany()?->getLogin()?->getProfilePictureUrl($this->pictureHelper);
            if ($currentJob["promotional_image_url"]) {
                $currentJob["promotional_image_url"] = $this->pictureHelper->getFullUrl($currentJob["promotional_image_url"]);
            }
            $jobsArray[] = $currentJob;
        }

        return new JsonResponse($jobsArray, Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/job-offers/available/course/{course_id}', name: 'available-job-offers-course_v1', methods: ['GET'])]
    public function getAvailableJobsFromCourse(ManagerRegistry $doctrine, Request $request): Response
    {
        $couseId = $request->get("course_id");
        $entityManager = $doctrine->getManager();
        $repository = $entityManager->getRepository(JobOffer::class);
        $jobsResult = $repository->findAll();
        $jobsArray = [];
        /** @var JobOffer $job */
        foreach ($jobsResult as $job) {
            if ($job->isActive() && $job->getTargetCourse()->getId() == $couseId) {
                $currentJob = $job->toArray();
                /** @var JobOffer $job */
                $currentJob["company_profile_picture"] = $job->getCompany()?->getLogin()?->getProfilePictureUrl($this->pictureHelper);
                if ($job->getPromotionalImageUrl()) {
                    $currentJob["promotional_image_url"] = $this->pictureHelper->getFullUrl($currentJob["promotional_image_url"]);
                }
                $jobsArray[] = $currentJob;
            }
        }

        return new JsonResponse($jobsArray, Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/job-offers/{student_id}', name: 'applied-jobs_v1', methods: ['GET'])]
    public function getAppliedJobsFromStudent(ManagerRegistry $doctrine, Request $request): Response
    {
        $studentId = $request->get("student_id");
        $entityManager = $doctrine->getManager();

        /** @var StudentRepository $repository */
        $repository = $entityManager->getRepository(Student::class);

        $student = $repository->find($studentId);
        if (!$student) return new JsonResponse([], Response::HTTP_BAD_REQUEST, [], false);

        $jobsResult = $student->getAppliedJobOffers();
        $jobsArray = [];

        /** @var JobOffer $job */
        foreach ($jobsResult as $job) {
            if ($job->isActive()) {
                $currentJob = $job->toArray();
                /** @var JobOffer $job */
                $currentJob["company_profile_picture"] = $job->getCompany()?->getLogin()?->getProfilePictureUrl($this->pictureHelper);
                if ($currentJob["promotional_image_url"]) {
                    $currentJob["promotional_image_url"] = $this->pictureHelper->getFullUrl($currentJob["promotional_image_url"]);
                }
                $jobsArray[] = $currentJob;
            }
        }

        return new JsonResponse($jobsArray, Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/job-offer/{job_id}/application', name: 'applications-job_v1', methods: ['GET'])]
    public function getJobApplicationsFromId(Request             $request,
                                             TranslatorInterface $translator,
                                             StudentService      $studentService,
                                             JobOfferService     $jobOfferService): Response
    {
        $jobId = $request->get("job_id");

        try {
            $students = $jobOfferService->getAllStudentApplications($jobId, $studentService);
        } catch (BadRequestHttpException $e) {
            return ResponseHelper::entityNotFoundBadRequestResponse("job", $translator);
        }

        return new JsonResponse($students, Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/job-offer', name: 'job-update_v1', methods: ['PUT'])]
    public function updateJobOffer(ManagerRegistry $doctrine,
                                  Request         $request): Response
    {
        $id = $request->get("id");
        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $newCourseId = $request->get("course_id");
        $newTitle = $request->get("title");
        $newDescription = $request->get("email");
        $newRole = $request->get("password");
        $newExperience = $request->get("ra");
        $newIsActive = $request->get("is_active");


        /** @var JobOfferRepository $repository */
        $repository = $doctrine->getRepository(JobOffer::class);

        /** @var JobOffer $job */
        $job = $repository->find($id);

        if (is_null($job))
            return ResponseHelper::entityNotFoundBadRequestResponse("job", $translator);

        if ($newCourseId) {
            /** @var Course $course */
            $course = $doctrine->getRepository(Course::class)->find($newCourseId);

            if (!$course) return ResponseHelper::entityNotFoundBadRequestResponse("course", $translator);
            $job->setTargetCourse($course);
        }

        if (!is_null($newTitle)) {
            $job->setTitle($newTitle);
        }

        if (!is_null($newDescription)) {
            $job->setDescription($newDescription);
        }

        if (!is_null($newRole)) {
            $job->setRole($newRole);
        }

        if (!is_null($newExperience)) {
            $job->setJobExperience($newExperience);
        }

        if (!is_null($newIsActive)) {
            $job->setIsActive($newIsActive);
        }
        $manager = $doctrine->getManager();
        $manager->persist($job);
        $manager->flush();

        return new JsonResponse([], Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/job-offer', name: 'get_job_by_v1', methods: ['GET'])]
    public function getJobFromId(ManagerRegistry $doctrine, Request $request): Response
    {
        $id = $request->get("id");

        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $entityManager = $doctrine->getManager();
        /** @var JobOfferRepository $repository */

        $repository = $entityManager->getRepository(JobOffer::class);
        $job = $repository->find($id);

        if (!$job) return new JsonResponse([], Response::HTTP_BAD_REQUEST, [], false);

        return new JsonResponse($job->toArray(), Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/job-offer', name: 'job-offer-delete_v1', methods: ['DELETE'])]
    public function deleteJobOffer(ManagerRegistry $doctrine, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $request->get("id");

        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $entityManager = $doctrine->getManager();
        /** @var JobOfferRepository $repository */

        $repository = $entityManager->getRepository(JobOffer::class);
        $jobOffer = $repository->find($id);

        $repository->remove($jobOffer, true);

        return new JsonResponse(array(), Response::HTTP_OK, [], false);
    }

    private function getJobsByLogin(?UserInterface $user, JobOfferRepository $repository, EntityManager $entityManager)
    {
        $userRoles = $user->getRoles();
        if (in_array('ROLE_COMPANY', $userRoles)) {
            /** @var CompanyRepository $companyRepo */
            $companyRepo = $entityManager->getRepository(Company::class);
            $company = $companyRepo->findOneBy(['login' => $this->getUser()->getId()]);
            return $repository->findBy(['company' => $company->getId()]);
        }
        return $repository->findAll();
    }
}