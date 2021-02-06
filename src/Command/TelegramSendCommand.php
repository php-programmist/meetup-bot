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

class TelegramSendCommand extends Command
{
    public const TYPE_INITIAL = 'initial';
    public const TYPE_NOTIFICATION = 'notification';
    public const TYPE_RESUME = 'resume';
    public const TYPES = [
        self::TYPE_INITIAL,
        self::TYPE_NOTIFICATION,
        self::TYPE_RESUME,
    ];
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

        parent::__construct($name);
        $this->logger = $logger;
    }

    protected static $defaultName = 'app:telegram:send';

    protected function configure()
    {
        $this
            ->setDescription('Send message of type')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of message')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');

        try {
            switch ($type) {
                case self::TYPE_INITIAL:
                    $this->telegramApiManager->sendInitialMessage();
                    break;
                case self::TYPE_NOTIFICATION:
                    $this->telegramApiManager->sendNotificationMessage();
                    break;
                case self::TYPE_RESUME:
                    $this->telegramApiManager->sendResumeMessage();
                    break;
                default:
                    throw new LogicException(sprintf('%s - is wrong type. Available types: %s',
                        $type, implode(', ', self::TYPES)));
            }
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            $this->logger->error('Executing app:telegram:send error:'. $e->getMessage());
            return Command::FAILURE;
        }

        $io->success('Message send');

        return Command::SUCCESS;
    }
}
