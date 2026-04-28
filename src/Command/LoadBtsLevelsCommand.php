<?php

namespace App\Command;

use App\Entity\Level;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-bts-levels',
    description: 'Load BTS levels into the database',
)]
class LoadBtsLevelsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $btsLevels = [
            ['code' => 'BTS SIO 1', 'name' => 'Services Informatiques aux Organisations 1ère année'],
            ['code' => 'BTS SIO 2', 'name' => 'Services Informatiques aux Organisations 2ème année'],
            ['code' => 'BTS CG 1', 'name' => 'Comptabilité et Gestion 1ère année'],
            ['code' => 'BTS CG 2', 'name' => 'Comptabilité et Gestion 2ème année'],
            ['code' => 'BTS SAM 1', 'name' => 'Support à l\'Action Managériale 1ère année'],
            ['code' => 'BTS SAM 2', 'name' => 'Support à l\'Action Managériale 2ème année'],
            ['code' => 'BTS GPME 1', 'name' => 'Gestion de la PME 1ère année'],
            ['code' => 'BTS GPME 2', 'name' => 'Gestion de la PME 2ème année'],
            ['code' => 'BTS SP3S 1', 'name' => 'Services à la Personne et aux Structures 1ère année'],
            ['code' => 'BTS SP3S 2', 'name' => 'Services à la Personne et aux Structures 2ème année'],
        ];

        $io->title('Chargement des classes BTS');

        foreach ($btsLevels as $btsData) {
            // Check if level already exists
            $existingLevel = $this->entityManager->getRepository(Level::class)->findOneBy([
                'levelCode' => $btsData['code']
            ]);

            if ($existingLevel) {
                $io->warning("La classe {$btsData['code']} existe déjà");
                continue;
            }

            $level = new Level();
            $level->setLevelCode($btsData['code']);
            $level->setLevelName($btsData['name']);

            $this->entityManager->persist($level);
            $io->info("Création du classe {$btsData['code']}");
        }

        $this->entityManager->flush();

        $io->success('Tous les classes BTS ont été chargés avec succès !');
        $io->text([
            'Niveaux créés :',
            '- BTS SIO (Services Informatiques aux Organisations)',
            '- BTS CG (Comptabilité et Gestion)',
            '- BTS SAM (Support à l\'Action Managériale)',
            '- BTS GPME (Gestion de la PME)',
            '- BTS SP3S (Services et Prestations des secteurs Sanitaire et Social)',
        ]);

        return Command::SUCCESS;
    }
}
