<?php


namespace App\Service;


use App\Entity\Member;
use App\Entity\Poll;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
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

    public const MESSAGE_INITIAL = 'Всем привет! Митап начнется через 30 минут! Будете сегодня?';
    public const MESSAGE_RESUME = 'Митап начнется через несколько минут! Пора подключаться! %s';
    public const MESSAGE_RESUME_PRESENT_ORDER = 'Порядок выступления:';
    public const MESSAGE_NOTIFICATION = '%sВас ждать сегодня?';
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
    private $telegramWebhook;
    /**
     * @var string
     */
    private $telegramWebhookToken;

    /**
     * @param Api $telegram
     * @param EntityManagerInterface $entityManager
     * @param MessageManager $messageManager
     * @param LoggerInterface $logger
     * @param string $telegramChatId
     * @param string $telegramWebhook
     * @param string $telegramWebhookToken
     */
    public function __construct(
        Api $telegram,
        EntityManagerInterface $entityManager,
        MessageManager $messageManager,
        LoggerInterface $logger,
        string $telegramChatId,
        string $telegramWebhook,
        string $telegramWebhookToken
    ) {
        $this->telegram = $telegram;
        $this->telegramChatId = $telegramChatId;
        $this->entityManager = $entityManager;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->telegramWebhook = $telegramWebhook;
        $this->telegramWebhookToken = $telegramWebhookToken;
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
            $update = $this->telegram->commandsHandler(true);
            if (null === $update) {
                throw new RuntimeException('Update object is NULL');
            }
            $this->handlePollUpdate($update);
            $this->handleMessageUpdate($update);
        } catch (Throwable $e) {
            $this->logger->error('Telegram webhook error: ' . $e->getMessage().'. Trace:'.$e->getTraceAsString());
        }
    }

    public function sendResumeMessage(): void
    {
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
        $text = $message->get('text');
        $username = $message->get('from')->get('username');
        $date = $message->get('date');

        if (empty($text) || empty($username) || null === $updateId) {
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
        $this->telegram->setWebhook(['url' => $this->telegramWebhook.$this->telegramWebhookToken]);
        $this->telegram->sendMessage([
            'chat_id' => $this->telegramChatId,
            'text' => 'Webhook set successful'
        ]);
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
}