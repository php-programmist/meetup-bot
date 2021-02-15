<?php

namespace App\Repository;

use App\Entity\Master;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Master|null find($id, $lockMode = null, $lockVersion = null)
 * @method Master|null findOneBy(array $criteria, array $orderBy = null)
 * @method Master[]    findAll()
 * @method Master[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MasterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Master::class);
    }

    // /**
    //  * @return Master[] Returns an array of Master objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Master
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
