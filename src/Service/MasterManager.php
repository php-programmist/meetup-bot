<?php


namespace App\Service;


use App\Entity\Master;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use LucidFrame\Console\ConsoleTable;

class MasterManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var RoundManager
     */
    private $roundManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @param RoundManager $roundManager
     */
    public function __construct(EntityManagerInterface $entityManager, RoundManager $roundManager)
    {
        $this->entityManager = $entityManager;
        $this->roundManager = $roundManager;
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
        $startedRound = $this->roundManager->getActiveRound();

        return $this->entityManager->getRepository(Master::class)->getRatingData($startedRound);
    }

    public function getWinner():?Master
    {
        $ratingData = $this->getRatingData();
        $winnerId = $ratingData[0]['id'];
        return $this->entityManager->getRepository(Master::class)->find($winnerId);
    }

    public function getRatingTable():string
    {
        $ratingData = $this->getRatingData();
        $table = new ConsoleTable();
        $table
            ->addHeader('Scrum-Мастер')
            ->addHeader('Рейтинг');

        foreach ($ratingData as $row) {
            $table->addRow()
                ->addColumn($row['fullName'])
                ->addColumn(sprintf('%.2f (%d)', $row['score'], $row['votes']));
        }
        return $table->getTable();
    }
}