<?php

namespace App\Controller\Api;

use App\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/roles')]
#[IsGranted('ROLE_USER')]
class RoleController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function list(RoleRepository $repo): JsonResponse
    {
        $roles = $repo->findAll();

        $data = array_map(fn($r) => [
            'id' => $r->getId(),
            'name' => $r->getName(),
        ], $roles);

        return $this->json($data);
    }
}
