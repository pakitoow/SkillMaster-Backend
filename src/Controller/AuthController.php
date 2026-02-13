<?php

namespace App\Controller;

use App\DTO\LoginRequest;
use App\DTO\RegisterRequest;
use App\Entity\User;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private AuthService $authService,
        private SerializerInterface $serializer,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $registerRequest = $this->serializer->deserialize(
                $request->getContent(),
                RegisterRequest::class,
                'json'
            );

            $user = $this->authService->register($registerRequest);
            $token = $this->jwtManager->create($user);

            return $this->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'token' => $token,
                    'user' => $this->authService->getUserData($user),
                ],
            ], Response::HTTP_CREATED);

        } catch (\InvalidArgumentException $e) {
            $errors = json_decode($e->getMessage(), true);

            return $this->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $loginRequest = $this->serializer->deserialize(
                $request->getContent(),
                LoginRequest::class,
                'json'
            );

            $user = $this->authService->validateCredentials(
                $loginRequest->email,
                $loginRequest->password
            );

            $token = $this->jwtManager->create($user);

            return $this->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $this->authService->getUserData($user),
                ],
            ], Response::HTTP_OK);

        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        // FIX: getUser() returns ?UserInterface — cast to concrete User class safely
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'user' => $this->authService->getUserData($user),
            ],
        ], Response::HTTP_OK);
    }
}
