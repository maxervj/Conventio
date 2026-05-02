<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260425090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les colonnes tel_mobile et tel_other manquantes pour Tutor (Single Table Inheritance)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD tel_mobile VARCHAR(20) DEFAULT NULL, ADD tel_other VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP tel_mobile, DROP tel_other');
    }
}
