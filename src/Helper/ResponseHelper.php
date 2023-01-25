<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseHelper
{

    static public function missingParameterResponse(string $param): JsonResponse
    {
        return new JsonResponse(array('error' => "$param must be set"), Response::HTTP_BAD_REQUEST, [], false);
    }
}