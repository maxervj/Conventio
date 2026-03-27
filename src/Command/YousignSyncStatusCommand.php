<?php

namespace App\Command;

use App\Repository\ConventionRepository;
use App\Service\YousignService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:yousign:sync-status',
    description: 'Synchronise le statut des conventions en attente de signature depuis Yousign',
)]
class YousignSyncStatusCommand extends Command
{
    public function __construct(
        private readonly ConventionRepository $conventionRepository,
        private readonly YousignService $yousignService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('convention-id', null, InputOption::VALUE_OPTIONAL, 'Synchroniser uniquement cette convention (ID)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $conventionId = $input->getOption('convention-id');

        if ($conventionId) {
            $conventions = array_filter(
                [$this->conventionRepository->find((int) $conventionId)],
                fn($c) => $c !== null && $c->getYousignRequestId() !== null
            );
        } else {
            $conventions = $this->conventionRepository->findBy(['status' => 'pending_signature']);
        }

        if (empty($conventions)) {
            $io->info('Aucune convention en attente de signature.');
            return Command::SUCCESS;
        }

        $io->section(sprintf('Synchronisation de %d convention(s)', count($conventions)));

        $updated = 0;
        $errors = 0;

        foreach ($conventions as $convention) {
            if (!$convention->getYousignRequestId()) {
                continue;
            }

            try {
                $previous = $convention->getYousignStatus();
                $this->yousignService->syncConventionStatus($convention);

                $new = $convention->getYousignStatus();
                $io->text(sprintf(
                    '  Convention #%d : %s → %s',
                    $convention->getId(),
                    $previous ?? 'null',
                    $new,
                ));
                $updated++;
            } catch (\Throwable $e) {
                $io->warning(sprintf('Convention #%d : erreur — %s', $convention->getId(), $e->getMessage()));
                $errors++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d convention(s) synchronisée(s), %d erreur(s).', $updated, $errors));

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
