<?php

namespace App\Command\Member;

use App\Service\MemberManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MemberShowCommand extends Command
{
    protected static $defaultName = 'app:member:show';
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
            ->setDescription('Shows all members');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $members = $this->memberManager->getMembers();
        if (empty($members)) {
            $io->writeln('Members not specified. Please add members with command <info>php bin/console app:member:add</info>');
            return Command::FAILURE;
        }

        $table = (new Table($output))
            ->setHeaders(['id', 'fullName', 'username']);
        foreach ($members as $member) {
            $table->addRow([$member->getId(), $member->getFullName(), $member->getUsername()]);
        }
        $table->render();

        return Command::SUCCESS;
    }
}
