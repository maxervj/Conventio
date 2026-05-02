<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260428092755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE convention_date (id INT AUTO_INCREMENT NOT NULL, convention_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, INDEX IDX_936420F2A2ACEBCC (convention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE convention_date ADD CONSTRAINT FK_936420F2A2ACEBCC FOREIGN KEY (convention_id) REFERENCES convention (id)');
        $this->addSql('DROP TABLE formation_level');
        $this->addSql('ALTER TABLE level ADD internship_date_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC13287D5F1 FOREIGN KEY (internship_date_id) REFERENCES internship_date (id)');
        $this->addSql('CREATE INDEX IDX_9AEACC13287D5F1 ON level (internship_date_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE formation_level (formation_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_EC6152665200282E (formation_id), INDEX IDX_EC6152665FB14BA7 (level_id), PRIMARY KEY(formation_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE convention_date DROP FOREIGN KEY FK_936420F2A2ACEBCC');
        $this->addSql('DROP TABLE convention_date');
        $this->addSql('ALTER TABLE level DROP FOREIGN KEY FK_9AEACC13287D5F1');
        $this->addSql('DROP INDEX IDX_9AEACC13287D5F1 ON level');
        $this->addSql('ALTER TABLE level DROP internship_date_id');
    }
}
