<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251121125452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your need
        $this->addSql('CREATE TABLE signature (id INT AUTO_INCREMENT NOT NULL, civilite_proviseur VARCHAR(255) NOT NULL, nom_proviseur VARCHAR(255) NOT NULL, prenom_proviseur VARCHAR(255) NOT NULL, email_proviseur VARCHAR(255) NOT NULL, civilite_ddf VARCHAR(255) NOT NULL, nom_ddf VARCHAR(255) NOT NULL, prenom_ddf VARCHAR(255) NOT NULL, email_ddf VARCHAR(255) NOT NULL, tel_ddf VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE signature');
    }
}
