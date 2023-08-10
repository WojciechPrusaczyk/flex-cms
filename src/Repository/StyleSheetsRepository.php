<?php

namespace App\Repository;

use App\Entity\StyleSheets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StyleSheets>
 *
 * @method StyleSheets|null find($id, $lockMode = null, $lockVersion = null)
 * @method StyleSheets|null findOneBy(array $criteria, array $orderBy = null)
 * @method StyleSheets[]    findAll()
 * @method StyleSheets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StyleSheetsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StyleSheets::class);
    }

//    /**
//     * @return StyleSheets[] Returns an array of StyleSheets objects
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

//    public function findOneBySomeField($value): ?StyleSheets
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
