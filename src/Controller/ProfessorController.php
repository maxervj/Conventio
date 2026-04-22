<?php

namespace App\Controller;

use App\Entity\Convention;
use App\Entity\Professor;
use App\Entity\Student;
use App\Repository\ConventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[\Symfony\Component\Routing\Annotation\Route('/professors')]
#[IsGranted('ROLE_PROFESSOR')]
final class ProfessorController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConventionRepository $conventionRepository,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    #[Route('/convention', name: 'convention_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();

        if ($user instanceof Professor) {
            $conventions = $this->conventionRepository->findByReferentProfessor($user);

            return $this->render('convention/index.html.twig', [
                'conventions' => $conventions,
                'role' => 'professor',
            ]);
        }

        if ($user instanceof Student) {
            $conventions = $this->conventionRepository->findByStudent($user);

            return $this->render('convention/index.html.twig', [
                'conventions' => $conventions,
                'role' => 'student',
            ]);
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * Détail d'une convention.
     */
    #[Route('/convention/{id}', name: 'convention_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Convention $convention): Response
    {
        $user = $this->getUser();

        // Only the student of the convention or the referent professor can view it
        if (
            !($user instanceof Student && $convention->getStudent() === $user)
            && !($user instanceof Professor && $convention->getReferentProfessor() === $user)
        ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette convention.');
        }

        // Use different template for professor referent
        if ($user instanceof Professor && $convention->getReferentProfessor() === $user) {
            return $this->render('convention/professor_show.html.twig', [
                'convention' => $convention,
            ]);
        }

        // Use different template for student
        if ($user instanceof Student && $convention->getStudent() === $user) {
            return $this->render('convention/student_show.html.twig', [
                'convention' => $convention,
            ]);
        }

        return $this->render('convention/show.html.twig', [
            'convention' => $convention,
            //'isReferentProfessor' => $user instanceof Professor && $convention->getReferentProfessor() === $user,
            //'isStudent' => $user instanceof Student,
            'isReferentProfessor' => false,
            'isStudent' => false,
        ]);
    }

    /**
     * L'étudiant demande la validation de sa convention par le professeur référent.
     */
    #[Route('/convention/{id}/request-validation', name: 'convention_request_validation', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_STUDENT')]
    public function requestValidation(Convention $convention, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Student || $convention->getStudent() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas demander la validation de cette convention.');
        }

        if (!$this->isCsrfTokenValid('request_validation_' . $convention->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        if (!$convention->isDraft()) {
            $this->addFlash('error', 'Cette convention ne peut pas être soumise à validation dans son état actuel.');
            return $this->redirectToRoute('convention_show', ['id' => $convention->getId()]);
        }

        $professor = $convention->getReferentProfessor();
        if (!$professor) {
            $this->addFlash('error', 'Aucun professeur référent n\'est associé à cette convention. Veuillez contacter votre établissement.');
            return $this->redirectToRoute('convention_show', ['id' => $convention->getId()]);
        }

        $convention->setStatus('pending_validation');
        $this->entityManager->flush();

        // Envoyer un email au professeur référent
        $this->sendValidationRequestEmail($convention);

        $this->addFlash('success', 'Votre demande de validation a été envoyée à ' . $professor->getFirstName() . ' ' . $professor->getLastName() . '.');

        return $this->redirectToRoute('convention_show', ['id' => $convention->getId()]);
    }

    /**
     * Le professeur référent valide la convention.
     */
    #[Route('/convention/{id}/validate', name: 'convention_validate', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROFESSOR')]
    public function validate(Convention $convention, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor || $convention->getReferentProfessor() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas le professeur référent de cette convention.');
        }

        if (!$this->isCsrfTokenValid('validate_convention_' . $convention->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        if (!$convention->isPendingValidation()) {
            $this->addFlash('error', 'Cette convention n\'est pas en attente de validation.');
            return $this->redirectToRoute('convention_show', ['id' => $convention->getId()]);
        }

        $convention->setStatus('validated');
        $convention->setValidatedAt(new \DateTime());
        $this->entityManager->flush();

        // Notifier l'étudiant
        $this->sendValidationConfirmationEmail($convention);

        $this->addFlash('success', 'La convention de ' . $convention->getStudent()->getFirstName() . ' ' . $convention->getStudent()->getLastName() . ' a été validée avec succès.');

        return $this->redirectToRoute('convention_show', ['id' => $convention->getId()]);
    }

    private function sendValidationRequestEmail(Convention $convention): void
    {
        $professor = $convention->getReferentProfessor();

        if (!$professor || !$professor->getEmail()) {
            return;
        }

        $conventionUrl = $this->urlGenerator->generate('convention_show', [
            'id' => $convention->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from('noreply@conventio.edu')
            ->to($professor->getEmail())
            ->subject(sprintf(
                'Demande de validation de convention — %s %s',
                $convention->getStudent()->getFirstName(),
                $convention->getStudent()->getLastName()
            ))
            ->htmlTemplate('emails/convention_validation_request.html.twig')
            ->context([
                'professor' => $professor,
                'convention' => $convention,
                'conventionUrl' => $conventionUrl,
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Ne pas bloquer si l'email échoue
        }
    }

    private function sendValidationConfirmationEmail(Convention $convention): void
    {
        $student = $convention->getStudent();

        if (!$student || !$student->getEmail()) {
            return;
        }

        $conventionUrl = $this->urlGenerator->generate('convention_show', [
            'id' => $convention->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from('noreply@conventio.edu')
            ->to($student->getEmail())
            ->subject('Votre convention de stage a été validée !')
            ->htmlTemplate('emails/convention_validated.html.twig')
            ->context([
                'student' => $student,
                'convention' => $convention,
                'conventionUrl' => $conventionUrl,
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Ne pas bloquer si l'email échoue
        }
    }
}
