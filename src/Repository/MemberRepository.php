<?php

namespace App\Repository;

use App\Entity\Member;
use App\Service\TelegramApiManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    /**
     * @return Member[]|array
     */
    public function getNotAnswered():array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.messages','messages')
            ->andWhere('messages.id is null')
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Member[]|array
     */
    public function getPresent():array
    {
        return $this->createQueryBuilder('m')
            ->join('m.messages','messages1')
            ->leftJoin('m.messages','messages2', Join::WITH, 'messages1.id < messages2.id')
            ->andWhere('messages2.id is null')
            ->andWhere('messages1.text = :text')
            ->setParameter('text',TelegramApiManager::ANSWER_YES)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Member[] Returns an array of Member objects
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
    public function findOneBySomeField($value): ?Member
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
