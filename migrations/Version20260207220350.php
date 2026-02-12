<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207220350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, id_consultation INT NOT NULL, consultation_type VARCHAR(255) NOT NULL, reason_for_visit VARCHAR(255) NOT NULL, symptoms_description VARCHAR(255) NOT NULL, date_consultation DATE NOT NULL, time_consultation TIME NOT NULL, duration INT NOT NULL, location VARCHAR(255) NOT NULL, fee INT NOT NULL, status VARCHAR(255) NOT NULL, notes VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE examens (id INT AUTO_INCREMENT NOT NULL, id_examen INT NOT NULL, type_examen VARCHAR(255) NOT NULL, date_examen DATE NOT NULL, resultat VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, notes VARCHAR(255) NOT NULL, id_consultation_id INT NOT NULL, INDEX IDX_B2E32DD78BA1AF57 (id_consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ordonnance (id INT AUTO_INCREMENT NOT NULL, id_ordonnance INT NOT NULL, date_ordonnance DATE NOT NULL, medicamment VARCHAR(255) NOT NULL, dosage VARCHAR(255) NOT NULL, forme VARCHAR(255) NOT NULL, duree_traitement INT NOT NULL, instructions VARCHAR(255) NOT NULL, id_consultation_id INT NOT NULL, INDEX IDX_924B326C8BA1AF57 (id_consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
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
    }
}
