<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260327134755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_info_collection CHANGE siret siret VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE convention ADD yousign_request_id VARCHAR(255) DEFAULT NULL, ADD yousign_document_id VARCHAR(255) DEFAULT NULL, ADD yousign_status VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE level CHANGE level_code level_code VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE is_verified is_verified TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64952E4DFAF FOREIGN KEY (referent_level_id) REFERENCES level (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6495200282E ON user (formation_id)');
        $this->addSql('CREATE INDEX IDX_8D93D64952E4DFAF ON user (referent_level_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_info_collection CHANGE siret siret VARCHAR(14) DEFAULT NULL');
        $this->addSql('ALTER TABLE convention DROP yousign_request_id, DROP yousign_document_id, DROP yousign_status');
        $this->addSql('ALTER TABLE level CHANGE level_code level_code INT NOT NULL');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6495200282E');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D64952E4DFAF');
        $this->addSql('DROP INDEX IDX_8D93D6495200282E ON `user`');
        $this->addSql('DROP INDEX IDX_8D93D64952E4DFAF ON `user`');
        $this->addSql('ALTER TABLE `user` CHANGE is_verified is_verified TINYINT(1) DEFAULT 0');
    }
}
