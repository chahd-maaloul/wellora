<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250220CreateChatTables extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Créer la table conversation
        $this->addSql('CREATE TABLE conversation (
            id INT AUTO_INCREMENT NOT NULL,
            patient_uuid VARCHAR(255) NOT NULL,
            coach_uuid VARCHAR(255) NOT NULL,
            goal_id INT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            last_message_at DATETIME DEFAULT NULL,
            UNIQUE INDEX UNIQ_8A8E26E9667D1AFE (goal_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Créer la table message
        $this->addSql('CREATE TABLE message (
            id INT AUTO_INCREMENT NOT NULL,
            conversation_id INT NOT NULL,
            sender_uuid VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            sent_at DATETIME NOT NULL,
            is_read TINYINT(1) DEFAULT 0 NOT NULL,
            read_at DATETIME DEFAULT NULL,
            INDEX IDX_B6BD307F9AC0396 (conversation_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Ajouter les clés étrangères
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9C1B57D2F FOREIGN KEY (patient_uuid) REFERENCES user (uuid)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E99C1B57D3 FOREIGN KEY (coach_uuid) REFERENCES user (uuid)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F1C1B57D4 FOREIGN KEY (sender_uuid) REFERENCES user (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE conversation');
    }
}