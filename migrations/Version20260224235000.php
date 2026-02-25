<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224235000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add doctor analysis and treatment fields to examens';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE examens ADD doctor_analysis LONGTEXT DEFAULT NULL, ADD doctor_treatment LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE examens DROP doctor_analysis, DROP doctor_treatment');
    }
}
