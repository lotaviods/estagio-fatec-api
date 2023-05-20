<?php

namespace App\Controller;

use App\Constraints\CompanyConstraints;
use App\DTO\LoginDTO;
use App\Entity\CompanyAddress;
use App\Form\Company\CompanyAddressForm;
use App\Mapper\AdminMapper;
use App\Mapper\CompanyMapper;
use App\Mapper\StudentMapper;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterController extends AbstractController
{
    private TranslatorInterface $translator;
    private ValidatorInterface $validator;

    public function __construct(TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $this->translator = $translator;
        $this->validator = $validator;
    }

    #[Route('api/v1/register/student', name: 'studentRegister_v1', methods: ['POST'])]
    public function studentRegister(Request $request, AuthService $service): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $service->registerStudent(
            StudentMapper::fromRequest($request),
            LoginDTO::fromRequest($request),
            $request->get("profile_picture"),
            $request->get("course_id")
        );
        return $this->json([], Response::HTTP_CREATED);
    }

    #[Route('api/v1/register/master', name: 'adminMasterRegister_v1', methods: ['POST'])]
    public function adminMasterRegister(Request $request, AuthService $service): JsonResponse
    {
        $service->registerAdminMaster(token: $request->get("invite_token"),
            loginDTO: LoginDTO::fromRequest($request),
            admin: AdminMapper::fromRequest($request),
            profileImage: $request->get("profile_picture")
        );

        return $this->json([], Response::HTTP_CREATED);
    }

    #[Route('api/v1/register/admin', name: 'adminRegister_v1', methods: ['POST'])]
    public function adminRegister(Request $request, AuthService $service): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $service->registerAdmin(
            loginDTO: LoginDTO::fromRequest($request),
            admin: AdminMapper::fromRequest($request),
            profileImage: $request->get("profile_picture")
        );

        return $this->json([], Response::HTTP_CREATED);
    }
}