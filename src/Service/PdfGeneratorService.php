<?php

namespace App\Service;

use App\Entity\Convention;
use App\Entity\InternshipCompanyInfo;
use App\Repository\SignatureRepository;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\Processor\FileProcessor;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

class PdfGeneratorService
{
    public function __construct(
        private readonly GotenbergPdfInterface $gotenberg,
        private readonly Filesystem $filesystem,
        private readonly SignatureRepository $signatureRepository,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {}

    private function getPdfDir(): string
    {
        $dir = $this->projectDir . '/var/pdf';
        $this->filesystem->mkdir($dir);

        return $dir;
    }

    /**
     * Streame directement le PDF de collecte d'informations vers le navigateur.
     */
    public function streamCollecteInfoPdf(
        InternshipCompanyInfo $companyInfo,
        ?Convention $convention = null,
    ): Response {
        return $this->gotenberg->html()
            ->content('pdf/collecte_info.html.twig', [
                'companyInfo' => $companyInfo,
                'convention'  => $convention,
            ])
            ->fileName(
                sprintf('collecte_info_%s', $companyInfo->getId()),
                HeaderUtils::DISPOSITION_ATTACHMENT,
            )
            ->generate()
            ->stream();
    }

    /**
     * Génère le PDF de collecte d'informations, le sauvegarde sur disque et retourne le chemin.
     * Utilisé pour l'intégration Yousign (parse_anchors: true détectera {{s1|signature|85|37}}).
     */
    public function saveCollecteInfoPdf(
        InternshipCompanyInfo $companyInfo,
        ?Convention $convention = null,
    ): string {
        $dir      = $this->getPdfDir();
        $filename = sprintf('collecte_info_%s', $companyInfo->getId());

        /** @var \SplFileInfo $fileInfo */
        $fileInfo = $this->gotenberg->html()
            ->content('pdf/collecte_info.html.twig', [
                'companyInfo' => $companyInfo,
                'convention'  => $convention,
            ])
            ->fileName($filename, HeaderUtils::DISPOSITION_ATTACHMENT)
            ->processor(new FileProcessor($this->filesystem, $dir))
            ->generate()
            ->process();

        return $fileInfo->getPathname();
    }

    /**
     * Génère le PDF de la convention de stage, le sauvegarde sur disque et retourne le chemin.
     * Utilisé pour l'envoi à Yousign (anchors s1 étudiant, s2 entreprise, s3 école).
     */
    public function saveConventionPdf(Convention $convention): string
    {
        $dir      = $this->getPdfDir();
        $filename = sprintf('convention_%s', $convention->getId());

        /** @var \SplFileInfo $fileInfo */
        $fileInfo = $this->gotenberg->html()
            ->content('pdf/convention.html.twig', [
                'convention'      => $convention,
                'schoolSignature' => $this->signatureRepository->findOneBy([]),
            ])
            ->fileName($filename, HeaderUtils::DISPOSITION_ATTACHMENT)
            ->processor(new FileProcessor($this->filesystem, $dir))
            ->generate()
            ->process();

        return $fileInfo->getPathname();
    }

    /**
     * Streame directement le PDF de la convention vers le navigateur.
     */
    public function streamConventionPdf(Convention $convention): Response
    {
        return $this->gotenberg->html()
            ->content('pdf/convention.html.twig', [
                'convention'      => $convention,
                'schoolSignature' => $this->signatureRepository->findOneBy([]),
            ])
            ->fileName(
                sprintf('convention_%s', $convention->getId()),
                HeaderUtils::DISPOSITION_ATTACHMENT,
            )
            ->generate()
            ->stream();
    }
}
