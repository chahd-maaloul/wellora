<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225215008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ai_conversations (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, user_message LONGTEXT DEFAULT NULL, ai_response LONGTEXT DEFAULT NULL, metadata JSON DEFAULT NULL, intent VARCHAR(50) DEFAULT NULL, calories_context INT DEFAULT NULL, protein_context INT DEFAULT NULL, carbs_context INT DEFAULT NULL, fats_context INT DEFAULT NULL, created_at DATETIME NOT NULL, is_starred TINYINT DEFAULT 0 NOT NULL, notes LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE commentaire_publication (id INT AUTO_INCREMENT NOT NULL, commentaire LONGTEXT NOT NULL, date_commentaire DATE NOT NULL, publication_parcours_id INT DEFAULT NULL, owner_patient_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_423CD66BB95B6570 (publication_parcours_id), INDEX IDX_423CD66B5C33DFA4 (owner_patient_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, consultation_type VARCHAR(255) NOT NULL, reason_for_visit VARCHAR(500) NOT NULL, symptoms_description LONGTEXT NOT NULL, date_consultation DATE NOT NULL, time_consultation TIME NOT NULL, duration INT NOT NULL, location VARCHAR(255) NOT NULL, fee INT NOT NULL, status VARCHAR(255) NOT NULL, notes VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, appointment_mode VARCHAR(50) NOT NULL, subjective LONGTEXT DEFAULT NULL, objective LONGTEXT DEFAULT NULL, assessment LONGTEXT DEFAULT NULL, plan LONGTEXT DEFAULT NULL, diagnoses JSON DEFAULT NULL, vitals JSON DEFAULT NULL, follow_up JSON DEFAULT NULL, medecin_id VARCHAR(36) DEFAULT NULL, patient_id VARCHAR(36) DEFAULT NULL, INDEX IDX_964685A64F31A84 (medecin_id), INDEX IDX_964685A66B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, last_message_at DATETIME DEFAULT NULL, patient_uuid VARCHAR(36) NOT NULL, coach_uuid VARCHAR(36) NOT NULL, goal_id INT DEFAULT NULL, INDEX IDX_8A8E26E958EE1D6E (patient_uuid), INDEX IDX_8A8E26E9524035BA (coach_uuid), UNIQUE INDEX UNIQ_8A8E26E9667D1AFE (goal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE daily_plan (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, status VARCHAR(255) NOT NULL, notes VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, calories INT NOT NULL, duree_min INT NOT NULL, goal_id INT NOT NULL, coach_id VARCHAR(36) DEFAULT NULL, INDEX IDX_995C44E8667D1AFE (goal_id), INDEX IDX_995C44E83C105691 (coach_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE daily_plan_exercises (daily_plan_id INT NOT NULL, exercises_id INT NOT NULL, INDEX IDX_9DAF22643778D36F (daily_plan_id), INDEX IDX_9DAF22641AFA70CA (exercises_id), PRIMARY KEY (daily_plan_id, exercises_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE doctor_availability (id INT AUTO_INCREMENT NOT NULL, day_of_week VARCHAR(20) NOT NULL, is_active TINYINT NOT NULL, start_time VARCHAR(5) NOT NULL, end_time VARCHAR(5) NOT NULL, location VARCHAR(50) DEFAULT NULL, breaks JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, medecin_uuid VARCHAR(36) NOT NULL, INDEX IDX_155FB69F1A9FE6F6 (medecin_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE doctor_leaves (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, title VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, reason LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, medecin_uuid VARCHAR(36) NOT NULL, INDEX IDX_39DA9DD21A9FE6F6 (medecin_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE doctor_locations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, address VARCHAR(500) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, medecin_uuid VARCHAR(36) NOT NULL, INDEX IDX_D8F42B511A9FE6F6 (medecin_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE doctor_substitutions (id INT AUTO_INCREMENT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, medecin_uuid VARCHAR(36) NOT NULL, substitute_uuid VARCHAR(36) NOT NULL, leave_id INT DEFAULT NULL, INDEX IDX_80872CA51A9FE6F6 (medecin_uuid), INDEX IDX_80872CA5B3D5EC9C (substitute_uuid), INDEX IDX_80872CA51B2ADB5C (leave_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE examens (id INT AUTO_INCREMENT NOT NULL, type_examen VARCHAR(255) NOT NULL, date_examen DATE NOT NULL, resultat LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, nom_examen VARCHAR(255) NOT NULL, date_realisation DATE DEFAULT NULL, result_file VARCHAR(255) DEFAULT NULL, doctor_analysis LONGTEXT DEFAULT NULL, doctor_treatment LONGTEXT DEFAULT NULL, consultation_id INT DEFAULT NULL, medecin_id VARCHAR(36) DEFAULT NULL, INDEX IDX_B2E32DD762FF6CDF (consultation_id), INDEX IDX_B2E32DD74F31A84 (medecin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE exercise_plan (id INT AUTO_INCREMENT NOT NULL, week_number INT NOT NULL, exercises JSON NOT NULL, focus VARCHAR(255) DEFAULT NULL, coach_notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, goal_id INT NOT NULL, INDEX IDX_847F39CF667D1AFE (goal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE exercises (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, difficulty_level VARCHAR(255) NOT NULL, default_unit VARCHAR(255) NOT NULL, video_url VARCHAR(255) DEFAULT NULL, video_file_name VARCHAR(255) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, duration INT DEFAULT NULL, calories INT DEFAULT NULL, sets INT DEFAULT NULL, reps INT DEFAULT NULL, user_id VARCHAR(36) DEFAULT NULL, INDEX IDX_FA14991A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE food_items (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, quantity NUMERIC(5, 2) DEFAULT NULL, unit VARCHAR(50) DEFAULT NULL, calories INT NOT NULL, protein NUMERIC(6, 1) DEFAULT NULL, carbs NUMERIC(6, 1) DEFAULT NULL, fats NUMERIC(6, 1) DEFAULT NULL, fiber NUMERIC(5, 1) DEFAULT NULL, sugar NUMERIC(5, 1) DEFAULT NULL, sodium INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, logged_at DATETIME DEFAULT NULL, category VARCHAR(50) DEFAULT NULL, is_recipe TINYINT DEFAULT 0 NOT NULL, food_log_id INT DEFAULT NULL, INDEX IDX_107F2CA7AA493725 (food_log_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE food_logs (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date DATE NOT NULL, meal_type VARCHAR(50) NOT NULL, name VARCHAR(255) DEFAULT NULL, total_calories INT DEFAULT NULL, total_protein NUMERIC(6, 1) DEFAULT NULL, total_carbs NUMERIC(6, 1) DEFAULT NULL, total_fats NUMERIC(6, 1) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, user_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_45A86E8DABFE1C6F (user_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE goal (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, description VARCHAR(255) DEFAULT NULL, category VARCHAR(50) NOT NULL, status VARCHAR(20) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, date DATE NOT NULL, relevant VARCHAR(255) DEFAULT NULL, difficulty_level VARCHAR(20) DEFAULT NULL, target_audience VARCHAR(20) DEFAULT NULL, target_value DOUBLE PRECISION DEFAULT NULL, current_value DOUBLE PRECISION DEFAULT NULL, unit VARCHAR(20) DEFAULT NULL, progress INT DEFAULT NULL, goal_type VARCHAR(50) DEFAULT NULL, frequency VARCHAR(20) DEFAULT NULL, sessions_per_week INT DEFAULT NULL, session_duration INT DEFAULT NULL, preferred_time TIME DEFAULT NULL, duration_weeks INT DEFAULT NULL, rest_days INT DEFAULT NULL, preferred_days JSON DEFAULT NULL, weight_start DOUBLE PRECISION DEFAULT NULL, weight_target DOUBLE PRECISION DEFAULT NULL, height INT DEFAULT NULL, ai_coach_advice LONGTEXT DEFAULT NULL, last_ai_analysis DATETIME DEFAULT NULL, ai_metrics JSON DEFAULT NULL, calories_target INT DEFAULT NULL, coach_notes LONGTEXT DEFAULT NULL, patient_satisfaction INT DEFAULT NULL, coach_id VARCHAR(255) NOT NULL, patient_id VARCHAR(36) NOT NULL, INDEX IDX_FCDCEB2E6B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE healthentry (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, poids DOUBLE PRECISION NOT NULL, glycemie DOUBLE PRECISION NOT NULL, tension VARCHAR(20) NOT NULL, sommeil INT NOT NULL, journal_id INT NOT NULL, INDEX IDX_6B813AD4478E8802 (journal_id), UNIQUE INDEX UNIQ_6B813AD4AA9E377A478E8802 (date, journal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE healthjournal (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, datedebut DATE NOT NULL, datefin DATE NOT NULL, user_id VARCHAR(36) DEFAULT NULL, INDEX IDX_437EE84BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE meal_plans (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date DATE NOT NULL, day_of_week VARCHAR(20) NOT NULL, meal_type VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, calories INT NOT NULL, protein NUMERIC(6, 1) DEFAULT NULL, carbs NUMERIC(6, 1) DEFAULT NULL, fats NUMERIC(6, 1) DEFAULT NULL, description LONGTEXT DEFAULT NULL, is_completed TINYINT NOT NULL, created_at DATETIME NOT NULL, generated_at DATETIME DEFAULT NULL, user_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_8FAD7007ABFE1C6F (user_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, sent_at DATETIME NOT NULL, is_read TINYINT NOT NULL, read_at DATETIME DEFAULT NULL, conversation_id INT NOT NULL, sender_uuid VARCHAR(36) NOT NULL, INDEX IDX_B6BD307F9AC0396 (conversation_id), INDEX IDX_B6BD307F2E95A675 (sender_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notificationrdv (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, sent_at DATE DEFAULT NULL, notifie_uuid VARCHAR(36) DEFAULT NULL, consultation_id INT NOT NULL, INDEX IDX_999847326C13E0D9 (notifie_uuid), INDEX IDX_9998473262FF6CDF (consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE nutrition_consultations (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, nutritionist_id INT NOT NULL, scheduled_at DATETIME NOT NULL, duration INT DEFAULT NULL, type VARCHAR(100) NOT NULL, notes LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, patient_name VARCHAR(255) DEFAULT NULL, nutritionist_name VARCHAR(255) DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, patient_uuid VARCHAR(36) DEFAULT NULL, nutritionist_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_2017C91258EE1D6E (patient_uuid), INDEX IDX_2017C9122021D617 (nutritionist_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE nutrition_goal_achievements (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, icon VARCHAR(50) DEFAULT NULL, tier VARCHAR(50) DEFAULT NULL, points INT DEFAULT NULL, unlocked TINYINT DEFAULT 0 NOT NULL, unlocked_at DATETIME DEFAULT NULL, metadata JSON DEFAULT NULL, created_at DATETIME DEFAULT NULL, goal_id INT DEFAULT NULL, INDEX IDX_81A83ED5667D1AFE (goal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE nutrition_goal_adjustments (id INT AUTO_INCREMENT NOT NULL, adjustment_type VARCHAR(50) DEFAULT NULL, reason LONGTEXT DEFAULT NULL, previous_calories INT DEFAULT NULL, new_calories INT DEFAULT NULL, previous_protein INT DEFAULT NULL, new_protein INT DEFAULT NULL, previous_carbs INT DEFAULT NULL, new_carbs INT DEFAULT NULL, previous_fats INT DEFAULT NULL, new_fats INT DEFAULT NULL, recommendations LONGTEXT DEFAULT NULL, days_until_next_review INT DEFAULT NULL, effective_from DATE DEFAULT NULL, is_active TINYINT DEFAULT 0 NOT NULL, created_at DATETIME DEFAULT NULL, goal_id INT DEFAULT NULL, INDEX IDX_60629452667D1AFE (goal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE nutrition_goal_milestones (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, milestone_type VARCHAR(50) DEFAULT NULL, target_weight NUMERIC(5, 2) DEFAULT NULL, target_value NUMERIC(5, 2) DEFAULT NULL, unit VARCHAR(20) DEFAULT NULL, target_days INT DEFAULT NULL, target_calories INT DEFAULT NULL, target_date DATE DEFAULT NULL, completed TINYINT DEFAULT 0 NOT NULL, completed_at DATETIME DEFAULT NULL, `order` INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, goal_id INT DEFAULT NULL, INDEX IDX_4C7E0E58667D1AFE (goal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE nutrition_goal_progress (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, weight NUMERIC(5, 2) DEFAULT NULL, calories_consumed INT DEFAULT NULL, calories_burned INT DEFAULT NULL, protein_consumed INT DEFAULT NULL, carbs_consumed INT DEFAULT NULL, fats_consumed INT DEFAULT NULL, water_intake INT DEFAULT NULL, steps INT DEFAULT NULL, adherence INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, goal_id INT DEFAULT NULL, INDEX IDX_92412132667D1AFE (goal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE nutrition_goals (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, goal_type VARCHAR(50) DEFAULT NULL, calories_target INT DEFAULT NULL, current_calories INT DEFAULT NULL, water_target INT DEFAULT NULL, protein_target INT DEFAULT NULL, carbs_target INT DEFAULT NULL, fats_target INT DEFAULT NULL, fiber_target INT DEFAULT NULL, sugar_target INT DEFAULT NULL, sodium_target INT DEFAULT NULL, weight_target NUMERIC(5, 2) DEFAULT NULL, current_weight NUMERIC(5, 2) DEFAULT NULL, start_weight NUMERIC(5, 2) DEFAULT NULL, bmr INT DEFAULT NULL, tdee INT DEFAULT NULL, target_protein_grams INT DEFAULT NULL, target_carb_grams INT DEFAULT NULL, target_fat_grams INT DEFAULT NULL, target_protein_percent INT DEFAULT NULL, target_carb_percent INT DEFAULT NULL, target_fat_percent INT DEFAULT NULL, target_meal_frequency INT DEFAULT NULL, weekly_weight_change_target NUMERIC(5, 2) DEFAULT NULL, expected_weight_change_per_week NUMERIC(5, 2) DEFAULT NULL, activity_level VARCHAR(50) DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, priority VARCHAR(50) DEFAULT NULL, start_date DATETIME DEFAULT NULL, target_date DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, user_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_AE09E63FABFE1C6F (user_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ordonnance (id INT AUTO_INCREMENT NOT NULL, date_ordonnance DATE NOT NULL, medicament VARCHAR(255) NOT NULL, dosage VARCHAR(255) NOT NULL, forme VARCHAR(255) NOT NULL, duree_traitement VARCHAR(255) NOT NULL, instructions VARCHAR(500) DEFAULT NULL, frequency VARCHAR(50) DEFAULT NULL, diagnosis_code VARCHAR(20) DEFAULT NULL, consultation_id INT DEFAULT NULL, medecin_id VARCHAR(36) DEFAULT NULL, INDEX IDX_924B326C62FF6CDF (consultation_id), INDEX IDX_924B326C4F31A84 (medecin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE parcours_de_sante (id INT AUTO_INCREMENT NOT NULL, nom_parcours VARCHAR(255) NOT NULL, localisation_parcours VARCHAR(255) NOT NULL, latitude_parcours DOUBLE PRECISION DEFAULT NULL, longitude_parcours DOUBLE PRECISION DEFAULT NULL, distance_parcours DOUBLE PRECISION NOT NULL, date_creation DATE NOT NULL, image_parcours VARCHAR(255) DEFAULT NULL, owner_patient_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_9E78F2C15C33DFA4 (owner_patient_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE publication_parcours (id INT AUTO_INCREMENT NOT NULL, image_publication VARCHAR(255) NOT NULL, ambiance INT NOT NULL, securite INT NOT NULL, date_publication DATE NOT NULL, text_publication LONGTEXT NOT NULL, experience VARCHAR(20) NOT NULL, type_publication VARCHAR(20) NOT NULL, parcours_de_sante_id INT NOT NULL, owner_patient_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_B6D62BB28E7843E8 (parcours_de_sante_id), INDEX IDX_B6D62BB25C33DFA4 (owner_patient_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE symptom (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(100) NOT NULL, intensite INT NOT NULL, zone VARCHAR(100) DEFAULT NULL, entry_id INT NOT NULL, INDEX IDX_E4C2F0A0BA364942 (entry_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE users (uuid VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, birthdate DATE DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, avatar_url VARCHAR(500) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, license_number VARCHAR(100) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, last_login_at DATETIME DEFAULT NULL, login_attempts INT NOT NULL, locked_until DATETIME DEFAULT NULL, is_email_verified TINYINT NOT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, email_verification_expires_at DATETIME DEFAULT NULL, last_session_id VARCHAR(128) DEFAULT NULL, google_id VARCHAR(100) DEFAULT NULL, is_two_factor_enabled TINYINT NOT NULL, totp_secret VARCHAR(255) DEFAULT NULL, backup_codes JSON DEFAULT NULL, plain_backup_codes JSON DEFAULT NULL, trusted_devices JSON DEFAULT NULL, years_of_experience INT NOT NULL, diploma_url VARCHAR(500) DEFAULT NULL, specialite VARCHAR(100) DEFAULT NULL, is_verified_by_admin TINYINT NOT NULL, verification_date DATETIME DEFAULT NULL, about LONGTEXT DEFAULT NULL, education VARCHAR(255) DEFAULT NULL, certifications VARCHAR(255) DEFAULT NULL, hospital_affiliations VARCHAR(255) DEFAULT NULL, awards VARCHAR(255) DEFAULT NULL, specializations JSON DEFAULT NULL, consultation_price INT DEFAULT NULL, role VARCHAR(255) NOT NULL, lot VARCHAR(50) DEFAULT NULL, token VARCHAR(100) DEFAULT NULL, rating NUMERIC(5, 2) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE water_intakes (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date DATE NOT NULL, glasses INT NOT NULL, milliliters INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, user_uuid VARCHAR(36) DEFAULT NULL, INDEX IDX_667ED295ABFE1C6F (user_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE commentaire_publication ADD CONSTRAINT FK_423CD66BB95B6570 FOREIGN KEY (publication_parcours_id) REFERENCES publication_parcours (id)');
        $this->addSql('ALTER TABLE commentaire_publication ADD CONSTRAINT FK_423CD66B5C33DFA4 FOREIGN KEY (owner_patient_uuid) REFERENCES users (uuid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A64F31A84 FOREIGN KEY (medecin_id) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A66B899279 FOREIGN KEY (patient_id) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E958EE1D6E FOREIGN KEY (patient_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9524035BA FOREIGN KEY (coach_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id)');
        $this->addSql('ALTER TABLE daily_plan ADD CONSTRAINT FK_995C44E8667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE daily_plan ADD CONSTRAINT FK_995C44E83C105691 FOREIGN KEY (coach_id) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE daily_plan_exercises ADD CONSTRAINT FK_9DAF22643778D36F FOREIGN KEY (daily_plan_id) REFERENCES daily_plan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE daily_plan_exercises ADD CONSTRAINT FK_9DAF22641AFA70CA FOREIGN KEY (exercises_id) REFERENCES exercises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doctor_availability ADD CONSTRAINT FK_155FB69F1A9FE6F6 FOREIGN KEY (medecin_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE doctor_leaves ADD CONSTRAINT FK_39DA9DD21A9FE6F6 FOREIGN KEY (medecin_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE doctor_locations ADD CONSTRAINT FK_D8F42B511A9FE6F6 FOREIGN KEY (medecin_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE doctor_substitutions ADD CONSTRAINT FK_80872CA51A9FE6F6 FOREIGN KEY (medecin_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE doctor_substitutions ADD CONSTRAINT FK_80872CA5B3D5EC9C FOREIGN KEY (substitute_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE doctor_substitutions ADD CONSTRAINT FK_80872CA51B2ADB5C FOREIGN KEY (leave_id) REFERENCES doctor_leaves (id)');
        $this->addSql('ALTER TABLE examens ADD CONSTRAINT FK_B2E32DD762FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE examens ADD CONSTRAINT FK_B2E32DD74F31A84 FOREIGN KEY (medecin_id) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE exercise_plan ADD CONSTRAINT FK_847F39CF667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id)');
        $this->addSql('ALTER TABLE exercises ADD CONSTRAINT FK_FA14991A76ED395 FOREIGN KEY (user_id) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE food_items ADD CONSTRAINT FK_107F2CA7AA493725 FOREIGN KEY (food_log_id) REFERENCES food_logs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE food_logs ADD CONSTRAINT FK_45A86E8DABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2E6B899279 FOREIGN KEY (patient_id) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE healthentry ADD CONSTRAINT FK_6B813AD4478E8802 FOREIGN KEY (journal_id) REFERENCES healthjournal (id)');
        $this->addSql('ALTER TABLE healthjournal ADD CONSTRAINT FK_437EE84BA76ED395 FOREIGN KEY (user_id) REFERENCES users (uuid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meal_plans ADD CONSTRAINT FK_8FAD7007ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F2E95A675 FOREIGN KEY (sender_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE notificationrdv ADD CONSTRAINT FK_999847326C13E0D9 FOREIGN KEY (notifie_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE notificationrdv ADD CONSTRAINT FK_9998473262FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE nutrition_consultations ADD CONSTRAINT FK_2017C91258EE1D6E FOREIGN KEY (patient_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE nutrition_consultations ADD CONSTRAINT FK_2017C9122021D617 FOREIGN KEY (nutritionist_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE nutrition_goal_achievements ADD CONSTRAINT FK_81A83ED5667D1AFE FOREIGN KEY (goal_id) REFERENCES nutrition_goals (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nutrition_goal_adjustments ADD CONSTRAINT FK_60629452667D1AFE FOREIGN KEY (goal_id) REFERENCES nutrition_goals (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nutrition_goal_milestones ADD CONSTRAINT FK_4C7E0E58667D1AFE FOREIGN KEY (goal_id) REFERENCES nutrition_goals (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nutrition_goal_progress ADD CONSTRAINT FK_92412132667D1AFE FOREIGN KEY (goal_id) REFERENCES nutrition_goals (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nutrition_goals ADD CONSTRAINT FK_AE09E63FABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE ordonnance ADD CONSTRAINT FK_924B326C62FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE ordonnance ADD CONSTRAINT FK_924B326C4F31A84 FOREIGN KEY (medecin_id) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE parcours_de_sante ADD CONSTRAINT FK_9E78F2C15C33DFA4 FOREIGN KEY (owner_patient_uuid) REFERENCES users (uuid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE publication_parcours ADD CONSTRAINT FK_B6D62BB28E7843E8 FOREIGN KEY (parcours_de_sante_id) REFERENCES parcours_de_sante (id)');
        $this->addSql('ALTER TABLE publication_parcours ADD CONSTRAINT FK_B6D62BB25C33DFA4 FOREIGN KEY (owner_patient_uuid) REFERENCES users (uuid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE symptom ADD CONSTRAINT FK_E4C2F0A0BA364942 FOREIGN KEY (entry_id) REFERENCES healthentry (id)');
        $this->addSql('ALTER TABLE water_intakes ADD CONSTRAINT FK_667ED295ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire_publication DROP FOREIGN KEY FK_423CD66BB95B6570');
        $this->addSql('ALTER TABLE commentaire_publication DROP FOREIGN KEY FK_423CD66B5C33DFA4');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A64F31A84');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A66B899279');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E958EE1D6E');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9524035BA');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9667D1AFE');
        $this->addSql('ALTER TABLE daily_plan DROP FOREIGN KEY FK_995C44E8667D1AFE');
        $this->addSql('ALTER TABLE daily_plan DROP FOREIGN KEY FK_995C44E83C105691');
        $this->addSql('ALTER TABLE daily_plan_exercises DROP FOREIGN KEY FK_9DAF22643778D36F');
        $this->addSql('ALTER TABLE daily_plan_exercises DROP FOREIGN KEY FK_9DAF22641AFA70CA');
        $this->addSql('ALTER TABLE doctor_availability DROP FOREIGN KEY FK_155FB69F1A9FE6F6');
        $this->addSql('ALTER TABLE doctor_leaves DROP FOREIGN KEY FK_39DA9DD21A9FE6F6');
        $this->addSql('ALTER TABLE doctor_locations DROP FOREIGN KEY FK_D8F42B511A9FE6F6');
        $this->addSql('ALTER TABLE doctor_substitutions DROP FOREIGN KEY FK_80872CA51A9FE6F6');
        $this->addSql('ALTER TABLE doctor_substitutions DROP FOREIGN KEY FK_80872CA5B3D5EC9C');
        $this->addSql('ALTER TABLE doctor_substitutions DROP FOREIGN KEY FK_80872CA51B2ADB5C');
        $this->addSql('ALTER TABLE examens DROP FOREIGN KEY FK_B2E32DD762FF6CDF');
        $this->addSql('ALTER TABLE examens DROP FOREIGN KEY FK_B2E32DD74F31A84');
        $this->addSql('ALTER TABLE exercise_plan DROP FOREIGN KEY FK_847F39CF667D1AFE');
        $this->addSql('ALTER TABLE exercises DROP FOREIGN KEY FK_FA14991A76ED395');
        $this->addSql('ALTER TABLE food_items DROP FOREIGN KEY FK_107F2CA7AA493725');
        $this->addSql('ALTER TABLE food_logs DROP FOREIGN KEY FK_45A86E8DABFE1C6F');
        $this->addSql('ALTER TABLE goal DROP FOREIGN KEY FK_FCDCEB2E6B899279');
        $this->addSql('ALTER TABLE healthentry DROP FOREIGN KEY FK_6B813AD4478E8802');
        $this->addSql('ALTER TABLE healthjournal DROP FOREIGN KEY FK_437EE84BA76ED395');
        $this->addSql('ALTER TABLE meal_plans DROP FOREIGN KEY FK_8FAD7007ABFE1C6F');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F2E95A675');
        $this->addSql('ALTER TABLE notificationrdv DROP FOREIGN KEY FK_999847326C13E0D9');
        $this->addSql('ALTER TABLE notificationrdv DROP FOREIGN KEY FK_9998473262FF6CDF');
        $this->addSql('ALTER TABLE nutrition_consultations DROP FOREIGN KEY FK_2017C91258EE1D6E');
        $this->addSql('ALTER TABLE nutrition_consultations DROP FOREIGN KEY FK_2017C9122021D617');
        $this->addSql('ALTER TABLE nutrition_goal_achievements DROP FOREIGN KEY FK_81A83ED5667D1AFE');
        $this->addSql('ALTER TABLE nutrition_goal_adjustments DROP FOREIGN KEY FK_60629452667D1AFE');
        $this->addSql('ALTER TABLE nutrition_goal_milestones DROP FOREIGN KEY FK_4C7E0E58667D1AFE');
        $this->addSql('ALTER TABLE nutrition_goal_progress DROP FOREIGN KEY FK_92412132667D1AFE');
        $this->addSql('ALTER TABLE nutrition_goals DROP FOREIGN KEY FK_AE09E63FABFE1C6F');
        $this->addSql('ALTER TABLE ordonnance DROP FOREIGN KEY FK_924B326C62FF6CDF');
        $this->addSql('ALTER TABLE ordonnance DROP FOREIGN KEY FK_924B326C4F31A84');
        $this->addSql('ALTER TABLE parcours_de_sante DROP FOREIGN KEY FK_9E78F2C15C33DFA4');
        $this->addSql('ALTER TABLE publication_parcours DROP FOREIGN KEY FK_B6D62BB28E7843E8');
        $this->addSql('ALTER TABLE publication_parcours DROP FOREIGN KEY FK_B6D62BB25C33DFA4');
        $this->addSql('ALTER TABLE symptom DROP FOREIGN KEY FK_E4C2F0A0BA364942');
        $this->addSql('ALTER TABLE water_intakes DROP FOREIGN KEY FK_667ED295ABFE1C6F');
        $this->addSql('DROP TABLE ai_conversations');
        $this->addSql('DROP TABLE commentaire_publication');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE daily_plan');
        $this->addSql('DROP TABLE daily_plan_exercises');
        $this->addSql('DROP TABLE doctor_availability');
        $this->addSql('DROP TABLE doctor_leaves');
        $this->addSql('DROP TABLE doctor_locations');
        $this->addSql('DROP TABLE doctor_substitutions');
        $this->addSql('DROP TABLE examens');
        $this->addSql('DROP TABLE exercise_plan');
        $this->addSql('DROP TABLE exercises');
        $this->addSql('DROP TABLE food_items');
        $this->addSql('DROP TABLE food_logs');
        $this->addSql('DROP TABLE goal');
        $this->addSql('DROP TABLE healthentry');
        $this->addSql('DROP TABLE healthjournal');
        $this->addSql('DROP TABLE meal_plans');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notificationrdv');
        $this->addSql('DROP TABLE nutrition_consultations');
        $this->addSql('DROP TABLE nutrition_goal_achievements');
        $this->addSql('DROP TABLE nutrition_goal_adjustments');
        $this->addSql('DROP TABLE nutrition_goal_milestones');
        $this->addSql('DROP TABLE nutrition_goal_progress');
        $this->addSql('DROP TABLE nutrition_goals');
        $this->addSql('DROP TABLE ordonnance');
        $this->addSql('DROP TABLE parcours_de_sante');
        $this->addSql('DROP TABLE publication_parcours');
        $this->addSql('DROP TABLE symptom');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE water_intakes');
    }
}
