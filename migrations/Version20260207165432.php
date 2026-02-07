<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207165432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire_publication (id INT AUTO_INCREMENT NOT NULL, commentaire LONGTEXT NOT NULL, date_commentaire DATE NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE parcours_de_sante (id INT AUTO_INCREMENT NOT NULL, nom_parcours VARCHAR(255) NOT NULL, localisation_parcours VARCHAR(255) NOT NULL, distance_parcours DOUBLE PRECISION NOT NULL, date_creation DATE NOT NULL, image_parcours VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE publication_parcours (id INT AUTO_INCREMENT NOT NULL, image_publication VARCHAR(255) NOT NULL, ambiance INT NOT NULL, securite INT NOT NULL, date_publication DATE NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE commentaire_publication');
        $this->addSql('DROP TABLE parcours_de_sante');
        $this->addSql('DROP TABLE publication_parcours');
    }
}
