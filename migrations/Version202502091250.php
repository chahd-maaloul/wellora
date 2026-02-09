<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202502091250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email verification fields to users table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD is_email_verified TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE users ADD email_verification_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD email_verification_expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP is_email_verified');
        $this->addSql('ALTER TABLE users DROP email_verification_token');
        $this->addSql('ALTER TABLE users DROP email_verification_expires_at');
    }
}
