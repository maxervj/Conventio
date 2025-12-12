<?php

namespace App\Service;

use App\Entity\LoginAttempt;
use App\Repository\LoginAttemptRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginAttemptService
{
    private const MAX_ATTEMPTS = 3;
    private const LOCKOUT_TIME_MINUTES = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoginAttemptRepository $attemptRepository,
        private RequestStack $requestStack
    ) {
    }

    /**
     * Check if an email is currently locked out due to too many failed attempts
     */
    public function isLockedOut(string $email): bool
    {
        $since = new \DateTime(sprintf('-%d minutes', self::LOCKOUT_TIME_MINUTES));
        $failedAttempts = $this->attemptRepository->countFailedAttempts($email, $since);

        return $failedAttempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Get the time remaining until lockout expires
     */
    public function getLockoutTimeRemaining(string $email): ?int
    {
        $since = new \DateTime(sprintf('-%d minutes', self::LOCKOUT_TIME_MINUTES));
        $attempts = $this->attemptRepository->getFailedAttempts($email, $since);

        if (count($attempts) < self::MAX_ATTEMPTS) {
            return null;
        }

        // Get the oldest attempt in the lockout window
        $oldestAttempt = end($attempts);
        $lockoutExpires = (clone $oldestAttempt->getAttemptedAt())
            ->modify(sprintf('+%d minutes', self::LOCKOUT_TIME_MINUTES));

        $now = new \DateTime();
        $diff = $lockoutExpires->getTimestamp() - $now->getTimestamp();

        return max(0, (int) ceil($diff / 60)); // Return minutes remaining
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt(string $email): void
    {
        $attempt = new LoginAttempt();
        $attempt->setEmail($email);
        $attempt->setIpAddress($this->getClientIp());
        $attempt->setSuccessful(false);

        $this->entityManager->persist($attempt);
        $this->entityManager->flush();
    }

    /**
     * Record a successful login attempt and clear failed attempts
     */
    public function recordSuccessfulAttempt(string $email): void
    {
        $attempt = new LoginAttempt();
        $attempt->setEmail($email);
        $attempt->setIpAddress($this->getClientIp());
        $attempt->setSuccessful(true);

        $this->entityManager->persist($attempt);
        $this->entityManager->flush();
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return '0.0.0.0';
        }

        return $request->getClientIp() ?? '0.0.0.0';
    }

    /**
     * Get remaining attempts before lockout
     */
    public function getRemainingAttempts(string $email): int
    {
        $since = new \DateTime(sprintf('-%d minutes', self::LOCKOUT_TIME_MINUTES));
        $failedAttempts = $this->attemptRepository->countFailedAttempts($email, $since);

        return max(0, self::MAX_ATTEMPTS - $failedAttempts);
    }

    /**
     * Clean up old login attempts (should be called periodically)
     */
    public function cleanupOldAttempts(): int
    {
        $before = new \DateTime('-30 days');
        return $this->attemptRepository->deleteOldAttempts($before);
    }
}
