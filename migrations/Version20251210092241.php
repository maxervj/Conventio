<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210092241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE internship_company_info CHANGE address address LONGTEXT DEFAULT NULL, CHANGE postal_code postal_code VARCHAR(10) DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT NULL, CHANGE country country VARCHAR(255) DEFAULT NULL, CHANGE responsible_last_name responsible_last_name VARCHAR(255) DEFAULT NULL, CHANGE responsible_first_name responsible_first_name VARCHAR(255) DEFAULT NULL, CHANGE responsible_function responsible_function VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE siret siret VARCHAR(14) DEFAULT NULL, CHANGE insurer_name insurer_name VARCHAR(255) DEFAULT NULL, CHANGE insurer_reference insurer_reference VARCHAR(255) DEFAULT NULL, CHANGE supervisor_last_name supervisor_last_name VARCHAR(255) DEFAULT NULL, CHANGE supervisor_first_name supervisor_first_name VARCHAR(255) DEFAULT NULL, CHANGE supervisor_function supervisor_function VARCHAR(255) DEFAULT NULL, CHANGE supervisor_phone supervisor_phone VARCHAR(20) DEFAULT NULL, CHANGE supervisor_email supervisor_email VARCHAR(255) DEFAULT NULL, CHANGE planned_activities planned_activities LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE internship_company_info CHANGE address address LONGTEXT NOT NULL, CHANGE postal_code postal_code VARCHAR(10) NOT NULL, CHANGE city city VARCHAR(255) NOT NULL, CHANGE country country VARCHAR(255) NOT NULL, CHANGE responsible_last_name responsible_last_name VARCHAR(255) NOT NULL, CHANGE responsible_first_name responsible_first_name VARCHAR(255) NOT NULL, CHANGE responsible_function responsible_function VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE siret siret VARCHAR(14) NOT NULL, CHANGE insurer_name insurer_name VARCHAR(255) NOT NULL, CHANGE insurer_reference insurer_reference VARCHAR(255) NOT NULL, CHANGE supervisor_last_name supervisor_last_name VARCHAR(255) NOT NULL, CHANGE supervisor_first_name supervisor_first_name VARCHAR(255) NOT NULL, CHANGE supervisor_function supervisor_function VARCHAR(255) NOT NULL, CHANGE supervisor_phone supervisor_phone VARCHAR(20) NOT NULL, CHANGE supervisor_email supervisor_email VARCHAR(255) NOT NULL, CHANGE planned_activities planned_activities LONGTEXT NOT NULL');
    }
}
