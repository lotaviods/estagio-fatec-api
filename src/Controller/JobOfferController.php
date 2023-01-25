<?php /** @noinspection ALL */

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Course;
use App\Entity\JobOffer;
use App\Repository\JobOfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManager;
use App\Helper\ResponseHelper;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class JobOfferController extends AbstractController
{

    #[Route('/api/job-offers/available', name: 'jobs_available')]
    public function getAllAvailableJobs(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $repository = $entityManager->getRepository(JobOffer::class);
        $jobsResult = $repository->findAll();
        $jobsArray = [];

        foreach ($jobsResult as $job) {
            if ($job->isActive())
                $jobsArray[] = $job->toArray();
        }

        return new JsonResponse($jobsArray, Response::HTTP_OK, [], false);;
    }

    #[Route('/api/job-offer', name: 'create_job_offer', methods: ['POST'])]
    public function createJobOffer(ManagerRegistry $doctrine, Request $request): Response
    {
        $companyId = $request->get("company_id");
        $jobDescription = $request->get("description");
        $targetCourse = $request->get("target_course_id");
        $jobExperience = $request->get("experience");

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

        if($targetCourse == null) return new
        JsonResponse(array('error' => "target course does not exist"),
            Response::HTTP_BAD_REQUEST, [], false);

        $repository = $entityManager->getRepository(JobOffer::class);

        $job = new JobOffer();

        $job->setJobExperience($jobExperience);
        $job->setDescription($jobDescription);
        $job->setCompany($company);
        $job->setTargetCourse($targetCourse);

        $repository->save($job, true);

        return new JsonResponse($job->toArray(), Response::HTTP_OK, [], false);;
    }

    #[Route('/api/job-offers', name: 'job-offers')]
    public function getAllJobs(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $repository = $entityManager->getRepository(JobOffer::class);
        $jobsResult = $repository->findAll();
        $jobsArray = [];

        foreach ($jobsResult as $job) {
            $jobsArray[] = $job->toArray();
        }

        return new JsonResponse($jobsArray, Response::HTTP_OK, [], false);;
    }
}