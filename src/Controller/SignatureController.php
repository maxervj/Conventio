<?php

namespace App\Controller;

use App\Entity\Signature;
use App\Form\SignatureType;
use App\Repository\SignatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/signature')]
#[IsGranted('ROLE_ADMIN')]
final class SignatureController extends AbstractController
{
    #[Route(name: 'app_signature_index', methods: ['GET'])]
    public function index(SignatureRepository $signatureRepository): Response
    {
        return $this->render('signature/index.html.twig', [
            'signatures' => $signatureRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_signature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $signature = new Signature();
        $form = $this->createForm(SignatureType::class, $signature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($signature);
            $entityManager->flush();

            $this->addFlash('success', 'Les informations de signature ont été créées.');

            return $this->redirectToRoute('app_signature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('signature/new.html.twig', [
            'signature' => $signature,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_signature_show', methods: ['GET'])]
    public function show(Signature $signature): Response
    {
        return $this->render('signature/show.html.twig', [
            'signature' => $signature,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_signature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Signature $signature, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SignatureType::class, $signature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Les informations de signature ont été mises à jour.');

            return $this->redirectToRoute('app_signature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('signature/edit.html.twig', [
            'signature' => $signature,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_signature_delete', methods: ['POST'])]
    public function delete(Request $request, Signature $signature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$signature->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($signature);
            $entityManager->flush();

            $this->addFlash('success', 'Les informations de signature ont été supprimées.');
        }

        return $this->redirectToRoute('app_signature_index', [], Response::HTTP_SEE_OTHER);
    }
}
