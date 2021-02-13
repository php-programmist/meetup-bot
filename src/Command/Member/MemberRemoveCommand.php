<?php

namespace App\Command\Member;

use App\Service\MemberManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class MemberRemoveCommand extends Command
{
    protected static $defaultName = 'app:member:remove';
    /**
     * @var MemberManager
     */
    private $memberManager;

    /**
     * @param MemberManager $memberManager
     * @param string|null $name
     */
    public function __construct(MemberManager $memberManager, string $name = null)
    {
        parent::__construct($name);
        $this->memberManager = $memberManager;
    }


    protected function configure()
    {
        $this
            ->setDescription('Remove member');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $question = new Question('Input username of member which you want to delete or press ENTER for exit');
        for (;;) {
            $username = $io->askQuestion($question);
            if (empty($username)) {
                break;
            }
            try {
                $this->memberManager->removeMember($username);
                $io->success('Removed member: ' . $username);
            } catch (Throwable $e) {
                $io->error($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
