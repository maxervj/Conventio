<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203074142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE internship_company_info (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, token VARCHAR(255) NOT NULL, is_completed TINYINT(1) DEFAULT 0 NOT NULL, completed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, expires_at DATETIME DEFAULT NULL, company_name VARCHAR(255) NOT NULL, address LONGTEXT NOT NULL, address_complement LONGTEXT DEFAULT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, responsible_last_name VARCHAR(255) NOT NULL, responsible_first_name VARCHAR(255) NOT NULL, responsible_function VARCHAR(255) NOT NULL, landline_phone VARCHAR(20) DEFAULT NULL, mobile_phone VARCHAR(20) DEFAULT NULL, email VARCHAR(255) NOT NULL, website VARCHAR(255) DEFAULT NULL, siret VARCHAR(14) NOT NULL, insurer_name VARCHAR(255) NOT NULL, insurer_reference VARCHAR(255) NOT NULL, internship_address LONGTEXT DEFAULT NULL, internship_postal_code VARCHAR(10) DEFAULT NULL, internship_city VARCHAR(255) DEFAULT NULL, internship_country VARCHAR(255) DEFAULT NULL, internship_phone VARCHAR(20) DEFAULT NULL, supervisor_last_name VARCHAR(255) NOT NULL, supervisor_first_name VARCHAR(255) NOT NULL, supervisor_function VARCHAR(255) NOT NULL, supervisor_phone VARCHAR(20) NOT NULL, supervisor_email VARCHAR(255) NOT NULL, has_travel TINYINT(1) DEFAULT 0 NOT NULL, covers_transport_costs TINYINT(1) DEFAULT 0 NOT NULL, transport_costs_details LONGTEXT DEFAULT NULL, covers_meal_costs TINYINT(1) DEFAULT 0 NOT NULL, meal_costs_details LONGTEXT DEFAULT NULL, covers_accommodation_costs TINYINT(1) DEFAULT 0 NOT NULL, accommodation_costs_details LONGTEXT DEFAULT NULL, provides_gratification TINYINT(1) DEFAULT 0 NOT NULL, gratification_details LONGTEXT DEFAULT NULL, work_schedule JSON NOT NULL, planned_activities LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_D5941F9C5F37A13B (token), INDEX IDX_D5941F9CCB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE internship_company_info ADD CONSTRAINT FK_D5941F9CCB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD last_name VARCHAR(255) NOT NULL, ADD user_type VARCHAR(255) NOT NULL, ADD verification_token VARCHAR(255) DEFAULT NULL, ADD tel_mobile VARCHAR(20) DEFAULT NULL, ADD tel_other VARCHAR(20) DEFAULT NULL, CHANGE is_verified is_verified TINYINT(1) DEFAULT 0, CHANGE discr first_name VARCHAR(255) NOT NULL, CHANGE personal_mail personal_email VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE internship_company_info DROP FOREIGN KEY FK_D5941F9CCB944F1A');
        $this->addSql('DROP TABLE internship_company_info');
        $this->addSql('ALTER TABLE user ADD discr VARCHAR(255) NOT NULL, ADD personal_mail VARCHAR(255) DEFAULT NULL, DROP first_name, DROP last_name, DROP user_type, DROP personal_email, DROP verification_token, DROP tel_mobile, DROP tel_other, CHANGE is_verified is_verified TINYINT(1) NOT NULL');
    }
}
