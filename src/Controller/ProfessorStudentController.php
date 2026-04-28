<?php

namespace App\Controller;

use App\Entity\Convention;
use App\Entity\Professor;
use App\Repository\ConventionRepository;
use App\Repository\InternshipCompanyInfoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;

#[Route('/professor')]
#[IsGranted('ROLE_PROFESSOR')]
class ProfessorStudentController extends AbstractController
{
    public function __construct(
        private ConventionRepository $conventionRepository,
        private InternshipCompanyInfoRepository $companyInfoRepository,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    #[Route('/my-students', name: 'professor_my_students', methods: ['GET'])]
    public function myStudents(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        $studentsByLevel = [];

        foreach ($user->getTaughtLevels() as $level) {
            if ($level->getStudents()->count() > 0) {
                $studentsByLevel[] = [
                    'level' => $level,
                    'students' => $level->getStudents()->toArray(),
                ];
            }
        }

        if ($user->getReferentLevel()) {
            $refLevel = $user->getReferentLevel();
            $alreadyIncluded = array_filter($studentsByLevel, fn($g) => $g['level'] === $refLevel);
            if (empty($alreadyIncluded) && $refLevel->getStudents()->count() > 0) {
                array_unshift($studentsByLevel, [
                    'level' => $refLevel,
                    'students' => $refLevel->getStudents()->toArray(),
                ]);
            }
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'studentsByLevel' => $studentsByLevel,
            'conventions' => $this->conventionRepository->findByReferentProfessor($user),
        ]);
    }

    #[Route('/collectes', name: 'professor_collectes', methods: ['GET'])]
    public function collectes(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        // Collecte les étudiants de toutes les classes du prof
        $students = [];
        foreach ($user->getTaughtLevels() as $level) {
            foreach ($level->getStudents() as $student) {
                $students[$student->getId()] = $student;
            }
        }
        if ($user->getReferentLevel()) {
            foreach ($user->getReferentLevel()->getStudents() as $student) {
                $students[$student->getId()] = $student;
            }
        }

        $collectes = [];
        foreach ($students as $student) {
            foreach ($this->companyInfoRepository->findBy(['student' => $student], ['createdAt' => 'DESC']) as $info) {
                $collectes[] = $info;
            }
        }

        return $this->render('professor/collectes.html.twig', [
            'collectes' => $collectes,
        ]);
    }

    #[Route('/collectes/{id}', name: 'professor_collecte_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showCollecte(int $id): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        $companyInfo = $this->companyInfoRepository->find($id);
        if (!$companyInfo) {
            throw $this->createNotFoundException('Collecte introuvable.');
        }

        $convention = $this->conventionRepository->findByCompanyInfo($companyInfo);

        $isReferentProfessor = $convention && $convention->getReferentProfessor() === $user;

        return $this->render('professor/collecte_show.html.twig', [
            'companyInfo' => $companyInfo,
            'convention' => $convention,
            'isReferentProfessor' => $isReferentProfessor,
        ]);
    }

    /**
     * Crée une convention depuis une collecte complétée.
     * Le professeur référent peut créer la convention directement sans attendre l'étudiant.
     */
    #[Route('/collectes/{id}/create-convention', name: 'professor_collecte_create_convention', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function createConventionFromCollecte(int $id, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        $companyInfo = $this->companyInfoRepository->find($id);
        if (!$companyInfo) {
            throw $this->createNotFoundException('Collecte introuvable.');
        }

        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('create_convention_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Vérifier si l'étudiant de la collecte est bien un référé du prof
        $student = $companyInfo->getStudent();
        if (!$this->isProfessorReferent($user, $student)) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas le professeur référent de cet étudiant.');
        }

        // Vérifier que la collecte est bien complétée
        if (!$companyInfo->isCompleted()) {
            $this->addFlash('error', 'La collecte doit être complétée avant de créer une convention.');
            return $this->redirectToRoute('professor_collecte_show', ['id' => $id]);
        }

        // Vérifier s'il n'existe pas déjà une convention pour cette collecte
        $existingConvention = $this->conventionRepository->findByCompanyInfo($companyInfo);
        if ($existingConvention) {
            $this->addFlash('warning', 'Une convention existe déjà pour cette collecte.');
            return $this->redirectToRoute('professor_collecte_show', ['id' => $id]);
        }

        // Créer la convention directement en statut "validated"
        $convention = new Convention();
        $convention->setStudent($student);
        $convention->setCompanyInfo($companyInfo);
        $convention->setReferentProfessor($user);
        $convention->setStatus('validated');
        $convention->setValidatedAt(new \DateTime());

        $this->entityManager->persist($convention);
        $this->entityManager->flush();

        $this->addFlash('success', 'Convention créée et validée avec succès !');

        return $this->redirectToRoute('professor_collecte_show', ['id' => $id]);
    }

    /**
     * Valide la convention directement depuis la page de collecte (si elle n'est pas encore validée).
     */
    #[Route('/collectes/{id}/validate-convention', name: 'professor_collecte_validate_convention', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function validateConventionFromCollecte(int $id, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        $companyInfo = $this->companyInfoRepository->find($id);
        if (!$companyInfo) {
            throw $this->createNotFoundException('Collecte introuvable.');
        }

        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('validate_convention_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Récupérer la convention associée à la collecte
        $convention = $this->conventionRepository->findByCompanyInfo($companyInfo);
        if (!$convention) {
            $this->addFlash('error', 'Aucune convention n\'est associée à cette collecte.');
            return $this->redirectToRoute('professor_collecte_show', ['id' => $id]);
        }

        // Vérifier que l'utilisateur est le prof référent
        if ($convention->getReferentProfessor() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas le professeur référent de cette convention.');
        }

        // Vérifier que la convention est en attente de validation
        if (!$convention->isPendingValidation()) {
            $this->addFlash('error', 'Cette convention n\'est pas en attente de validation.');
            return $this->redirectToRoute('professor_collecte_show', ['id' => $id]);
        }

        // Valider la convention
        $convention->setStatus('validated');
        $convention->setValidatedAt(new \DateTime());
        $this->entityManager->flush();

        $this->addFlash('success', 'Convention validée avec succès !');

        return $this->redirectToRoute('professor_collecte_show', ['id' => $id]);
    }

    /**
     * Vérifie si un professeur est le référent pédagogique d'un étudiant.
     */
    private function isProfessorReferent(Professor $professor, mixed $student): bool
    {
        // Vérifier si le prof enseigne une classe contenant cet étudiant
        foreach ($professor->getTaughtLevels() as $level) {
            if ($level->getStudents()->contains($student)) {
                return true;
            }
        }

        // Vérifier si le prof est le prof référent du niveau de l'étudiant
        if ($professor->getReferentLevel()) {
            foreach ($student->getLevels() as $level) {
                if ($level === $professor->getReferentLevel()) {
                    return true;
                }
            }
        }

        return false;
    }
}
