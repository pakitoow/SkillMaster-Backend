<?php

namespace App\Service;

use App\DTO\RegisterRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {
    }

    public function register(RegisterRequest $request): User
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new \InvalidArgumentException(json_encode($errorMessages));
        }

        if ($this->userRepository->emailExists($request->email)) {
            throw new \InvalidArgumentException(json_encode([
                'email' => 'An account with this email already exists'
            ]));
        }

        $user = new User();
        $user->setFullName($request->fullName);
        $user->setEmail($request->email);
        
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $request->password
        );
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user, true);

        return $user;
    }

    public function validateCredentials(string $email, string $password): User
    {
        $user = $this->userRepository->findOneByEmail($email);

        if (!$user) {
            throw new \InvalidArgumentException('Invalid credentials');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new \InvalidArgumentException('Invalid credentials');
        }

        return $user;
    }

    public function getUserData(User $user): array
    {
        return [
            'id' => $user->getId(),
            'fullName' => $user->getFullName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('c'),
        ];
    }
}
