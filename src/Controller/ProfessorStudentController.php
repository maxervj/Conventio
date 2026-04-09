<?php

namespace App\Controller;

use App\Entity\Professor;
use App\Entity\Student;
use App\Entity\Level;
use App\Entity\InternshipCompanyInfo;
use App\Repository\StudentRepository;
use App\Repository\LevelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/professors')]
#[IsGranted('ROLE_PROFESSOR')]
class ProfessorStudentController extends AbstractController
{
    public function __construct(
        private StudentRepository $studentRepository,
        private LevelRepository $levelRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Liste des étudiants dont le professeur enseigne les niveaux
     */
    #[Route('/my-students', name: 'professor_my_students', methods: ['GET'])]
    public function myStudents(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer les niveaux enseignés par le professeur
        $levels = $user->getTaughtLevels();

        // Récupérer tous les étudiants de ces niveaux
        $students = [];
        foreach ($levels as $level) {
            $studentsInLevel = $level->getStudents()->toArray();
            $students = array_merge($students, $studentsInLevel);
        }

        // Supprimer les doublons
        $students = array_unique($students, SORT_REGULAR);

        return $this->render('professor/my_students.html.twig', [
            'students' => $students,
            'professor' => $user,
            'levels' => $levels,
        ]);
    }

    /**
     * Afficher le formulaire pour ajouter un étudiant existant à un niveau
     */
    #[Route('/students/new', name: 'professor_student_new', methods: ['GET', 'POST'])]
    public function newStudent(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            return $this->assignStudent($request);
        }

        // Récupérer les niveaux enseignés par le professeur
        $levels = $user->getTaughtLevels();

        // Récupérer tous les étudiants qui ne sont pas encore dans les niveaux du professeur
        $allStudents = $this->studentRepository->findAll();
        $availableStudents = [];

        foreach ($allStudents as $student) {
            // Vérifier si l'étudiant n'est dans aucun des niveaux du prof
            $isInProfessorLevel = false;
            foreach ($student->getLevels() as $studentLevel) {
                if ($levels->contains($studentLevel)) {
                    $isInProfessorLevel = true;
                    break;
                }
            }

            // Si l'étudiant n'est pas dans les niveaux du prof, l'ajouter à la liste
            if (!$isInProfessorLevel) {
                $availableStudents[] = $student;
            }
        }

        // Trier par nom
        usort($availableStudents, function($a, $b) {
            return strcmp($a->getLastName() . $a->getFirstName(), $b->getLastName() . $b->getFirstName());
        });

        return $this->render('professor/student_new.html.twig', [
            'professor' => $user,
            'levels' => $levels,
            'availableStudents' => $availableStudents,
        ]);
    }

    /**
     * Voir le détail d'un étudiant
     */
    #[Route('/students/{id}', name: 'professor_student_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showStudent(Student $student): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        // Vérifier que l'étudiant appartient à au moins un niveau du professeur
        $isStudentOfProfessor = false;
        foreach ($user->getTaughtLevels() as $level) {
            if ($student->getLevels()->contains($level)) {
                $isStudentOfProfessor = true;
                break;
            }
        }

        if (!$isStudentOfProfessor) {
            throw $this->createAccessDeniedException('Cet étudiant ne fait pas partie de vos niveaux.');
        }

        return $this->render('professor/student_show.html.twig', [
            'student' => $student,
            'professor' => $user,
        ]);
    }

    /**
     * Assigner un étudiant existant à un niveau du professeur
     */
    private function assignStudent(Request $request): Response
    {
        $user = $this->getUser();

        // Récupérer les données du formulaire
        $studentId = $request->request->get('student');
        $levelId = $request->request->get('level');

        // Validation basique
        if (!$studentId || !$levelId) {
            $this->addFlash('error', 'Vous devez sélectionner un étudiant et un niveau.');
            return $this->redirectToRoute('professor_student_new');
        }

        // Récupérer l'étudiant
        $student = $this->studentRepository->find($studentId);
        if (!$student) {
            $this->addFlash('error', 'Étudiant non trouvé.');
            return $this->redirectToRoute('professor_student_new');
        }

        // Vérifier que le niveau appartient au professeur
        $level = null;
        foreach ($user->getTaughtLevels() as $prof_level) {
            if ((string)$prof_level->getId() === $levelId) {
                $level = $prof_level;
                break;
            }
        }

        if (!$level) {
            throw $this->createAccessDeniedException('Ce niveau ne vous appartient pas.');
        }

        // Assigner l'étudiant au niveau
        if (!$student->getLevels()->contains($level)) {
            $student->addLevel($level);
            $this->entityManager->flush();

            $this->addFlash('success', 'Étudiant ' . $student->getFirstName() . ' ' . $student->getLastName() . ' ajouté au niveau ' . $level->getLevelName() . ' !');
        } else {
            $this->addFlash('warning', 'Cet étudiant est déjà dans ce niveau.');
        }

        return $this->redirectToRoute('professor_my_students');
    }

    /**
     * Afficher les collectes (InternshipCompanyInfo) complétées d'un étudiant
     */
    #[Route('/students/{id}/collections', name: 'professor_student_collections', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showCollections(Student $student): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Professor) {
            throw $this->createAccessDeniedException();
        }

        // Vérifier que l'étudiant est dans l'un des niveaux du professeur
        $isStudentOfProfessor = false;
        foreach ($user->getTaughtLevels() as $level) {
            if ($student->getLevels()->contains($level)) {
                $isStudentOfProfessor = true;
                break;
            }
        }

        if (!$isStudentOfProfessor) {
            throw $this->createAccessDeniedException('Cet étudiant ne fait pas partie de vos niveaux.');
        }

        // Récupérer les collectes complétées de cet étudiant
        $collections = $this->entityManager
            ->getRepository(InternshipCompanyInfo::class)
            ->findBy(['student' => $student, 'isCompleted' => true], ['completedAt' => 'DESC']);

        return $this->render('professor/student_collections.html.twig', [
            'student' => $student,
            'professor' => $user,
            'collections' => $collections,
        ]);
    }
}


