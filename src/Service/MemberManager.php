<?php


namespace App\Service;


use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class MemberManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findOrFail(string $username):Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username'=>$username]);
        if (null === $member) {
            throw new RuntimeException(sprintf('Member with username %s not found',$username));
        }

        return $member;
    }
}