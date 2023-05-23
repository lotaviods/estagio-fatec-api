<?php

namespace App\Service;

use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardService
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getDashboardInfo(?UserInterface $user): array
    {
        $resultArray = [];

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $resultArray["panel_data"] = $this->getEntitiesCount();
        }

        return $resultArray;
    }


    private function getEntitiesCount()
    {
        //todo: Make cache of this result
        $manager = $this->doctrine->getManager();
        $rsm = new ResultSetMappingBuilder($manager);
        $rsm->addScalarResult('student_count', 'students', 'integer');
        $rsm->addScalarResult('course_count', 'courses', 'integer');
        $rsm->addScalarResult('company_count', 'companies', 'integer');
        $rsm->addScalarResult('job_offer_count', 'job_offers', 'integer');
        /** @var NativeQuery $query */
        $query = $manager->createNativeQuery('
        SELECT
            (SELECT COUNT(*) FROM student) AS student_count,
            (SELECT COUNT(*) FROM course) AS course_count,
            (SELECT COUNT(*) FROM company) AS company_count,
            (SELECT COUNT(*) FROM job_offer) AS job_offer_count
    ', $rsm);

        return $query->getArrayResult()[0];
    }
}