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

    /**
     * @return StyleSheets[] Returns an array of StyleSheets objects
     */
//    public function findAllAvilableStylesheets(): array
//    {
//        $now = new \DateTime();
//
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.active = TRUE')
//            ->andWhere('s.start_being_active > '.$now->format())
//            ->andWhere('s.stop_being_active = :val')
//            ->orderBy('s.id', 'ASC')
//            ->getQuery()
//            ->getResult()
//        ;
//    }

// function checking, if stylesheet provided by id is actually active
    public function isStylesheetActive(int $id): ?bool
    {
        $now = new \DateTime();
        $nowString = $now->format('Y-m-d G:i:s');

        return null != $this->createQueryBuilder('s')
            ->andWhere('s.id = :id')
            ->andWhere('s.active = TRUE')
            ->andWhere('s.startBeingActive <= :now')
            ->andWhere('s.stopBeingActive > :now')
            ->setParameter('id', $id)
            ->setParameter('now', $nowString)
            ->getQuery()
            ->getOneOrNullResult();

    }
}
