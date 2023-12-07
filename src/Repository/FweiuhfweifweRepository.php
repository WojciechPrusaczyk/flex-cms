<?php

namespace App\Repository;

use App\Entity\Fweiuhfweifwe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fweiuhfweifwe>
 *
 * @method Fweiuhfweifwe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fweiuhfweifwe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fweiuhfweifwe[]    findAll()
 * @method Fweiuhfweifwe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FweiuhfweifweRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fweiuhfweifwe::class);
    }

//    /**
//     * @return Fweiuhfweifwe[] Returns an array of Fweiuhfweifwe objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Fweiuhfweifwe
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
