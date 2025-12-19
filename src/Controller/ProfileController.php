<?php

namespace App\Controller;

use App\Entity\Convention;
use App\Entity\Professor;
use App\Entity\Student;
use App\Form\ChangePasswordFormType;
use App\Form\ProfessorProfileType;
use App\Form\StudentProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $conventions = [];
        if ($user instanceof Professor) {
            $conventions = $entityManager->getRepository(Convention::class)->findBy([
                'referentProfessor' => $user,
            ]);
        } elseif ($user instanceof Student) {
            $conventions = $entityManager->getRepository(Convention::class)->findBy([
                'student' => $user,
            ]);
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'conventions' => $conventions,
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user instanceof Professor) {
            $form = $this->createForm(ProfessorProfileType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

                return $this->redirectToRoute('app_profile');
            }

            return $this->render('profile/edit.html.twig', [
                'form' => $form,
                'user' => $user,
            ]);
        } elseif ($user instanceof Student) {
            $form = $this->createForm(StudentProfileType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

                return $this->redirectToRoute('app_profile');
            }

            return $this->render('profile/edit_student.html.twig', [
                'form' => $form,
                'user' => $user,
            ]);
        }

        throw $this->createAccessDeniedException();
    }

    #[Route('/change-password', name: 'app_profile_change_password')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();

            if (!$userPasswordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');

                return $this->render('profile/change_password.html.twig', [
                    'form' => $form,
                ]);
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été changé avec succès.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form,
        ]);
    }
}
