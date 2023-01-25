<?php /** @noinspection ALL */

namespace App\Controller;

use App\Entity\Course;
use App\Helper\ResponseHelper;
use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManager;

class CourseController extends AbstractController
{
    #[Route('/api/courses/detail', name: 'couses')]
    public function getAllClasses(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $repository = $entityManager->getRepository(Course::class);

        $repository->findAll();

        $coursesResult = $repository->findAll();
        $courseArray = [];

        foreach ($coursesResult as $course) {
            $courseArray[] = $course->toArray();
        }

        return new JsonResponse($courseArray, Response::HTTP_OK, [], false);;
    }

    #[Route('/api/course', name: 'create_couse')]
    public function createCourse(ManagerRegistry $doctrine, Request $request)
    {
        $name = $request->get("name");

        if($name == null) return ResponseHelper::missingParameterResponse("name");

        $entityManager = $doctrine->getManager();

        $repository = $entityManager->getRepository(Course::class);

        $course = new Course();

        $course->setName($name);

        /** @var CourseRepository $repository */
        $repository->save();

        $coursesResult = $repository->findAll();
        $courseArray = [];

        foreach ($coursesResult as $course) {
            $courseArray[] = $course->toArray();
        }

        return new JsonResponse($courseArray, Response::HTTP_OK, [], false);;
    }
}