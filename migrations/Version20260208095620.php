<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208095620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table with Single Table Inheritance for all roles';
    }

    public function up(Schema $schema): void
    {
        // Create users table with all columns for Single Table Inheritance
        $this->addSql('CREATE TABLE users (
            uuid BINARY(16) NOT NULL,
            email VARCHAR(180) NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            birthdate DATE DEFAULT NULL,
            phone INT DEFAULT NULL,
            avatar_url VARCHAR(500) DEFAULT NULL,
            address VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            reset_token VARCHAR(255) DEFAULT NULL,
            reset_token_expires_at DATETIME DEFAULT NULL,
            last_login_at DATETIME DEFAULT NULL,
            login_attempts INT NOT NULL DEFAULT 0,
            locked_until DATETIME DEFAULT NULL,
            role VARCHAR(255) NOT NULL,
            -- Patient specific
            lot VARCHAR(50) DEFAULT NULL,
            token VARCHAR(100) DEFAULT NULL,
            -- Medecin specific
            specialite VARCHAR(100) DEFAULT NULL,
            license_number VARCHAR(100) DEFAULT NULL,
            experience_years INT DEFAULT NULL,
            diploma_url VARCHAR(500) DEFAULT NULL,
            is_verified_by_admin TINYINT(1) NOT NULL DEFAULT 0,
            verification_date DATETIME DEFAULT NULL,
            -- Coach specific
            nom VARCHAR(100) DEFAULT NULL,
            years_of_experience INT DEFAULT NULL,
            -- Nutritionist specific (uses years_of_experience, diploma_url, is_verified_by_admin, verification_date)
            -- Administrator specific (no additional columns)
            PRIMARY KEY (uuid)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9EC7E7152 ON users (license_number)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
