<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em     = static::getContainer()->get(EntityManagerInterface::class);

        // Clean db before each test
        $this->em->getConnection()->executeStatement('TRUNCATE TABLE "user" CASCADE');
    }

    protected function createUser(
        string $email    = 'test@test.com',
        string $password = 'password123',
        string $fullName = 'Test User'
    ): User {
        $user   = new User();
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user->setEmail($email);
        $user->setFullName($fullName);
        $user->setPassword($hasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    protected function getToken(User $user): string
    {
        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        return $jwtManager->create($user);
    }

    protected function jsonRequest(
        string $method,
        string $url,
        array  $data    = [],
        string $token   = ''
    ): array {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if ($token) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        $this->client->request(
            $method,
            $url,
            [],
            [],
            $headers,
            $data ? json_encode($data) : null
        );

        return json_decode($this->client->getResponse()->getContent(), true) ?? [];
    }
}
