<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add license_number column to coach and nutritionist tables';
    }

    public function up(Schema $schema): void
    {
        // Add license_number to coach table
        $this->addSql('ALTER TABLE coach ADD license_number VARCHAR(100) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_COACH_LICENSE_NUMBER ON coach (license_number)');

        // Add license_number to nutritionist table
        $this->addSql('ALTER TABLE nutritionist ADD license_number VARCHAR(100) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_NUTRITIONIST_LICENSE_NUMBER ON nutritionist (license_number)');
    }

    public function down(Schema $schema): void
    {
        // Drop indexes first
        $this->addSql('DROP INDEX UNIQ_COACH_LICENSE_NUMBER ON coach');
        $this->addSql('DROP INDEX UNIQ_NUTRITIONIST_LICENSE_NUMBER ON nutritionist');

        // Remove columns
        $this->addSql('ALTER TABLE coach DROP license_number');
        $this->addSql('ALTER TABLE nutritionist DROP license_number');
    }
}
