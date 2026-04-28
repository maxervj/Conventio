<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260428165423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout table formation, suppression formation_level, nettoyage yousign, ajout id_level sur level';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE formation (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE formation_level');
        $this->addSql('ALTER TABLE convention DROP yousign_student_signer_id, DROP yousign_company_signer_id, DROP yousign_school_signer_id');
        $this->addSql('ALTER TABLE level ADD id_level INT NOT NULL, CHANGE level_code level_code INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE is_verified is_verified TINYINT(1) DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE formation_level (formation_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_EC6152665200282E (formation_id), INDEX IDX_EC6152665FB14BA7 (level_id), PRIMARY KEY(formation_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE formation');
        $this->addSql('ALTER TABLE convention ADD yousign_student_signer_id VARCHAR(255) DEFAULT NULL, ADD yousign_company_signer_id VARCHAR(255) DEFAULT NULL, ADD yousign_school_signer_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE level DROP id_level, CHANGE level_code level_code VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE is_verified is_verified TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
