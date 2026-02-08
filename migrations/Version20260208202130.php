<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208202130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire_publication (id INT AUTO_INCREMENT NOT NULL, commentaire LONGTEXT NOT NULL, date_commentaire DATE NOT NULL, publication_parcours_id INT DEFAULT NULL, INDEX IDX_423CD66BB95B6570 (publication_parcours_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE parcours_de_sante (id INT AUTO_INCREMENT NOT NULL, nom_parcours VARCHAR(255) NOT NULL, localisation_parcours VARCHAR(255) NOT NULL, distance_parcours DOUBLE PRECISION NOT NULL, date_creation DATE NOT NULL, image_parcours VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE publication_parcours (id INT AUTO_INCREMENT NOT NULL, image_publication VARCHAR(255) NOT NULL, ambiance INT NOT NULL, securite INT NOT NULL, date_publication DATE NOT NULL, text_publication LONGTEXT NOT NULL, parcours_de_sante_id INT DEFAULT NULL, INDEX IDX_B6D62BB28E7843E8 (parcours_de_sante_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commentaire_publication ADD CONSTRAINT FK_423CD66BB95B6570 FOREIGN KEY (publication_parcours_id) REFERENCES publication_parcours (id)');
        $this->addSql('ALTER TABLE publication_parcours ADD CONSTRAINT FK_B6D62BB28E7843E8 FOREIGN KEY (parcours_de_sante_id) REFERENCES parcours_de_sante (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire_publication DROP FOREIGN KEY FK_423CD66BB95B6570');
        $this->addSql('ALTER TABLE publication_parcours DROP FOREIGN KEY FK_B6D62BB28E7843E8');
        $this->addSql('DROP TABLE commentaire_publication');
        $this->addSql('DROP TABLE parcours_de_sante');
        $this->addSql('DROP TABLE publication_parcours');
    }
}
