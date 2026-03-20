<?php

namespace App\Controller;

use App\Entity\Convention;
use App\Repository\ConventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/conventions')]
#[IsGranted('ROLE_ADMIN')]
final class ConventionController extends AbstractController
{
    #[Route(name: 'admin_convention_list', methods: ['GET'])]
    public function list(ConventionRepository $conventionRepository): Response
    {
        // Récupérer les conventions validées groupées par formation
        $conventions = $conventionRepository->findValidatedGroupedByFormation();

        // Grouper par formation
        $groupedByFormation = [];
        foreach ($conventions as $convention) {
            $formation = $convention->getStudent()->getFormation();
            $formationName = $formation ? $formation->getLibelle() : 'Sans formation';

            if (!isset($groupedByFormation[$formationName])) {
                $groupedByFormation[$formationName] = [];
            }

            $groupedByFormation[$formationName][] = $convention;
        }

        // Trier les formations alphabétiquement
        ksort($groupedByFormation);

        return $this->render('convention/list.html.twig', [
            'groupedByFormation' => $groupedByFormation,
        ]);
    }

    #[Route('/{id}', name: 'admin_convention_show', methods: ['GET'])]
    public function show(Convention $convention): Response
    {
        // Vérifier que la convention est validée
        if (!$convention->isValidated()) {
            throw $this->createNotFoundException('Cette convention n\'est pas disponible pour validation.');
        }

        return $this->render('convention/show.html.twig', [
            'convention' => $convention,
        ]);
    }

    #[Route('/{id}/pdf', name: 'admin_convention_generate_pdf', methods: ['GET'])]
    public function generatePdf(Convention $convention, GotenbergPdfInterface $gotenberg): Response
    {
        // Vérifier que la convention est validée
        if (!$convention->isValidated()) {
            throw $this->createNotFoundException('Cette convention n\'est pas disponible pour génération PDF.');
        }

        // Générer le PDF via Gotenberg
        return $gotenberg->html()
            ->content('convention/pdf.html.twig', [
                'convention' => $convention,
            ])
            ->generate()
            ->stream();
    }

    #[Route('/{id}/refuse', name: 'admin_convention_refuse', methods: ['POST'])]
    public function refuse(
        Convention $convention,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérifier que la convention est validée
        if (!$convention->isValidated()) {
            throw $this->createNotFoundException('Cette convention n\'est pas disponible.');
        }

        // Valider le token CSRF
        if (!$this->isCsrfTokenValid('refuse_' . $convention->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('admin_convention_show', ['id' => $convention->getId()]);
        }

        // Récupérer la raison du refus
        $rejectionReason = $request->request->get('rejectionReason', '');

        if (empty($rejectionReason)) {
            $this->addFlash('error', 'La raison du refus est obligatoire.');
            return $this->redirectToRoute('admin_convention_show', ['id' => $convention->getId()]);
        }

        // Mettre à jour la convention
        $convention->setStatus('refused');
        $convention->setRejectionReason($rejectionReason);
        $convention->setValidatedAt(new \DateTime());

        $entityManager->flush();

        $this->addFlash('success', 'La convention a été refusée avec succès.');

        return $this->redirectToRoute('admin_convention_list');
    }

    #[Route('/{id}/approve', name: 'admin_convention_approve', methods: ['POST'])]
    public function approve(
        Convention $convention,
        Request $request,
        EntityManagerInterface $entityManager,
        GotenbergPdfInterface $gotenberg
    ): Response {
        // Vérifier que la convention est validée
        if (!$convention->isValidated()) {
            throw $this->createNotFoundException('Cette convention n\'est pas disponible.');
        }

        // Valider le token CSRF
        if (!$this->isCsrfTokenValid('approve_' . $convention->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('admin_convention_show', ['id' => $convention->getId()]);
        }

        // Générer le PDF
        try {
            $pdf = $gotenberg->html()
                ->content('convention/pdf.html.twig', [
                    'convention' => $convention,
                ])
                ->generate();

            // Sauvegarder le PDF (à adapter selon votre système de fichiers)
            $filename = 'convention_' . $convention->getId() . '_' . time() . '.pdf';
            $filepath = 'uploads/conventions/' . $filename;

            // Créer le répertoire s'il n'existe pas
            if (!is_dir('uploads/conventions')) {
                mkdir('uploads/conventions', 0755, true);
            }

            // Sauvegarder le PDF
            file_put_contents($filepath, $pdf->getContent());

            // Mettre à jour la convention
            $convention->setStatus('signed');
            $convention->setSignedAt(new \DateTime());
            $convention->setDocumentPath($filepath);
            $convention->setValidationNotes('Approuvé par admin le ' . (new \DateTime())->format('d/m/Y H:i'));

            $entityManager->flush();

            $this->addFlash('success', 'La convention a été approuvée et le PDF a été généré.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_convention_list');
    }
}

