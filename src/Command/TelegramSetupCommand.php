<?php

namespace App\Command;

use App\Service\TelegramApiManager;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class TelegramSetupCommand extends Command
{
    /**
     * @var TelegramApiManager
     */
    private $telegramApiManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TelegramApiManager $telegramApiManager
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(TelegramApiManager $telegramApiManager, LoggerInterface $logger,string $name = null)
    {
        $this->telegramApiManager = $telegramApiManager;
        $this->logger = $logger;
        parent::__construct($name);
    }

    protected static $defaultName = 'app:telegram:setup';

    protected function configure()
    {
        $this
            ->setDescription('Setup webhook')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->telegramApiManager->setupWebhook();
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            $this->logger->error('Executing app:telegram:setup error:'. $e->getMessage().'. Trace: '.$e->getTraceAsString());
            return Command::FAILURE;
        }

        $io->success('Webhook set successful');

        return Command::SUCCESS;
    }
}
