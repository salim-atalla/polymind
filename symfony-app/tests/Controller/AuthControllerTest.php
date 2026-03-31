<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

class AuthControllerTest extends ApiTestCase
{
    public function testRegisterSuccess(): void
    {
        $response = $this->jsonRequest('POST', '/api/auth/register', [
            'email'    => 'newuser@test.com',
            'password' => 'password123',
            'fullName' => 'New User',
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('user', $response);
        $this->assertEquals('newuser@test.com', $response['user']['email']);
    }

    public function testRegisterDuplicateEmail(): void
    {
        $this->createUser('duplicate@test.com');

        $response = $this->jsonRequest('POST', '/api/auth/register', [
            'email'    => 'duplicate@test.com',
            'password' => 'password123',
            'fullName' => 'Test User',
        ]);

        $this->assertResponseStatusCodeSame(409);
        $this->assertArrayHasKey('error', $response);
    }

    public function testRegisterInvalidEmail(): void
    {
        $response = $this->jsonRequest('POST', '/api/auth/register', [
            'email'    => 'not-an-email',
            'password' => 'password123',
            'fullName' => 'Test User',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertArrayHasKey('errors', $response);
    }

    public function testMeSuccess(): void
    {
        $user  = $this->createUser();
        $token = $this->getToken($user);

        $response = $this->jsonRequest('GET', '/api/auth/me', [], $token);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('test@test.com', $response['email']);
        $this->assertEquals('Test User', $response['fullName']);
    }

    public function testMeUnauthorized(): void
    {
        $this->jsonRequest('GET', '/api/auth/me');
        $this->assertResponseStatusCodeSame(401);
    }
}
