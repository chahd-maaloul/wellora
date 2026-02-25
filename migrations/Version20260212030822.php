<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212030822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, consultation_type VARCHAR(255) NOT NULL, reason_for_visit VARCHAR(500) NOT NULL, symptoms_description LONGTEXT NOT NULL, date_consultation DATE NOT NULL, time_consultation TIME NOT NULL, duration INT NOT NULL, location VARCHAR(255) NOT NULL, fee INT NOT NULL, status VARCHAR(255) NOT NULL, notes VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, appointment_mode VARCHAR(50) NOT NULL, subjective LONGTEXT DEFAULT NULL, objective LONGTEXT DEFAULT NULL, assessment LONGTEXT DEFAULT NULL, plan LONGTEXT DEFAULT NULL, diagnoses JSON DEFAULT NULL, vitals JSON DEFAULT NULL, follow_up JSON DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE examens (id INT AUTO_INCREMENT NOT NULL, type_examen VARCHAR(255) NOT NULL, date_examen DATE NOT NULL, resultat LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, nom_examen VARCHAR(255) NOT NULL, date_realisation DATE DEFAULT NULL, result_file VARCHAR(255) DEFAULT NULL, id_consultation_id INT NOT NULL, INDEX IDX_B2E32DD78BA1AF57 (id_consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ordonnance (id INT AUTO_INCREMENT NOT NULL, date_ordonnance DATE NOT NULL, medicament VARCHAR(255) NOT NULL, dosage VARCHAR(255) NOT NULL, forme VARCHAR(255) NOT NULL, duree_traitement VARCHAR(255) NOT NULL, instructions VARCHAR(500) DEFAULT NULL, frequency VARCHAR(50) DEFAULT NULL, diagnosis_code VARCHAR(20) DEFAULT NULL, id_consultation_id INT NOT NULL, INDEX IDX_924B326C8BA1AF57 (id_consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (uuid VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, birthdate DATE DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, avatar_url VARCHAR(500) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, license_number VARCHAR(100) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, last_login_at DATETIME DEFAULT NULL, login_attempts INT NOT NULL, locked_until DATETIME DEFAULT NULL, is_email_verified TINYINT NOT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, email_verification_expires_at DATETIME DEFAULT NULL, last_session_id VARCHAR(128) DEFAULT NULL, role VARCHAR(255) NOT NULL, lot VARCHAR(50) DEFAULT NULL, token VARCHAR(100) DEFAULT NULL, specialite VARCHAR(100) DEFAULT NULL, years_of_experience INT DEFAULT NULL, diploma_url VARCHAR(500) DEFAULT NULL, is_verified_by_admin TINYINT DEFAULT NULL, verification_date DATETIME DEFAULT NULL, nom VARCHAR(100) DEFAULT NULL, rating NUMERIC(5, 2) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (uuid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE examens ADD CONSTRAINT FK_B2E32DD78BA1AF57 FOREIGN KEY (id_consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE ordonnance ADD CONSTRAINT FK_924B326C8BA1AF57 FOREIGN KEY (id_consultation_id) REFERENCES consultation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE examens DROP FOREIGN KEY FK_B2E32DD78BA1AF57');
        $this->addSql('ALTER TABLE ordonnance DROP FOREIGN KEY FK_924B326C8BA1AF57');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE examens');
        $this->addSql('DROP TABLE ordonnance');
        $this->addSql('DROP TABLE users');
    }
}
