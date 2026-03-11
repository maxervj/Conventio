<?php

namespace App\Command;

use App\Repository\ConventionRepository;
use App\Service\YousignService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:yousign:send',
    description: 'Envoie une demande de signature électronique Yousign pour une convention',
)]
class YousignSendSignatureCommand extends Command
{
    public function __construct(
        private readonly ConventionRepository $conventionRepository,
        private readonly YousignService $yousignService,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('convention-id', InputArgument::REQUIRED, 'ID de la convention à signer')
            ->addOption('pdf-path', null, InputOption::VALUE_OPTIONAL, 'Chemin absolu vers le PDF (par défaut : var/pdf/convention_{id}.pdf)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer l\'envoi même si une demande Yousign existe déjà');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $conventionId = (int) $input->getArgument('convention-id');
        $convention = $this->conventionRepository->find($conventionId);

        if (!$convention) {
            $io->error(sprintf('Convention #%d introuvable.', $conventionId));
            return Command::FAILURE;
        }

        if ($convention->getYousignRequestId() && !$input->getOption('force')) {
            $io->warning(sprintf(
                'La convention #%d a déjà une demande Yousign en cours : %s (status: %s). Utilisez --force pour renvoyer.',
                $conventionId,
                $convention->getYousignRequestId(),
                $convention->getYousignStatus(),
            ));
            return Command::FAILURE;
        }

        $pdfPath = $input->getOption('pdf-path')
            ?? $this->projectDir . '/var/pdf/convention_' . $conventionId . '.pdf';

        if (!file_exists($pdfPath)) {
            $io->error(sprintf('PDF introuvable : %s', $pdfPath));
            return Command::FAILURE;
        }

        $io->section(sprintf('Envoi de la demande de signature pour la convention #%d', $conventionId));
        $io->text('PDF : ' . $pdfPath);

        try {
            $this->yousignService->sendConventionSignatureRequest($convention, $pdfPath);
            $this->entityManager->flush();

            $io->success(sprintf(
                'Demande de signature envoyée avec succès. ID Yousign : %s',
                $convention->getYousignRequestId()
            ));
        } catch (\Throwable $e) {
            $io->error('Erreur lors de l\'envoi : ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
