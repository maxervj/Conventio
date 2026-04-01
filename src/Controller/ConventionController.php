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

#[Route('/conventions')]
#[IsGranted('ROLE_USER')]
class ConventionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConventionRepository $conventionRepository,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    /**
     * Liste des conventions : pour le prof référent, toutes ses conventions ; pour l'étudiant, les siennes.
     */

}

