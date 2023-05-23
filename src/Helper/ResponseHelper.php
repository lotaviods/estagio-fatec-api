<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;

class ResponseHelper
{

    static public function missingParameterResponse(string $param): JsonResponse
    {
        return new JsonResponse(array('error' => "$param must be set"), Response::HTTP_BAD_REQUEST, [], false);
    }

    static public function entityNotFoundBadRequestResponse(string $param, Translator $translator): JsonResponse
    {
        return new JsonResponse(array($translator->trans('error') => $translator->trans('entity_not_exist', ['{{ entity }}' => $translator->trans($param)])),
            Response::HTTP_BAD_REQUEST, [], false);
    }
}