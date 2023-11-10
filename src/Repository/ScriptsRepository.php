<?php

namespace App\Repository;

use App\Entity\Scripts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Scripts>
 *
 * @method Scripts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scripts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scripts[]    findAll()
 * @method Scripts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScriptsRepository extends ServiceEntityRepository
{
    // name provided when creating entity
    public $namelessName = "New Script";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scripts::class);
    }

    /**
     * @return Scripts[] Returns an array of Scripts objects
     */
    public function findAllAvilableScripts(): array
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

//    public function findOneBySomeField($value): ?Scripts
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    // function checking, if stylesheet provided by id is actually active
    public function isScriptActive(int $id): ?bool
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

    public function countNamelessScripts(): ?int
    {
        return count( $this->createQueryBuilder('s')
            ->where('s.name LIKE :val')
            ->setParameter('val', "%$this->namelessName%")
            ->getQuery()->getArrayResult() );
    }
}
