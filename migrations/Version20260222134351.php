<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222134351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire_publication (id INT AUTO_INCREMENT NOT NULL, commentaire LONGTEXT NOT NULL, date_commentaire DATE NOT NULL, publication_parcours_id INT DEFAULT NULL, owner_patient_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_423CD66BB95B6570 (publication_parcours_id), INDEX IDX_423CD66B5C33DFA4 (owner_patient_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE parcours_de_sante (id INT AUTO_INCREMENT NOT NULL, nom_parcours VARCHAR(255) NOT NULL, localisation_parcours VARCHAR(255) NOT NULL, latitude_parcours DOUBLE PRECISION DEFAULT NULL, longitude_parcours DOUBLE PRECISION DEFAULT NULL, distance_parcours DOUBLE PRECISION NOT NULL, date_creation DATE NOT NULL, image_parcours VARCHAR(255) DEFAULT NULL, owner_patient_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_9E78F2C15C33DFA4 (owner_patient_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE publication_parcours (id INT AUTO_INCREMENT NOT NULL, image_publication VARCHAR(255) NOT NULL, ambiance INT NOT NULL, securite INT NOT NULL, date_publication DATE NOT NULL, text_publication LONGTEXT NOT NULL, experience VARCHAR(20) NOT NULL, type_publication VARCHAR(20) NOT NULL, parcours_de_sante_id INT NOT NULL, owner_patient_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_B6D62BB28E7843E8 (parcours_de_sante_id), INDEX IDX_B6D62BB25C33DFA4 (owner_patient_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (uuid VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, birthdate DATE DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, avatar_url VARCHAR(500) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, license_number VARCHAR(100) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, last_login_at DATETIME DEFAULT NULL, login_attempts INT NOT NULL, locked_until DATETIME DEFAULT NULL, is_email_verified TINYINT NOT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, email_verification_expires_at DATETIME DEFAULT NULL, last_session_id VARCHAR(128) DEFAULT NULL, google_id VARCHAR(100) DEFAULT NULL, is_two_factor_enabled TINYINT NOT NULL, totp_secret VARCHAR(255) DEFAULT NULL, backup_codes JSON DEFAULT NULL, plain_backup_codes JSON DEFAULT NULL, trusted_devices JSON DEFAULT NULL, role VARCHAR(255) NOT NULL, lot VARCHAR(50) DEFAULT NULL, token VARCHAR(100) DEFAULT NULL, specialite VARCHAR(100) DEFAULT NULL, years_of_experience INT DEFAULT NULL, diploma_url VARCHAR(500) DEFAULT NULL, is_verified_by_admin TINYINT DEFAULT NULL, verification_date DATETIME DEFAULT NULL, nom VARCHAR(100) DEFAULT NULL, rating NUMERIC(5, 2) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (uuid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commentaire_publication ADD CONSTRAINT FK_423CD66BB95B6570 FOREIGN KEY (publication_parcours_id) REFERENCES publication_parcours (id)');
        $this->addSql('ALTER TABLE commentaire_publication ADD CONSTRAINT FK_423CD66B5C33DFA4 FOREIGN KEY (owner_patient_uuid) REFERENCES users (uuid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE parcours_de_sante ADD CONSTRAINT FK_9E78F2C15C33DFA4 FOREIGN KEY (owner_patient_uuid) REFERENCES users (uuid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE publication_parcours ADD CONSTRAINT FK_B6D62BB28E7843E8 FOREIGN KEY (parcours_de_sante_id) REFERENCES parcours_de_sante (id)');
        $this->addSql('ALTER TABLE publication_parcours ADD CONSTRAINT FK_B6D62BB25C33DFA4 FOREIGN KEY (owner_patient_uuid) REFERENCES users (uuid) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire_publication DROP FOREIGN KEY FK_423CD66BB95B6570');
        $this->addSql('ALTER TABLE commentaire_publication DROP FOREIGN KEY FK_423CD66B5C33DFA4');
        $this->addSql('ALTER TABLE parcours_de_sante DROP FOREIGN KEY FK_9E78F2C15C33DFA4');
        $this->addSql('ALTER TABLE publication_parcours DROP FOREIGN KEY FK_B6D62BB28E7843E8');
        $this->addSql('ALTER TABLE publication_parcours DROP FOREIGN KEY FK_B6D62BB25C33DFA4');
        $this->addSql('DROP TABLE commentaire_publication');
        $this->addSql('DROP TABLE parcours_de_sante');
        $this->addSql('DROP TABLE publication_parcours');
        $this->addSql('DROP TABLE users');
    }
}
