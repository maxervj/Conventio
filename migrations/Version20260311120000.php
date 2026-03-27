<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des champs Yousign sur la table convention';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE convention ADD yousign_request_id VARCHAR(255) DEFAULT NULL, ADD yousign_document_id VARCHAR(255) DEFAULT NULL, ADD yousign_status VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE convention DROP yousign_request_id, DROP yousign_document_id, DROP yousign_status');
    }
}
