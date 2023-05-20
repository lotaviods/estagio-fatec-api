<?php

namespace App\Constraints;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyAddressConstraints
{
    public static function getConstraints(TranslatorInterface $translator): Collection
    {
        return new Collection(fields: [
            'street' => [
                new NotBlank([])
            ],
            'number' => [
                new NotBlank([])
            ],
            'neighborhood' => [
                new NotBlank([])
            ],
            'zip_code' => [
                new NotBlank([])
            ],
            'city' => [
                new NotBlank([])
            ],
            'state' => [
                new NotBlank([])
            ],
        ], allowExtraFields: true, allowMissingFields: false, missingFieldsMessage: $translator->trans("field_are_missing"));
    }

}