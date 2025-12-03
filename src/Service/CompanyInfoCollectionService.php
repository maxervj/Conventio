<?php

namespace App\Service;

use App\Entity\InternshipCompanyInfo;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyInfoCollectionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator
    ) {}

    /**
     * Create a new company info collection request for a student
     */
    public function createCollectionRequest(Student $student, int $expirationDays = 30): InternshipCompanyInfo
    {
        $companyInfo = new InternshipCompanyInfo();
        $companyInfo->setStudent($student);

        // Set expiration date
        $expiresAt = new \DateTime();
        $expiresAt->modify("+{$expirationDays} days");
        $companyInfo->setExpiresAt($expiresAt);

        $this->entityManager->persist($companyInfo);
        $this->entityManager->flush();

        return $companyInfo;
    }

    /**
     * Generate the collection form URL with language parameter
     */
    public function generateCollectionUrl(InternshipCompanyInfo $companyInfo, string $locale = 'fr'): string
    {
        return $this->urlGenerator->generate(
            'company_info_form',
            [
                'token' => $companyInfo->getToken(),
                'lang' => $locale
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * Send collection email to company
     */
    public function sendCollectionEmail(
        InternshipCompanyInfo $companyInfo,
        string $companyEmail,
        string $locale = 'fr'
    ): void {
        $url = $this->generateCollectionUrl($companyInfo, $locale);

        $email = (new TemplatedEmail())
            ->from('noreply@conventio.edu')
            ->to($companyEmail)
            ->subject($this->translator->trans('email.collection_request_subject', [], 'messages', $locale))
            ->htmlTemplate('emails/company_info_request.html.twig')
            ->context([
                'student' => $companyInfo->getStudent(),
                'url' => $url,
                'expiresAt' => $companyInfo->getExpiresAt(),
                'locale' => $locale
            ]);

        $this->mailer->send($email);
    }

    /**
     * Check if a token is valid (not expired and not completed)
     */
    public function isTokenValid(string $token): bool
    {
        $companyInfo = $this->entityManager
            ->getRepository(InternshipCompanyInfo::class)
            ->findByToken($token);

        if (!$companyInfo) {
            return false;
        }

        return !$companyInfo->isCompleted() && !$companyInfo->isExpired();
    }

    /**
     * Get statistics about collection requests
     */
    public function getStatistics(): array
    {
        $repository = $this->entityManager->getRepository(InternshipCompanyInfo::class);

        $qb = $repository->createQueryBuilder('i');

        $total = (clone $qb)->select('COUNT(i.id)')->getQuery()->getSingleScalarResult();

        $completed = (clone $qb)
            ->select('COUNT(i.id)')
            ->where('i.isCompleted = true')
            ->getQuery()
            ->getSingleScalarResult();

        $expired = (clone $qb)
            ->select('COUNT(i.id)')
            ->where('i.expiresAt < :now')
            ->andWhere('i.isCompleted = false')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();

        $pending = $total - $completed - $expired;

        return [
            'total' => $total,
            'completed' => $completed,
            'expired' => $expired,
            'pending' => $pending,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
        ];
    }
}
