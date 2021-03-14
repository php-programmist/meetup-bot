<?php

namespace App\Repository;

use App\Entity\Master;
use App\Entity\Round;
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

    public function findNextMaster(Master $master): ?Master
    {
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.ordering > :ordering')
            ->setParameter('ordering', $master->getOrdering())
            ->orderBy('m.ordering')
            ->addOrderBy('m.id')
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();

        return $result[0] ?? null;
    }

    public function findFirstMaster(): ?Master
    {
        $result = $this->createQueryBuilder('m')
            ->orderBy('m.ordering')
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();

        return $result[0] ?? null;
    }

    public function getRatingData(?Round $round): array
    {
        $builder = $this->createQueryBuilder('master')
            ->select('m.fullName')
            ->addSelect('coalesce(avg(ratings.score),0) as score')
            ->addSelect('count(ratings.score) as votes')
            ->join('master.member', 'm')
            ->leftJoin('master.ratings', 'ratings')
            ->groupBy('m.fullName')
            ->orderBy('score', 'desc')
            ->addOrderBy('votes', 'desc');

        if (null !== $round) {
            $builder
                ->andWhere('ratings.createdAt > :startedAt')
                ->setParameter('startedAt', $round->getStartedAt());
        }

        return $builder->getQuery()
            ->getResult();
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
