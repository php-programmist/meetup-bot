<?php


namespace App\Service;


use App\Entity\Master;
use App\Entity\Round;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

class MasterManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getActiveMaster():Master
    {
        $master = $this->entityManager->getRepository(Master::class)->findOneBy(['active' => true]);
        if (null === $master) {
            throw new LogicException('Active master not found');
        }
        return $master;
    }

    public function getNextMaster():Master
    {
        $activeMaster = $this->getActiveMaster();
        $masterRepository = $this->entityManager->getRepository(Master::class);
        $master = $masterRepository->findNextMaster($activeMaster);
        if (null === $master) {
            $master = $masterRepository->findFirstMaster();
        }
        if (null === $master) {
            throw new LogicException('Next master could not be found');
        }
        return $master;
    }

    public function changeMaster():Master
    {
        $activeMaster = $this->getActiveMaster();
        $activeMaster->setActive(false);

        $nextMaster = $this->getNextMaster();
        $nextMaster->setActive(true);

        $this->entityManager->flush();

        return $nextMaster;
    }

    public function getRatingData():array
    {
        $startedRound = $this->entityManager->getRepository(Round::class)->findOneBy(['status'=>Round::STATUS_STARTED],['startedAt'=>'asc']);

        return $this->entityManager->getRepository(Master::class)->getRatingData($startedRound);
    }
}