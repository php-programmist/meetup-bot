<?php


namespace App\Service;


use App\Entity\Holiday;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

class HolidayManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $date
     * @throws Exception
     */
    public function addHoliday(string $date):void
    {
        $holiday = (new Holiday())->setDate(new DateTime($date));
        $this->entityManager->persist($holiday);
        $this->entityManager->flush();
    }

    /**
     * @param string $date
     * @throws Exception
     */
    public function removeHoliday(string $date):void
    {
        $holiday = $this->findOrFail($date);
        $this->entityManager->remove($holiday);
        $this->entityManager->flush();
    }

    /**
     * @return Holiday[]|array
     */
    public function getHolidays(): array
    {
        return $this->entityManager
            ->getRepository(Holiday::class)
            ->findBy([],['date'=>'asc']);
    }

    /**
     * @param string $date
     * @return Holiday
     * @throws Exception
     */
    public function findOrFail(string $date):Holiday
    {
        $holiday = $this->entityManager
            ->getRepository(Holiday::class)
            ->findOneBy(['date' => new DateTime($date)]);

        if (null === $holiday) {
            throw new RuntimeException(sprintf('Holiday %s not found',$date));
        }

        return $holiday;
    }
}