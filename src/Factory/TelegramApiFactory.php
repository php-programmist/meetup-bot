<?php


namespace App\Factory;


use RuntimeException;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramApiFactory
{
    /**
     * @var string
     */
    private $telegramBotToken;
    /**
     * @var string
     */
    private $telegramWebhook;
    /**
     * @var string
     */
    private $telegramWebhookToken;

    /**
     * @param string $telegramBotToken
     * @param string $telegramWebhook
     * @param string $telegramWebhookToken
     */
    public function __construct(string $telegramBotToken, string $telegramWebhook, string $telegramWebhookToken)
    {
        $this->telegramBotToken = $telegramBotToken;
        $this->telegramWebhook = $telegramWebhook;
        $this->telegramWebhookToken = $telegramWebhookToken;
    }

    /**
     * @return Api
     * @throws TelegramSDKException
     */
    public function __invoke(): Api
    {
        $this->checkVariables();
        $api = new Api($this->telegramBotToken);
        $api->setWebhook(['url' => $this->telegramWebhook.$this->telegramWebhookToken]);

        return $api;
    }

    private function checkVariables(): void
    {
        if (empty($this->telegramBotToken)) {
            throw new RuntimeException('You need to specify TELEGRAM_BOT_TOKEN variable in .env');
        }
        if (empty($this->telegramWebhook)) {
            throw new RuntimeException('You need to specify TELEGRAM_WEBHOOK variable in .env');
        }
    }
}