<?php


namespace App\Service;


use App\Entity\Master;
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
}