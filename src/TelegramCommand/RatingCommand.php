<?php


namespace App\TelegramCommand;

use App\Service\MasterManager;
use Telegram\Bot\Commands\Command;

class RatingCommand extends Command
{
    protected $name = 'rating';
    /**
     * @var string Command Description
     */
    protected $description = 'Отображает таблицу с текущим рейтингом Scrum-мастеров';

    private $masterManager;

    public function __construct(MasterManager $masterManager)
    {
        $this->masterManager = $masterManager;
    }


    public function handle()
    {
        $this->replyWithMessage([
            'text' => $this->masterManager->getRatingTable(),
            'parse_mode' => 'html',
        ]);
    }
}
