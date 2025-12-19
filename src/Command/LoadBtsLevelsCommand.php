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
            ['id_level' => 1, 'code' => 1001, 'name' => 'BTS SIO 1ère année'],
            ['id_level' => 2, 'code' => 1002, 'name' => 'BTS SIO 2ème année'],
            ['id_level' => 3, 'code' => 2001, 'name' => 'BTS MCO 1ère année'],
            ['id_level' => 4, 'code' => 2002, 'name' => 'BTS MCO 2ème année'],
            ['id_level' => 5, 'code' => 3001, 'name' => 'BTS NDRC 1ère année'],
            ['id_level' => 6, 'code' => 3002, 'name' => 'BTS NDRC 2ème année'],
            ['id_level' => 7, 'code' => 4001, 'name' => 'BTS GPME 1ère année'],
            ['id_level' => 8, 'code' => 4002, 'name' => 'BTS GPME 2ème année'],
            ['id_level' => 9, 'code' => 5001, 'name' => 'BTS CG 1ère année'],
            ['id_level' => 10, 'code' => 5002, 'name' => 'BTS CG 2ème année'],
            ['id_level' => 11, 'code' => 6001, 'name' => 'BTS SAM 1ère année'],
            ['id_level' => 12, 'code' => 6002, 'name' => 'BTS SAM 2ème année'],
        ];

        $io->title('Chargement des niveaux BTS');

        foreach ($btsLevels as $btsData) {
            // Check if level already exists
            $existingLevel = $this->entityManager->getRepository(Level::class)->findOneBy([
                'id_level' => $btsData['id_level']
            ]);

            if ($existingLevel) {
                $io->warning("Le niveau {$btsData['name']} existe déjà, mise à jour...");
                $level = $existingLevel;
            } else {
                $level = new Level();
                $io->info("Création du niveau {$btsData['name']}");
            }

            $level->setIdLevel($btsData['id_level']);
            $level->setLevelCode($btsData['code']);
            $level->setLevelName($btsData['name']);

            $this->entityManager->persist($level);
        }

        $this->entityManager->flush();

        $io->success('Tous les niveaux BTS ont été chargés avec succès !');
        $io->text([
            'Niveaux créés :',
            '- BTS SIO (Services Informatiques aux Organisations)',
            '- BTS MCO (Management Commercial Opérationnel)',
            '- BTS NDRC (Négociation et Digitalisation de la Relation Client)',
            '- BTS GPME (Gestion de la PME)',
            '- BTS CG (Comptabilité et Gestion)',
            '- BTS SAM (Support à l\'Action Managériale)',
        ]);

        return Command::SUCCESS;
    }
}
