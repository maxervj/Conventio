<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217122403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE professor_taught_levels (professor_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_5D980DE57D2D84D5 (professor_id), INDEX IDX_5D980DE55FB14BA7 (level_id), PRIMARY KEY(professor_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE professor_taught_levels ADD CONSTRAINT FK_5D980DE57D2D84D5 FOREIGN KEY (professor_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professor_taught_levels ADD CONSTRAINT FK_5D980DE55FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD referent_level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64952E4DFAF FOREIGN KEY (referent_level_id) REFERENCES level (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64952E4DFAF ON user (referent_level_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE professor_taught_levels DROP FOREIGN KEY FK_5D980DE57D2D84D5');
        $this->addSql('ALTER TABLE professor_taught_levels DROP FOREIGN KEY FK_5D980DE55FB14BA7');
        $this->addSql('DROP TABLE professor_taught_levels');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64952E4DFAF');
        $this->addSql('DROP INDEX IDX_8D93D64952E4DFAF ON user');
        $this->addSql('ALTER TABLE user DROP referent_level_id');
    }
}
