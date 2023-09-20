<?php

namespace App\Repository;

use App\Entity\Sections;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sections>
 *
 * @method Sections|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sections|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sections[]    findAll()
 * @method Sections[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SectionsRepository extends ServiceEntityRepository
{
    // name provided when creating entity
    public $namelessName = "New Section";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sections::class);
    }

    // function checking, if section provided by id is actually active
    public function isSectionActive(int $id): ?bool
    {
        $now = new \DateTime();
        $nowString = $now->format('Y-m-d G:i:s');

        return null != $this->createQueryBuilder('s')
                ->andWhere('s.id = :id')
                ->andWhere('s.isActive = TRUE')
                ->andWhere('s.startBeingActive <= :now')
                ->andWhere('s.stopBeingActive > :now')
                ->setParameter('id', $id)
                ->setParameter('now', $nowString)
                ->getQuery()
                ->getOneOrNullResult();

    }

    public function countNamelessSections(): ?int
    {
        return count( $this->createQueryBuilder('s')
            ->where('s.name LIKE :val')
            ->setParameter('val', "%$this->namelessName%")
            ->getQuery()->getArrayResult() );
    }

    public function findValidPosition(): ?int
    {
        return count( $this->createQueryBuilder('s')
            ->getQuery()->getArrayResult() ) + 1;
    }

//    /**
//     * @return Sections[] Returns an array of Sections objects
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

//    public function findOneBySomeField($value): ?Sections
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
