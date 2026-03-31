<?php

namespace App\Tests\Controller;

use App\Entity\Conversation;
use App\Tests\ApiTestCase;

class ConversationControllerTest extends ApiTestCase
{
    public function testListConversationsEmpty(): void
    {
        $user  = $this->createUser();
        $token = $this->getToken($user);

        $response = $this->jsonRequest('GET', '/api/conversations', [], $token);

        $this->assertResponseStatusCodeSame(200);
        $this->assertIsArray($response);
        $this->assertCount(0, $response);
    }

    public function testListConversationsUnauthorized(): void
    {
        $this->jsonRequest('GET', '/api/conversations');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testShowConversationNotFound(): void
    {
        $user  = $this->createUser();
        $token = $this->getToken($user);

        $response = $this->jsonRequest(
            'GET',
            '/api/conversations/00000000-0000-0000-0000-000000000000',
            [],
            $token
        );

        $this->assertResponseStatusCodeSame(404);
        $this->assertArrayHasKey('error', $response);
    }

    public function testDeleteConversationNotFound(): void
    {
        $user  = $this->createUser();
        $token = $this->getToken($user);

        $response = $this->jsonRequest(
            'DELETE',
            '/api/conversations/00000000-0000-0000-0000-000000000000',
            [],
            $token
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShowConversationBelongsToOtherUser(): void
    {
        $owner       = $this->createUser('owner@test.com');
        $otherUser   = $this->createUser('other@test.com');
        $otherToken  = $this->getToken($otherUser);

        // Create conversation for owner
        $conversation = new Conversation();
        $conversation->setTitle('Owner conversation');
        $conversation->setUser($owner);
        $this->em->persist($conversation);
        $this->em->flush();

        $response = $this->jsonRequest(
            'GET',
            '/api/conversations/' . $conversation->getId(),
            [],
            $otherToken
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
