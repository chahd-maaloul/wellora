<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260221230000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ai_conversations table for AI chat history';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ai_conversations (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            user_message LONGTEXT DEFAULT NULL,
            ai_response LONGTEXT DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            intent VARCHAR(50) DEFAULT NULL,
            calories_context INT DEFAULT NULL,
            protein_context INT DEFAULT NULL,
            carbs_context INT DEFAULT NULL,
            fats_context INT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            is_starred TINYINT(1) DEFAULT 0 NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            PRIMARY KEY (id),
            INDEX IDX_AI_CONVERSATIONS_USER_ID (user_id),
            INDEX IDX_AI_CONVERSATIONS_INTENT (intent),
            INDEX IDX_AI_CONVERSATIONS_CREATED_AT (created_at),
            INDEX IDX_AI_CONVERSATIONS_STARRED (is_starred)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_conversations');
    }
}
