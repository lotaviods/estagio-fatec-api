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

    public static function fromRequestToAddress(Request $request, CompanyAddress $address): CompanyAddress
    {
        $newStreet = $request->get("street");
        $newNumber = $request->get("number");
        $newNeighborhood = $request->get("neighborhood");
        $newComplement = $request->get("complement");
        $newZipCode = $request->get("zip_code");
        $newCity = $request->get("city");
        $newCountry = $request->get("country");
        $newState = $request->get("state");
        $newLatitude = $request->get("latitude");
        $newLongitude = $request->get("longitude");

        if (!is_null($newStreet)) {
            $address->setStreet($newStreet);
        }

        if (!is_null($newNumber)) {
            $address->setNumber($newNumber);
        }

        if (!is_null($newNeighborhood)) {
            $address->setNeighborhood($newNeighborhood);
        }

        if (!is_null($newComplement)) {
            $address->setComplement($newComplement);
        }

        if (!is_null($newZipCode)) {
            $address->setZipCode($newZipCode);
        }

        if (!is_null($newCity)) {
            $address->setCity($newCity);
        }

        if (!is_null($newCountry)) {
            $address->setCountry($newCountry);
        }

        if (!is_null($newState)) {
            $address->setState($newState);
        }

        if (!is_null($newLatitude)) {
            $address->setLatitude($newLatitude);
        }
        if(!is_null($newLongitude)) {
            $address->setLongitude($newLongitude);
        }
        return $address;
    }
}