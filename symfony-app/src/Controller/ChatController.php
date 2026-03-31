<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\MessageRole;
use App\Entity\User;
use App\Service\FastApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/chat')]
class ChatController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private FastApiClient          $fastApiClient,
    ) {}

    #[OA\Post(
        path: '/api/chat',
        summary: 'Send a prompt and get AI response',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['prompt'],
                properties: [
                    new OA\Property(
                        property: 'prompt',
                        type: 'string',
                        example: 'Write a Python hello world'
                    ),
                    new OA\Property(
                        property: 'conversation_id',
                        type: 'string',
                        nullable: true,
                        example: 'uuid-here'
                    ),
                ]
            )
        ),
        tags: ['Chat'],
        responses: [
            new OA\Response(response: 201, description: 'AI response returned successfully'),
            new OA\Response(response: 400, description: 'Prompt is required'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 503, description: 'AI service unavailable'),
        ]
    )]
    #[Route('', name: 'chat_send', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (empty($data['prompt'])) {
            return $this->json(['error' => 'Prompt is required'], 400);
        }

        $prompt         = $data['prompt'];
        $conversationId = $data['conversation_id'] ?? null;

        // Get or create conversation
        $conversation = null;
        if ($conversationId) {
            $conversation = $this->em->getRepository(Conversation::class)
                ->find($conversationId);

            // make sure conversation belongs to this user
            if (!$conversation || $conversation->getUser() !== $user) {
                return $this->json(['error' => 'Conversation not found'], 404);
            }
        }

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setUser($user);
            $conversation->setTitle($this->generateTitle($prompt));
            $this->em->persist($conversation);
        }

        // Save user message
        $userMessage = new Message();
        $userMessage->setConversation($conversation);
        $userMessage->setRole(MessageRole::USER);
        $userMessage->setContent($prompt);
        $this->em->persist($userMessage);

        // Call FastAPI router
        try {
            $result = $this->fastApiClient->routePrompt(
                $prompt,
                $user->getId(),
                $conversation->getId(),
            );
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'AI service unavailable: ' . $e->getMessage()
            ], 503);
        }

        // Save assistant message
        $assistantMessage = new Message();
        $assistantMessage->setConversation($conversation);
        $assistantMessage->setRole(MessageRole::ASSISTANT);
        $assistantMessage->setContent($result['response']);
        $assistantMessage->setModelUsed($result['model_used']);
        $assistantMessage->setProvider($result['provider']);
        $assistantMessage->setDetectedIntent($result['detected_intent']);
        $assistantMessage->setResponseTimeMs($result['response_time_ms']);
        $this->em->persist($assistantMessage);

        $this->em->flush();

        return $this->json([
            'conversation_id' => $conversation->getId(),
            'user_message'    => [
                'id'         => $userMessage->getId(),
                'role'       => $userMessage->getRole()->value,
                'content'    => $userMessage->getContent(),
                'created_at' => $userMessage->getCreatedAt()?->format('c'),
            ],
            'assistant_message' => [
                'id'               => $assistantMessage->getId(),
                'role'             => $assistantMessage->getRole()->value,
                'content'          => $assistantMessage->getContent(),
                'model_used'       => $assistantMessage->getModelUsed(),
                'provider'         => $assistantMessage->getProvider(),
                'detected_intent'  => $assistantMessage->getDetectedIntent(),
                'response_time_ms' => $assistantMessage->getResponseTimeMs(),
                'created_at'       => $assistantMessage->getCreatedAt()?->format('c'),
            ],
        ], 201);
    }

    private function generateTitle(string $prompt): string
    {
        return strlen($prompt) > 50
            ? substr($prompt, 0, 50) . '...'
            : $prompt;
    }
}
