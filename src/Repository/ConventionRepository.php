<?php

namespace App\Repository;

use App\Entity\Convention;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Convention>
 */
class ConventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Convention::class);
    }

    /**
     * Trouve toutes les conventions d'un étudiant
     */
    public function findByStudent(Student $student): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.student = :student')
            ->setParameter('student', $student)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les conventions en attente de validation
     */
    public function findPendingValidation(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameter('status', 'pending_validation')
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les conventions validées mais non signées
     */
    public function findValidatedNotSigned(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameter('status', 'validated')
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
