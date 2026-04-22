<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260422091449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495200282E');
        $this->addSql('ALTER TABLE professor_formation DROP FOREIGN KEY FK_439A9A097D2D84D5');
        $this->addSql('ALTER TABLE professor_formation DROP FOREIGN KEY FK_439A9A095200282E');
        $this->addSql('DROP TABLE formation');
        $this->addSql('DROP TABLE professor_formation');
        $this->addSql('ALTER TABLE level CHANGE level_code level_code VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX IDX_8D93D6495200282E ON user');
        $this->addSql('ALTER TABLE user DROP formation_id, CHANGE is_verified is_verified TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE formation (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, libelle VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE professor_formation (professor_id INT NOT NULL, formation_id INT NOT NULL, INDEX IDX_439A9A097D2D84D5 (professor_id), INDEX IDX_439A9A095200282E (formation_id), PRIMARY KEY(professor_id, formation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE professor_formation ADD CONSTRAINT FK_439A9A097D2D84D5 FOREIGN KEY (professor_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professor_formation ADD CONSTRAINT FK_439A9A095200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level CHANGE level_code level_code INT NOT NULL');
        $this->addSql('ALTER TABLE `user` ADD formation_id INT DEFAULT NULL, CHANGE is_verified is_verified TINYINT(1) DEFAULT 0');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D6495200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6495200282E ON `user` (formation_id)');
    }
}
