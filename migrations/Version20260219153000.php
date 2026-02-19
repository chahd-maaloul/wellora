<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add 2FA fields to User entity (TOTP, Backup Codes, Trusted Devices)';
    }

    public function up(Schema $schema): void
    {
        // Add 2FA fields
        $this->addSql('ALTER TABLE users ADD is_two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE users ADD totp_secret VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD backup_codes JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD trusted_devices JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN is_two_factor_enabled');
        $this->addSql('ALTER TABLE users DROP COLUMN totp_secret');
        $this->addSql('ALTER TABLE users DROP COLUMN backup_codes');
        $this->addSql('ALTER TABLE users DROP COLUMN trusted_devices');
    }
}
