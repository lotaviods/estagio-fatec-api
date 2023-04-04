<?php

namespace App\Mapper;

use App\Entity\Administrator;
use Symfony\Component\HttpFoundation\Request;

class AdminMapper {
    public static function fromRequest(Request $request): Administrator
    {
        return new Administrator();
    }
}
