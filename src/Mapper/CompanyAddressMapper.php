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

        if ($newStreet) {
            $address->setStreet($newStreet);
        }

        if ($newNumber) {
            $address->setNumber($newNumber);
        }

        if ($newNeighborhood) {
            $address->setNeighborhood($newNeighborhood);
        }

        if ($newComplement) {
            $address->setComplement($newComplement);
        }

        if ($newZipCode) {
            $address->setZipCode($newZipCode);
        }

        if ($newCity) {
            $address->setCity($newCity);
        }

        if ($newCountry) {
            $address->setCountry($newCountry);
        }

        if ($newState) {
            $address->setState($newState);
        }

        if ($newLatitude) {
            $address->setLatitude($newLatitude);
        }
        if($newLongitude) {
            $address->setLongitude($newLongitude);
        }
        return $address;
    }
}