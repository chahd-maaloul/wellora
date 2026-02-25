<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219093552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, consultation_type VARCHAR(255) NOT NULL, reason_for_visit VARCHAR(500) NOT NULL, symptoms_description LONGTEXT NOT NULL, date_consultation DATE NOT NULL, time_consultation TIME NOT NULL, duration INT NOT NULL, location VARCHAR(255) NOT NULL, fee INT NOT NULL, status VARCHAR(255) NOT NULL, notes VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, appointment_mode VARCHAR(50) NOT NULL, subjective LONGTEXT DEFAULT NULL, objective LONGTEXT DEFAULT NULL, assessment LONGTEXT DEFAULT NULL, plan LONGTEXT DEFAULT NULL, diagnoses JSON DEFAULT NULL, vitals JSON DEFAULT NULL, follow_up JSON DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE daily_plan (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, status VARCHAR(255) NOT NULL, notes VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, calories INT NOT NULL, duree_min INT NOT NULL, goal_id INT NOT NULL, coach_id VARCHAR(36) DEFAULT NULL, INDEX IDX_995C44E8667D1AFE (goal_id), INDEX IDX_995C44E83C105691 (coach_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE daily_plan_exercises (daily_plan_id INT NOT NULL, exercises_id INT NOT NULL, INDEX IDX_9DAF22643778D36F (daily_plan_id), INDEX IDX_9DAF22641AFA70CA (exercises_id), PRIMARY KEY (daily_plan_id, exercises_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE examens (id INT AUTO_INCREMENT NOT NULL, type_examen VARCHAR(255) NOT NULL, date_examen DATE NOT NULL, resultat LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, nom_examen VARCHAR(255) NOT NULL, date_realisation DATE DEFAULT NULL, result_file VARCHAR(255) DEFAULT NULL, id_consultation_id INT NOT NULL, INDEX IDX_B2E32DD78BA1AF57 (id_consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE exercises (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, difficulty_level VARCHAR(255) NOT NULL, default_unit VARCHAR(255) NOT NULL, video_url VARCHAR(255) DEFAULT NULL, video_file_name VARCHAR(255) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, duration INT DEFAULT NULL, calories INT DEFAULT NULL, sets INT DEFAULT NULL, reps INT DEFAULT NULL, user_id VARCHAR(36) DEFAULT NULL, INDEX IDX_FA14991A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE goal (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, description VARCHAR(255) DEFAULT NULL, category VARCHAR(50) NOT NULL, status VARCHAR(20) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, date DATE NOT NULL, relevant VARCHAR(255) DEFAULT NULL, difficulty_level VARCHAR(20) DEFAULT NULL, target_audience VARCHAR(20) DEFAULT NULL, target_value DOUBLE PRECISION DEFAULT NULL, current_value DOUBLE PRECISION DEFAULT NULL, unit VARCHAR(20) DEFAULT NULL, progress INT DEFAULT NULL, goal_type VARCHAR(50) DEFAULT NULL, frequency VARCHAR(20) DEFAULT NULL, sessions_per_week INT DEFAULT NULL, session_duration INT DEFAULT NULL, preferred_time TIME DEFAULT NULL, duration_weeks INT DEFAULT NULL, rest_days INT DEFAULT NULL, preferred_days JSON DEFAULT NULL, weight_start DOUBLE PRECISION DEFAULT NULL, weight_target DOUBLE PRECISION DEFAULT NULL, height INT DEFAULT NULL, calories_target INT DEFAULT NULL, coach_id VARCHAR(255) NOT NULL, patient_id VARCHAR(36) NOT NULL, INDEX IDX_FCDCEB2E6B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ordonnance (id INT AUTO_INCREMENT NOT NULL, date_ordonnance DATE NOT NULL, medicament VARCHAR(255) NOT NULL, dosage VARCHAR(255) NOT NULL, forme VARCHAR(255) NOT NULL, duree_traitement VARCHAR(255) NOT NULL, instructions VARCHAR(500) DEFAULT NULL, frequency VARCHAR(50) DEFAULT NULL, diagnosis_code VARCHAR(20) DEFAULT NULL, id_consultation_id INT NOT NULL, INDEX IDX_924B326C8BA1AF57 (id_consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE users (uuid VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, birthdate DATE DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, avatar_url VARCHAR(500) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, license_number VARCHAR(100) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, last_login_at DATETIME DEFAULT NULL, login_attempts INT NOT NULL, locked_until DATETIME DEFAULT NULL, is_email_verified TINYINT NOT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, email_verification_expires_at DATETIME DEFAULT NULL, last_session_id VARCHAR(128) DEFAULT NULL, role VARCHAR(255) NOT NULL, lot VARCHAR(50) DEFAULT NULL, token VARCHAR(100) DEFAULT NULL, specialite VARCHAR(100) DEFAULT NULL, years_of_experience INT DEFAULT NULL, diploma_url VARCHAR(500) DEFAULT NULL, is_verified_by_admin TINYINT DEFAULT NULL, verification_date DATETIME DEFAULT NULL, nom VARCHAR(100) DEFAULT NULL, rating NUMERIC(5, 2) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE daily_plan ADD CONSTRAINT FK_995C44E8667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id)');
        $this->addSql('ALTER TABLE daily_plan ADD CONSTRAINT FK_995C44E83C105691 FOREIGN KEY (coach_id) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE daily_plan_exercises ADD CONSTRAINT FK_9DAF22643778D36F FOREIGN KEY (daily_plan_id) REFERENCES daily_plan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE daily_plan_exercises ADD CONSTRAINT FK_9DAF22641AFA70CA FOREIGN KEY (exercises_id) REFERENCES exercises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE examens ADD CONSTRAINT FK_B2E32DD78BA1AF57 FOREIGN KEY (id_consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE exercises ADD CONSTRAINT FK_FA14991A76ED395 FOREIGN KEY (user_id) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2E6B899279 FOREIGN KEY (patient_id) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE ordonnance ADD CONSTRAINT FK_924B326C8BA1AF57 FOREIGN KEY (id_consultation_id) REFERENCES consultation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_plan DROP FOREIGN KEY FK_995C44E8667D1AFE');
        $this->addSql('ALTER TABLE daily_plan DROP FOREIGN KEY FK_995C44E83C105691');
        $this->addSql('ALTER TABLE daily_plan_exercises DROP FOREIGN KEY FK_9DAF22643778D36F');
        $this->addSql('ALTER TABLE daily_plan_exercises DROP FOREIGN KEY FK_9DAF22641AFA70CA');
        $this->addSql('ALTER TABLE examens DROP FOREIGN KEY FK_B2E32DD78BA1AF57');
        $this->addSql('ALTER TABLE exercises DROP FOREIGN KEY FK_FA14991A76ED395');
        $this->addSql('ALTER TABLE goal DROP FOREIGN KEY FK_FCDCEB2E6B899279');
        $this->addSql('ALTER TABLE ordonnance DROP FOREIGN KEY FK_924B326C8BA1AF57');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE daily_plan');
        $this->addSql('DROP TABLE daily_plan_exercises');
        $this->addSql('DROP TABLE examens');
        $this->addSql('DROP TABLE exercises');
        $this->addSql('DROP TABLE goal');
        $this->addSql('DROP TABLE ordonnance');
        $this->addSql('DROP TABLE users');
    }
}
