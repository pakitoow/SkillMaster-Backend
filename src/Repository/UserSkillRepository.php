<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSkill>
 */
class UserSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSkill::class);
    }

    /** @return UserSkill[] */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('us')
            ->join('us.skill', 's')
            ->addSelect('s')
            ->where('us.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndSkill(User $user, int $skillId): ?UserSkill
    {
        return $this->createQueryBuilder('us')
            ->where('us.owner = :user')
            ->andWhere('us.skill = :skillId')
            ->setParameter('user', $user)
            ->setParameter('skillId', $skillId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
