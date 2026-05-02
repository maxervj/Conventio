<?php

namespace App\Command;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(name: 'app:test-mail', description: 'Envoie un email de test vers Mailpit')]
class TestMailCommand extends Command
{
    public function __construct(private readonly MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new TemplatedEmail())
            ->from('noreply@conventio.edu')
            ->to('admin@lycee.fr')
            ->subject('[TEST Mailpit] Conventio — ' . date('H:i:s'))
            ->html('<h2>Email de test Mailpit</h2><p>Envoyé le ' . date('d/m/Y à H:i:s') . '</p>');

        try {
            $this->mailer->send($email);
            $output->writeln('<info>✓ Email envoyé avec succès — vérifiez http://localhost:8025</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>✗ Échec : ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
