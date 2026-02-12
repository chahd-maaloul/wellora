<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208135758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD rating DOUBLE PRECISION DEFAULT NULL, CHANGE birthdate birthdate DATE DEFAULT NULL, CHANGE phone phone VARCHAR(20) DEFAULT NULL, CHANGE avatar_url avatar_url VARCHAR(500) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE reset_token_expires_at reset_token_expires_at DATETIME DEFAULT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT NULL, CHANGE locked_until locked_until DATETIME DEFAULT NULL, CHANGE lot lot VARCHAR(50) DEFAULT NULL, CHANGE token token VARCHAR(100) DEFAULT NULL, CHANGE specialite specialite VARCHAR(100) DEFAULT NULL, CHANGE license_number license_number VARCHAR(100) DEFAULT NULL, CHANGE diploma_url diploma_url VARCHAR(500) DEFAULT NULL, CHANGE verification_date verification_date DATETIME DEFAULT NULL, CHANGE nom nom VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP rating, CHANGE birthdate birthdate DATE DEFAULT \'NULL\', CHANGE phone phone VARCHAR(20) DEFAULT \'NULL\', CHANGE avatar_url avatar_url VARCHAR(500) DEFAULT \'NULL\', CHANGE address address VARCHAR(255) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_token_expires_at reset_token_expires_at DATETIME DEFAULT \'NULL\', CHANGE last_login_at last_login_at DATETIME DEFAULT \'NULL\', CHANGE locked_until locked_until DATETIME DEFAULT \'NULL\', CHANGE lot lot VARCHAR(50) DEFAULT \'NULL\', CHANGE token token VARCHAR(100) DEFAULT \'NULL\', CHANGE specialite specialite VARCHAR(50) DEFAULT \'NULL\', CHANGE license_number license_number VARCHAR(100) DEFAULT \'NULL\', CHANGE diploma_url diploma_url VARCHAR(500) DEFAULT \'NULL\', CHANGE verification_date verification_date DATETIME DEFAULT \'NULL\', CHANGE nom nom VARCHAR(100) DEFAULT \'NULL\'');
    }
}
