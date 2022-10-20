<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'create-user',
    description: 'Create Fake User',
)]
class CreateUserCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $users
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to create a user...')
            ->addArgument('name', InputArgument::OPTIONAL, 'The username of the new user')
            ->addArgument('role', InputArgument::OPTIONAL, 'The role of the new user')
            ;
    }
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }
    protected function interact(InputInterface $input, OutputInterface $output): void
    {

        $this->io->title('Add User Command Interactive Wizard');

        // Ask for the username if it's not defined
        $name = $input->getArgument('name');
            $name = $this->io->ask('name', null);
            $input->setArgument('name', $name);

        $role = $input->getArgument('role');
        $role = $this->io->ask('role', null);
            $input->setArgument('role', $role);
        

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getArgument('name');
        $role = $input->getArgument('role');
        
        $user = new User();
        $user->setName($name);
        $user->setRole($role);
        $user->setCreatedAt(new DateTimeImmutable('now'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('user '.$user->getName().' has been created');

        return Command::SUCCESS;
    }
}
