<?php


namespace App\TelegramCommand;

use App\Entity\Member;

class RemoveAbsentCommand extends AbstractChangeAbsentCommand
{
    protected $name = 'removeAbsent';
    /**
     * @var string Command Description
     */
    protected $description = 'Уменьшает счетчик пропусков указанного участника на 1. Необходимо через пробел после команды указать фамилию и имя участника';

    protected $pattern = '{lastName} {firstName}';

    protected function changeAbsent(Member $member): string
    {
        $this->memberManager->decrementAbsentCounter($member);

        return sprintf('Количество пропусков участника %s уменьшено и теперь составляет: %d',$member->getFullName(), $member->getAbsentCounter());
    }
}
