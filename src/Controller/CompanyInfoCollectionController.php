<?php

namespace App\Controller;

use App\Entity\Convention;
use App\Entity\InternshipCompanyInfo;
use App\Entity\Student;
use App\Form\CollectionRequestFormType;
use App\Form\InternshipCompanyInfoFormType;
use App\Repository\ConventionRepository;
use App\Repository\InternshipCompanyInfoRepository;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyInfoCollectionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InternshipCompanyInfoRepository $companyInfoRepository,
        private ConventionRepository $conventionRepository,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private PdfGeneratorService $pdfGenerator,
    ) {}

    // Liste des collectes d'informations de l'étudiant
    #[Route('/student/my-requests', name: 'student_my_requests', methods: ['GET'])]
    #[IsGranted('ROLE_STUDENT')]
    public function myRequests(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Student) {
            throw $this->createAccessDeniedException('Cette fonctionnalité est réservée aux étudiants.');
        }

        // Récupérer toutes les collectes d'informations de l'étudiant
        $companyInfos = $this->companyInfoRepository->findBy(
            ['student' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('student/my_requests.html.twig', [
            'companyInfos' => $companyInfos,
        ]);
    }

    // Formulaire pour que l'étudiant demande la collecte d'informations à une entreprise
    #[Route('/student/request-company-info', name: 'student_request_company_info', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function requestCompanyInfo(Request $request): Response
    {
        $user = $this->getUser();


        if (!$user instanceof Student) {
            throw $this->createAccessDeniedException('Cette fonctionnalité est réservée aux étudiants.');
        }
        // Crée le formulaire de demande
        $form = $this->createForm(CollectionRequestFormType::class);
        $form->handleRequest($request);
        // Traite la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Create new collection request
            $companyInfo = new InternshipCompanyInfo();
            $companyInfo->setStudent($user);
            $companyInfo->setCompanyName($data['companyName']);
            $companyInfo->setContactName($data['contactName']);
            $companyInfo->setContactEmail($data['contactEmail']);
            $companyInfo->setInternshipStartDate($data['internshipStartDate']);
            $companyInfo->setInternshipEndDate($data['internshipEndDate']);

            // Le token est généré automatiquement dans le constructeur de InternshipCompanyInfo
            // Vérifier que le token est bien présent
            if (!$companyInfo->getToken()) {
                $companyInfo->setToken(bin2hex(random_bytes(32)));
            }

            $this->entityManager->persist($companyInfo);

            // Crée automatiquement la convention liée à cette collecte
            $convention = new Convention();
            $convention->setStudent($user);
            $convention->setCompanyInfo($companyInfo);

            // Assigne le prof référent depuis les niveaux de l'étudiant
            $professor = null;
            foreach ($user->getLevels() as $level) {
                foreach ($level->getReferentProfessors() as $ref) {
                    $professor = $ref;
                    break 2;
                }
                foreach ($level->getTeachers() as $teacher) {
                    $professor = $teacher;
                    break 2;
                }
            }
            if ($professor) {
                $convention->setReferentProfessor($professor);
            }

            $this->entityManager->persist($convention);
            $this->entityManager->flush();

            // Envoi de l'email
            $emailError = null;
            try {
                $this->sendCollectionRequestEmail($companyInfo);
            } catch (\Exception $e) {
                $emailError = $e->getMessage();
            }

            // Redirige vers la page de confirmation de l'envoi
            return $this->redirectToRoute('student_request_success', [
                'contactEmail' => $companyInfo->getContactEmail(),
                'emailError' => $emailError,
            ]);

        }
        // Affiche le formulaire de demande
        return $this->render('company_info/request_form.html.twig', [
            'form' => $form->createView(),
        ]);


    }

    // Page de confirmation après envoi de la demande
    #[Route('/student/request-success', name: 'student_request_success', methods: ['GET'])]
    #[IsGranted('ROLE_STUDENT')]
    public function requestSuccess(Request $request): Response
    {
        return $this->render('company_info/request_success.html.twig', [
            'contactEmail' => $request->query->get('contactEmail'),
            'emailError' => $request->query->get('emailError'),
        ]);
    }

    // Formulaire de collecte d'informations auprès de l'entreprise
    #[Route('/company-info/{token}', name: 'company_info_form', methods: ['GET', 'POST'])]
    public function form(string $token, Request $request): Response
    {
        // Set locale from query parameter if provided
        $locale = $request->query->get('lang', 'fr');
        $request->setLocale($locale);

        // Save locale in session for persistence
        $session = $request->getSession();
        $session->set('_locale', $locale);

        $companyInfo = $this->companyInfoRepository->findValidToken($token);

        if (!$companyInfo) {
            $existingInfo = $this->companyInfoRepository->findByToken($token);

            if (!$existingInfo) {
                return $this->render('company_info/error.html.twig', [
                    'error' => 'error_invalid'
                ]);
            }

            if ($existingInfo->isCompleted()) {
                return $this->render('company_info/error.html.twig', [
                    'error' => 'error_completed'
                ]);
            }

            if ($existingInfo->isExpired()) {
                return $this->render('company_info/error.html.twig', [
                    'error' => 'error_expired'
                ]);
            }

            // Token trouvé mais findValidToken n'a pas retourné de résultat (ex: expiresAt null)
            // On utilise directement l'entité existante
            $companyInfo = $existingInfo;
        }

        // Check if confirmation step
        $isConfirmation = $request->query->get('confirm', false);

        $form = $this->createForm(InternshipCompanyInfoFormType::class, $companyInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gère les horaires de travail séparément
            $workSchedule = $request->request->all('work_schedule');
            if ($workSchedule) {
                $companyInfo->setWorkSchedule($workSchedule);
            }

            // Valide que l'au moins un numéro de téléphone est fourni
            if (!$companyInfo->getLandlinePhone() && !$companyInfo->getMobilePhone()) {
                $this->addFlash('error', $this->translator->trans('company_info.at_least_one_phone'));
                return $this->render('company_info/form.html.twig', [
                    'form' => $form->createView(),
                    'companyInfo' => $companyInfo,
                    'token' => $token,
                    'locale' => $locale
                ]);
            }

            $this->entityManager->persist($companyInfo);
            $this->entityManager->flush();

            // Redirect to confirmation page
            return $this->redirectToRoute('company_info_confirm', [
                'token' => $token,
                'lang' => $locale
            ]);
        }

        return $this->render('company_info/form.html.twig', [
            'form' => $form->createView(),
            'companyInfo' => $companyInfo,
            'token' => $token,
            'locale' => $locale
        ]);
    }
    // Page de confirmation avant soumission finale
    #[Route('/company-info/{token}/confirm', name: 'company_info_confirm', methods: ['GET', 'POST'])]
    public function confirm(string $token, Request $request): Response
    {
        $locale = $request->query->get('lang', $request->getSession()->get('_locale', 'fr'));
        $request->setLocale($locale);
        $request->getSession()->set('_locale', $locale);

        $companyInfo = $this->companyInfoRepository->findValidToken($token);

        if (!$companyInfo) {
            return $this->redirectToRoute('company_info_form', ['token' => $token, 'lang' => $locale]);
        }

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');

            if ($action === 'modify') {
                return $this->redirectToRoute('company_info_form', [
                    'token' => $token,
                    'lang' => $locale
                ]);
            }

            if ($action === 'confirm') {
                // Marquer comme complété
                $companyInfo->setIsCompleted(true);
                $companyInfo->setCompletedAt(new \DateTime());

                // Passe la convention en attente de validation prof
                $convention = $this->conventionRepository->findByCompanyInfo($companyInfo);
                if ($convention && $convention->isDraft()) {
                    $convention->setStatus('pending_validation');
                }

                $this->entityManager->flush();

                // Send email to student
                $this->sendNotificationToStudent($companyInfo);

                return $this->redirectToRoute('company_info_success', [
                    'token' => $token,
                    'lang' => $locale
                ]);
            }
        }

        return $this->render('company_info/confirmation.html.twig', [
            'companyInfo' => $companyInfo,
            'token' => $token,
            'locale' => $locale
        ]);
    }
    /**
     * L'étudiant télécharge le PDF de sa collecte d'informations (avec ancre de signature Yousign).
     */
    #[Route('/student/company-info/{id}/pdf', name: 'student_company_info_pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_STUDENT')]
    public function downloadPdfForStudent(InternshipCompanyInfo $companyInfo): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Student || $companyInfo->getStudent() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce document.');
        }

        $convention = $this->conventionRepository->findOneBy(['companyInfo' => $companyInfo]);

        return $this->pdfGenerator->streamCollecteInfoPdf($companyInfo, $convention);
    }

    /**
     * Téléchargement public du PDF de collecte via le token (accessible par l'entreprise).
     * Disponible uniquement si la collecte est complétée.
     */
    #[Route('/company-info/{token}/pdf', name: 'company_info_pdf', methods: ['GET'])]
    public function downloadPdfByToken(string $token): Response
    {
        $companyInfo = $this->companyInfoRepository->findByToken($token);

        if (!$companyInfo) {
            throw $this->createNotFoundException('Collecte d\'informations introuvable.');
        }

        if (!$companyInfo->isCompleted()) {
            throw $this->createAccessDeniedException('Le PDF n\'est disponible qu\'après complétion du formulaire.');
        }

        $convention = $this->conventionRepository->findOneBy(['companyInfo' => $companyInfo]);

        return $this->pdfGenerator->streamCollecteInfoPdf($companyInfo, $convention);
    }

    // Page de succès après soumission
    #[Route('/company-info/{token}/success', name: 'company_info_success', methods: ['GET'])]
    public function success(string $token, Request $request): Response
    {
        $locale = $request->query->get('lang', $request->getSession()->get('_locale', 'fr'));
        $request->setLocale($locale);
        $request->getSession()->set('_locale', $locale);

        $companyInfo = $this->companyInfoRepository->findByToken($token);

        if (!$companyInfo || !$companyInfo->isCompleted()) {
            return $this->redirectToRoute('company_info_form', ['token' => $token, 'lang' => $locale]);
        }

        return $this->render('company_info/success.html.twig', [
            'locale' => $locale,
            'companyInfo' => $companyInfo,
        ]);
    }
    // Envoie une notification par email à l'étudiant une fois les informations soumises
    private function sendNotificationToStudent(InternshipCompanyInfo $companyInfo): void
    {
        $student = $companyInfo->getStudent();

        if (!$student || !$student->getEmail()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from('noreply@conventio.edu')
            ->to($student->getEmail())
            ->subject($this->translator->trans('email.company_info_completed_subject'))
            ->htmlTemplate('emails/company_info_completed.html.twig')
            ->context([
                'student' => $student,
                'companyInfo' => $companyInfo
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error but don't fail the request
        }
    }
    // Envoie un email à l'entreprise pour collecter les informations
    private function sendCollectionRequestEmail(InternshipCompanyInfo $companyInfo): void
    {
        $student = $companyInfo->getStudent();
        $contactEmail = $companyInfo->getContactEmail();

        if (!$contactEmail) {
            throw new \RuntimeException('Aucune adresse email de contact n\'a été fournie.');
        }

        if (!$student) {
            throw new \RuntimeException('Aucun étudiant associé à cette demande.');
        }

        // Vérifier que le token existe
        $token = $companyInfo->getToken();
        if (!$token) {
            throw new \RuntimeException('Aucun token n\'a été généré pour cette demande.');
        }

        // Generate collection URL with token
        $collectionUrl = $this->urlGenerator->generate('company_info_form', [
            'token' => $token,
            'lang' => 'fr'
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $startDate = $companyInfo->getInternshipStartDate() ? $companyInfo->getInternshipStartDate()->format('d/m/Y') : 'Non spécifiée';
        $endDate = $companyInfo->getInternshipEndDate() ? $companyInfo->getInternshipEndDate()->format('d/m/Y') : 'Non spécifiée';

        $email = (new TemplatedEmail())
            ->from('noreply@conventio.edu')
            ->to($contactEmail)
            ->subject(sprintf('Convention de stage - %s - %s %s du %s au %s',
                $companyInfo->getCompanyName(),
                $student->getFirstName(),
                $student->getLastName(),
                $startDate,
                $endDate
            ))
            ->htmlTemplate('emails/collection_request.html.twig')
            ->context([
                'student' => $student,
                'contactName' => $companyInfo->getContactName(),
                'companyName' => $companyInfo->getCompanyName(),
                'collectionUrl' => $collectionUrl,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'expiresAt' => $companyInfo->getExpiresAt()
            ]);

        // Envoyer l'email et laisser l'exception remonter si ça échoue
        $this->mailer->send($email);
    }
}
