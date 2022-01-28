<?php


namespace App\TelegramCommand;

use App\Entity\Member;

class AddAbsentCommand extends AbstractChangeAbsentCommand
{
    protected $name = 'addAbsent';
    /**
     * @var string Command Description
     */
    protected $description = 'Увеличивает счетчик пропусков указанного участника на 1. Необходимо через пробел после команды указать фамилию и имя участника';

    protected $pattern = '{lastName} {firstName}';

    protected function changeAbsent(Member $member): string
    {
        $this->memberManager->incrementAbsentCounter($member);

        return sprintf('Количество пропусков участника %s увеличено и теперь составляет: %d',$member->getFullName(), $member->getAbsentCounter());
    }
}
