<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

class ChatControllerTest extends ApiTestCase
{
    public function testSendChatRequiresAuth(): void
    {
        $this->jsonRequest('POST', '/api/chat', [
            'prompt' => 'Hello'
        ]);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testSendChatRequiresPrompt(): void
    {
        $user  = $this->createUser();
        $token = $this->getToken($user);

        $response = $this->jsonRequest('POST', '/api/chat', [], $token);

        $this->assertResponseStatusCodeSame(400);
        $this->assertArrayHasKey('error', $response);
    }

    public function testSendChatInvalidConversationId(): void
    {
        $user  = $this->createUser();
        $token = $this->getToken($user);

        $response = $this->jsonRequest('POST', '/api/chat', [
            'prompt'          => 'Hello',
            'conversation_id' => '00000000-0000-0000-0000-000000000000',
        ], $token);

        $this->assertResponseStatusCodeSame(404);
        $this->assertArrayHasKey('error', $response);
    }
}
