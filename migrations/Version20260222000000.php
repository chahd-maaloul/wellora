<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create nutrition_consultations table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE nutrition_consultations (
            id INT AUTO_INCREMENT NOT NULL,
            patient_id INT DEFAULT NULL,
            nutritionist_id INT DEFAULT NULL,
            scheduled_at DATETIME NOT NULL,
            duration INT DEFAULT NULL,
            type VARCHAR(100) DEFAULT NULL,
            notes LONGTEXT DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            patient_name VARCHAR(255) DEFAULT NULL,
            nutritionist_name VARCHAR(255) DEFAULT NULL,
            price NUMERIC(10, 2) DEFAULT NULL,
            INDEX IDX_NUTRITION_CONSULTATIONS_PATIENT (patient_id),
            INDEX IDX_NUTRITION_CONSULTATIONS_NUTRITIONIST (nutritionist_id),
            INDEX IDX_NUTRITION_CONSULTATIONS_SCHEDULED (scheduled_at),
            INDEX IDX_NUTRITION_CONSULTATIONS_STATUS (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE nutrition_consultations');
    }
}
