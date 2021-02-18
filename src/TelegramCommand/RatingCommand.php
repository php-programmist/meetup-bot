<?php


namespace App\TelegramCommand;

use App\Service\MasterManager;
use LucidFrame\Console\ConsoleTable;
use Telegram\Bot\Commands\Command;

class RatingCommand extends Command
{
    protected $name = 'rating';

    private $masterManager;

    public function __construct(MasterManager $masterManager)
    {
        $this->masterManager = $masterManager;
    }


    public function handle()
    {
        $ratingData = $this->masterManager->getRatingData();
        $table = new ConsoleTable();
        $table
            ->addHeader('Scrum-Мастер')
            ->addHeader('Рейтинг');

        foreach ($ratingData as $row) {
            $table->addRow()
                ->addColumn($row['fullName'])
                ->addColumn(sprintf('%.2f (%d)', $row['score'], $row['votes']));
        }
        $output = $table->getTable();
        $this->replyWithMessage([
            'text' => $output,
            'parse_mode' => 'html',
        ]);
    }
}
