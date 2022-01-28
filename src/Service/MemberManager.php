<?php


namespace App\Service;


use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use LucidFrame\Console\ConsoleTable;
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
            ->findBy([ 'disabled' => false ], ['id' => 'asc']);
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

    public function getAbsentMembers():array
    {
        $all = $this->getMembers();

        $presentIds = array_map(static function (Member $member){
            return $member->getId();
        }, $this->getPresentMembers());

        $maybePresentIds = array_map(static function (Member $member){
            return $member->getId();
        }, $this->getMaybePresentMembers());

        $notAbsentIds = array_merge($presentIds, $maybePresentIds);

        return array_filter($all, static function(Member $member) use ($notAbsentIds) {
            return !in_array($member->getId(), $notAbsentIds, true);
        });
    }

    public function getAbsentTable():string
    {
        $data = $this->entityManager
            ->getRepository(Member::class)
            ->getAbsentData();

        $table = new ConsoleTable();
        $table
            ->addHeader('Участник')
            ->addHeader('Пропуски');

        foreach ($data as $row) {
            $table->addRow()
                ->addColumn($row['fullName'])
                ->addColumn($row['absentCounter']);
        }
        return 'Антирейтинг пропусков:'.PHP_EOL.$table->getTable();
    }
}