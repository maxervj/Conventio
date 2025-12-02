<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:add-user',
    description: 'Add a short description for your command',
)]
class AddUserCommand extends Command
{
    private UserPasswordHasherInterface $hasher;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->hasher = $hasher;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addOption('role', null,  InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Option description', [])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $control = true;

        if ($email) {
            $io->note(sprintf('Creating user: %s', $email));
        } else {
            $io->error('You must pass the username!');
            $control = false;
        }

        if ($password && strlen($password) >= 6) {
            $io->note(sprintf('With password: %s', $password));
            $user = new User();
            $violations = $this->validator->validate($email, [
                new Email(),
            ]);
            if (count($violations) > 0) {
                $io->error('The email is not valid!');
                $control = false;
            } else {
                $user->setEmail($email);
                $hashedPassword = $this->hasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
                if ($input->getOption('role')) {
                    $user->setRoles($input->getOption('role'));
                }
                // Save the user entity, e.g. using Doctrine's EntityManager
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $io->success(sprintf('User %s successfully created!', $email));
            }
        } else {
            $io->error('You must pass the password (at least 6 characters!)');
            $control = false;
        }

        if (!$control) {
            return Command::FAILURE;
        } else {
            return Command::SUCCESS;
        }
    }
}
