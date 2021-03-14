<?php


namespace App\Service;


use App\Entity\Master;
use App\Entity\Member;
use App\Entity\Poll;
use App\TelegramCommand\RatingCommand;
use App\TelegramCommand\StartCommand;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Update;
use Throwable;

class TelegramApiManager
{
    public const ANSWER_YES = 'Да';
    public const ANSWER_NO = 'Нет';
    public const ANSWER_MAYBE = 'Возможно';
    public const ANSWERS = [self::ANSWER_YES, self::ANSWER_NO, self::ANSWER_MAYBE];
    public const STICKERS = [
        'CAACAgIAAxkBAAEJehxgTeVDkp42kybCBDhjELYSpkr93AACSgIAAladvQrJasZoYBh68B4E',
        'CAACAgIAAxkBAAEJeiJgTed4VeEV4nRLGvoHB-0rlK5tgAAC3gADVp29CqXvdzhVgxXEHgQ',
        'CAACAgIAAxkBAAEJeiRgTeeiR6mBJfNaFXtapuha90-9KAACTQADWbv8JSiBoG3dG4L3HgQ',
        'CAACAgIAAxkBAAEJeiZgTefBzNPvZIJUGrXRI2rOYzFcdQACKQADJHFiGiKockiM5SMwHgQ',
        'CAACAgIAAxkBAAEJeihgTefrwfhs_NWFh0Fy6KAYzG0h4AACGQADWbv8Ja1zjKUaUJOvHgQ',
        'CAACAgIAAxkBAAEJeipgTeg2SKQOav8AAXuvcIG17WzVKKEAAnwDAAJHFWgJOQF6ZvTunlgeBA',
        'CAACAgIAAxkBAAEJeixgTehcvRgWRK4jqJebo_7Yg4Cx8gACHwADlp-MDldYXcQNhO6MHgQ',
        'CAACAgIAAxkBAAEJei5gTeh8Br-Iq1GzaqlvBrBPE0HUwwACHQADr8ZRGlyO-uEKz2-8HgQ',
    ];

    public const MESSAGE_INITIAL = 'Всем привет! Митап начнется через 30 минут! Будете сегодня?';
    public const MESSAGE_RESUME = 'Митап начнется через несколько минут! Пора подключаться! %s';
    public const MESSAGE_RESUME_PRESENT_ORDER = 'Порядок выступления:';
    public const MESSAGE_NOTIFICATION = '%sВас ждать сегодня?';
    public const MESSAGE_QUESTIONNAIRE = '%s - Пожалуйста, оцените работу скрам-мастера';
    public const MESSAGE_NEXT_MASTER = 'На следующей неделе ведущим будет %s';
    public const NEW_MASTER_MESSAGE = 'Ведущий на этой неделе - %s';
    public const MATER_WINNER_MESSAGE = 'Поздравляем победителя очередного раунда - %s';
    /**
     * @var Api
     */
    private $telegram;
    /**
     * @var string
     */
    private $telegramChatId;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var MessageManager
     */
    private $messageManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $telegramWebhookToken;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var MasterManager
     */
    private $masterManager;
    /**
     * @var RatingCommand
     */
    private $ratingCommand;
    /**
     * @var RoundManager
     */
    private $roundManager;

    /**
     * @param Api $telegram
     * @param EntityManagerInterface $entityManager
     * @param MessageManager $messageManager
     * @param LoggerInterface $logger
     * @param RouterInterface $router
     * @param MasterManager $masterManager
     * @param RatingCommand $ratingCommand
     * @param RoundManager $roundManager
     * @param string $telegramChatId
     * @param string $telegramWebhookToken
     */
    public function __construct(
        Api $telegram,
        EntityManagerInterface $entityManager,
        MessageManager $messageManager,
        LoggerInterface $logger,
        RouterInterface $router,
        MasterManager $masterManager,
        RatingCommand $ratingCommand,
        RoundManager $roundManager,
        string $telegramChatId,
        string $telegramWebhookToken
    ) {
        $this->telegram = $telegram;
        $this->telegramChatId = $telegramChatId;
        $this->entityManager = $entityManager;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->telegramWebhookToken = $telegramWebhookToken;
        $this->router = $router;
        $this->masterManager = $masterManager;
        $this->ratingCommand = $ratingCommand;
        $this->roundManager = $roundManager;
    }

    /**
     * @throws TelegramSDKException|Exception
     */
    public function sendInitialMessage(): void
    {
        $this->messageManager->deleteAllMessages();

        $message = $this->telegram->sendPoll([
            'chat_id' => $this->telegramChatId,
            'question' => self::MESSAGE_INITIAL,
            'options' => self::ANSWERS,
            'is_anonymous' => false,
        ]);

        $this->savePoll($message->get('message_id'));
    }

    /**
     * @throws TelegramSDKException
     */
    public function sendNotificationMessage(): void
    {
        $notAnsweredMembers = $this->entityManager
            ->getRepository(Member::class)
            ->getNotAnswered();
        if (empty($notAnsweredMembers)) {
            return;
        }

        $keyboard = [
            self::ANSWERS,
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $this->telegramChatId,
            'text' => $this->getNotificationMessage($notAnsweredMembers),
            'reply_markup' => $reply_markup
        ]);

    }

    public function handleUpdate(): void
    {
        try {
            $this->addCommands();
            $update = $this->telegram->commandsHandler(true);
            if (null === $update) {
                throw new RuntimeException('Update object is NULL');
            }
            $this->handlePollUpdate($update);
            $this->handleMessageUpdate($update);
        } catch (Throwable $e) {
            $this->logger->error('Telegram webhook error: ' . $e->getMessage() . '. Trace:' . $e->getTraceAsString());
        }
    }

    /**
     * @throws TelegramSDKException
     */
    public function sendResumeMessage(): void
    {
        $this->finishPoll();

        $this->telegram->sendMessage([
            'chat_id' => $this->telegramChatId,
            'text' => sprintf(self::MESSAGE_RESUME, $this->getPresentMessage()),
            'reply_markup' => Keyboard::remove()
        ]);
    }

    /**
     * @throws TelegramSDKException
     */
    public function sendQuestionnaireMessage(): void
    {
        $url = $this->router->generate('questionnaire', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = sprintf(self::MESSAGE_QUESTIONNAIRE, $url);

        $message = $this->addNextMasterMessage($message);

        $this->telegram->sendMessage([
            'chat_id' => $this->telegramChatId,
            'text' => sprintf($message),
        ]);
    }

    private function getPresentMessage(): string
    {
        $present = $this->entityManager
            ->getRepository(Member::class)
            ->getPresent();

        if (empty($present)) {
            return '';
        }

        //перемешиваем список присутствующих
        shuffle($present);

        $message = self::MESSAGE_RESUME_PRESENT_ORDER . PHP_EOL;

        /** @var Member $member */
        foreach ($present as $key => $member) {
            $message .= $key + 1;
            $message .= '.' . $member->getFullName() . PHP_EOL;
        }
        return $message;
    }

    private function savePoll(int $messageId): void
    {
        $poll = (new Poll())
            ->setMessageId($messageId);
        $this->entityManager->persist($poll);
        $this->entityManager->flush();
    }

    private function handlePollUpdate(Update $update): void
    {
        $pollAnswer = $update->get('poll_answer');
        if (null === $pollAnswer) {
            return;
        }
        $text = $this->preparePollAnswer($pollAnswer);
        $username = $pollAnswer->get('user')->get('username');
        $updateId = $update->get('update_id');

        if (empty($text) || empty($username) || null === $updateId) {
            return;
        }

        $this->messageManager->saveMessage($text, $username, $updateId);
    }

    private function handleMessageUpdate(Update $update): void
    {
        $message = $update->get('message');
        if (null === $message) {
            return;
        }

        $updateId = $update->get('update_id');
        $text = trim($message->get('text'));
        $username = $message->get('from')->get('username');
        $date = $message->get('date');

        if (null === $updateId || empty($username) || !in_array($text,self::ANSWERS)) {
            return;
        }

        $this->messageManager->saveMessage($text, $username, $updateId, $date);
    }

    private function preparePollAnswer(object $pollAnswer): string
    {
        $optionId = $pollAnswer->get('option_ids')->get('0');
        return self::ANSWERS[$optionId] ?? '';
    }

    /**
     * @throws TelegramSDKException
     */
    public function setupWebhook(): void
    {
        $url = $this->router->generate('webhook', ['token' => $this->telegramWebhookToken],
            UrlGeneratorInterface::ABSOLUTE_URL);

        $this->telegram->setWebhook(['url' => $url]);
        $this->telegram->sendMessage([
            'chat_id' => $this->telegramChatId,
            'text' => 'Webhook set successful - ' . $url
        ]);
    }

    /**
     * @param Master $newMaster
     * @throws TelegramSDKException
     */
    public function sendNewMasterMessage(Master $newMaster): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $this->telegramChatId,
            'text' => sprintf(self::NEW_MASTER_MESSAGE,$newMaster),
        ]);
    }

    /**
     * @throws TelegramSDKException
     */
    public function sendFinishRoundMessage(): void
    {
        $rating = $this->masterManager->getRatingTable();
        $winner = $this->masterManager->getWinner();
        $this->telegram->sendSticker([
            'chat_id' => $this->telegramChatId,
            'sticker' => array_rand(array_flip(self::STICKERS)),
        ]);
        $this->telegram->sendMessage([
            'chat_id' => $this->telegramChatId,
            'text' => sprintf(self::MATER_WINNER_MESSAGE,$winner),
        ]);
        $this->telegram->sendMessage([
            'chat_id' => $this->telegramChatId,
            'text' => $rating,
            'parse_mode' => 'html',
        ]);
        $this->roundManager->startNextRound($winner);
    }

    /**
     * @param $notAnsweredMembers
     * @return string
     */
    private function getNotificationMessage($notAnsweredMembers): string
    {
        $message = '';
        /** @var Member $member */
        foreach ($notAnsweredMembers as $member) {
            $message .= sprintf('@%s, ', $member->getUsername());
        }
        return sprintf(self::MESSAGE_NOTIFICATION, $message);
    }

    private function finishPoll(): void
    {
        try {
            /** @var Poll $poll */
            $poll = $this->entityManager->getRepository(Poll::class)->findOpened();
            if (null === $poll) {
                return;
            }
            $this->telegram->stopPoll([
                'chat_id' => $this->telegramChatId,
                'message_id' => $poll->getMessageId()
            ]);
            $poll->setFinishedAt(new DateTime());
            $this->entityManager->flush();
        } catch (TelegramSDKException $e) {
        }
    }

    /**
     * @param string $message
     * @return string
     */
    private function addNextMasterMessage(string $message): string
    {
        if (date("l") === 'Friday') {
            try {
                $nextMaster = $this->masterManager->getNextMaster();
                $message .= PHP_EOL . sprintf(self::MESSAGE_NEXT_MASTER, $nextMaster->getMember());
            } catch (Throwable $e) {
                $this->logger->error($e->getMessage());
            }
        }
        return $message;
    }

    /**
     * @throws TelegramSDKException
     */
    private function addCommands(): void
    {
        $this->telegram->addCommands([
            $this->ratingCommand,
            StartCommand::class,
        ]);
    }
}