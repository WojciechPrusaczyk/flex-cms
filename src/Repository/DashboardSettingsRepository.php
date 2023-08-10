<?php

namespace App\Repository;

use App\Entity\DashboardSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DashboardSettings>
 *
 * @method DashboardSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method DashboardSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method DashboardSettings[]    findAll()
 * @method DashboardSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DashboardSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DashboardSettings::class);
    }

//    /**
//     * @return DashboardSettings[] Returns an array of DashboardSettings objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DashboardSettings
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
