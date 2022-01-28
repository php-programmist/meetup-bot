<?php


namespace App\Service;


use App\Entity\Master;
use App\Entity\Member;
use App\Entity\Round;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class RoundManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getActiveRound():?Round
    {
        return $this->entityManager
            ->getRepository(Round::class)
            ->findOneBy(
                ['status'=>Round::STATUS_STARTED],
                ['startedAt'=>'asc']
            );
    }

    public function startNextRound(?Master $winner):void
    {
        $currentRound = $this->getActiveRound();
        if (null !== $currentRound) {
            $currentRound->setStatus(Round::STATUS_FINISHED)
                ->setFinishedAt(new DateTime())
                ->setWinner($winner);
            $this->entityManager->flush();
        }

        $this->entityManager
            ->getRepository(Member::class)
            ->resetAbsentCounters();

        $newRound = new Round();
        $this->entityManager->persist($newRound);
        $this->entityManager->flush();
    }
}