<?php

namespace App\Repository;

use App\Entity\InternshipCompanyInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InternshipCompanyInfo>
 */
class InternshipCompanyInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InternshipCompanyInfo::class);
    }

    public function findByToken(string $token): ?InternshipCompanyInfo
    {
        return $this->findOneBy(['token' => $token]);
    }

    public function findValidToken(string $token): ?InternshipCompanyInfo
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.token = :token')
            ->andWhere('i.isCompleted = false')
            ->andWhere('i.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTime())
            ->getQuery();

        return $qb->getOneOrNullResult();
    }
}
