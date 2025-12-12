<?php

namespace App\Repository;

use App\Entity\LoginAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LoginAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginAttempt::class);
    }

    /**
     * Get failed login attempts for an email within a time window
     */
    public function getFailedAttempts(string $email, \DateTimeInterface $since): array
    {
        return $this->createQueryBuilder('la')
            ->where('la.email = :email')
            ->andWhere('la.successful = :successful')
            ->andWhere('la.attemptedAt >= :since')
            ->setParameter('email', $email)
            ->setParameter('successful', false)
            ->setParameter('since', $since)
            ->orderBy('la.attemptedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count failed login attempts for an email within a time window
     */
    public function countFailedAttempts(string $email, \DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('la')
            ->select('COUNT(la.id)')
            ->where('la.email = :email')
            ->andWhere('la.successful = :successful')
            ->andWhere('la.attemptedAt >= :since')
            ->setParameter('email', $email)
            ->setParameter('successful', false)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Delete old login attempts (cleanup)
     */
    public function deleteOldAttempts(\DateTimeInterface $before): int
    {
        return $this->createQueryBuilder('la')
            ->delete()
            ->where('la.attemptedAt < :before')
            ->setParameter('before', $before)
            ->getQuery()
            ->execute();
    }
}
