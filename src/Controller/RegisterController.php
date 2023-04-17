<?php

namespace App\Controller;

use App\DTO\LoginDTO;
use App\Entity\CompanyAddress;
use App\Form\CompanyAddressForm;
use App\Mapper\AdminMapper;
use App\Mapper\CompanyMapper;
use App\Mapper\StudentMapper;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    #[Route('api/v1/register/student', name: 'studentRegister_v1', methods: ['POST'])]
    public function studentRegister(Request $request, AuthService $service): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $service->registerStudent(StudentMapper::fromRequest($request), LoginDTO::fromRequest($request));
        return $this->json([], Response::HTTP_CREATED);
    }

    #[Route('api/v1/register/master', name: 'adminMasterRegister_v1', methods: ['POST'])]
    public function adminMasterRegister(Request $request, AuthService $service): JsonResponse
    {
        $service->registerAdminMaster(token: $request->get("invite_token"), loginDTO: LoginDTO::fromRequest($request), admin: AdminMapper::fromRequest($request));

        return $this->json([], Response::HTTP_CREATED);
    }

    #[Route('api/v1/register/admin', name: 'adminRegister_v1', methods: ['POST'])]
    public function adminRegister(Request $request, AuthService $service): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $service->registerAdmin(loginDTO: LoginDTO::fromRequest($request), admin: AdminMapper::fromRequest($request));

        return $this->json([], Response::HTTP_CREATED);
    }

    #[Route('api/v1/register/company', name: 'companyRegister_v1', methods: ['POST'])]
    public function companyRegister(Request $request, AuthService $service): JsonResponse
    {
        $data = $request->request->all();

        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(CompanyAddressForm::class, new CompanyAddress());

        $form->submit($data);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            return new JsonResponse([$this->translator->trans('errors') => $errors], Response::HTTP_BAD_REQUEST);
        }

        $service->registerCompany(
            loginDTO: LoginDTO::fromRequest($request),
            company: CompanyMapper::fromRequest($request),
            companyAddress: $form->getData()
        );

        return $this->json([], Response::HTTP_CREATED);
    }
}