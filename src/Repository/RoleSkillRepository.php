<?php

namespace App\Repository;

use App\Entity\RoleSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoleSkill>
 */
class RoleSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoleSkill::class);
    }

    /** @return RoleSkill[] */
    public function findByRole(int $roleId): array
    {
        return $this->createQueryBuilder('rs')
            ->join('rs.skill', 's')
            ->addSelect('s')
            ->where('rs.role = :roleId')
            ->setParameter('roleId', $roleId)
            ->getQuery()
            ->getResult();
    }
}
