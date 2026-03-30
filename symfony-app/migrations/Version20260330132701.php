<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260330132701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation (id UUID NOT NULL, title VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_8A8E26E9A76ED395 ON conversation (user_id)');
        $this->addSql('CREATE TABLE message (id UUID NOT NULL, role VARCHAR(255) NOT NULL, content TEXT NOT NULL, model_used VARCHAR(100) DEFAULT NULL, provider VARCHAR(50) DEFAULT NULL, detected_intent VARCHAR(50) DEFAULT NULL, response_time_ms INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, conversation_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B6BD307F9AC0396 ON message (conversation_id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation DROP CONSTRAINT FK_8A8E26E9A76ED395');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F9AC0396');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE message');
    }
}
