<?php

namespace App\Repository;

use App\Entity\Convention;
use App\Entity\Professor;
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
     * Trouve toutes les conventions dont le professeur est référent
     */
    public function findByReferentProfessor(Professor $professor): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.referentProfessor = :professor')
            ->setParameter('professor', $professor)
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

    /**
     * Trouve les conventions validées
     * Retourne un tableau de conventions triées par level
     */
    public function findValidatedGroupedByLevel(): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.student', 's')
            ->leftJoin('s.levels', 'l')
            ->andWhere('c.status = :status')
            ->setParameter('status', 'validated')
            ->orderBy('l.levelName', 'ASC')
            ->addOrderBy('s.firstName', 'ASC')
            ->addOrderBy('s.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
