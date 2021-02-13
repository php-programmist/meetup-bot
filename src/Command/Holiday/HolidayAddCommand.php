<?php

namespace App\Command\Holiday;

use App\Service\HolidayManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class HolidayAddCommand extends Command
{
    protected static $defaultName = 'app:holiday:add';
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
            ->setDescription('Add holiday');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $question = new Question('Input date of holiday (Y-m-d) or press ENTER for exit');
        for (;;) {
            $value = $io->askQuestion($question);
            if (empty($value)) {
                break;
            }
            try {
                $this->holidayManager->addHoliday($value);
                $io->success('Added holiday: ' . $value);
            } catch (Throwable $e) {
                $io->error($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
