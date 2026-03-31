<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface      $em,
        private UserPasswordHasherInterface $hasher,
        private JWTTokenManagerInterface    $jwtManager,
        private ValidatorInterface          $validator,
    ) {}

    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'fullName'],
                properties: [
                    new OA\Property(property: 'email',    type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'fullName', type: 'string', example: 'John Doe'),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string'),
                        new OA\Property(property: 'user',  type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 409, description: 'Email already in use'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    #[Route('/register', name: 'auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setFullName($data['fullName'] ?? '');
        $user->setPassword(
            $this->hasher->hashPassword($user, $data['password'] ?? '')
        );

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        $existing = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $user->getEmail()]);
        if ($existing) {
            return $this->json(['error' => 'Email already in use'], 409);
        }

        $this->em->persist($user);
        $this->em->flush();

        $token = $this->jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user'  => [
                'id'       => $user->getId(),
                'email'    => $user->getEmail(),
                'fullName' => $user->getFullName(),
                'roles'    => $user->getRoles(),
            ],
        ], 201);
    }

    #[OA\Get(
        path: '/api/auth/me',
        summary: 'Get current authenticated user',
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Current user data'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    #[Route('/me', name: 'auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'fullName'  => $user->getFullName(),
            'roles'     => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()?->format('c'),
        ]);
    }
}
