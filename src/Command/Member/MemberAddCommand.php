<?php

namespace App\Command\Member;

use App\Service\MemberManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class MemberAddCommand extends Command
{
    protected static $defaultName = 'app:member:add';
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
            ->setDescription('Add member');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $questionFullName = new Question('Input full name of member or press ENTER for exit');
        $questionUsername = new Question('Input username of member or press ENTER for exit');
        for (;;) {
            $fullName = $io->askQuestion($questionFullName);
            if (empty($fullName)) {
                break;
            }
            $username = $io->askQuestion($questionUsername);
            if (empty($username)) {
                break;
            }
            try {
                $this->memberManager->addMember($fullName,$username);
                $io->success('Added member: ' . $username);
            } catch (Throwable $e) {
                $io->error($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
