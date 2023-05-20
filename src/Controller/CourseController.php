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
    #[Route('/api/v1/courses', name: 'couses_v1')]
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

    #[Route('/api/v1/course', name: 'create_couse_v1', methods: ['POST'])]
    public function createCourse(ManagerRegistry $doctrine, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $name = $request->get("name");
        $description = $request->get("description");

        if ($name == null) return ResponseHelper::missingParameterResponse("name");

        $entityManager = $doctrine->getManager();

        $repository = $entityManager->getRepository(Course::class);

        $course = new Course();

        $course->setName($name);
        $course->setDescription($description);

        /** @var CourseRepository $repository */
        $repository->save($course, true);


        return new JsonResponse(array(), Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/course', name: 'delete_couse_v1', methods: ['DELETE'])]
    public function deleteCourse(ManagerRegistry $doctrine, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $request->get("id");

        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $entityManager = $doctrine->getManager();
        /** @var CourseRepository $repository */

        $repository = $entityManager->getRepository(Course::class);
        $course = $repository->find($id);

        $repository->remove($course, true);

        return new JsonResponse(array(), Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/course/{id}', name: 'get_couse_by_id_v1', methods: ['GET'])]
    public function getCourse(ManagerRegistry $doctrine, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $request->get("id");

        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $entityManager = $doctrine->getManager();
        /** @var CourseRepository $repository */

        $repository = $entityManager->getRepository(Course::class);
        $course = $repository->find($id);

        return new JsonResponse($course->toArray(), Response::HTTP_OK, [], false);;
    }

    #[Route('/api/v1/course/{id}', name: 'update_couse_by_id_v1', methods: ['PUT'])]
    public function updateCourse(ManagerRegistry $doctrine, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $request->get("id");
        $newName = $request->get("name");
        $newDescription = $request->get("description");
        if ($id == null) return ResponseHelper::missingParameterResponse("id");

        $entityManager = $doctrine->getManager();
        /** @var CourseRepository $repository */

        $repository = $entityManager->getRepository(Course::class);
        $course = $repository->findOneBy(['id' => $id]);

        if ($course == null) return new
        JsonResponse(array('error' => "course does not exist"),
            Response::HTTP_BAD_REQUEST, [], false);

        if (!is_null($newName))
            $course->setName($newName);
        if (!is_null($newDescription))
            $course->setDescription($newDescription);

        $repository->save($course, true);

        return new JsonResponse($course->toArray(), Response::HTTP_OK, [], false);;
    }
}