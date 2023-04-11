<?php

namespace App\Repository;

use App\Entity\MasterAdminCreationInvite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminCreationInvite>
 *
 * @method MasterAdminCreationInvite|null find($id, $lockMode = null, $lockVersion = null)
 * @method MasterAdminCreationInvite|null findOneBy(array $criteria, array $orderBy = null)
 * @method MasterAdminCreationInvite[]    findAll()
 * @method MasterAdminCreationInvite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MasterAdminCreationInviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MasterAdminCreationInvite::class);
    }

    public function save(MasterAdminCreationInvite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MasterAdminCreationInvite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AdminCreationInvite[] Returns an array of AdminCreationInvite objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AdminCreationInvite
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
