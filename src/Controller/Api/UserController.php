<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\UserSkill;
use App\Repository\RoleRepository;
use App\Repository\SkillRepository;
use App\Repository\UserSkillRepository;
use App\Service\GapAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{

    private function requireUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Not authenticated');
        }
        return $user;
    }


    #[Route('/target-role', methods: ['PATCH'])]
    public function setTargetRole(
        Request $request,
        RoleRepository $roleRepo,
        EntityManagerInterface $em,
    ): JsonResponse {
        $user = $this->requireUser();
        $data = json_decode($request->getContent(), true);

        if (empty($data['roleId'])) {
            return $this->json(['success' => false, 'message' => 'roleId is required'], Response::HTTP_BAD_REQUEST);
        }

        $role = $roleRepo->find((int) $data['roleId']);

        if (!$role) {
            return $this->json(['success' => false, 'message' => 'Role not found'], Response::HTTP_NOT_FOUND);
        }

        $user->setTargetRole($role);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Target role updated',
            'data'    => [
                'targetRole' => [
                    'id'   => $role->getId(),
                    'name' => $role->getName(),
                ],
            ],
        ]);
    }


    #[Route('/skills', methods: ['GET'])]
    public function getSkills(UserSkillRepository $repo): JsonResponse
    {
        $user   = $this->requireUser();
        $skills = $repo->findByUser($user);

        $data = array_map(fn(UserSkill $us) => [
            'id'        => $us->getId(),
            'skillId'   => $us->getSkill()->getId(),
            'skillName' => $us->getSkill()->getName(),
            'category'  => $us->getSkill()->getCategory(),
            'level'     => $us->getLevel(),
            'source'    => $us->getSource(),
            'updatedAt' => $us->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ], $skills);

        return $this->json(['success' => true, 'data' => $data]);
    }


    #[Route('/skills/{skillId}', methods: ['PUT'])]
    public function upsertSkill(
        int $skillId,
        Request $request,
        SkillRepository $skillRepo,
        UserSkillRepository $userSkillRepo,
        EntityManagerInterface $em,
    ): JsonResponse {
        $user = $this->requireUser();
        $data = json_decode($request->getContent(), true);

        $level  = isset($data['level'])  ? (int) $data['level']  : null;
        $source = $data['source'] ?? 'manual';

        if ($level === null || $level < 1 || $level > 5) {
            return $this->json(
                ['success' => false, 'message' => 'level must be an integer between 1 and 5'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $skill = $skillRepo->find($skillId);
        if (!$skill) {
            return $this->json(['success' => false, 'message' => 'Skill not found'], Response::HTTP_NOT_FOUND);
        }

        // Upsert
        $userSkill = $userSkillRepo->findOneByUserAndSkill($user, $skillId);

        if (!$userSkill) {
            $userSkill = new UserSkill();
            $userSkill->setOwner($user);
            $userSkill->setSkill($skill);
            $em->persist($userSkill);
        }

        $userSkill->setLevel($level);
        $userSkill->setSource($source);
        $userSkill->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Skill saved',
            'data'    => [
                'id'        => $userSkill->getId(),
                'skillId'   => $skill->getId(),
                'skillName' => $skill->getName(),
                'category'  => $skill->getCategory(),
                'level'     => $userSkill->getLevel(),
                'source'    => $userSkill->getSource(),
                'updatedAt' => $userSkill->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    #[Route('/skills/{skillId}', methods: ['DELETE'])]
    public function deleteSkill(
        int $skillId,
        UserSkillRepository $repo,
        EntityManagerInterface $em,
    ): JsonResponse {
        $user      = $this->requireUser();
        $userSkill = $repo->findOneByUserAndSkill($user, $skillId);

        if (!$userSkill) {
            return $this->json(['success' => false, 'message' => 'Skill not found for this user'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($userSkill);
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Skill removed']);
    }

    #[Route('/gap-analysis', methods: ['GET'])]
    public function gapAnalysis(GapAnalysisService $service): JsonResponse
    {
        $user   = $this->requireUser();
        $result = $service->analyze($user);

        return $this->json(['success' => true, 'data' => $result]);
    }

    #[Route('/dashboard', methods: ['GET'])]
    public function dashboard(
        UserSkillRepository $repo,
        GapAnalysisService $gapService,
    ): JsonResponse {
        $user   = $this->requireUser();
        $skills = $repo->findByUser($user);

        $skillCount = count($skills);
        $avgLevel   = $skillCount > 0
            ? round(array_sum(array_map(fn($s) => $s->getLevel(), $skills)) / $skillCount, 1)
            : 0;

        // Gap stats
        $gap         = $gapService->analyze($user);
        $progressPct = $gap['progressPct'];
        $topMissing  = array_slice($gap['missing'], 0, 3);

        $targetRole = $user->getTargetRole();

        return $this->json([
            'success' => true,
            'data'    => [
                'skillCount'  => $skillCount,
                'avgLevel'    => $avgLevel,
                'progressPct' => $progressPct,
                'targetRole'  => $targetRole ? ['id' => $targetRole->getId(), 'name' => $targetRole->getName()] : null,
                'topMissing'  => array_map(fn($m) => [
                    'skillName'     => $m['skillName'],
                    'requiredLevel' => $m['requiredLevel'],
                    'userLevel'     => $m['userLevel'],
                    'gap'           => $m['gap'],
                ], $topMissing),
            ],
        ]);
    }
}
