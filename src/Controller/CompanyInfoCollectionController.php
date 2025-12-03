<?php

namespace App\Controller;

use App\Entity\InternshipCompanyInfo;
use App\Form\CompanyInfoFormType;
use App\Repository\InternshipCompanyInfoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyInfoCollectionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InternshipCompanyInfoRepository $companyInfoRepository,
        private MailerInterface $mailer,
        private TranslatorInterface $translator
    ) {}

    #[Route('/company-info/{token}', name: 'company_info_form', methods: ['GET', 'POST'])]
    public function form(string $token, Request $request): Response
    {
        // Set locale from query parameter if provided
        $locale = $request->query->get('lang', 'fr');
        $request->setLocale($locale);

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
        }

        // Check if confirmation step
        $isConfirmation = $request->query->get('confirm', false);

        $form = $this->createForm(CompanyInfoFormType::class, $companyInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle work schedule from request (will be processed via JavaScript)
            $workSchedule = $request->request->all('work_schedule');
            if ($workSchedule) {
                $companyInfo->setWorkSchedule($workSchedule);
            }

            // Validate that at least one phone is provided
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

    #[Route('/company-info/{token}/confirm', name: 'company_info_confirm', methods: ['GET', 'POST'])]
    public function confirm(string $token, Request $request): Response
    {
        $locale = $request->query->get('lang', 'fr');
        $request->setLocale($locale);

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
                // Mark as completed
                $companyInfo->setIsCompleted(true);
                $companyInfo->setCompletedAt(new \DateTime());
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

    #[Route('/company-info/{token}/success', name: 'company_info_success', methods: ['GET'])]
    public function success(string $token, Request $request): Response
    {
        $locale = $request->query->get('lang', 'fr');
        $request->setLocale($locale);

        $companyInfo = $this->companyInfoRepository->findByToken($token);

        if (!$companyInfo || !$companyInfo->isCompleted()) {
            return $this->redirectToRoute('company_info_form', ['token' => $token, 'lang' => $locale]);
        }

        return $this->render('company_info/success.html.twig', [
            'locale' => $locale
        ]);
    }

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
}
