<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251205132322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE professor_level (professor_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_129D61E87D2D84D5 (professor_id), INDEX IDX_129D61E85FB14BA7 (level_id), PRIMARY KEY(professor_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE professor_level ADD CONSTRAINT FK_129D61E87D2D84D5 FOREIGN KEY (professor_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professor_level ADD CONSTRAINT FK_129D61E85FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6495FB14BA7 ON user (level_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE professor_level DROP FOREIGN KEY FK_129D61E87D2D84D5');
        $this->addSql('ALTER TABLE professor_level DROP FOREIGN KEY FK_129D61E85FB14BA7');
        $this->addSql('DROP TABLE professor_level');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6495FB14BA7');
        $this->addSql('DROP INDEX IDX_8D93D6495FB14BA7 ON `user`');
        $this->addSql('ALTER TABLE `user` DROP level_id');
    }
}
