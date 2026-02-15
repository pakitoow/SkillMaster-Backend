<?php

namespace App\Controller\Api;

use App\Repository\SkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/skills')]
#[IsGranted('ROLE_USER')]
class SkillController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function list(SkillRepository $repo): JsonResponse
    {
        $skills = $repo->findAll();

        $data = array_map(fn($s) => [
            'id' => $s->getId(),
            'name' => $s->getName(),
            'category' => $s->getCategory(),
        ], $skills);

        return $this->json($data);
    }
}
