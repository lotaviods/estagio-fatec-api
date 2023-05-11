<?php

namespace App\Form\Company;

use App\Entity\CompanyAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyAddressForm extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class, [
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('company_street_empty_field'),
                    ]),
                ],
            ])
            ->add('number', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('company_address_number_empty_field'),
                    ]),
                ]
            ])
            ->add('neighborhood', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('company_neighborhood_empty_field'),
                    ]),
                ],
            ])
            ->add('complement', TextType::class, [
                'required' => false,
            ])
            ->add('zipCode', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('company_zip_code_empty_field'),
                    ]),
                ]
            ])
            ->add('city', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('company_city_empty_field'),
                    ]),
                ]
            ])
            ->add('state', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('company_state_empty_field'),
                    ]),
                ]
            ])
            ->add('country', CountryType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('company_country_empty_field'),
                    ]),
                ]
            ])
            ->add('latitude', NumberType::class, [
                'required' => false
            ])
            ->add('longitude', NumberType::class, [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CompanyAddress::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }
}