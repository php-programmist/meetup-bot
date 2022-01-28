<?php


namespace App\TelegramCommand;

use App\Entity\Member;
use App\Service\MemberManager;
use RuntimeException;
use Telegram\Bot\Commands\Command;
use Throwable;

abstract class AbstractChangeAbsentCommand extends Command
{
    protected $memberManager;

    public function __construct(MemberManager $memberManager)
    {
        $this->memberManager = $memberManager;
    }


    public function handle()
    {
        try {
            $this->checkFrom();
            $member = $this->getMember();
            $message = $this->changeAbsent($member);
        } catch (Throwable $e) {
            $message = $e->getMessage();
        }

        $this->replyWithMessage([
            'text' => $message,
            'parse_mode' => 'html',
        ]);
    }

    abstract protected function changeAbsent(Member $member):string;

    protected function checkFrom():void
    {
        $fromUsername = $this->getUpdate()
                ->getMessage()
                ->getRawResponse()['from']['username'] ?? '';

        if (empty($fromUsername)) {
            throw new RuntimeException('Не удалось получить Ваш логин');
        }

        $fromMember = $this->memberManager->findOrFail($fromUsername);
        if (null === $fromMember->getMaster()){
            throw new RuntimeException('Только Scrum-мастер может изменять счетчик пропусков');
        }
        if (!$fromMember->getMaster()->getActive()){
            throw new RuntimeException('Только текущий Scrum-мастер может изменять счетчик пропусков');
        }
    }

    protected function getMember():Member
    {
        $args = $this->getArguments();
        if (count($args) < 2) {
            throw new RuntimeException('Укажите фамилию и имя участника через пробел после команды');
        }
        $fullName = $args['lastName'].' '.$args['firstName'];
        return $this->memberManager->findByFullNameOrFail($fullName);
    }
}
