<?php

namespace App\Mapper;

use App\Entity\Company;
use App\Entity\Student;
use Symfony\Component\HttpFoundation\Request;

class CompanyMapper
{
    public static function fromRequest(Request $request): Company
    {
        $company = new Company();
        $company->setActive(true);
        $company->setProfilePicture($request->get("profile_picture"));
        return $company;
    }
}
