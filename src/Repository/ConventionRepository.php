<?php

namespace App\Repository;

use App\Entity\Convention;
use App\Entity\InternshipCompanyInfo;
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
     * Trouve la convention liée à une collecte d'informations
     */
    public function findByCompanyInfo(InternshipCompanyInfo $companyInfo): ?Convention
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.companyInfo = :companyInfo')
            ->setParameter('companyInfo', $companyInfo)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Toutes les conventions actives (hors brouillon) pour la vue admin
     */
    public function findAllActiveForAdmin(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.status != :draft')
            ->setParameter('draft', 'draft')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
