<?php

namespace App\Repository;

use App\Entity\Master;
use App\Entity\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
            ->andWhere('m.disabled = 0')
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
            ->andWhere('m.disabled = 0')
            ->orderBy('m.ordering')
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();

        return $result[0] ?? null;
    }

    public function getRatingData(?Round $round): array
    {
        $builder = $this->createQueryBuilder('master')
            ->select('m.id, m.fullName')
            ->addSelect('coalesce(avg(ratings.score),0) as score')
            ->addSelect('coalesce(count(ratings.score),0) as votes')
            ->join('master.member', 'm')
            ->andWhere('master.disabled = 0')
            ->groupBy('m.id')
            ->orderBy('score', 'desc')
            ->addOrderBy('votes', 'desc');

        if (null !== $round) {
            $builder
                ->leftJoin('master.ratings', 'ratings', Join::WITH,'ratings.createdAt > :startedAt')
                ->setParameter('startedAt', $round->getStartedAt());
        }else{
            $builder
                ->leftJoin('master.ratings', 'ratings');
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
