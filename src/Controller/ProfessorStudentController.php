<?php

namespace App\Controller;

use App\Entity\Professor;
use App\Repository\ConventionRepository;
use App\Repository\InternshipCompanyInfoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/professor')]
#[IsGranted('ROLE_PROFESSOR')]
class ProfessorStudentController extends AbstractController
{
    public function __construct(
        private ConventionRepository $conventionRepository,
        private InternshipCompanyInfoRepository $companyInfoRepository,
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
}
