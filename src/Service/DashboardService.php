<?php

namespace App\Service;

use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardService
{
    private ManagerRegistry $doctrine;

    private TranslatorInterface $translator;

    public function __construct(ManagerRegistry $doctrine, TranslatorInterface $translator)
    {
        $this->doctrine = $doctrine;
        $this->translator = $translator;
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
            "company_current_month_created_jobs" => [],
            "job_month_data" => [
                "open" => $this->countJobsCreatedLast12Months(),
                "closed" => $this->countInactiveJobsLast12Months()
            ]
        ];
    }

    public function countJobsCreatedLast12Months()
    {
        $manager = $this->doctrine->getManager();

        $sql = "
            SELECT 
                MONTHNAME(jo.created_at) AS month,
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
        $results = $query->getResult();

        return $this->translateMonths($results);
    }

    public function countInactiveJobsLast12Months()
    {
        $manager = $this->doctrine->getManager();

        $sql = "
            SELECT 
                MONTHNAME(jo.created_at) AS month,
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
        $rsm->addScalarResult('inactive_job_count', 'job_count');

        $query = $manager->createNativeQuery($sql, $rsm);
        $results = $query->getResult();

        return $this->translateMonths($results);
    }

    private function translateMonths($results)
    {
        $monthlyResults = [];

        // Create an array for all the months
        $allMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Initialize the monthly results array with counts of 0 for all months
        foreach ($allMonths as $month) {
            $translatedMonth = $this->translator->trans('month_' . strtolower($month));
            $monthlyResults[$translatedMonth] = 0;
        }

        // Populate the monthly results array with the query results
        foreach ($results as $result) {
            $month = $result['month'];
            $translatedMonth = $this->translator->trans('month_' . strtolower($month));
            $monthlyResults[$translatedMonth] = $result['job_count'];
        }


        return $monthlyResults;
    }
}