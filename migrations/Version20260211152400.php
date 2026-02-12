<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211152400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add appointment_mode and SOAP fields to consultation table';
    }

    public function up(Schema $schema): void
    {
        // Add appointment_mode column
        $this->addSql('ALTER TABLE consultation ADD COLUMN appointment_mode VARCHAR(50) NOT NULL DEFAULT \'in-person\'');
        
        // Add SOAP fields
        $this->addSql('ALTER TABLE consultation ADD COLUMN subjective LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE consultation ADD COLUMN objective LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE consultation ADD COLUMN assessment LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE consultation ADD COLUMN plan LONGTEXT DEFAULT NULL');
        
        // Add JSON fields
        $this->addSql('ALTER TABLE consultation ADD COLUMN diagnoses JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE consultation ADD COLUMN vitals JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE consultation ADD COLUMN follow_up JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove the added columns
        $this->addSql('ALTER TABLE consultation DROP COLUMN appointment_mode');
        $this->addSql('ALTER TABLE consultation DROP COLUMN subjective');
        $this->addSql('ALTER TABLE consultation DROP COLUMN objective');
        $this->addSql('ALTER TABLE consultation DROP COLUMN assessment');
        $this->addSql('ALTER TABLE consultation DROP COLUMN plan');
        $this->addSql('ALTER TABLE consultation DROP COLUMN diagnoses');
        $this->addSql('ALTER TABLE consultation DROP COLUMN vitals');
        $this->addSql('ALTER TABLE consultation DROP COLUMN follow_up');
    }
}
