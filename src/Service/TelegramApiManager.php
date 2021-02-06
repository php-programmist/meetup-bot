<?php


namespace App\Service;


use Telegram\Bot\Api;

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
     * @param Api $telegram
     * @param string $telegramChatId
     */
    public function __construct(Api $telegram, string $telegramChatId)
    {
        $this->telegram = $telegram;
        $this->telegramChatId = $telegramChatId;
    }

    public function sendInitialMessage(): void
    {
        $message = $this->telegram->sendPoll([
            'chat_id' => $this->telegramChatId,
            'question' => self::MESSAGE_INITIAL,
            'options' => self::ANSWERS,
            'is_anonymous' => false,
        ]);

        echo $message->get('message_id');
    }

    public function sendNotificationMessage(): void
    {
    }

    public function sendResumeMessage(): void
    {
    }
}