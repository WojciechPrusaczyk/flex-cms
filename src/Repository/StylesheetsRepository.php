<?php

namespace App\Repository;

use App\Entity\Stylesheets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stylesheets>
 *
 * @method Stylesheets|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stylesheets|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stylesheets[]    findAll()
 * @method Stylesheets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StylesheetsRepository extends ServiceEntityRepository
{
    // name provided when creating entity
    public $namelessName = "New Stylesheet";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stylesheets::class);
    }

    /**
     * @return Stylesheets[] Returns an array of Stylesheets objects
     */
    public function findAllAvilableStylesheets(): array
    {
        $now = new \DateTime();
        $nowString = $now->format('Y-m-d G:i:s');

        return $this->createQueryBuilder('s')
            ->andWhere('s.active = TRUE')
            ->andWhere('s.startBeingActive <= :now')
            ->andWhere('s.stopBeingActive > :now')
            ->setParameter('now', $nowString)
            ->getQuery()
            ->getResult()
        ;
    }

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

    public function countNamelessStylesheets(): ?int
    {
        return count( $this->createQueryBuilder('s')
            ->where('s.name LIKE :val')
            ->setParameter('val', "%$this->namelessName%")
            ->getQuery()->getArrayResult() );
    }
}
