<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\RoleSkillRepository;
use App\Repository\UserSkillRepository;

class GapAnalysisService
{
    public function __construct(
        private UserSkillRepository $userSkillRepo,
        private RoleSkillRepository $roleSkillRepo,
    ) {}

    /**
     * Returns full gap analysis for a user vs their target role.
     *
     * Structure:
     * [
     *   'hasTargetRole' => bool,
     *   'targetRole'    => ['id', 'name'] | null,
     *   'matched'       => [...],
     *   'missing'       => [...],
     *   'exceeded'      => [...],
     *   'progressPct'   => int,
     *   'totalRequired' => int,
     *   'totalMatched'  => int,
     * ]
     */
    public function analyze(User $user): array
    {
        $targetRole = $user->getTargetRole();

        if ($targetRole === null) {
            return [
                'hasTargetRole' => false,
                'targetRole'    => null,
                'matched'       => [],
                'missing'       => [],
                'exceeded'      => [],
                'progressPct'   => 0,
                'totalRequired' => 0,
                'totalMatched'  => 0,
            ];
        }

        // Index user skills by skillId → level
        $userSkills = $this->userSkillRepo->findByUser($user);
        $userMap = [];
        foreach ($userSkills as $us) {
            $userMap[$us->getSkill()->getId()] = $us->getLevel();
        }

        // Compare against role requirements
        $roleSkills  = $this->roleSkillRepo->findByRole($targetRole->getId());
        $matched     = [];
        $missing     = [];
        $requiredIds = [];

        foreach ($roleSkills as $rs) {
            $skill         = $rs->getSkill();
            $skillId       = $skill->getId();
            $requiredLevel = $rs->getRequiredLevel();
            $userLevel     = $userMap[$skillId] ?? 0;
            $requiredIds[] = $skillId;

            $entry = [
                'skillId'       => $skillId,
                'skillName'     => $skill->getName(),
                'category'      => $skill->getCategory(),
                'requiredLevel' => $requiredLevel,
                'userLevel'     => $userLevel,
                'gap'           => max(0, $requiredLevel - $userLevel),
            ];

            if ($userLevel >= $requiredLevel) {
                $matched[] = $entry;
            } else {
                $missing[] = $entry;
            }
        }

        // Skills the user has that are NOT required by the role
        $exceeded = [];
        foreach ($userSkills as $us) {
            if (!in_array($us->getSkill()->getId(), $requiredIds, true)) {
                $exceeded[] = [
                    'skillId'   => $us->getSkill()->getId(),
                    'skillName' => $us->getSkill()->getName(),
                    'category'  => $us->getSkill()->getCategory(),
                    'userLevel' => $us->getLevel(),
                ];
            }
        }

        $total       = count($roleSkills);
        $totalMatched = count($matched);
        $progressPct  = $total > 0 ? (int) round(($totalMatched / $total) * 100) : 0;

        // Sort missing by gap descending (biggest gap first)
        usort($missing, fn($a, $b) => $b['gap'] <=> $a['gap']);

        return [
            'hasTargetRole' => true,
            'targetRole'    => [
                'id'   => $targetRole->getId(),
                'name' => $targetRole->getName(),
            ],
            'matched'       => $matched,
            'missing'       => $missing,
            'exceeded'      => $exceeded,
            'progressPct'   => $progressPct,
            'totalRequired' => $total,
            'totalMatched'  => $totalMatched,
        ];
    }
}
