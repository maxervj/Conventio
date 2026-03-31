<?php

namespace App\Controller;

use App\Entity\Convention;
use App\Repository\ConventionRepository;
use App\Service\ConventionPdfService;
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
    public function generatePdf(Convention $convention, ConventionPdfService $pdfService): Response
    {
        // Vérifier que la convention est validée
        if (!$convention->isValidated()) {
            throw $this->createNotFoundException('Cette convention n\'est pas disponible pour génération PDF.');
        }

        try {
            $pdfContent = $pdfService->generateConventionPdf($convention);

            return new Response(
                $pdfContent,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="convention_' . $convention->getId() . '.pdf"',
                ]
            );
        } catch (\Exception $e) {
            throw $this->createAccessDeniedException('Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
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

        // Mettre à jour la convention : enregistrer uniquement la raison du refus
        $convention->setRejectionReason($rejectionReason);

        $entityManager->flush();

        $this->addFlash('success', 'La convention a été refusée avec succès.');

        return $this->redirectToRoute('admin_convention_list');
    }

    #[Route('/{id}/approve', name: 'admin_convention_approve', methods: ['POST'])]
    public function approve(
        Convention $convention,
        Request $request,
        EntityManagerInterface $entityManager,
        ConventionPdfService $pdfService
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
            $pdfContent = $pdfService->generateConventionPdf($convention);

            // Sauvegarder le PDF
            $filename = 'convention_' . $convention->getId() . '_' . time() . '.pdf';
            $filepath = 'uploads/conventions/' . $filename;

            // Créer le répertoire s'il n'existe pas
            if (!is_dir('uploads/conventions')) {
                mkdir('uploads/conventions', 0755, true);
            }

            // Sauvegarder le PDF
            file_put_contents($filepath, $pdfContent);

            // Mettre à jour la convention
            $convention->setStatus('signed');
            $convention->setSignedAt(new \DateTime());
            $convention->setDocumentPath($filepath);
            $convention->setValidationNotes('Approuvé par DDF le ' . (new \DateTime())->format('d/m/Y H:i'));

            $entityManager->flush();

            $this->addFlash('success', 'La convention a été approuvée et le PDF a été généré.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_convention_list');
    }
}

