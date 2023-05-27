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
            "accepted_students_by_companies_in_month" => $this->acceptedStudentsByCompanies(),
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

    public function acceptedStudentsByCompanies()
    {
        $manager = $this->doctrine->getManager();

        $sql = "
            SELECT l.name AS company_name, COUNT(sjas.id) AS accepted_students
            FROM company c
            JOIN login l ON l.id = c.login_id
            JOIN job_offer jo ON jo.company_id = c.id
            JOIN student_job_application_status sjas ON sjas.job_id = jo.id
            JOIN student s ON s.id = sjas.student_id
            WHERE sjas.status = 1
            GROUP BY l.name;
";

        $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($manager);
        $rsm->addScalarResult('company_id', 'companyId');
        $rsm->addScalarResult('company_name', 'companyName');
        $rsm->addScalarResult('accepted_students', 'acceptedStudents');

        $query = $manager->createNativeQuery($sql, $rsm);
        $results = $query->getResult();

        return $results;

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