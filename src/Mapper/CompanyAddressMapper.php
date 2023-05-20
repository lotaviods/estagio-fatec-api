<?php

namespace App\Mapper;

use App\Entity\CompanyAddress;
use Symfony\Component\HttpFoundation\Request;

class CompanyAddressMapper
{
    public static function fromRequest(Request $request): CompanyAddress
    {
        $companyAddress = new CompanyAddress();
        $companyAddress->setStreet($request->get("street"));
        $companyAddress->setNumber($request->get("number"));
        $companyAddress->setNeighborhood($request->get("neighborhood"));
        $companyAddress->setComplement($request->get("complement"));
        $companyAddress->setZipCode($request->get("zip_code"));
        $companyAddress->setCity($request->get("city"));
        $companyAddress->setCountry($request->get("country"));
        $companyAddress->setState($request->get("state"));
        $companyAddress->setLatitude($request->get("latitude"));
        $companyAddress->setLongitude($request->get("longitude"));

        return $companyAddress;
    }
}