<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260109131518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE formation_level (formation_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_EC6152665200282E (formation_id), INDEX IDX_EC6152665FB14BA7 (level_id), PRIMARY KEY(formation_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE formation_level ADD CONSTRAINT FK_EC6152665200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation_level ADD CONSTRAINT FK_EC6152665FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation_level DROP FOREIGN KEY FK_EC6152665200282E');
        $this->addSql('ALTER TABLE formation_level DROP FOREIGN KEY FK_EC6152665FB14BA7');
        $this->addSql('DROP TABLE formation_level');
    }
}
