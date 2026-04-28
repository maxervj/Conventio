<?php

namespace App\Controller;

use App\Entity\InternshipDate;
use App\Entity\Level;
use App\Form\LevelType;
use App\Form\SelectionClasseType;
use App\Repository\LevelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/level')]
#[IsGranted('ROLE_ADMIN')]
final class LevelController extends AbstractController
{
    #[Route('', name: 'app_level_index', methods: ['GET'])]
    public function index(LevelRepository $levelRepository): Response
    {
        return $this->render('level/index.html.twig', [
            'levels' => $levelRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_level_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $level = new Level();
        $form = $this->createForm(LevelType::class, $level);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $startDate = $form->get('internshipDateStart')->getData();
            $endDate = $form->get('internshipDateEnd')->getData();

            if ($startDate && $endDate) {
                $internshipDate = new InternshipDate();
                $internshipDate->setStartDate($startDate);
                $internshipDate->setEndDate($endDate);
                $level->setInternshipDate($internshipDate);
                $entityManager->persist($internshipDate);
            }

            $entityManager->persist($level);
            $entityManager->flush();

            $this->addFlash('success', 'La classe a été créée avec succès.');

            return $this->redirectToRoute('app_level_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('level/new.html.twig', [
            'level' => $level,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_level_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Level $level, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LevelType::class, $level);

        if ($level->getInternshipDate()) {
            $form->get('internshipDateStart')->setData($level->getInternshipDate()->getStartDate());
            $form->get('internshipDateEnd')->setData($level->getInternshipDate()->getEndDate());
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $startDate = $form->get('internshipDateStart')->getData();
            $endDate = $form->get('internshipDateEnd')->getData();

            if ($startDate && $endDate) {
                if (!$level->getInternshipDate()) {
                    $internshipDate = new InternshipDate();
                    $level->setInternshipDate($internshipDate);
                } else {
                    $internshipDate = $level->getInternshipDate();
                }
                $internshipDate->setStartDate($startDate);
                $internshipDate->setEndDate($endDate);
                $entityManager->persist($internshipDate);
            }

            $entityManager->flush();

            $this->addFlash('success', 'La classe a été modifiée avec succès.');

            return $this->redirectToRoute('app_level_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('level/edit.html.twig', [
            'level' => $level,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_level_delete', methods: ['POST'])]
    public function delete(Request $request, Level $level, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $level->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($level);
            $entityManager->flush();

            $this->addFlash('success', 'La classe a été supprimée.');
        }

        return $this->redirectToRoute('app_level_index', [], Response::HTTP_SEE_OTHER);
    }
}
