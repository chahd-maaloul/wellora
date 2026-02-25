<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219100303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE food_items (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, quantity NUMERIC(5, 2) DEFAULT NULL, unit VARCHAR(50) DEFAULT NULL, calories INT NOT NULL, protein NUMERIC(6, 1) DEFAULT NULL, carbs NUMERIC(6, 1) DEFAULT NULL, fats NUMERIC(6, 1) DEFAULT NULL, fiber NUMERIC(5, 1) DEFAULT NULL, sugar NUMERIC(5, 1) DEFAULT NULL, sodium INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, logged_at DATETIME DEFAULT NULL, food_log_id INT DEFAULT NULL, INDEX IDX_107F2CA7AA493725 (food_log_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE food_logs (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date DATE NOT NULL, meal_type VARCHAR(50) NOT NULL, total_calories INT DEFAULT NULL, total_protein NUMERIC(6, 1) DEFAULT NULL, total_carbs NUMERIC(6, 1) DEFAULT NULL, total_fats NUMERIC(6, 1) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE nutrition_goals (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, calories_target INT DEFAULT NULL, water_target INT DEFAULT NULL, protein_target INT DEFAULT NULL, carbs_target INT DEFAULT NULL, fats_target INT DEFAULT NULL, fiber_target INT DEFAULT NULL, sugar_target INT DEFAULT NULL, sodium_target INT DEFAULT NULL, weight_target NUMERIC(5, 2) DEFAULT NULL, current_weight NUMERIC(5, 2) DEFAULT NULL, start_weight NUMERIC(5, 2) DEFAULT NULL, activity_level VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE water_intakes (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date DATE NOT NULL, glasses INT NOT NULL, milliliters INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE food_items ADD CONSTRAINT FK_107F2CA7AA493725 FOREIGN KEY (food_log_id) REFERENCES food_logs (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE consultation_request');
        $this->addSql('DROP TABLE food_item');
        $this->addSql('DROP TABLE food_plan');
        $this->addSql('DROP TABLE food_plan_food_item');
        $this->addSql('DROP TABLE grocery_item');
        $this->addSql('DROP TABLE grocery_list');
        $this->addSql('DROP TABLE nutritionist');
        $this->addSql('DROP TABLE nutrition_goal');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE water_log');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultation_request (id INT AUTO_INCREMENT NOT NULL, patient_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, patient_email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, requested_at DATETIME NOT NULL, duration_minutes INT NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, nutritionist_id INT NOT NULL)');
        $this->addSql('CREATE TABLE food_item (id INT AUTO_INCREMENT NOT NULL, nom_item VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, calories INT NOT NULL, protein NUMERIC(5, 2) NOT NULL, fat NUMERIC(5, 2) NOT NULL, carbs NUMERIC(5, 2) NOT NULL, logged_at DATETIME DEFAULT \'NULL\', meal_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, patient_id INT DEFAULT NULL)');
        $this->addSql('CREATE TABLE food_plan (id INT AUTO_INCREMENT NOT NULL, nom_plan VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, calories INT NOT NULL, protein NUMERIC(5, 2) NOT NULL, fat NUMERIC(5, 2) NOT NULL, carbs NUMERIC(5, 2) NOT NULL, plan_date DATE NOT NULL, meal_type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, nutrition_goal_id INT NOT NULL)');
        $this->addSql('CREATE TABLE food_plan_food_item (food_plan_id INT NOT NULL, food_item_id INT NOT NULL)');
        $this->addSql('CREATE TABLE grocery_item (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, category VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, quantity NUMERIC(10, 2) DEFAULT \'1.00\' NOT NULL, unit VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'\'\'pcs\'\'\' NOT NULL COLLATE `utf8mb4_general_ci`, checked TINYINT DEFAULT 0 NOT NULL, estimated_price NUMERIC(10, 2) DEFAULT \'NULL\', position INT DEFAULT 0 NOT NULL, grocery_list_id INT NOT NULL)');
        $this->addSql('CREATE TABLE grocery_list (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, patient_id INT DEFAULT NULL)');
        $this->addSql('CREATE TABLE nutritionist (id INT AUTO_INCREMENT NOT NULL, nom_nutritioniste VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`)');
        $this->addSql('CREATE TABLE nutrition_goal (id INT AUTO_INCREMENT NOT NULL, goal_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, daily_calories INT NOT NULL, protein_percent NUMERIC(5, 2) NOT NULL, fat_percent NUMERIC(5, 2) NOT NULL, carb_percent NUMERIC(5, 2) NOT NULL, start_date DATE NOT NULL, target_date DATE NOT NULL, is_active TINYINT NOT NULL, notes VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, nutritionist_id INT NOT NULL, patient_id INT DEFAULT NULL)');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, birth_date DATE DEFAULT \'NULL\', gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, weight NUMERIC(5, 2) DEFAULT \'NULL\', height NUMERIC(5, 2) DEFAULT \'NULL\')');
        $this->addSql('CREATE TABLE water_log (id INT AUTO_INCREMENT NOT NULL, log_date DATE NOT NULL, glasses INT NOT NULL, patient_id INT DEFAULT NULL)');
        $this->addSql('ALTER TABLE food_items DROP FOREIGN KEY FK_107F2CA7AA493725');
        $this->addSql('DROP TABLE food_items');
        $this->addSql('DROP TABLE food_logs');
        $this->addSql('DROP TABLE nutrition_goals');
        $this->addSql('DROP TABLE water_intakes');
    }
}
