<?php


namespace App\TelegramCommand;

use App\Service\MemberManager;
use Telegram\Bot\Commands\Command;

class AbsentCommand extends Command
{
    protected $name = 'absent';
    /**
     * @var string Command Description
     */
    protected $description = 'Отображает таблицу с текущим рейтингом пропусков';

    private $memberManager;

    public function __construct(MemberManager $memberManager)
    {
        $this->memberManager = $memberManager;
    }


    public function handle()
    {
        $this->replyWithMessage([
            'text' => $this->memberManager->getAbsentTable(),
            'parse_mode' => 'html',
        ]);
    }
}
