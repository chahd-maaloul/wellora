<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208112000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add performance indexes to users table';
    }

    public function up(Schema $schema): void
    {
        // Add indexes for better query performance
        $this->addSql('CREATE INDEX IDX_users_role ON users (role)');
        $this->addSql('CREATE INDEX IDX_users_is_active ON users (is_active)');
        $this->addSql('CREATE INDEX IDX_users_created_at ON users (created_at)');
    }

    public function down(Schema $schema): void
    {
        // Remove the indexes
        $this->addSql('DROP INDEX IDX_users_role ON users');
        $this->addSql('DROP INDEX IDX_users_is_active ON users');
        $this->addSql('DROP INDEX IDX_users_created_at ON users');
    }
}
