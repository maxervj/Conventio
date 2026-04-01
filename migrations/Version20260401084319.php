<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401084319 extends AbstractMigration
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
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_info_collection CHANGE siret siret VARCHAR(14) DEFAULT NULL');
        $this->addSql('ALTER TABLE convention DROP yousign_request_id, DROP yousign_document_id, DROP yousign_status');
    }
}
