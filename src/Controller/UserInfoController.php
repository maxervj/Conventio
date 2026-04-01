<?php

namespace App\Controller;

use App\Entity\Professor;
use App\Entity\User;
use App\Form\SelectionClasseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/userinfo')]
final class UserInfoController extends AbstractController
{
    #[Route(name: 'app_user_info')]
    #[IsGranted('ROLE_USER')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $user->getId()]);

        return $this->render('user_info/index.html.twig', [
            'user' => $user,
            'user.contractCategory' => $user->getContractCategory(),
        ]);
    }

    #[Route('/classe', name: 'app_user_info_modif_classe', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROFESSOR')]
    public function indexClasse(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        $professor = $entityManager->getRepository(Professor::class)->find($user->getId());
        $form = $this->createForm(SelectionClasseType::class, $professor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($professor);
            $entityManager->flush();

            $this->addFlash('success', 'Les niveaux ont été mis à jour.');
            return $this->redirectToRoute('app_user_info');
        }

        return $this->render('user_info/modif_classe.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
