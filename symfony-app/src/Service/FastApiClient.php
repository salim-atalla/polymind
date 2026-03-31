<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class FastApiClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(FASTAPI_URL)%')]
        private string $fastApiUrl,
    ) {}

    public function routePrompt(
        string $prompt,
        string $userId,
        ?string $conversationId = null
    ): array {
        $response = $this->httpClient->request('POST', $this->fastApiUrl . '/api/route', [
            'json' => [
                'prompt'          => $prompt,
                'user_id'         => $userId,
                'conversation_id' => $conversationId,
            ],
            'timeout' => 30,
        ]);

        return $response->toArray();
    }
}
