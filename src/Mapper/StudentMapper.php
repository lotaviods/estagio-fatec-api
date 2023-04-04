<?php

namespace App\Mapper;

use App\Entity\Student;
use Symfony\Component\HttpFoundation\Request;

class StudentMapper
{
    public static function fromRequest(Request $request): Student
    {
        $student = new Student();
        $student->setName($request->get("full_name"));
        $student->setEmail($request->get("email"));
        $student->setRa($request->get("ra"));

        return $student;
    }
}