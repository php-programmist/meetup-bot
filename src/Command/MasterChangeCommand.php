<?php

namespace App\Command;

use App\Service\MasterManager;
use App\Service\TelegramApiManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class MasterChangeCommand extends Command
{
    protected static $defaultName = 'app:master:change';
    protected static $defaultDescription = 'Changes active master to the next';
    /**
     * @var MasterManager
     */
    private $masterManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var TelegramApiManager
     */
    private $telegramApiManager;

    /**
     * @param MasterManager $masterManager
     * @param TelegramApiManager $telegramApiManager
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        MasterManager $masterManager,
        TelegramApiManager $telegramApiManager,
        LoggerInterface $logger,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->masterManager = $masterManager;
        $this->logger = $logger;
        $this->telegramApiManager = $telegramApiManager;
    }


    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $newMaster = $this->masterManager->changeMaster();
            $this->telegramApiManager->sendNewMasterMessage($newMaster);
            if ($newMaster->isFirstInRound()) {
                $this->telegramApiManager->sendFinishRoundMessage();
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success(sprintf('New active master - %s',$newMaster));
        return Command::SUCCESS;
    }
}
