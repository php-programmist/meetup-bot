<?php


namespace App\TelegramCommand;

use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected $name = 'start';

    /**
     * @var string Command Description
     */
    protected $description = 'Отображает список доступных команд';

    public function handle()
    {
        $commands = $this->telegram->getCommands();

        $text = 'Приветствую! Список доступных команд:'.PHP_EOL;
        foreach ($commands as $name => $handler) {
            if ($name === 'start') {
                continue;
            }
            /* @var Command $handler */
            $text .= sprintf('/%s - %s'.PHP_EOL, $name, $handler->getDescription());
        }

        $this->replyWithMessage(compact('text'));
    }
}
