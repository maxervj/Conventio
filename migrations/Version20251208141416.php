<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251208141416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Augmente la taille des colonnes civilite_proviseur, civilite_ddf et tel_ddf pour permettre le chiffrement des données sensibles';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signature CHANGE civilite_proviseur civilite_proviseur VARCHAR(500) NOT NULL, CHANGE civilite_ddf civilite_ddf VARCHAR(500) NOT NULL, CHANGE tel_ddf tel_ddf VARCHAR(500) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signature CHANGE civilite_proviseur civilite_proviseur VARCHAR(10) NOT NULL, CHANGE civilite_ddf civilite_ddf VARCHAR(10) NOT NULL, CHANGE tel_ddf tel_ddf VARCHAR(20) NOT NULL');
    }
}
