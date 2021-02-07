<?php

namespace App\Command;

use App\Service\HolidayManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HolidayWatchCommand extends Command
{
    protected static $defaultName = 'app:holiday:watch';
    /**
     * @var HolidayManager
     */
    private $holidayManager;

    /**
     * @param HolidayManager $holidayManager
     * @param string|null $name
     */
    public function __construct(HolidayManager $holidayManager, string $name = null)
    {
        parent::__construct($name);
        $this->holidayManager = $holidayManager;
    }


    protected function configure()
    {
        $this
            ->setDescription('Shows all holidays');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $holidays =$this->holidayManager->getHolidays();
        if (empty($holidays)) {
            $io->writeln('Holidays not specified. Please add holidays with command <info>php bin/console app:holiday:add</info>');
            return Command::FAILURE;
        }

        $table = (new Table($output))
            ->setHeaders(['date']);
        foreach ($holidays as $holiday) {
            $table->addRow([$holiday->getDate()->format('Y-m-d')]);
        }
        $table->render();

        return Command::SUCCESS;
    }
}
