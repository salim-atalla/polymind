<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

enum MessageRole: string
{
    case USER      = 'user';
    case ASSISTANT = 'assistant';
}

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    #[ORM\Column(enumType: MessageRole::class)]
    private ?MessageRole $role = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $modelUsed = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $detectedIntent = null;

    #[ORM\Column(nullable: true)]
    private ?int $responseTimeMs = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }
    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;
        return $this;
    }

    public function getRole(): ?MessageRole
    {
        return $this->role;
    }
    public function setRole(MessageRole $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getModelUsed(): ?string
    {
        return $this->modelUsed;
    }
    public function setModelUsed(?string $modelUsed): static
    {
        $this->modelUsed = $modelUsed;
        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }
    public function setProvider(?string $provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    public function getDetectedIntent(): ?string
    {
        return $this->detectedIntent;
    }
    public function setDetectedIntent(?string $detectedIntent): static
    {
        $this->detectedIntent = $detectedIntent;
        return $this;
    }

    public function getResponseTimeMs(): ?int
    {
        return $this->responseTimeMs;
    }
    public function setResponseTimeMs(?int $responseTimeMs): static
    {
        $this->responseTimeMs = $responseTimeMs;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isFromUser(): bool
    {
        return $this->role === MessageRole::USER;
    }
}
