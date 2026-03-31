<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/conversations')]
class ConversationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface  $em,
        private ConversationRepository  $conversationRepo,
    ) {}

    #[OA\Get(
        path: '/api/conversations',
        summary: 'List all conversations for current user',
        tags: ['Conversations'],
        responses: [
            new OA\Response(response: 200, description: 'List of conversations'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    #[Route('', name: 'conversations_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var User $user */
        $user          = $this->getUser();
        $conversations = $this->conversationRepo
            ->findByUserOrderedByDate($user->getId());

        return $this->json(array_map(fn(Conversation $c) => [
            'id'           => $c->getId(),
            'title'        => $c->getTitle(),
            'created_at'   => $c->getCreatedAt()?->format('c'),
            'updated_at'   => $c->getUpdatedAt()?->format('c'),
            'last_message' => $c->getLastMessage()?->getContent(),
        ], $conversations));
    }

    #[OA\Get(
        path: '/api/conversations/{id}',
        summary: 'Get a conversation with all messages',
        tags: ['Conversations'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Conversation with messages'),
            new OA\Response(response: 404, description: 'Conversation not found'),
        ]
    )]
    #[Route('/{id}', name: 'conversation_show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        /** @var User $user */
        $user         = $this->getUser();
        $conversation = $this->em->getRepository(Conversation::class)->find($id);

        if (!$conversation || $conversation->getUser() !== $user) {
            return $this->json(['error' => 'Conversation not found'], 404);
        }

        return $this->json([
            'id'         => $conversation->getId(),
            'title'      => $conversation->getTitle(),
            'created_at' => $conversation->getCreatedAt()?->format('c'),
            'messages'   => array_map(fn($m) => [
                'id'               => $m->getId(),
                'role'             => $m->getRole()->value,
                'content'          => $m->getContent(),
                'model_used'       => $m->getModelUsed(),
                'provider'         => $m->getProvider(),
                'detected_intent'  => $m->getDetectedIntent(),
                'response_time_ms' => $m->getResponseTimeMs(),
                'created_at'       => $m->getCreatedAt()?->format('c'),
            ], $conversation->getMessages()->toArray()),
        ]);
    }

    #[OA\Delete(
        path: '/api/conversations/{id}',
        summary: 'Delete a conversation',
        tags: ['Conversations'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Conversation deleted'),
            new OA\Response(response: 404, description: 'Conversation not found'),
        ]
    )]
    #[Route('/{id}', name: 'conversation_delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        /** @var User $user */
        $user         = $this->getUser();
        $conversation = $this->em->getRepository(Conversation::class)->find($id);

        if (!$conversation || $conversation->getUser() !== $user) {
            return $this->json(['error' => 'Conversation not found'], 404);
        }

        $this->em->remove($conversation);
        $this->em->flush();

        return $this->json(['message' => 'Conversation deleted'], 200);
    }
}
