<?php


namespace App\Service;


use App\Entity\Poll;
use Doctrine\ORM\EntityManagerInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

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
     * @param Api $telegram
     * @param EntityManagerInterface $entityManager
     * @param string $telegramChatId
     */
    public function __construct(Api $telegram, EntityManagerInterface $entityManager, string $telegramChatId)
    {
        $this->telegram = $telegram;
        $this->telegramChatId = $telegramChatId;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws TelegramSDKException
     */
    public function sendInitialMessage(): void
    {
        $message = $this->telegram->sendPoll([
            'chat_id' => $this->telegramChatId,
            'question' => self::MESSAGE_INITIAL,
            'options' => self::ANSWERS,
            'is_anonymous' => false,
        ]);

        $this->savePoll($message->get('message_id'));
    }

    public function sendNotificationMessage(): void
    {
    }

    public function sendResumeMessage(): void
    {
    }

    private function savePoll(int $messageId)
    {
        $poll = (new Poll())
            ->setMessageId($messageId);
        $this->entityManager->persist($poll);
        $this->entityManager->flush();
    }
}