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

    public function findOrFail(string $username): Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        if (null === $member) {
            throw new RuntimeException(sprintf('Member with username %s not found', $username));
        }

        return $member;
    }

    /**
     * @return Member[]|array
     */
    public function getMembers(): array
    {
        return $this->entityManager
            ->getRepository(Member::class)
            ->findBy([], ['id' => 'asc']);
    }

    public function addMember(string $fullName, string $username): void
    {
        $member = (new Member())
            ->setFullName($fullName)
            ->setUsername($username);
        $this->entityManager->persist($member);
        $this->entityManager->flush();
    }

    public function removeMember($username): void
    {
        $member = $this->findOrFail($username);
        $this->entityManager->remove($member);
        $this->entityManager->flush();
    }

    /**
     * @return Member[]|array
     */
    public function getPresentMembers():array
    {
        return $this->entityManager
            ->getRepository(Member::class)
            ->findWithLastAnswer(TelegramApiManager::ANSWER_YES);
    }

    /**
     * @return Member[]|array
     */
    public function getMaybePresentMembers():array
    {
        return $this->entityManager
            ->getRepository(Member::class)
            ->findWithLastAnswer(TelegramApiManager::ANSWER_MAYBE);
    }
}