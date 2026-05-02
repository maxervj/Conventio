<?php

namespace App\Controller;

use App\Entity\Convention;
use App\Entity\Professor;
use App\Entity\Student;
use App\Entity\Level;
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

        // Récupérer tous les étudiants du professeur
        $students = [];
        foreach ($user->getTaughtLevels() as $level) {
            foreach ($level->getStudents() as $student) {
                if (!isset($students[$student->getId()])) {
                    $students[$student->getId()] = $student;
                }
            }
        }
        if ($user->getReferentLevel()) {
            foreach ($user->getReferentLevel()->getStudents() as $student) {
                if (!isset($students[$student->getId()])) {
                    $students[$student->getId()] = $student;
                }
            }
        }

        // Récupérer les niveaux pour les stats
        $levels = [];
        foreach ($user->getTaughtLevels() as $level) {
            if (!isset($levels[$level->getId()])) {
                $levels[$level->getId()] = $level;
            }
        }
        if ($user->getReferentLevel() && !isset($levels[$user->getReferentLevel()->getId()])) {
            $levels[$user->getReferentLevel()->getId()] = $user->getReferentLevel();
        }

        return $this->render('professor/my_students.html.twig', [
            'user' => $user,
            'students' => array_values($students),
            'levels' => array_values($levels),
        ]);
    }

    #[Route('/students/{id}', name: 'professor_student_show', methods: ['GET'])]
    public function showStudent(int $id): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('professor/student_show.html.twig', [
            'student' => $this->getStudentOrThrow($id),
        ]);
    }

    #[Route('/students/{id}/collections', name: 'professor_student_collections', methods: ['GET'])]
    public function studentCollections(int $id): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        $student = $this->getStudentOrThrow($id);
        $collections = $this->companyInfoRepository->findBy(['student' => $student], ['createdAt' => 'DESC']);

        return $this->render('professor/student_collections.html.twig', [
            'student' => $student,
            'collections' => $collections,
        ]);
    }

    private function getStudentOrThrow(int $id)
    {
        $user = $this->getUser();
        $entityManager = $this->entityManager;
        $student = $entityManager->getRepository(Student::class)->find($id);

        if (!$student) {
            throw $this->createNotFoundException('Étudiant introuvable.');
        }

        // Vérifier que le prof enseigne cet étudiant
        if (!$this->isProfessorReferent($user, $student)) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir cet étudiant.');
        }

        return $student;
    }

    #[Route('/students', name: 'professor_student_new', methods: ['GET', 'POST'])]
    public function newStudent(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer les niveaux du professeur
        $levels = $user->getTaughtLevels()->toArray();
        if (empty($levels) && $user->getReferentLevel()) {
            $levels[] = $user->getReferentLevel();
        }

        // Récupérer les étudiants disponibles (non assignés à ces niveaux)
        $assignedStudents = [];
        foreach ($levels as $level) {
            foreach ($level->getStudents() as $student) {
                $assignedStudents[$student->getId()] = $student;
            }
        }

        // Récupérer tous les étudiants
        $allStudents = $this->entityManager->getRepository(Student::class)->findAll();
        $availableStudents = array_filter($allStudents, fn($s) => !isset($assignedStudents[$s->getId()]));

        if ($request->isMethod('POST')) {
            $studentId = $request->request->get('student');
            $levelId = $request->request->get('level');

            if (!$studentId || !$levelId) {
                $this->addFlash('error', 'Veuillez sélectionner un étudiant et un niveau.');
                return $this->redirectToRoute('professor_student_new');
            }

            $student = $this->entityManager->getRepository(Student::class)->find($studentId);
            $level = $this->entityManager->getRepository(Level::class)->find($levelId);

            if (!$student || !$level) {
                $this->addFlash('error', 'Étudiant ou niveau introuvable.');
                return $this->redirectToRoute('professor_student_new');
            }

            // Vérifier que le prof enseigne ce niveau
            if (!in_array($level, $levels)) {
                throw $this->createAccessDeniedException('Vous ne pouvez pas assigner un étudiant à ce niveau.');
            }

            // Assigner l'étudiant au niveau
            if (!$level->getStudents()->contains($student)) {
                $level->addStudent($student);
                $this->entityManager->flush();
                $this->addFlash('success', 'Étudiant assigné avec succès à ' . $level->getLevelName());
            }

            return $this->redirectToRoute('professor_my_students');
        }

        return $this->render('professor/student_new.html.twig', [
            'availableStudents' => array_values($availableStudents),
            'levels' => $levels,
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
