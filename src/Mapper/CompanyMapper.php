<?php

namespace App\Mapper;

use App\Entity\Company;
use App\Entity\Student;
use Symfony\Component\HttpFoundation\Request;

class CompanyMapper
{
    public static function fromRequest(): Company
    {
        $company = new Company();
        return $company;
    }
}
