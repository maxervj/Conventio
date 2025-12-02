<?php

namespace App\Command;

use App\Entity\Student;
use App\Entity\Professor;
use App\Entity\Tutor;
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
    description: 'Create a new user (Student, Professor, or Tutor)',
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
            ->addArgument('email', InputArgument::REQUIRED, 'Email address')
            ->addArgument('password', InputArgument::REQUIRED, 'Password (minimum 6 characters)')
            ->addArgument('firstName', InputArgument::REQUIRED, 'First name')
            ->addArgument('lastName', InputArgument::REQUIRED, 'Last name')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'User type: student, professor, or tutor', 'student')
            ->addOption('role', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional roles (e.g., ROLE_ADMIN)', [])
            ->addOption('personal-email', null, InputOption::VALUE_OPTIONAL, 'Personal email (for students only)')
            ->setHelp(<<<'HELP'
The <info>%command.name%</info> command creates a new user:

  <info>php %command.full_name% john.doe@example.com password123 John Doe</info>

You can specify the user type with the --type option:
  <info>php %command.full_name% john.doe@example.com password123 John Doe --type=professor</info>

Available types: student, professor, tutor

For students, you can add a personal email:
  <info>php %command.full_name% john.doe@school.edu password123 John Doe --personal-email=john@gmail.com</info>

You can also add custom roles:
  <info>php %command.full_name% john.doe@example.com password123 John Doe --role=ROLE_ADMIN</info>
HELP
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $firstName = $input->getArgument('firstName');
        $lastName = $input->getArgument('lastName');
        $userType = strtolower($input->getOption('type'));
        $personalEmail = $input->getOption('personal-email');

        // Validate email
        $violations = $this->validator->validate($email, [new Email()]);
        if (count($violations) > 0) {
            $io->error('The email is not valid!');
            return Command::FAILURE;
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(Student::class)->findOneBy(['email' => $email])
            ?? $this->entityManager->getRepository(Professor::class)->findOneBy(['email' => $email])
            ?? $this->entityManager->getRepository(Tutor::class)->findOneBy(['email' => $email]);

        if ($existingUser) {
            $io->error(sprintf('A user with email "%s" already exists!', $email));
            return Command::FAILURE;
        }

        // Validate password length


        if (strlen($password) < 6) {
            $io->error('Password must be at least 6 characters long!');
            return Command::FAILURE;
        }

        // Validate student email domain
        if ($userType === 'student' && !str_ends_with(strtolower($email), '@lycee-faure.fr')) {
            $io->error('Students must use an email address ending with "@lycee-faure.fr"');
            return Command::FAILURE;
        }

        // Create user based on type
        $user = match($userType) {
            'student' => new Student(),
            'professor' => new Professor(),
            'tutor' => new Tutor(),
            default => null
        };

        if ($user === null) {
            $io->error(sprintf('Invalid user type "%s". Valid types are: student, professor, tutor', $userType));
            return Command::FAILURE;
        }

        // Set common properties
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $hashedPassword = $this->hasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Set roles
        if ($input->getOption('role')) {
            $user->setRoles($input->getOption('role'));
        }

        // Set student-specific properties
        if ($user instanceof Student) {
            if ($personalEmail) {
                $user->setPersonalEmail($personalEmail);
            }
            // New students are not verified by default
            $user->setIsVerified(false);

            $io->note('Student accounts are not verified by default. Use the verification system to verify the account.');
        }

        // Persist user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success([
            sprintf('User "%s %s" (%s) successfully created!', $firstName, $lastName, $email),
            sprintf('Type: %s', ucfirst($userType)),
            sprintf('Roles: %s', implode(', ', $user->getRoles())),
        ]);

        if ($user instanceof Student && !$user->isVerified()) {
            $io->warning('This student account is NOT verified and cannot log in yet.');
        }

        return Command::SUCCESS;
    }
}
