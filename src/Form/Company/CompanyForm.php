<?php

namespace App\Form\Company;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyForm extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', TextType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => $this->translator->trans('company_email_empty_field'),
                ]),
            ],
        ])
            ->add('full_name',TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('company_name_empty_field'),
                    ]),
                ],
            ])
            ->add('password', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('company_password_empty_field'),
                    ]),
                ],
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('company_description_empty_field'),
                    ]),
                ],
            ])->add('profile_picture', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }
}