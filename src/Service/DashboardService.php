<?php

namespace App\Service;

use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;
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
            $resultArray["chart_data"] = $this->getChartData();
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

    private function getChartData()
    {
        return [
            "company_current_month_created_jobs" => $this->countJobOffersByCompanyThisMonth(),
            "job_month_data" => [
                "open" => $this->countJobsCreatedLast12Months(),
                "closed" => $this->countInactiveJobsLast12Months()
            ]
        ];
    }
    public function countJobOffersByCompanyThisMonth()
    {
        $manager = $this->doctrine->getManager();

        $sql = "
            SELECT c.id as company_id, l.name as description, COUNT(jo.id) as job_offer_count
            FROM job_offer jo
            JOIN company c ON jo.company_id = c.id
            JOIN login l ON c.login_id = l.id
            WHERE jo.created_at >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')
              AND jo.created_at < DATE_ADD(DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01'), INTERVAL 1 MONTH)
            GROUP BY c.id, l.name
            ORDER BY job_offer_count DESC
            LIMIT 10
        ";
        $rsm = new ResultSetMappingBuilder($manager);
        $rsm->addScalarResult('description', 'description');
        $rsm->addScalarResult('job_offer_count', 'job_offer_count');

        $query = $manager->createNativeQuery($sql, $rsm);


        return $query->getResult();
    }

    public function countJobsCreatedLast12Months()
    {
        $manager = $this->doctrine->getManager();

        $sql = "
            SELECT 
                DATE_FORMAT(jo.created_at, '%Y-%m') AS month,
                COUNT(jo.id) AS job_count
            FROM
                job_offer jo
            WHERE
                jo.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
            GROUP BY
                month
            ORDER BY
                month ASC
        ";

        $rsm = new ResultSetMappingBuilder($manager);
        $rsm->addScalarResult('month', 'month');
        $rsm->addScalarResult('job_count', 'job_count');

        $query = $manager->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function countInactiveJobsLast12Months()
    {
        $manager = $this->doctrine->getManager();

        $sql = "
            SELECT 
                DATE_FORMAT(jo.created_at, '%Y-%m') AS month,
                COUNT(jo.id) AS inactive_job_count
            FROM
                job_offer jo
            WHERE
                jo.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
                AND jo.is_active = 0
            GROUP BY
                month
            ORDER BY
                month ASC
        ";

        $rsm = new ResultSetMappingBuilder($manager);
        $rsm->addScalarResult('month', 'month');
        $rsm->addScalarResult('inactive_job_count', 'inactive_job_count');

        $query = $manager->createNativeQuery($sql, $rsm);

        return  $query->getResult();
    }
}