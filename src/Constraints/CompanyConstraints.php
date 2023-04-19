<?php

namespace App\Constraints;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyConstraints
{
    public static function getConstraints(TranslatorInterface $translator): Collection
    {
        return new Collection(fields: [
            'description' => [
                new NotBlank([
                    'message' => $translator->trans('company_description_empty_field'),
                ])
            ],
            'password' => [
                new NotBlank([
                    'message' => $translator->trans('company_password_empty_field'),
                ])
            ],
            'full_name' => [
                new NotBlank([
                    'message' => $translator->trans('company_name_empty_field'),
                ])
            ],
            'email' => [
                new NotBlank([
                    'message' => $translator->trans('company_email_empty_field'),
                ]),
            ],
            'profile_picture' => [
                new NotBlank([
                    'message' => $translator->trans('company_profile_picture_empty_field'),
                ])
            ],
        ], allowExtraFields: true, allowMissingFields: false, missingFieldsMessage: $translator->trans("field_are_missing"));
    }

}