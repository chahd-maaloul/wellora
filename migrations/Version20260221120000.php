<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260221120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create meal_plans table for AI-generated meal plans';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE meal_plans (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date DATE NOT NULL, day_of_week VARCHAR(20) NOT NULL, meal_type VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, calories INT NOT NULL, protein NUMERIC(6, 1) DEFAULT NULL, carbs NUMERIC(6, 1) DEFAULT NULL, fats NUMERIC(6, 1) DEFAULT NULL, description LONGTEXT DEFAULT NULL, is_completed TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, generated_at DATETIME DEFAULT NULL, INDEX IDX_7A7DCFF6A76ED395 (user_id), INDEX IDX_7A7DCFF6BAD26411 (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE meal_plans');
    }
}
